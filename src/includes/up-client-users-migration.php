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
    private static $newUsersMap = array();
    private static $projects = array();

    private static function prepareMigration()
    {
        global $wpdb;
        self::$db = &$wpdb;

        self::cacheUsers();
        self::cacheClientUsers();
        self::cacheProjects();
    }

    private static function cacheProjects()
    {
        $db = &self::$db;

        $rowset = $db->get_results('
            SELECT `post_id`, `meta_key`, `meta_value`
            FROM `'. $db->prefix .'postmeta`
            WHERE `meta_key` IN ("_upstream_project_activity", "_upstream_project_discussion", "_upstream_project_members", "_upstream_project_milestones", "_upstream_project_tasks", "_upstream_project_bugs", "_upstream_project_files")'
        );

        if (count($rowset) > 0) {
            foreach ($rowset as $row) {
                $project_id = (int)$row->post_id;

                if (!isset(self::$projects[$project_id])) {
                    self::$projects[$project_id] = array(
                        'id'         => $project_id,
                        'discussion' => array(),
                        'members'    => array(),
                        'milestones' => array(),
                        'tasks'      => array(),
                        'bugs'       => array(),
                        'files'      => array(),
                        'has_changed' => false
                    );
                }

                $metaKeyType = str_replace('_upstream_project_', '', $row->meta_key);

                self::$projects[$project_id][$metaKeyType] = maybe_unserialize($row->meta_value);

                if ($metaKeyType === 'members') {
                    foreach (self::$projects[$project_id][$metaKeyType] as $memberIndex => $memberId) {
                        $memberId = (string)$memberId;

                        self::$projects[$project_id][$metaKeyType][$memberIndex] = $memberId;

                        if (!is_numeric($memberId) && isset(self::$clientUsersMap[$memberId])) {
                            array_push(self::$clientUsersMap[$memberId]['projects'], $project_id);
                        }
                    }
                }
            }
        }
    }

    public static function run()
    {
        // @todo : check if we really need to prepare and run the migration.
        self::prepareMigration();

        $db = &self::$db;

        if (count(self::$clientUsersMap) === 0) {
            return;
        }

        foreach (self::$clientUsersMap as $rawClientUser) {
            $rawClientUser = (array)$rawClientUser;
            $client_id = (int)$rawClientUser['client_id'];

            if (empty($rawClientUser) || !isset($rawClientUser['id']) || empty($rawClientUser['id'])) {
                // @todo : There's no need to worry about this user id, since he has no valid id.
                continue;
            }

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
            if (!self::isUserEmailUnique($clientUserEmail)) {
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

            self::$newUsersMap[$rawClientUser['id']] = $userId;

            // @todo : migrate user custom capabilities

            echo "<p>\$user_id: <code style=\"color: rgb(204, 0, 0);\">". $rawClientUser['id'] ."</code> -> <code style=\"color: rgb(78, 154, 6);\">". $userId ."</code></p>";

            // Flag all projects that will have their `_upstream_project_members` meta updated.
            foreach (self::$clientUsersMap[$rawClientUser['id']]['projects'] as $projectId) {
                $project = self::$projects[$projectId];

                $memberIndex = array_search($rawClientUser['id'], $project['members']);
                $project['members'][$memberIndex] = (string)$userId;

                foreach (array('milestones', 'tasks', 'bugs', 'files') as $itemType) {
                    foreach ($project[$itemType] as $projectItemIndex => $projectItem) {
                        if ((string)$projectItem['created_by'] === $rawClientUser['id']) {
                            $projectItem['created_by'] = (string)$userId;
                            $project[$itemType][$projectItemIndex] = $projectItem;
                        }
                    }
                }

                $project['has_changed'] = true;

                self::$projects[$projectId] = $project;
            }
        }

        $updatedUsersOldIds = array_keys(self::$newUsersMap);

        $convertUsersLegacyIdFromHaystack = function(&$haystack) {
            foreach ($haystack as &$needle) {
                if (isset(self::$newUsersMap[$needle])) {
                    $needle = (string)self::$newUsersMap[$needle];
                }
            }
        };

        foreach (self::$projects as $project) {
            if ($project['has_changed']) {
                foreach (array('members', 'milestones', 'tasks', 'bugs', 'files', 'discussion') as $itemType) {
                    //@todo update_post_meta($project['id'], '_upstream_project_' . $itemType, $project[$itemType]);
                }

                // Update project activity.
                $projectActivities = (array)get_post_meta($project['id'], '_upstream_project_activity');
                if (!empty($projectActivities)) {
                    foreach ($projectActivities[0] as $activityIndex => $activity) {
                        $activityUserId = (string)$activity['user_id'];
                        if (isset(self::$newUsersMap[$activityUserId])) {
                            $activity['user_id'] = self::$newUsersMap[$activityUserId];
                        }

                        if (isset($activity['fields'])) {
                            if (isset($activity['fields']['single'])) {
                                foreach ($activity['fields']['single'] as $activitySingleIndentifier => $activitySingle) {
                                    if ($activitySingleIndentifier === '_upstream_project_client_users') {
                                        if (isset($activitySingle['add'])) {
                                            if (is_array($activitySingle['add'])) {
                                                $convertUsersLegacyIdFromHaystack($activitySingle['add']);
                                            }
                                        }

                                        if (isset($activitySingle['from'])) {
                                            if (is_array($activitySingle['from'])) {
                                                $convertUsersLegacyIdFromHaystack($activitySingle['from']);
                                            }
                                        }

                                        if (isset($activitySingle['to'])) {
                                            if (is_array($activitySingle['to'])) {
                                                $convertUsersLegacyIdFromHaystack($activitySingle['to']);
                                            }
                                        }
                                    }

                                    $activity['fields']['single'][$activitySingleIndentifier] = $activitySingle;
                                }
                            }

                            if (isset($activity['fields']['group'])) {
                                foreach ($activity['fields']['group'] as $groupIdentifier => $groupData) {
                                    if (isset($groupData['add'])) {
                                        foreach ($groupData['add'] as $rowIndex => $row) {
                                            if (isset($row['created_by']) && isset(self::$newUsersMap[$row['created_by']])) {
                                                $row['created_by'] = self::$newUsersMap[$row['created_by']];
                                                $groupData['add'][$rowIndex] = $row;
                                            }
                                        }
                                    }

                                    if (isset($groupData['remove'])) {
                                        foreach ($groupData['remove'] as $rowIndex => $row) {
                                            if (isset($row['created_by']) && isset(self::$newUsersMap[$row['created_by']])) {
                                                $row['created_by'] = self::$newUsersMap[$row['created_by']];
                                                $groupData['remove'][$rowIndex] = $row;
                                            }
                                        }
                                    }

                                    $activity['fields']['group'][$groupIdentifier] = $groupData;
                                }
                            }
                        }

                        $projectActivities[0][$activityIndex] = $activity;
                    }

                    //@todo update_post_meta($project['id'], '_upstream_project_activity', $projectActivities);
                }
            }
        }
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

    private static $clientUsersMap = array();
    private static $clientsMap = array();

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

            self::$clientsMap[$client_id] = array();

            $clientUsersMapList = (array)maybe_unserialize($row->meta_value);
            foreach ($clientUsersMapList as $clientUser) {
                if (is_array($clientUser) && !empty($clientUser)) {
                    $clientUser['id'] = (string)$clientUser['id'];
                    $clientUser['client_id'] = $client_id;
                    $clientUser['projects'] = array();

                    self::$clientUsersMap[$clientUser['id']] = $clientUser;
                    array_push(self::$clientsMap[$client_id], $clientUser['id']);
                }
            }
        }
    }
}
