<?php
namespace UpStream\Migrations;

// Prevent direct access.
if (!defined('ABSPATH')) exit;

final class ClientUsers
{
    private static $db;
    private static $usersCache = array(
        'data'     => array(),
        'username' => array(),
        'email'    => array(),
        'projects' => array()
    );
    private static $clientUsersCache = array(
        'clients' => array(),
        'users'   => array()
    );
    private static $projectsCache = array();

    private static $projectsWithNewMembers = array();
    private static $newUsersIdsMap = array();

    private static function prepareMigration()
    {
        global $wpdb;
        self::$db = &$wpdb;

        self::cacheUsers();
        self::cacheClientUsers();
        self::cacheProjectsMembers();
    }

    public static function run()
    {
        // @todo : check if we really need to prepare and run the migration.
        self::prepareMigration();

        $db = &self::$db;

        echo '<pre>';
        print_r(self::$projectsCache);
        echo '</pre>';

        if (count(self::$clientUsersCache['clients']) === 0) {
            return;
        }

        foreach (self::$clientUsersCache['clients'] as $client_id => $clientUsers) {
            $client_id = (int)$client_id;
            $rawClientUsers = (array)$clientUsers;

            foreach ($rawClientUsers as $rawClientUser) {
                $rawClientUser = (array)$rawClientUser;
                if (empty($rawClientUser) || !isset($rawClientUser['id']) || empty($rawClientUser['id'])) {
                    // @todo : There's no need to worry about this user id, since he has no valid id.
                    continue;
                }

                //var_dump($rawClientUser);

                $clientUserEmail = isset($rawClientUser['email']) ? trim($rawClientUser['email']) : null;
                // Check if user's email address is potentially acceptable.
                if (empty($clientUserEmail)) {
                    // @todo : user should be flagged so Admin might create the User manually via Users Page or Client form.
                    // err: ERR_EMAIL_EMPTY
                    continue;
                } else if (!is_email($clientUserEmail) || !filter_var($clientUserEmail, FILTER_VALIDATE_EMAIL)) {
                    // @todo : user should be flagged so Admin might create the User manually via Users Page or Client form.
                    // err: ERR_EMAIL_INVALID
                    continue;
                }

                // Check if user's email address is unique.
                if (isset(self::$usersCache['email'][$clientUserEmail])) {
                    // @todo : user should be flagged so Admin might create the User manually via Users Page or Client form.
                    // err: ERR_EMAIL_NOT_AVAILABLE
                    continue;
                }

                $clientUserFirstName = isset($rawClientUser['fname']) ? trim($rawClientUser['fname']) : '';
                $clientUserLastName = isset($rawClientUser['lname']) ? trim($rawClientUser['lname']) : '';

                $clientUserName = $clientUserFirstName;

                if (!empty($clientUserLastName)) {
                    $clientUserName .= ' ' . $clientUserLastName;
                } else {
                    $clientUserName = $clientUserEmail;
                }

                $userData = array(
                    'user_login'    => $clientUserEmail,
                    'user_pass'     => $clientUserEmail,
                    'user_nicename' => $clientUserEmail,
                    'user_email'    => $clientUserEmail,
                    'display_name'  => $clientUserName,
                    'nickname'      => $clientUserName,
                    'first_name'    => $clientUserFirstName,
                    'last_name'     => $clientUserLastName,
                    'role'          => 'upstream_client_user'
                );

                // @todo : phone

                $userId = wp_insert_user($userData);
                //$userId = null;
                if (is_wp_error($userId)) {
                    // @todo : user should be flagged so Admin might create the User manually via Users Page or Client form.
                    // err: $userId->get_error_message()
                    continue;
                }

                self::$newUsersIdsMap[$rawClientUser['id']] = $userId;

                // @todo : migrate user custom capabilities

                // Flag all projects that will have their `_upstream_project_members` meta updated.
                foreach (self::$clientUsersCache['users'][$rawClientUser['id']]['member_of'] as $projectId) {
                    if (!isset(self::$projectsWithNewMembers[$projectId])) {
                        self::$projectsWithNewMembers[$projectId] = array();
                    }

                    array_push(self::$projectsWithNewMembers[$projectId], $rawClientUser['id']);
                }

                // @todo : migrate project-milestones data for this user
                // @todo : migrate project-tasks data for this user
                // @todo : migrate project-bugs data for this user
                // @todo : migrate project-files data for this user
                // @todo : migrate project-discussion data for this user
            }
        }

        // Check if there's any `_upstream_project_members` changes.
        // @todo : remove 0
        if (0 && !empty(self::$projectsWithNewMembers)) {
            foreach (self::$projectsWithNewMembers as $projectId => $clientUsersIds) {
                $membersList = array_keys(self::$projectsCache['data'][$projectId]['members']);

                foreach ($clientUsersIds as $clientUserId) {
                    $clientUserNewId = self::$newUsersIdsMap[$clientUserId];
                    $clientUserIdIndex = array_search($clientUserId, $membersList);
                    $membersList[$clientUserIdIndex] = $clientUserNewId;
                }

                update_post_meta($projectId, '_upstream_project_members', $membersList);
            }
        }

        die();
    }

    protected static function isUserUsernameUnique($username)
    {
        return !isset(self::$usersCache['username'][$username]);
    }

    protected static function isUserEmailUnique($email)
    {
        return !isset(self::$usersCache['email'][$email]);
    }

    protected static function cacheUser($id, $username, $email, $projects = array())
    {
        if (!self::isUserUsernameUnique($username) || !self::isUserEmailUnique($email)) {
            return;
        }

        $user = array(
            'id'       => (int)$id,
            'username' => $username,
            'email'    => $email,
            'projects' => $projects
        );

        self::$usersCache['data'][$user['id']] = $user;
        self::$usersCache['username'][$user['username']] = &self::$usersCache['data'][$user['id']];
        self::$usersCache['email'][$user['email']] = &self::$usersCache['data'][$user['id']];
    }

    protected static function cacheUsers()
    {
        $db = &self::$db;

        $rowset = $db->get_results(sprintf('
            SELECT `ID`, `user_login`, `user_email`
            FROM `%s`',
            $db->prefix . 'users'
        ));

        if (count($rowset) > 0) {
            foreach ($rowset as $rowIndex => $row) {
                self::cacheUser($row->ID, $row->user_login, $row->user_email);
            }
        }
    }

    protected static function cacheProjectsMembers()
    {
        $db = &self::$db;

        $rowset = (array)$db->get_results(sprintf('
            SELECT `post_id`, `meta_value`
            FROM `%s`
            WHERE `meta_key` = "_upstream_project_members"',
            $db->prefix . 'postmeta'
        ));

        foreach ($rowset as $row) {
            $project = array(
                'id'      => (int)$row->post_id,
                'members' => array()
            );

            $members = (array)maybe_unserialize($row->meta_value);
            foreach ($members as $projectMemberUserId) {
                if (!empty($projectMemberUserId)) {
                    if (is_numeric($projectMemberUserId)) {
                        $projectMemberUserId = (int)$projectMemberUserId;

                        $project['members'][$projectMemberUserId] = &self::$usersCache['data'][$projectMemberUserId];

                        array_push(self::$usersCache['data'][$projectMemberUserId]['projects'], $project['id']);
                    } else {
                        if (isset(self::$clientUsersCache['users'][$projectMemberUserId])) {
                            array_push(self::$clientUsersCache['users'][$projectMemberUserId]['member_of'], $project['id']);

                            $project['members'][$projectMemberUserId] = &self::$clientUsersCache['users'][$projectMemberUserId];
                        }
                    }
                }
            }

            self::$projectsCache['data'][$project['id']] = $project;
        }
    }

    private static function cacheClientUsers()
    {
        $db = &self::$db;

        $rowset = (array)$db->get_results(sprintf('
            SELECT `post_id`, `meta_value`
            FROM `%s`
            WHERE `meta_key` = "_upstream_client_users"',
            $db->prefix . 'postmeta'
        ));

        foreach ($rowset as $row) {
            $client_id = (int)$row->post_id;
            self::$clientUsersCache[$client_id] = array();

            $clientUsersList = (array)maybe_unserialize($row->meta_value);
            foreach ($clientUsersList as $clientUser) {
                if (is_array($clientUser) && !empty($clientUser)) {
                    $clientUser['member_of'] = array();

                    self::$clientUsersCache['clients'][$client_id][$clientUser['id']] = $clientUser;
                    self::$clientUsersCache['users'][$clientUser['id']] = &self::$clientUsersCache['clients'][$client_id][$clientUser['id']];
                }
            }
        }
    }
}
