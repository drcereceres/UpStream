<?php
namespace UpStream;

use \UpStream\Struct;

class Comment extends Struct
{
    public $id;
    public $project_id;
    public $parent_id;
    public $content;
    public $state;
    public $created_by;
    public $created_at;
    public $currentUserCap;
    protected $author;

    public static function convertStateToWpPatterns($state)
    {
        if (is_numeric($state)) {
            $state = (int)$state;
            if ($state === -1) {
                $state = 'trash';
            }
        } else if ($state === 'approve') {
            $state = 1;
        } else if ($state === 'hold') {
            $state = 0;
        }

        return $state;
    }

    public static function convertStateToInt($state)
    {
        if (is_numeric($state)) {
            $state = (int)$state;
        } else if ($state === 'approve') {
            $state = 1;
        } else if ($state === 'hold') {
            $state = 0;
        } else if ($state === 'trash') {
            $state = -1;
        } else if ($state === 'spam') {
            $state = -2;
        }

        return $state;
    }

    public static function arrayToWPPatterns($customData)
    {
        $defaultData = array(
            'comment_post_ID'      => 0,
            'comment_author'       => "",
            'comment_author_email' => "",
            'comment_author_IP'    => "",
            'comment_date'         => "",
            'comment_date_gmt'     => "",
            'comment_content'      => null,
            'comment_agent'        => "",
            'user_id'              => 0,
            'comment_approved'     => 1
        );

        $data = array_merge($defaultData, (array)$customData);

        return $data;
    }

    public function toWpPatterns()
    {
        $data = array(
            'comment_id'           => (int)$this->id,
            'comment_post_ID'      => (int)$this->project_id,
            'user_id'              => (int)$this->created_by->id,
            'comment_author'       => $this->created_by->name,
            'comment_author_email' => $this->created_by->email,
            'comment_content'      => $this->content,
            'comment_approved'     => self::convertStateToWpPatterns($this->state),
            'comment_author_IP'    => isset($this->created_by->ip) ? $this->created_by->ip : "",
            'comment_agent'        => isset($this->created_by->agent) ? $this->created_by->agent : "",
            'comment_parent'       => (int)$this->parent_id > 0 ? $this->parent_id : 0
        );

        return $data;
    }

    public function __construct($content = "", $project_id = 0, $user_id = 0)
    {
        if (!empty($content)) {
            $this->content = $content;
        }

        if ((int)$project_id <= 0) {
            $this->project_id = upstream_post_id();
        } else {
            $this->project_id = (int)$project_id;
        }

        if ((int)$user_id > 0) {
            $author = get_user_by('id', $user_id);
        }

        $user = wp_get_current_user();

        $userHasAdminCapabilities = isUserEitherManagerOrAdmin($user);
        $userCanModerateComments = !$userHasAdminCapabilities ? user_can($user, 'moderate_comments') : true;
        $this->currentUserCap = (object)array(
            'can_reply'    => !$userHasAdminCapabilities ? user_can($user, 'publish_project_discussion') : true,
            'can_moderate' => $userCanModerateComments,
            'can_delete'   => !$userHasAdminCapabilities ? $userCanModerateComments || user_can($user, 'delete_project_discussion') : true
        );

        $this->author = isset($author) ? $author : $user;

        $this->created_by = (object)array(
            'id'     => $author->ID,
            'name'   => $author->display_name,
            'avatar' => getUserAvatarURL($author->ID),
            'email'  => $author->user_email
        );

        $this->created_at = (object)array(
            'timestamp' => 0,
            'utc'       => "",
            'localized' => "",
            'humanized' => ""
        );

        $this->parent_id = 0;
        $this->state = 1;
    }

    public function doFilters()
    {
        $data = $this->toWpPatterns();

        $safeData = wp_filter_comment($data);

        $this->created_by->id = (int)$safeData['user_id'];
        $this->created_by->agent = $safeData['comment_agent'];
        $this->created_by->name = $safeData['comment_author'];
        $this->created_by->email = $safeData['comment_author_email'];
        $this->created_by->ip = $safeData['comment_author_IP'];
        $this->content = $safeData['comment_content'];
    }

    public function isNew()
    {
        return (int)$this->id <= 0;
    }

    public function save()
    {
        if ($this->isNew()) {
            $this->doFilters();

            // Check whether a comment passes internal checks to be allowed to add.
            if (!check_comment($this->created_by->name, $this->created_by->email, "", $this->content, $this->created_by->ip, $this->created_by->agent, "")) {
                throw new \Exception(__('Invalid comment.', 'upstream'));
            }

            $data = $this->toWpPatterns();

            $integrityCheck = wp_allow_comment($data, true);
            if (is_wp_error($integrityCheck)) {
                throw new \Exception($integrityCheck->get_error_message());
            }

            $this->state = $integrityCheck !== "spam" ? (int)$integrityCheck : $integrityCheck;

            $this->created_at->timestamp = time();
            $this->created_at->utc = date('Y-m-d H:i:s', $this->created_at->timestamp);
            $data['comment_date_gmt'] = $this->created_at->utc;

            $dateFormat = get_option('date_format');
            $timeFormat = get_option('time_format');
            $theDateTimeFormat = $dateFormat . ' ' . $timeFormat;
            $utcTimeZone = new \DateTimeZone('UTC');
            $currentTimezone = upstreamGetTimeZone();
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $this->created_at->utc, $utcTimeZone);
            $date->setTimezone($currentTimezone);
            $this->created_at->localized = $date->format($theDateTimeFormat);
            $data['comment_date'] = $date->format('Y-m-d H:i:s');

            $this->created_at->humanized = _x('just now', 'Comment was very recently added.', 'upstream');

            $comment_id = wp_insert_comment($data);
            if (!$comment_id) {
                throw new \Exception(__('Unable to save the data into database.', 'upstream'));
            }

            $this->id = $comment_id;

            return $this->id;
        } else {
            $data = $this->toWpPatterns();
            $success = (bool)wp_update_comment($data);
            if (!$success) {
                throw new \Exception(__('Unable to save the data into database.', 'upstream'));
            }

            return true;
        }
    }

    protected static function updateApprovalState($comment_id, $newState)
    {
        if (!in_array(strtolower((string)$newState), array('1', '0', 'spam', 'trash'))) {
            return false;
        }

        $data = array(
            'comment_ID'       => (int)$comment_id,
            'comment_approved' => $newState
        );

        $success = (bool)wp_update_comment($data);
        return $success;
    }

    public function unapprove()
    {
        if (!$this->isNew()) {
            $success = self::updateApprovalState($this->id, 0);
            if ($success) {
                $this->state = 0;
            }

            return $success;
        }

        return false;
    }

    public function approve()
    {
        if (!$this->isNew()) {
            $success = self::updateApprovalState($this->id, 1);
            if ($success) {
                $this->state = 1;
            }

            return $success;
        }

        return false;
    }

    public function updateHumanizedDate()
    {
        $dateFormat = get_option('date_format');
        $timeFormat = get_option('time_format');
        $theDateTimeFormat = $dateFormat . ' ' . $timeFormat;
        $utcTimeZone = new \DateTimeZone('UTC');
        $currentTimezone = upstreamGetTimeZone();
        $currentTimestamp = time();

        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $this->created_at->utc, $utcTimeZone);
        $date->setTimezone($currentTimezone);
        $dateTimestamp = $date->getTimestamp();

        $this->created_at->localized = $date->format($theDateTimeFormat);
        $this->created_at->humanized = sprintf(
            _x('%s ago', '%s = human-readable time difference', 'upstream'),
            human_time_diff($dateTimestamp, $currentTimestamp)
        );
    }

    public function render($return = false, $useAdminLayout = true, $commentsCache = array())
    {
        if (empty($this->currentUserCap)) {
            $user = wp_get_current_user();
            $userHasAdminCapabilities = isUserEitherManagerOrAdmin();
            $this->currentUserCap->can_reply = !$userHasAdminCapabilities ? user_can($user, 'publish_project_discussion') : true;
            $userCanModerate = !$userHasAdminCapabilities ? user_can($user, 'moderate_comments') : true;
            $this->currentUserCap->can_moderate = $userCanModerate;
            $this->currentUserCap->can_delete = !$userHasAdminCapabilities ? ($userCanModerate || user_can($user, 'delete_project_discussion') || $user->ID === (int)$created_by->id) : true;
        }

        $this->updateHumanizedDate();

        if ((bool)$return === true) {
            ob_start();

            if ((bool)$useAdminLayout === true) {
                upstream_admin_display_message_item($this, $commentsCache);
            } else {
                upstream_display_message_item($this, $commentsCache);
            }

            $html = ob_get_contents();

            ob_end_clean();

            return $html;
        } else {
            if ((bool)$useAdminLayout === true) {
                upstream_admin_display_message_item($this);
            } else {
                upstream_display_message_item($this);
            }
        }
    }

    public static function load($comment_id)
    {
        $data = get_comment($comment_id);

        if (empty($data)) {
            return null;
        }

        $comment = new Comment($data->comment_content, $data->comment_post_ID, $data->user_id);
        $comment->id = (int)$data->comment_ID;
        $comment->created_at->timestamp = strtotime($data->comment_date_gmt);
        $comment->created_at->utc = $data->comment_date_gmt;
        $comment->created_at->localized = $data->comment_date;
        $comment->updateHumanizedDate();
        $comment->state = self::convertStateToInt($data->comment_approved);
        $comment->parent_id = (int)$data->comment_parent;

        return $comment;
    }
}
