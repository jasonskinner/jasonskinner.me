<?php

/*
 * Plugin Name: wpDiscuz
 * Description: Better comment system. Wordpress post comments and discussion plugin. Allows your visitors discuss, vote for comments and share.
 * Version: 5.3.1
 * Author: gVectors Team (A. Chakhoyan, G. Zakaryan, H. Martirosyan)
 * Author URI: https://gvectors.com/
 * Plugin URI: http://wpdiscuz.com/
 * Text Domain: wpdiscuz
 * Domain Path: /languages/
 */
if (!defined('ABSPATH')) {
    exit();
}

define('WPDISCUZ_DS', DIRECTORY_SEPARATOR);
define('WPDISCUZ_DIR_PATH', dirname(__FILE__));
define('WPDISCUZ_DIR_NAME', basename(WPDISCUZ_DIR_PATH));

include_once WPDISCUZ_DIR_PATH . '/includes/interface.WpDiscuzConstants.php';
include_once WPDISCUZ_DIR_PATH . '/utils/functions.php';
include_once WPDISCUZ_DIR_PATH . '/options/class.WpdiscuzOptions.php';
include_once WPDISCUZ_DIR_PATH . '/options/class.WpdiscuzOptionsSerialized.php';
include_once WPDISCUZ_DIR_PATH . '/utils/class.WpdiscuzHelper.php';
include_once WPDISCUZ_DIR_PATH . '/utils/class.WpdiscuzHelperEmail.php';
include_once WPDISCUZ_DIR_PATH . '/utils/class.WpdiscuzHelperOptimization.php';
include_once WPDISCUZ_DIR_PATH . '/includes/class.WpdiscuzDBManager.php';
include_once WPDISCUZ_DIR_PATH . '/includes/class.WpdiscuzCss.php';
include_once WPDISCUZ_DIR_PATH . '/forms/wpDiscuzForm.php';
include_once WPDISCUZ_DIR_PATH . '/utils/class.WpdiscuzCache.php';
include_once WPDISCUZ_DIR_PATH . '/utils/class.WpdiscuzHelperAjax.php';

class WpdiscuzCore implements WpDiscuzConstants {

    public $dbManager;
    public $helper;
    public $helperAjax;
    public $helperEmail;
    public $helperOptimization;
    public $optionsSerialized;
    public $wpdiscuzOptionsJs;
    private $css;
    private $options;
    private $wpdiscuzWalker;
    public $commentsArgs;
    private $version;
    public $wpdiscuzForm;
    public $form;
    private $cache;
    public $subscriptionData;
    public $isWpdiscuzLoaded;
    private $requestUri;
    public static $CURRENT_BLOG_ID;
    private static $_instance = null;

    private function __construct() {
        if (!is_admin() && !session_id()) {
            session_start();
        }
        $this->version = get_option(self::OPTION_SLUG_VERSION, '1.0.0');
        $this->dbManager = new WpdiscuzDBManager();
        $this->optionsSerialized = new WpdiscuzOptionsSerialized($this->dbManager);
        $this->options = new WpdiscuzOptions($this->optionsSerialized, $this->dbManager);
        $this->wpdiscuzForm = new wpDiscuzForm($this->optionsSerialized, $this->version);
        $this->helper = new WpdiscuzHelper($this->optionsSerialized, $this->dbManager, $this->wpdiscuzForm);
        $this->helperEmail = new WpdiscuzHelperEmail($this->optionsSerialized, $this->dbManager);
        $this->helperOptimization = new WpdiscuzHelperOptimization($this->optionsSerialized, $this->dbManager, $this->helperEmail, $this->wpdiscuzForm);
        $this->helperAjax = new WpdiscuzHelperAjax($this->optionsSerialized, $this->dbManager, $this->helper, $this->helperEmail);
        $this->css = new WpdiscuzCss($this->optionsSerialized, $this->helper);
        $this->wpdiscuzWalker = new WpdiscuzWalker($this->helper, $this->helperOptimization, $this->dbManager, $this->optionsSerialized);
        $this->cache = new WpdiscuzCache($this->optionsSerialized, $this->helper, $this->dbManager);
        $this->requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

        if ($this->optionsSerialized->isLoadOnlyParentComments) {
            add_action('wp_ajax_wpdShowReplies', array(&$this, 'showReplies'));
            add_action('wp_ajax_nopriv_wpdShowReplies', array(&$this, 'showReplies'));
        }

        self::$CURRENT_BLOG_ID = get_current_blog_id();
        register_activation_hook(__FILE__, array(&$this, 'pluginActivation'));
        register_deactivation_hook(__FILE__, array(&$this->wpdiscuzForm, 'removeAllFiles'));

        if (!get_option(self::OPTION_SLUG_DEACTIVATION) && (strpos($this->requestUri, '/plugins.php') !== false)) {
            add_action('admin_footer', array(&$this->helper, 'wpdDeactivationReasonModal'));
        }
        /* GRAVATARS CACHE */
        register_activation_hook(__FILE__, array(&$this, 'registerGravatarsJobs'));
        register_deactivation_hook(__FILE__, array(&$this, 'deregisterGravatarsJobs'));
        add_filter('cron_schedules', array(&$this, 'setGravatarsIntervals'));
        add_action(self::GRAVATARS_CACHE_ADD_ACTION, array(&$this->cache, 'cacheGravatars'));
        add_action(self::GRAVATARS_CACHE_DELETE_ACTION, array(&$this->cache, 'deleteGravatars'));
        /* GRAVATARS CACHE */
        add_action('wpmu_new_blog', array(&$this, 'addNewBlog'));
        add_action('delete_blog', array(&$this, 'deleteBlog'));
        add_action('wp_head', array(&$this, 'initCurrentPostType'));
        add_action('wp_head', array(&$this->css, 'initCustomCss'));
        if (!$this->optionsSerialized->hideUserSettingsButton) {
            add_action('wp_footer', array(&$this, 'addContentModal'));
            add_action('wp_ajax_wpdGetInfo', array(&$this->helper, 'wpdGetInfo'));
            add_action('wp_ajax_nopriv_wpdGetInfo', array(&$this->helper, 'wpdGetInfo'));
            add_action('wp_ajax_wpdGetActivityPage', array(&$this->helper, 'getActivityPage'));
            add_action('wp_ajax_nopriv_wpdGetActivityPage', array(&$this->helper, 'getActivityPage'));
            add_action('wp_ajax_wpdGetSubscriptionsPage', array(&$this->helper, 'getSubscriptionsPage'));
            add_action('wp_ajax_nopriv_wpdGetSubscriptionsPage', array(&$this->helper, 'getSubscriptionsPage'));
            add_action('wp_ajax_wpdGetFollowsPage', array(&$this->helper, 'getFollowsPage'));
            add_action('wp_ajax_nopriv_wpdGetFollowsPage', array(&$this->helper, 'getFollowsPage'));
            add_action('wp_ajax_wpdDeleteComment', array(&$this->helperAjax, 'deleteComment'));
            add_action('wp_ajax_nopriv_wpdDeleteComment', array(&$this->helperAjax, 'deleteComment'));
            add_action('wp_ajax_wpdCancelSubscription', array(&$this->helperAjax, 'deleteSubscription'));
            add_action('wp_ajax_nopriv_wpdCancelSubscription', array(&$this->helperAjax, 'deleteSubscription'));
            add_action('wp_ajax_wpdCancelFollow', array(&$this->helperAjax, 'deleteFollow'));
            add_action('wp_ajax_nopriv_wpdCancelFollow', array(&$this->helperAjax, 'deleteFollow'));
            add_action('wp_ajax_wpdEmailDeleteLinks', array(&$this->helperAjax, 'emailDeleteLinks'));
            add_action('wp_ajax_nopriv_wpdGuestAction', array(&$this->helperAjax, 'guestAction'));
        }

        add_action('init', array(&$this, 'wpdiscuzTextDomain'));
        add_action('admin_init', array(&$this, 'pluginNewVersion'), 1);
        add_action('admin_enqueue_scripts', array(&$this, 'adminPageStylesScripts'), 100);
        add_action('wp_enqueue_scripts', array(&$this, 'frontEndStylesScripts'));
        add_action('admin_menu', array(&$this, 'addPluginOptionsPage'), 8);
        add_action('admin_notices', array(&$this->helper, 'hashVotesNote'));


        //$wp_version = get_bloginfo('version');
        //if (version_compare($wp_version, '4.2.0', '>=')) {
        add_action('wp_ajax_dismiss_wpdiscuz_addon_note', array(&$this->options, 'dismissAddonNote'));
        add_action('admin_notices', array(&$this->options, 'addonNote'));
        //add_action('wp_ajax_dismiss_wpdiscuz_tip_note', array(&$this->options, 'dismissTipNote'));
        //add_action('admin_notices', array(&$this->options, 'tipNote'));
        //}
        add_action('wp_ajax_wpdLoadMoreComments', array(&$this, 'loadMoreComments'));
        add_action('wp_ajax_nopriv_wpdLoadMoreComments', array(&$this, 'loadMoreComments'));
        add_action('wp_ajax_wpdVoteOnComment', array(&$this, 'voteOnComment'));
        add_action('wp_ajax_nopriv_wpdVoteOnComment', array(&$this, 'voteOnComment'));
        add_action('wp_ajax_wpdSorting', array(&$this, 'sorting'));
        add_action('wp_ajax_nopriv_wpdSorting', array(&$this, 'sorting'));
        add_action('wp_ajax_wpdAddComment', array(&$this, 'addComment'));
        add_action('wp_ajax_nopriv_wpdAddComment', array(&$this, 'addComment'));
        add_action('wp_ajax_wpdGetSingleComment', array(&$this, 'getSingleComment'));
        add_action('wp_ajax_nopriv_wpdGetSingleComment', array(&$this, 'getSingleComment'));
        add_action('wp_ajax_addSubscription', array(&$this->helperEmail, 'addSubscription'));
        add_action('wp_ajax_nopriv_addSubscription', array(&$this->helperEmail, 'addSubscription'));
        add_action('wp_ajax_wpdCheckNotificationType', array(&$this->helperEmail, 'checkNotificationType'));
        add_action('wp_ajax_nopriv_wpdCheckNotificationType', array(&$this->helperEmail, 'checkNotificationType'));
        add_action('wp_ajax_wpdRedirect', array(&$this, 'redirect'));
        add_action('wp_ajax_nopriv_wpdRedirect', array(&$this, 'redirect'));
        add_action('wp_ajax_wpdMostReactedComment', array(&$this, 'mostReactedComment'));
        add_action('wp_ajax_nopriv_wpdMostReactedComment', array(&$this, 'mostReactedComment'));
        add_action('wp_ajax_wpdHottestThread', array(&$this, 'hottestThread'));
        add_action('wp_ajax_nopriv_wpdHottestThread', array(&$this, 'hottestThread'));

        add_action('admin_post_removeVoteData', array(&$this->helperOptimization, 'removeVoteData'));
        add_action('admin_post_resetPhrases', array(&$this->helperOptimization, 'resetPhrases'));
        add_action('admin_post_disableAddonsDemo', array(&$this->helper, 'disableAddonsDemo'));
        add_action('comment_post', array(&$this->helperEmail, 'notificationFromDashboard'), 10, 2);
        add_action('transition_comment_status', array(&$this->helperOptimization, 'statusEventHandler'), 10, 3);
        $plugin = plugin_basename(__FILE__);
        add_filter("plugin_action_links_$plugin", array(&$this, 'addPluginSettingsLink'));
        add_filter('comments_clauses', array(&$this, 'commentsClauses'));

        add_action('wp_ajax_wpdEditComment', array(&$this, 'editComment'));
        add_action('wp_ajax_nopriv_wpdEditComment', array(&$this, 'editComment'));
        add_action('wp_ajax_wpdSaveEditedComment', array(&$this, 'saveEditedComment'));
        add_action('wp_ajax_nopriv_wpdSaveEditedComment', array(&$this, 'saveEditedComment'));

        if ($this->optionsSerialized->commentListUpdateType) {
            add_action('wp_ajax_wpdUpdateAutomatically', array(&$this, 'updateAutomatically'));
            add_action('wp_ajax_nopriv_wpdUpdateAutomatically', array(&$this, 'updateAutomatically'));
            add_action('wp_ajax_wpdUpdateOnClick', array(&$this, 'updateOnClick'));
            add_action('wp_ajax_nopriv_wpdUpdateOnClick', array(&$this, 'updateOnClick'));
        }

        if ($this->optionsSerialized->commentReadMoreLimit) {
            add_action('wp_ajax_wpdReadMore', array(&$this, 'readMore'));
            add_action('wp_ajax_nopriv_wpdReadMore', array(&$this, 'readMore'));
        }

        add_action('wp_loaded', array(&$this, 'addNewRoles'));
        add_filter('comments_template_query_args', array(&$this, 'commentsTemplateQueryArgs'));
        add_action('pre_get_comments', array(&$this, 'preGetComments'));
        add_filter('found_comments_query', array(&$this, 'foundCommentsQuery'), 10, 2);

        add_action('profile_update', array(&$this->helperOptimization, 'onProfileUpdate'), 10, 2);
        add_filter('comment_row_actions', array(&$this->helper, 'commentRowStickAction'), 10, 2);
        add_filter('admin_comment_types_dropdown', array(&$this->helper, 'addCommentTypes'));
    }

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function pluginActivation($networkwide) {
        if (function_exists('is_multisite') && is_multisite() && $networkwide) {
            $oldBlogID = $this->dbManager->getBlogID();
            $oldSitePluginVersion = $this->version;
            $blogIDs = $this->dbManager->getBlogIDs();
            foreach ($blogIDs as $blogID) {
                switch_to_blog($blogID);
                $this->version = get_option(self::OPTION_SLUG_VERSION, '1.0.0');
                $this->actiavteWpDiscuz();
            }
            switch_to_blog($oldBlogID);
            $this->version = $oldSitePluginVersion;
            return;
        }
        $this->actiavteWpDiscuz();
    }

    public function addNewBlog($blogID) {
        if (is_plugin_active_for_network('wpdiscuz/class.WpdiscuzCore.php')) {
            $oldBlogID = $this->dbManager->getBlogID();
            $oldSitePluginVersion = $this->version;
            switch_to_blog($blogID);
            $this->version = get_option(self::OPTION_SLUG_VERSION, '1.0.0');
            $this->actiavteWpDiscuz();
            switch_to_blog($oldBlogID);
            $this->version = $oldSitePluginVersion;
        }
    }

    public function deleteBlog($blogID) {
        if (is_plugin_active_for_network('wpdiscuz/class.WpdiscuzCore.php')) {
            $oldBlogID = $this->dbManager->getBlogID();
            switch_to_blog($blogID);
            $this->dbManager->dropTables();
            switch_to_blog($oldBlogID);
        }
    }

    private function actiavteWpDiscuz() {
        $this->dbManager->dbCreateTables();
        $this->pluginNewVersion();
    }

    public function wpdiscuzTextDomain() {
        load_plugin_textdomain('wpdiscuz', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public function registerGravatarsJobs() {
        if (!wp_next_scheduled(self::GRAVATARS_CACHE_ADD_ACTION)) {
            wp_schedule_event(current_time('timestamp'), self::GRAVATARS_CACHE_ADD_KEY_RECURRENCE, self::GRAVATARS_CACHE_ADD_ACTION);
        }

        if (!wp_next_scheduled(self::GRAVATARS_CACHE_DELETE_ACTION)) {
            wp_schedule_event(current_time('timestamp'), self::GRAVATARS_CACHE_DELETE_KEY_RECURRENCE, self::GRAVATARS_CACHE_DELETE_ACTION);
        }
    }

    public function deregisterGravatarsJobs() {
        if (wp_next_scheduled(self::GRAVATARS_CACHE_ADD_ACTION)) {
            wp_clear_scheduled_hook(self::GRAVATARS_CACHE_ADD_ACTION);
        }

        if (wp_next_scheduled(self::GRAVATARS_CACHE_DELETE_ACTION)) {
            wp_clear_scheduled_hook(self::GRAVATARS_CACHE_DELETE_ACTION);
        }
    }

    public function setGravatarsIntervals($schedules) {
        $cacheAddInterval = array(
            'interval' => self::GRAVATARS_CACHE_ADD_RECURRENCE * HOUR_IN_SECONDS,
            'display' => __('Every 3 hours', 'wpdiscuz')
        );
        $cacheDeleteInterval = array(
            'interval' => self::GRAVATARS_CACHE_DELETE_RECURRENCE * HOUR_IN_SECONDS,
            'display' => __('Every 48 hours', 'wpdiscuz')
        );
        $schedules[self::GRAVATARS_CACHE_ADD_KEY_RECURRENCE] = $cacheAddInterval;
        $schedules[self::GRAVATARS_CACHE_DELETE_KEY_RECURRENCE] = $cacheDeleteInterval;
        return $schedules;
    }

    public function updateAutomatically() {
        $messageArray = array('code' => 0);
        $postId = isset($_POST['postId']) ? intval($_POST['postId']) : 0;
        $loadLastCommentId = isset($_POST['loadLastCommentId']) ? intval($_POST['loadLastCommentId']) : 0;
        $visibleCommentIds = isset($_POST['visibleCommentIds']) ? $_POST['visibleCommentIds'] : '';
        if ($visibleCommentIds && $postId && $loadLastCommentId) {
            $cArgs = $this->getDefaultCommentsArgs($postId);
            $lastCommentId = $this->dbManager->getLastCommentId($cArgs);
            if ($lastCommentId > $loadLastCommentId) {
                $visibleCommentIds = array_filter(explode(',', $visibleCommentIds));
                $messageArray['code'] = 1;
                $messageArray['loadLastCommentId'] = $lastCommentId;
                $commentListArgs = $this->getCommentListArgs($postId);
                $commentListArgs['new_loaded_class'] = 'wc-new-loaded-comment';
                $sentEmail = isset($_POST['email']) ? trim($_POST['email']) : '';
                $email = !empty($commentListArgs['current_user']->ID) ? $commentListArgs['current_user']->user_email : $sentEmail;
                $newCommentIds = $this->dbManager->getNewCommentIds($cArgs, $loadLastCommentId, $email);
                $newCommentIds = apply_filters('wpdiscuz_live_update_new_comment_ids', $newCommentIds, $postId, $commentListArgs['current_user']);
                if ($this->optionsSerialized->commentListUpdateType == 1) {
                    $messageArray['message'] = array();
                    foreach ($newCommentIds as $newCommentId) {
                        if (!in_array($newCommentId, $visibleCommentIds)) {
                            $comment = get_comment($newCommentId);
                            if (($comment->comment_parent && (in_array($comment->comment_parent, $visibleCommentIds) || in_array($comment->comment_parent, $newCommentIds))) || !$comment->comment_parent) {
                                $commentHtml = wp_list_comments($commentListArgs, array($comment));
                                $commentObject = array('comment_parent' => $comment->comment_parent, 'comment_html' => $commentHtml);
                                if ($comment->comment_parent) {
                                    array_push($messageArray['message'], $commentObject);
                                } else {
                                    array_unshift($messageArray['message'], $commentObject);
                                }
                            }
                        }
                    }
                } else {
                    $commentIds = '';
                    foreach ($visibleCommentIds as $cId) {
                        $commentIds .= intval($cId) . ',';
                    }
                    $commentIds = trim($commentIds, ',');
                    $authorComments = $this->dbManager->getAuthorVisibleComments($cArgs, $commentIds, $email);
                    $messageArray['message']['author_replies'] = array();
                    $messageArray['message']['comments'] = array();
                    foreach ($newCommentIds as $newCommentId) {
                        if (!in_array($newCommentId, $visibleCommentIds)) {
                            $comment = get_comment($newCommentId);
                            if ($this->helperOptimization->isReplyInAuthorTree($comment->comment_ID, $authorComments)) { // if is in author tree add as reply
                                $messageArray['message']['author_replies'][] = $newCommentId;
                            } else { // add as new comment
                                if ($comment->comment_parent) {
                                    array_push($messageArray['message']['comments'], $newCommentId);
                                } else {
                                    array_unshift($messageArray['message']['comments'], $newCommentId);
                                }
                            }
                        }
                    }
                    asort($messageArray['message']['author_replies']);
                }
                $messageArray['wc_all_comments_count_new'] = get_comments_number($postId);
            }
        }
        wp_die(json_encode($messageArray));
    }

    public function updateOnClick() {
        $messageArray = array('code' => 0);
        $postId = isset($_POST['postId']) ? intval($_POST['postId']) : 0;
        $newCommentIds = isset($_POST['newCommentIds']) ? trim($_POST['newCommentIds']) : '';

        if ($postId && $newCommentIds) {
            $messageArray['code'] = 1;
            $newCommentIds = explode(',', trim($newCommentIds, ','));
            $postId = trim(intval($postId));
            $commentListArgs = $this->getCommentListArgs($postId);
            $commentListArgs['new_loaded_class'] = 'wc-new-loaded-comment';
            $messageArray['message'] = array();
            foreach ($newCommentIds as $newCommentId) {
                $comment = get_comment($newCommentId);
                $commentHtml = wp_list_comments($commentListArgs, array($comment));
                $commentObject = array('comment_parent' => $comment->comment_parent, 'comment_html' => $commentHtml);
                $messageArray['message'][] = $commentObject;
            }
        }
        wp_die(json_encode($messageArray));
    }

    public function addComment() {
        $messageArray = array();
        $isAnonymous = false;
        $uniqueId = isset($_POST['wpdiscuz_unique_id']) ? trim($_POST['wpdiscuz_unique_id']) : '';
        $postId = isset($_POST['postId']) ? intval($_POST['postId']) : '';

        if (!current_user_can('moderate_comments') && $key = trim($this->optionsSerialized->antispamKey)) {
            if (!isset($_POST['ahk']) || (!($ahk = trim($_POST['ahk'])) || $key != $ahk)) {
                die(__('We are sorry, but this comment cannot be posted. Please try later.', 'wpdiscuz'));
            }
        }

        if ($uniqueId && $postId) {
            $form = $this->wpdiscuzForm->getForm($postId);
            $form->initFormFields();

            do_action('wpdiscuz_add_comment');
            if (!comments_open($postId)) {
                die(__('We are sorry, you are not allowed to comment more than one time!', 'wpdiscuz'));
            }

            if (function_exists('zerospam_get_key') && isset($_POST['wpdiscuz_zs']) && ($wpdiscuzZS = $_POST['wpdiscuz_zs'])) {
                $_POST['zerospam_key'] = $wpdiscuzZS == md5(zerospam_get_key()) ? zerospam_get_key() : '';
            }
            $commentDepth = isset($_POST['wc_comment_depth']) && intval($_POST['wc_comment_depth']) ? intval($_POST['wc_comment_depth']) : 1;
            $isInSameContainer = '1';
            $currentUser = WpdiscuzHelper::getCurrentUser();
            if ($commentDepth > $this->optionsSerialized->wordpressThreadCommentsDepth) {
                $commentDepth = $this->optionsSerialized->wordpressThreadCommentsDepth;
                $isInSameContainer = '0';
            } else if (!$this->optionsSerialized->wordpressThreadComments) {
                $isInSameContainer = '0';
            }
            $notificationType = isset($_POST['wpdiscuz_notification_type']) ? $_POST['wpdiscuz_notification_type'] : '';

            $form->validateDefaultCaptcha($currentUser);
            $form->validateFields($currentUser);

            $website_url = '';
            if ($currentUser && $currentUser->ID) {
                $user_id = $currentUser->ID;
                $name = $this->helper->getCurrentUserDisplayName($currentUser);
                $email = $currentUser->user_email;
            } else {
                $user_id = 0;
                $name = $form->validateDefaultName($currentUser);
                $email = $form->validateDefaultEmail($currentUser, $isAnonymous);
                $website_url = $form->validateDefaultWebsite($currentUser);
            }

            $comment_content = $this->helper->replaceCommentContentCode(trim($_POST['wc_comment']));
            $comment_content = $this->helper->filterCommentText($comment_content);
            if (!$comment_content) {
                $messageArray['code'] = 'wc_msg_required_fields';
                wp_die(json_encode($messageArray));
            }
            $commentMinLength = intval($this->optionsSerialized->commentTextMinLength);
            $commentMaxLength = intval($this->optionsSerialized->commentTextMaxLength);
            $contentLength = function_exists('mb_strlen') ? mb_strlen($comment_content) : strlen($comment_content);
            if ($commentMinLength > 0 && $contentLength < $commentMinLength) {
                $messageArray['code'] = 'wc_msg_input_min_length';
                wp_die(json_encode($messageArray));
            }

            if ($commentMaxLength > 0 && $contentLength > $commentMaxLength) {
                $messageArray['code'] = 'wc_msg_input_max_length';
                wp_die(json_encode($messageArray));
            }

            if ($name && $email && $comment_content) {
                $name = urldecode($name);
                $email = urldecode($email);
                $website_url = $website_url ? urldecode($website_url) : '';
                $stickyComment = isset($_POST['wc_sticky_comment']) && ($sticky = intval($_POST['wc_sticky_comment'])) ? $sticky : '';
                $closedComment = isset($_POST['wc_closed_comment']) && ($closed = absint($_POST['wc_closed_comment'])) ? $closed : '';
                $uid_data = $this->helper->getUIDData($uniqueId);
                $comment_parent = intval($uid_data[0]);
                $parentComment = $comment_parent ? get_comment($comment_parent) : null;
                $comment_parent = isset($parentComment->comment_ID) ? $parentComment->comment_ID : 0;
                if ($parentComment && $parentComment->comment_karma) {
                    wp_die(__('This is closed comment thread', 'wpdiscuz'));
                }
                $wc_user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
                $new_commentdata = array(
                    'user_id' => $user_id,
                    'comment_post_ID' => $postId,
                    'comment_parent' => $comment_parent,
                    'comment_author' => $name,
                    'comment_author_email' => $email,
                    'comment_content' => $comment_content,
                    'comment_author_url' => $website_url,
                    'comment_agent' => $wc_user_agent,
                    'comment_type' => $stickyComment ? self::WPDISCUZ_STICKY_COMMENT : '',
                    'comment_karma' => $closedComment
                );

                $new_comment_id = wp_new_comment(wp_slash($new_commentdata));
                $form->saveCommentMeta($new_comment_id);
                $newComment = get_comment($new_comment_id);
                $held_moderate = 1;
                if ($newComment->comment_approved === '1') {
                    $held_moderate = 0;
                }
                if ($notificationType == WpdiscuzCore::SUBSCRIPTION_POST && class_exists('Prompt_Comment_Form_Handling') && $this->optionsSerialized->usePostmaticForCommentNotification) {
                    $_POST[Prompt_Comment_Form_Handling::SUBSCRIBE_CHECKBOX_NAME] = 1;
                    Prompt_Comment_Form_Handling::handle_form($new_comment_id, $newComment->comment_approved);
                } else if (!$isAnonymous && $notificationType) {
                    $noNeedMemberConfirm = ($currentUser->ID && $this->optionsSerialized->disableMemberConfirm);
                    $noNeedGuestsConfirm = (!$currentUser->ID && $this->optionsSerialized->disableGuestsConfirm);
                    if ($noNeedMemberConfirm || $noNeedGuestsConfirm) {
                        $this->dbManager->addEmailNotification($new_comment_id, $postId, $email, self::SUBSCRIPTION_COMMENT, 1);
                    } else {
                        $confirmData = $this->dbManager->addEmailNotification($new_comment_id, $postId, $email, self::SUBSCRIPTION_COMMENT);
                        if ($confirmData) {
                            $this->helperEmail->confirmEmailSender($confirmData['id'], $confirmData['activation_key'], $postId, $email);
                        }
                    }
                }
                $messageArray['code'] = $uniqueId;
                $messageArray['redirect'] = $this->optionsSerialized->redirectPage;
                $messageArray['new_comment_id'] = $new_comment_id;
                $messageArray['comment_author'] = $name;
                $messageArray['comment_author_email'] = $email;
                $messageArray['comment_author_url'] = $website_url;
                $messageArray['is_main'] = $comment_parent ? 0 : 1;
                $messageArray['held_moderate'] = $held_moderate;
                $messageArray['is_in_same_container'] = $isInSameContainer;
                $messageArray['wc_all_comments_count_new'] = get_comments_number($postId);
                if (!$this->optionsSerialized->hideDiscussionStat) {
                    $messageArray['threadsCount'] = $this->dbManager->getThreadsCount($postId, false);
                    $messageArray['repliesCount'] = $this->dbManager->getRepliesCount($postId, false);
                    $messageArray['authorsCount'] = $this->dbManager->getAuthorsCount($postId, false);
                }

                $commentListArgs = $this->getCommentListArgs($postId);
                $commentListArgs['addComment'] = $commentDepth;
                $commentListArgs['comment_author_email'] = $email;
                $messageArray['message'] = wp_list_comments($commentListArgs, array($newComment));
                $messageArray['message'] = wp_unslash($messageArray['message']);
            } else {
                $messageArray['code'] = 'wc_invalid_field';
            }
        } else {
            $messageArray['code'] = 'wc_msg_required_fields';
        }
        $messageArray['callbackFunctions'] = array();
        $messageArray = apply_filters('wpdiscuz_comment_post', $messageArray);
        wp_die(json_encode($messageArray));
    }

    /**
     * get comment text from db
     */
    public function editComment() {
        $messageArray = array('code' => 0);
        $commentId = isset($_POST['commentId']) ? intval($_POST['commentId']) : 0;
        if ($commentId) {
            $comment = get_comment($commentId);
            $postID = $comment->comment_post_ID;
            $form = $this->wpdiscuzForm->getForm($postID);
            $form->initFormFields();
            $currentUser = WpdiscuzHelper::getCurrentUser();
            $highLevelUser = current_user_can('moderate_comments');
            $isCurrentUserCanEdit = $this->helper->isCommentEditable($comment) && $this->helper->canUserEditComment($comment, $currentUser);
            if (!$comment->comment_karma && ($highLevelUser || $isCurrentUserCanEdit)) {
                $messageArray['code'] = 1;
                $messageArray['message'] = $form->renderEditFrontCommentForm($comment);
            } else {
                $messageArray['code'] = 'wc_comment_edit_not_possible';
            }
        } else {
            $messageArray['code'] = 'wc_comment_edit_not_possible';
        }

        wp_die(json_encode($messageArray));
    }

    /**
     * save edited comment via ajax
     */
    public function saveEditedComment() {
        $messageArray = array('code' => 0);
        $commentId = isset($_POST['commentId']) ? intval($_POST['commentId']) : 0;
        $trimmedContent = isset($_POST['wc_comment']) ? trim($_POST['wc_comment']) : '';
        if (!$trimmedContent) {
            $messageArray['code'] = 'wc_msg_required_fields';
            wp_die(json_encode($messageArray));
        }
        if ($commentId) {
            $comment = get_comment($commentId);
            $currentUser = WpdiscuzHelper::getCurrentUser();
            $uniqueId = $comment->comment_ID . '_' . $comment->comment_parent;
            $highLevelUser = current_user_can('moderate_comments');
            $isCurrentUserCanEdit = $this->helper->isCommentEditable($comment) && $this->helper->canUserEditComment($comment, $currentUser);
            if (!$comment->comment_karma && ($highLevelUser || $isCurrentUserCanEdit)) {
                $isInRange = $this->helper->isContentInRange($trimmedContent);

                if (!$isInRange && !$highLevelUser) {
                    $commentMinLength = intval($this->optionsSerialized->commentTextMinLength);
                    $commentMaxLength = intval($this->optionsSerialized->commentTextMaxLength);
                    $contentLength = function_exists('mb_strlen') ? mb_strlen($trimmedContent) : strlen($trimmedContent);
                    if ($commentMinLength > 0 && $contentLength < $commentMinLength) {
                        $messageArray['code'] = 'wc_msg_input_min_length';
                        wp_die(json_encode($messageArray));
                    }

                    if ($commentMaxLength > 0 && $contentLength > $commentMaxLength) {
                        $messageArray['code'] = 'wc_msg_input_max_length';
                        wp_die(json_encode($messageArray));
                    }
                }

                if ($isInRange || $highLevelUser) {
                    $form = $this->wpdiscuzForm->getForm($comment->comment_post_ID);
                    $form->initFormFields();
                    $form->validateFields($currentUser);
                    $messageArray['code'] = 1;
                    if ($trimmedContent != $comment->comment_content) {
                        $trimmedContent = $this->helper->replaceCommentContentCode($trimmedContent);
                        $commentContent = $this->helper->filterCommentText($trimmedContent);
                        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
                        $commentarr = array(
                            'comment_ID' => $commentId,
                            'comment_content' => $commentContent,
                            'comment_agent' => $userAgent,
                            'comment_approved' => $comment->comment_approved
                        );
                        wp_update_comment(wp_slash($commentarr));
                    }

                    $form->saveCommentMeta($comment->comment_ID);
                    $commentContent = isset($commentContent) ? $commentContent : $trimmedContent;
                    $commentContent = apply_filters('wpdiscuz_before_comment_text', $commentContent, $comment);
                    if ($this->optionsSerialized->enableImageConversion) {
                        $commentContent = $this->helper->makeClickable($commentContent);
                    }
                    $commentContent = apply_filters('comment_text', $commentContent, $comment);
                    $commentReadMoreLimit = $this->optionsSerialized->commentReadMoreLimit;
                    if (strstr($commentContent, '[/spoiler]')) {
                        $commentReadMoreLimit = 0;
                        $commentContent = WpdiscuzHelper::spoiler($commentContent);
                    }
                    if ($commentReadMoreLimit && count(explode(' ', strip_tags($commentContent))) > $commentReadMoreLimit) {
                        $commentContent = WpdiscuzHelper::getCommentExcerpt($commentContent, $uniqueId, $this->optionsSerialized);
                    }

                    $commentHtml = '<div class="wc-comment-text">' . $commentContent;
                    if ($comment->comment_approved === '0') {
                        $commentHtml .= '<p class="wc_held_for_moderate">' . $this->optionsSerialized->phrases['wc_held_for_moderate'] . '</p>';
                    }
                    $commentHtml .= '</div>';
                    if ($this->optionsSerialized->enableTwitterShare) {
                        $commentLink = get_comment_link($comment);
                        $messageArray['twitterShareLink'] = 'https://twitter.com/intent/tweet?text=' . $this->helper->getTwitterShareContent($commentHtml, $commentLink) . '&url=' . urlencode($commentLink);
                    }
                    $form->renderFrontCommentMetaHtml($comment->comment_ID, $commentHtml);
                    $messageArray['message'] = $commentHtml;
                }
            } else {
                $messageArray['code'] = 'wc_comment_edit_not_possible';
            }
        }
        $messageArray['callbackFunctions'] = array();
        $messageArray = apply_filters('wpdiscuz_comment_edit_save', $messageArray);
        wp_die(json_encode($messageArray));
    }

    /**
     * Gets single comment with its full thread and displays in comment list
     */
    public function getSingleComment() {
        $response = array('code' => 0);
        $commentId = isset($_POST['commentId']) ? intval($_POST['commentId']) : 0;
        $comment = get_comment($commentId);
        $postId = isset($_POST['postId']) ? intval($_POST['postId']) : 0;
        if ($commentId && $postId && $comment && $comment->comment_post_ID == $postId) {
            $commentListArgs = $this->getCommentListArgs($postId);
            $this->commentsArgs = $this->getDefaultCommentsArgs($postId);
            if ($comment->comment_approved === '1' || ($comment->comment_approved === '0' && $commentListArgs['high_level_user'])) {
                $commentStatusIn = array('1');
                if ($this->commentsArgs['status'] === 'all') {
                    $commentStatusIn[] = '0';
                }
                if ($parentComment = $this->helperOptimization->getCommentRoot($commentId, $commentStatusIn)) {
                    $tree = $parentComment->get_children(array(
                        'format' => 'flat',
                        'status' => $this->commentsArgs['status'],
                        'orderby' => $this->commentsArgs['orderby']
                    ));
                    $comments = array_merge(array($parentComment), $tree);

                    $commentListArgs['isSingle'] = true;
                    $commentListArgs['new_loaded_class'] = 'wc-new-loaded-comment';
                    $response['message'] = wp_list_comments($commentListArgs, $comments);
                    $response['parentCommentID'] = $parentComment->comment_ID;
                    $response['callbackFunctions'] = array();
                    $response = apply_filters('wpdiscuz_image_callbacks', $response);
                }
            }
        }
        wp_die(json_encode($response));
    }

    /**
     * redirect first commenter to the selected page from options
     */
    public function redirect() {
        $messageArray = array('code' => 0);
        $commentId = isset($_POST['commentId']) ? intval($_POST['commentId']) : 0;
        if ($this->optionsSerialized->redirectPage && $commentId) {
            $comment = get_comment($commentId);
            if ($comment->comment_ID) {
                $userCommentCount = get_comments(array('author_email' => $comment->comment_author_email, 'count' => true));
                if ($userCommentCount == 1) {
                    $messageArray['code'] = 1;
                    $messageArray['redirect_to'] = get_permalink($this->optionsSerialized->redirectPage);
                }
            }
        }
        $this->commentsArgs['caller'] = '';
        wp_die(json_encode($messageArray));
    }

    public function loadMoreComments() {
        $postId = isset($_POST['postId']) ? intval($_POST['postId']) : 0;
        $lastParentId = isset($_POST['lastParentId']) ? intval($_POST['lastParentId']) : 0;
        if ($lastParentId >= 0 && $postId) {
            $limit = ($this->optionsSerialized->commentListLoadType == 1) ? 0 : $this->optionsSerialized->wordpressCommentPerPage;
            $args = array('limit' => $limit);
            $orderBy = isset($_POST['orderBy']) ? trim($_POST['orderBy']) : '';
            $args['offset'] = isset($_POST['offset']) && trim($_POST['offset']) ? intval($_POST['offset']) * $this->optionsSerialized->wordpressCommentPerPage : 0;
            if ($orderBy == 'by_vote') {
                $args['orderby'] = $orderBy;
            } else {
                $args['order'] = isset($_POST['order']) && trim($_POST['order']) ? trim($_POST['order']) : $this->optionsSerialized->wordpressCommentOrder;
                $args['last_parent_id'] = $lastParentId;
            }
            $args['post_id'] = $postId;
            $commentData = $this->getWPComments($args);
            $commentData['loadLastCommentId'] = $this->dbManager->getLastCommentId($this->commentsArgs);
            $commentData['callbackFunctions'] = array();
            $commentData = apply_filters('wpdiscuz_image_callbacks', $commentData);
            wp_die(json_encode($commentData));
        }
    }

    public function voteOnComment() {
        $messageArray = array('code' => 0);
        if ($this->optionsSerialized->votingButtonsShowHide) {
            wp_die(json_encode($messageArray));
        }
        $isUserLoggedIn = is_user_logged_in();
        if (!$this->optionsSerialized->isGuestCanVote && !$isUserLoggedIn) {
            $messageArray['code'] = 'wc_login_to_vote';
            wp_die(json_encode($messageArray));
        }

        $commentId = isset($_POST['commentId']) ? intval($_POST['commentId']) : 0;
        $voteType = isset($_POST['voteType']) ? intval($_POST['voteType']) : 0;

        if ($commentId && $voteType) {
            if ($isUserLoggedIn) {
                $userIdOrIp = get_current_user_id();
            } else {
                $userIdOrIp = md5($this->helper->getRealIPAddr());
            }
            $isUserVoted = $this->dbManager->isUserVoted($userIdOrIp, $commentId);
            $comment = get_comment($commentId);
            if (!$isUserLoggedIn && md5($comment->comment_author_IP) == $userIdOrIp) {
                $messageArray['code'] = 'wc_deny_voting_from_same_ip';
                wp_die(json_encode($messageArray));
            }
            if ($comment->user_id == $userIdOrIp) {
                $messageArray['code'] = 'wc_self_vote';
                wp_die(json_encode($messageArray));
            }

            if ($isUserVoted != '') {
                $vote = intval($isUserVoted) + $voteType;

                if ($vote >= -1 && $vote <= 1) {
                    $this->dbManager->updateVoteType($userIdOrIp, $commentId, $vote);
                    $voteCount = intval(get_comment_meta($commentId, self::META_KEY_VOTES, true)) + $voteType;
                    update_comment_meta($commentId, self::META_KEY_VOTES, '' . $voteCount);
                    do_action('wpdiscuz_update_vote', $voteType, $isUserVoted, $comment);
                    $messageArray['code'] = 1;
                    $messageArray['buttonsStyle'] = 'total';
                    if ($this->optionsSerialized->votingButtonsStyle) {
                        $messageArray['buttonsStyle'] = 'separate';
                        $messageArray['likeCount'] = $this->dbManager->getLikeCount($commentId);
                        $messageArray['dislikeCount'] = $this->dbManager->getDislikeCount($commentId);
                    }
                } else {
                    $messageArray['code'] = 'wc_vote_only_one_time';
                }
            } else {
                $this->dbManager->addVoteType($userIdOrIp, $commentId, $voteType, intval($isUserLoggedIn));
                $voteCount = intval(get_comment_meta($commentId, self::META_KEY_VOTES, true)) + $voteType;
                update_comment_meta($commentId, self::META_KEY_VOTES, '' . $voteCount);
                do_action('wpdiscuz_add_vote', $voteType, $comment);
                $messageArray['code'] = 1;
                $messageArray['buttonsStyle'] = 'total';
                if ($this->optionsSerialized->votingButtonsStyle) {
                    $messageArray['buttonsStyle'] = 'separate';
                    $messageArray['likeCount'] = $this->dbManager->getLikeCount($commentId);
                    $messageArray['dislikeCount'] = $this->dbManager->getDislikeCount($commentId);
                }
            }
        } else {
            $messageArray['code'] = 'wc_voting_error';
        }
        $messageArray['callbackFunctions'] = array();
        $messageArray = apply_filters('wpdiscuz_comment_vote', $messageArray);
        wp_die(json_encode($messageArray));
    }

    public function sorting() {
        $messageArray = array('code' => 0);
        $postId = isset($_POST['postId']) ? intval($_POST['postId']) : 0;
        $orderBy = isset($_POST['orderBy']) ? trim($_POST['orderBy']) : '';
        $order = isset($_POST['order']) ? trim($_POST['order']) : '';

        if ($postId && $orderBy && $order) {
            $args = array('order' => $order, 'post_id' => $postId);
            if (in_array($orderBy, array('by_vote', 'comment_date_gmt'))) {
                $args['orderby'] = $orderBy;
                if ($orderBy == 'by_vote') {
                    if ($this->optionsSerialized->wordpressCommentOrder == 'asc') {
                        $args['order'] = 'asc';
                    }
                }
            } else {
                $args['orderby'] = 'comment_date_gmt';
            }
            $args['first_load'] = 1;
            $commentData = $this->getWPComments($args);
            $messageArray['code'] = 1;
            $messageArray['loadCount'] = 1;
            $messageArray['last_parent_id'] = $commentData['last_parent_id'];
            $messageArray['is_show_load_more'] = $commentData['is_show_load_more'];
            $messageArray['message'] = $commentData['comment_list'];
            $messageArray['callbackFunctions'] = array();
            $messageArray = apply_filters('wpdiscuz_image_callbacks', $messageArray);
        }
        wp_die(json_encode($messageArray));
    }

    /**
     * loads the comment content on click via ajax
     */
    public function readMore() {
        $response = array('code' => 0);
        $commentId = isset($_POST['commentId']) ? intval($_POST['commentId']) : 0;
        if ($commentId) {
            $comment = get_comment($commentId);
            $commentContent = $this->helper->filterCommentText($comment->comment_content);
            $commentContent = apply_filters('wpdiscuz_before_comment_text', $commentContent, $comment);
            if ($this->optionsSerialized->enableImageConversion) {
                $commentContent = $this->helper->makeClickable($commentContent);
            }
            $commentContent = apply_filters('comment_text', $commentContent, $comment);
            $response['code'] = 1;
            $response['message'] = $commentContent;
            $response['callbackFunctions'] = array();
            $response = apply_filters('wpdiscuz_image_callbacks', $response);
        } else {
            $response['message'] = 'error';
        }
        wp_die(json_encode($response));
    }

    /**
     * get comments by comment type
     */
    public function getWPComments($args = array()) {
        global $post;
        $postId = !empty($post->ID) ? $post->ID : $args['post_id'];
        $defaults = $this->getDefaultCommentsArgs($postId);
        $this->commentsArgs = wp_parse_args($args, $defaults);
        $commentListArgs = $this->getCommentListArgs($this->commentsArgs['post_id']);
        do_action('wpdiscuz_before_getcomments', $this->commentsArgs, $commentListArgs['current_user'], $args);
        $commentData = array();
        $commentList = $this->_getWPComments($commentListArgs, $commentData);
        $wcWpComments = wp_list_comments($commentListArgs, $commentList);
        $commentData['comment_list'] = $wcWpComments;
        $this->commentsArgs['caller'] = '';
        if ($this->cache->doGravatarsCache && $this->cache->gravatars) {
            $this->dbManager->addGravatars($this->cache->gravatars);
        }
        return $commentData;
    }

    public function _getWPComments(&$commentListArgs, &$commentData) {
        $commentList = array();
        if ($this->optionsSerialized->wordpressIsPaginate) {// PAGINATION
            $page = get_query_var('cpage');
            $this->commentsArgs['number'] = $this->optionsSerialized->wordpressCommentPerPage;
            $this->commentsArgs['order'] = 'asc';
            $this->commentsArgs['caller'] = '';
            if ($this->optionsSerialized->wordpressThreadComments) {
                $this->commentsArgs['parent'] = 0;
            }

            if ($page) {
                $this->commentsArgs['offset'] = ($page - 1) * $this->optionsSerialized->wordpressCommentPerPage;
            } else if ($this->optionsSerialized->wordpressDefaultCommentsPage == 'oldest') {
                $this->commentsArgs['offset'] = 0;
            }

            $commentListArgs['page'] = 0;
            $commentListArgs['per_page'] = 0;
            $commentListArgs['reverse_top_level'] = ($this->optionsSerialized->wordpressCommentOrder == 'desc');

            $parentComments = get_comments($this->commentsArgs);

            if ($this->optionsSerialized->wordpressThreadComments) {
                foreach ($parentComments as $parentComment) {
                    $commentList[] = $parentComment;
                    $children = $parentComment->get_children(array(
                        'format' => 'flat',
                        'status' => $this->commentsArgs['status'],
                        'orderby' => $this->commentsArgs['orderby']
                    ));
                    if ($this->optionsSerialized->isLoadOnlyParentComments) {
                        $commentListArgs['wpdiscuz_child_count_' . $parentComment->comment_ID] = count($children);
                    } else {
                        $commentList = array_merge($commentList, $children);
                    }
                }
            } else {
                $commentList = $parentComments;
            }

            $this->getStickyComments(true, $commentList, $commentListArgs);
        } else {
            $this->commentsArgs['comment__in'] = $this->dbManager->getRootCommentIds($this->commentsArgs);
            $commentData['last_parent_id'] = $this->commentsArgs['comment__in'] ? $this->commentsArgs['comment__in'][count($this->commentsArgs['comment__in']) - 1] : 0;
            $commentData['is_show_load_more'] = $this->dbManager->isShowLoadMore;
            if ($this->optionsSerialized->wordpressThreadComments) {
                if ($this->commentsArgs['comment__in']) {
                    $parentComments = get_comments($this->commentsArgs);
                    foreach ($parentComments as $parentComment) {
                        $commentList[] = $parentComment;
                        $children = $parentComment->get_children(array(
                            'format' => 'flat',
                            'status' => $this->commentsArgs['status'],
                            'orderby' => $this->commentsArgs['orderby']
                        ));
                        if ($this->optionsSerialized->isLoadOnlyParentComments) {
                            $commentListArgs['wpdiscuz_child_count_' . $parentComment->comment_ID] = count($children);
                        } else {
                            $commentList = array_merge($commentList, $children);
                        }
                    }
                }
            } else {
                $commentList = get_comments($this->commentsArgs);
            }

            $this->getStickyComments(false, $commentList, $commentListArgs);
            $commentListArgs['page'] = 1;
            $commentListArgs['last_parent_id'] = $commentData['last_parent_id'];
        }
        return $commentList;
    }

    public function commentsTemplateQueryArgs($args) {
        global $post;
        if ($this->isWpdiscuzLoaded) {
            if ($this->optionsSerialized->wordpressIsPaginate) {
                $args['caller'] = 'wpdiscuz';
            } else {
                $args['post__not_in'] = $post->ID;
            }
        }
        return $args;
    }

    public function preGetComments($queryObj) {
        if ($this->commentsArgs['caller'] === 'wpdiscuz-') {
            $vars = $queryObj->query_vars;
            $vars['comment__in'] = '';
            $queryObj->query_vars = $vars;
        }
    }

    public function foundCommentsQuery($q, $qObj) {
        if (isset($qObj->query_vars['caller']) && $qObj->query_vars['caller'] == 'wpdiscuz') {
            global $wpdb, $post, $user_ID;
            $commenter = wp_get_current_commenter();
            $where = "WHERE comment_approved = '1'";
            $where .= " AND comment_post_ID = {$post->ID}";
            if ($this->optionsSerialized->wordpressThreadComments) {
                $where .= " AND comment_parent = 0";
            }
            $typesNotIn = array(self::WPDISCUZ_STICKY_COMMENT);
            $typesNotIn = apply_filters('wpdiscuz_found_comments_query', $typesNotIn);
            foreach ($typesNotIn as &$type) {
                $type = esc_sql($type);
            }
            $where .= " AND comment_type NOT IN ('" . implode("','", $typesNotIn) . "')";
            $q = "SELECT COUNT(*) FROM {$wpdb->comments} $where";
        }
        return $q;
    }

    /**
     * add comments clauses
     * add new orderby clause when sort type is vote and wordpress commnts order is older (ASC)
     */
    public function commentsClauses($args) {
        global $wpdb;
        if ($this->commentsArgs['caller'] === 'wpdiscuz' && ($this->commentsArgs['comment__in'] || isset($this->commentsArgs['sticky']))) {
            $orderby = '';
            $args['caller'] = $this->commentsArgs['caller'] = 'wpdiscuz-';
            if (!$this->optionsSerialized->votingButtonsShowHide && $this->commentsArgs['orderby'] == 'by_vote') {
                $args['join'] .= " LEFT JOIN " . $wpdb->commentmeta . " ON " . $wpdb->comments . ".comment_ID = " . $wpdb->commentmeta . ".comment_id  AND (" . $wpdb->commentmeta . ".meta_key = '" . self::META_KEY_VOTES . "')";
                $orderby = " IFNULL(" . $wpdb->commentmeta . ".meta_value,0)+0 DESC, ";
            }
            $args['orderby'] = $orderby . $wpdb->comments . '.comment_date_gmt ';
            $args['orderby'] .= isset($args['order']) ? '' : $this->commentsArgs['order'];
        }
        return $args;
    }

    public function getDefaultCommentsArgs($postId = 0) {
        global $user_ID;
        $commenter = wp_get_current_commenter();
        $args = array(
            'caller' => 'wpdiscuz',
            'post_id' => intval($postId),
            'offset' => 0,
            'last_parent_id' => 0,
            'orderby' => 'comment_date_gmt',
            'order' => $this->optionsSerialized->wordpressCommentOrder,
            'date_order' => $this->optionsSerialized->wordpressCommentOrder,
            'limit' => $this->optionsSerialized->commentListLoadType == 3 ? 0 : $this->optionsSerialized->wordpressCommentPerPage,
            'is_threaded' => $this->optionsSerialized->wordpressThreadComments,
            'status' => !$this->optionsSerialized->wordpressIsPaginate && current_user_can('moderate_comments') ? 'all' : 'approve',
            'comment__in' => '',
            'update_comment_meta_cache' => false,
            'no_found_rows' => false,
            'type__not_in' => array(self::WPDISCUZ_STICKY_COMMENT),
        );
        if (!$this->optionsSerialized->wordpressIsPaginate) {
            if ($user_ID) {
                $args['include_unapproved'] = array($user_ID);
            } elseif (!empty($commenter['comment_author_email'])) {
                $args['include_unapproved'] = array($commenter['comment_author_email']);
            }
        }
        return apply_filters('wpdiscuz_comments_args', $args);
    }

    /**
     * register options page for plugin
     */
    public function addPluginOptionsPage() {
        add_submenu_page('edit-comments.php', 'WPDISCUZ', 'WPDISCUZ', 'manage_options', '#', '');
        add_submenu_page('edit-comments.php', '&raquo; ' . __('Settings', 'wpdiscuz'), '&raquo; ' . __('Settings', 'wpdiscuz'), 'manage_options', self::PAGE_SETTINGS, array(&$this->options, 'mainOptionsForm'));
        if (!$this->optionsSerialized->isUsePoMo) {
            add_submenu_page('edit-comments.php', '&raquo; ' . __('Phrases', 'wpdiscuz'), '&raquo; ' . __('Phrases', 'wpdiscuz'), 'manage_options', self::PAGE_PHRASES, array(&$this->options, 'phrasesOptionsForm'));
        }
        add_submenu_page('edit-comments.php', '&raquo; ' . __('Tools', 'wpdiscuz'), '&raquo; ' . __('Tools', 'wpdiscuz'), 'manage_options', self::PAGE_TOOLS, array(&$this->options, 'tools'));
        add_submenu_page('edit-comments.php', '&raquo; ' . __('Addons', 'wpdiscuz'), '&raquo; ' . __('Addons', 'wpdiscuz'), 'manage_options', self::PAGE_ADDONS, array(&$this->options, 'addons'));
    }

    /**
     * Scripts and styles registration on administration pages
     */
    public function adminPageStylesScripts() {
        global $typenow, $pagenow;
        $wp_version = get_bloginfo('version');
        $wpdiscuzPages = apply_filters('wpdiscuz_admin_pages', array(self::PAGE_SETTINGS, self::PAGE_PHRASES, self::PAGE_TOOLS, self::PAGE_ADDONS));
        wp_register_style('wpdiscuz-font-awesome', plugins_url(WPDISCUZ_DIR_NAME . '/assets/third-party/font-awesome-5.0.6/css/fontawesome-all.min.css'), null, $this->version);
        if ((isset($_GET['page']) && in_array($_GET['page'], $wpdiscuzPages)) || ($typenow == 'wpdiscuz_form') || $pagenow == 'edit-comments.php' || $pagenow == 'comment.php') {
            $args = array(
                'msgConfirmResetOptions' => __('Do you really want to reset all options?', 'wpdiscuz'),
                'msgConfirmRemoveVotes' => __('Do you really want to remove voting data?', 'wpdiscuz'),
                'msgConfirmResetPhrases' => __('Do you really want to reset phrases?', 'wpdiscuz'),
                'msgConfirmPurgeGravatarsCache' => __('Do you really want to delete gravatars cache?', 'wpdiscuz'),
                'msgConfirmPurgeStatisticsCache' => __('Do you really want to delete statistics cache?', 'wpdiscuz'),
            );

            wp_enqueue_style('wpdiscuz-font-awesome');
            wp_register_style('wpdiscuz-cp-index-css', plugins_url(WPDISCUZ_DIR_NAME . '/assets/third-party/colorpicker/css/index.css'), null, $this->version);
            wp_enqueue_style('wpdiscuz-cp-index-css');
            wp_register_style('wpdiscuz-cp-compatibility-css', plugins_url(WPDISCUZ_DIR_NAME . '/assets/third-party/colorpicker/css/compatibility.css'), null, $this->version);
            wp_enqueue_style('wpdiscuz-cp-compatibility-css');
            wp_register_script('wpdiscuz-cp-colors-js', plugins_url(WPDISCUZ_DIR_NAME . '/assets/third-party/colorpicker/js/colors.js'), array('jquery'), $this->version, false);
            wp_enqueue_script('wpdiscuz-cp-colors-js');
            wp_register_script('wpdiscuz-cp-colorpicker-js', plugins_url(WPDISCUZ_DIR_NAME . '/assets/third-party/colorpicker/js/jqColorPicker.min.js'), array('jquery'), $this->version, false);
            wp_enqueue_script('wpdiscuz-cp-colorpicker-js');
            wp_register_script('wpdiscuz-cp-index-js', plugins_url(WPDISCUZ_DIR_NAME . '/assets/third-party/colorpicker/js/index.js'), array('jquery'), $this->version, false);
            wp_enqueue_script('wpdiscuz-cp-index-js');
            wp_register_style('wpdiscuz-easy-responsive-tabs-css', plugins_url(WPDISCUZ_DIR_NAME . '/assets/third-party/easy-responsive-tabs/css/easy-responsive-tabs.min.css'), null, $this->version);
            wp_enqueue_style('wpdiscuz-easy-responsive-tabs-css');
            wp_register_script('wpdiscuz-easy-responsive-tabs-js', plugins_url(WPDISCUZ_DIR_NAME . '/assets/third-party/easy-responsive-tabs/js/easy-responsive-tabs.js'), array('jquery'), $this->version, true);
            wp_enqueue_script('wpdiscuz-easy-responsive-tabs-js');
            wp_register_style('wpdiscuz-options-css', plugins_url(WPDISCUZ_DIR_NAME . '/assets/css/options.css'), null, $this->version);
            wp_enqueue_style('wpdiscuz-options-css');
            wp_register_script('wpdiscuz-options-js', plugins_url(WPDISCUZ_DIR_NAME . '/assets/js/wpdiscuz-options.js'), array('jquery'), $this->version);
            wp_enqueue_script('wpdiscuz-options-js');
            wp_localize_script('wpdiscuz-options-js', 'wpdiscuzObj', $args);
            wp_enqueue_script('thickbox');
            wp_register_script('wpdiscuz-jquery-cookie', plugins_url(WPDISCUZ_DIR_NAME . '/assets/third-party/wpdcookiejs/customcookie.js'), array('jquery'), $this->version, true);
            wp_enqueue_script('wpdiscuz-jquery-cookie');
            wp_register_script('wpdiscuz-contenthover', plugins_url(WPDISCUZ_DIR_NAME . '/assets/third-party/contenthover/jquery.contenthover.min.js'), array('jquery'), $this->version, true);
            wp_enqueue_script('wpdiscuz-contenthover');
        }
        if (version_compare($wp_version, '4.2.0', '>=')) {
            wp_register_script('wpdiscuz-addon-notes', plugins_url(WPDISCUZ_DIR_NAME . '/assets/js/wpdiscuz-notes.js'), array('jquery'), $this->version, true);
            wp_enqueue_script('wpdiscuz-addon-notes');
        }

        if (!get_option(self::OPTION_SLUG_DEACTIVATION) && (strpos($this->requestUri, '/plugins.php') !== false)) {
            $reasonArgs = array(
                'msgReasonRequired' => __('Please check one of reasons before sending feedback!', 'wpdiscuz'),
                'msgReasonDescRequired' => __('Please provide more information', 'wpdiscuz'),
                'adminUrl' => get_admin_url()
            );
            wp_register_style('wpdiscuz-lity-css', plugins_url(WPDISCUZ_DIR_NAME . '/assets/third-party/lity/lity.css'), null, $this->version);
            wp_enqueue_style('wpdiscuz-lity-css');
            wp_register_script('wpdiscuz-lity-js', plugins_url(WPDISCUZ_DIR_NAME . '/assets/third-party/lity/lity.js'), array('jquery'), $this->version);
            wp_enqueue_script('wpdiscuz-lity-js');
            wp_register_style('wpdiscuz-deactivation-css', plugins_url(WPDISCUZ_DIR_NAME . '/assets/css/wpdiscuz-deactivation.css'));
            wp_enqueue_style('wpdiscuz-deactivation-css');
            wp_register_script('wpdiscuz-deactivation-js', plugins_url(WPDISCUZ_DIR_NAME . '/assets/js/wpdiscuz-deactivation.js'), array('jquery'), $this->version);
            wp_enqueue_script('wpdiscuz-deactivation-js');
            wp_localize_script('wpdiscuz-deactivation-js', 'deactivationObj', $reasonArgs);
        }
    }

    /**
     * Styles and scripts registration to use on front page
     */
    public function frontEndStylesScripts() {
        global $post;
        $this->isWpdiscuzLoaded = $this->helper->isLoadWpdiscuz($post);
        wp_register_style('wpdiscuz-font-awesome', plugins_url(WPDISCUZ_DIR_NAME . '/assets/third-party/font-awesome-5.0.6/css/fontawesome-all.min.css'), null, $this->version);
        wp_register_style('wpdiscuz-ratings', plugins_url(WPDISCUZ_DIR_NAME . '/assets/css/wpdiscuz-ratings.css'), null, $this->version);
        wp_register_style('wpdiscuz-ratings-rtl', plugins_url(WPDISCUZ_DIR_NAME . '/assets/css/wpdiscuz-ratings-rtl.css'), null, $this->version);

        if (!$this->isWpdiscuzLoaded && $this->optionsSerialized->ratingCssOnNoneSingular) {
            if (!$this->optionsSerialized->disableFontAwesome) {
                wp_enqueue_style('wpdiscuz-font-awesome');
            }
            wp_enqueue_style('wpdiscuz-ratings');
            if (is_rtl()) {
                wp_enqueue_style('wpdiscuz-ratings-rtl');
            }
        }

        if ($this->isWpdiscuzLoaded) {
            if (!$this->optionsSerialized->disableFontAwesome) {
                wp_enqueue_style('wpdiscuz-font-awesome');
            }

            if (is_rtl()) {
                wp_register_style('wpdiscuz-frontend-rtl-css', plugins_url(WPDISCUZ_DIR_NAME . '/assets/css/wpdiscuz-rtl.css'), null, $this->version);
                wp_enqueue_style('wpdiscuz-frontend-rtl-css');
            } else {
                $this->helper->registerWpDiscuzStyle($this->version);
                wp_enqueue_style('wpdiscuz-frontend-css');
            }

            wp_register_script('wpdiscuz-cookie-js', plugins_url(WPDISCUZ_DIR_NAME . '/assets/third-party/wpdcookiejs/customcookie.js'), array('jquery'), $this->version, $this->optionsSerialized->isLoadScriptsInFooter);
            wp_enqueue_script('wpdiscuz-cookie-js');
            wp_register_script('autogrowtextarea-js', plugins_url(WPDISCUZ_DIR_NAME . '/assets/third-party/autogrow/jquery.autogrowtextarea.min.js'), array('jquery'), $this->version, $this->optionsSerialized->isLoadScriptsInFooter);
            wp_enqueue_script('autogrowtextarea-js');
            $form = $this->wpdiscuzForm->getForm($post->ID);
            $form->initFormMeta();
            $this->wpdiscuzOptionsJs = $this->optionsSerialized->getOptionsForJs();
            $this->wpdiscuzOptionsJs['version'] = $this->version;
            $this->wpdiscuzOptionsJs['wc_post_id'] = $post->ID;
            $this->wpdiscuzOptionsJs['loadLastCommentId'] = 0;
            $this->wpdiscuzOptionsJs['lastVisitKey'] = self::COOKIE_LAST_VISIT;
            if ($this->optionsSerialized->enableLastVisitCookie) {
                $this->wpdiscuzOptionsJs['lastVisitExpires'] = current_time('timestamp') + MONTH_IN_SECONDS;
                $this->wpdiscuzOptionsJs['lastVisitCookie'] = $this->getLastVisitCookie();
            }
            $this->wpdiscuzOptionsJs['isCookiesEnabled'] = has_action('set_comment_cookies');
            if ($this->optionsSerialized->commentListUpdateType) {
                $cArgs = $this->getDefaultCommentsArgs($post->ID);
                $this->wpdiscuzOptionsJs['loadLastCommentId'] = $this->dbManager->getLastCommentId($cArgs);
            }
            $this->wpdiscuzOptionsJs = apply_filters('wpdiscuz_js_options', $this->wpdiscuzOptionsJs, $this->optionsSerialized);
            wp_enqueue_script('jquery-form');
            wp_register_script('wpdiscuz-ajax-js', plugins_url(WPDISCUZ_DIR_NAME . '/assets/js/wpdiscuz.js'), array('jquery'), $this->version, $this->optionsSerialized->isLoadScriptsInFooter);
            wp_enqueue_script('wpdiscuz-ajax-js');
            wp_localize_script('wpdiscuz-ajax-js', 'wpdiscuzAjaxObj', array('url' => admin_url('admin-ajax.php'), 'customAjaxUrl' => plugins_url(WPDISCUZ_DIR_NAME . '/utils/ajax/wpdiscuz-ajax.php'), 'wpdiscuz_options' => $this->wpdiscuzOptionsJs));

            if ($this->optionsSerialized->isQuickTagsEnabled) {
                wp_enqueue_script('quicktags');
                wp_register_script('wpdiscuz-quicktags', plugins_url('/assets/third-party/quicktags/wpdiscuz-quictags.js', __FILE__), null, $this->version, $this->optionsSerialized->isLoadScriptsInFooter);
                wp_enqueue_script('wpdiscuz-quicktags');
            }

            if (!$this->optionsSerialized->hideUserSettingsButton) {
                $ucArgs = array(
                    'msgConfirmDeleteComment' => $this->optionsSerialized->phrases['wc_confirm_comment_delete'],
                    'msgConfirmCancelSubscription' => $this->optionsSerialized->phrases['wc_confirm_cancel_subscription'],
                    'msgConfirmCancelFollow' => $this->optionsSerialized->phrases['wc_confirm_cancel_follow'],
                );
                wp_register_style('wpdiscuz-user-content-css', plugins_url(WPDISCUZ_DIR_NAME . '/assets/css/wpdiscuz-user-content.css'), null, $this->version);
                wp_enqueue_style('wpdiscuz-user-content-css');
                wp_register_script('wpdiscuz-user-content-js', plugins_url(WPDISCUZ_DIR_NAME . '/assets/js/wpdiscuz-user-content.js'), array('jquery'), $this->version, $this->optionsSerialized->isLoadScriptsInFooter);
                wp_enqueue_script('wpdiscuz-user-content-js');
                wp_localize_script('wpdiscuz-user-content-js', 'wpdiscuzUCObj', $ucArgs);
                wp_register_script('wpdiscuz-lity-js', plugins_url(WPDISCUZ_DIR_NAME . '/assets/third-party/lity/lity.js'), array('jquery'), $this->version, $this->optionsSerialized->isLoadScriptsInFooter);
                wp_enqueue_script('wpdiscuz-lity-js');
            }

            do_action('wpdiscuz_front_scripts', $this->optionsSerialized);
        }
    }

    public function pluginNewVersion() {
        $pluginData = get_plugin_data(__FILE__);
        if (version_compare($pluginData['Version'], $this->version, '>')) {
            $this->dbManager->createEmailNotificationTable();
            $this->dbManager->createAvatarsCacheTable();
            $this->dbManager->createFollowUsersTable();
            $this->wpdiscuzForm->createDefaultForm($this->version);
            $options = $this->changeOldOptions(get_option(self::OPTION_SLUG_OPTIONS), $pluginData);
            $this->addNewOptions($options);
            $this->addNewPhrases();
            update_option(self::OPTION_SLUG_VERSION, $pluginData['Version']);

            if (version_compare($this->version, '2.1.2', '<=') && version_compare($this->version, '1.0.0', '!=')) {
                $this->dbManager->alterPhrasesTable();
            }

            if (version_compare($this->version, '2.1.7', '<=') && version_compare($this->version, '1.0.0', '!=')) {
                $this->dbManager->alterVotingTable();
            }

            if (version_compare($this->version, '5.1.2', '<=')) {
                $this->dbManager->deleteOldStatisticCaches();
            }

            $this->dbManager->alterNotificationTable($this->version);
        }
        do_action('wpdiscuz_check_version');
    }

    /**
     * merge old and new options
     */
    private function addNewOptions($options) {
        $this->optionsSerialized->initOptions($options);
        $wc_new_options = $this->optionsSerialized->toArray();
        update_option(self::OPTION_SLUG_OPTIONS, serialize($wc_new_options));
    }

    /**
     * merge old and new phrases
     */
    private function addNewPhrases() {
        if ($this->dbManager->isPhraseExists('wc_be_the_first_text')) {
            $wc_saved_phrases = $this->dbManager->getPhrases();
            $this->optionsSerialized->initPhrases();
            $wc_phrases = $this->optionsSerialized->phrases;
            $wc_new_phrases = array_merge($wc_phrases, $wc_saved_phrases);
            $this->dbManager->updatePhrases($wc_new_phrases);
        }
    }

    /**
     * change old options if needed
     */
    private function changeOldOptions($options, $pluginData) {
        $oldOptions = maybe_unserialize($options);
        if (isset($oldOptions['wc_comment_list_order'])) {
            update_option('comment_order', $oldOptions['wc_comment_list_order']);
        }
        if (isset($oldOptions['wc_comment_count'])) {
            update_option('comments_per_page', $oldOptions['wc_comment_count']);
        }
        if (isset($oldOptions['wc_load_all_comments'])) {
            $this->optionsSerialized->commentListLoadType = 1;
        }
        if (isset($this->optionsSerialized->disableFontAwesome) && $this->optionsSerialized->disableFontAwesome && $pluginData['Version'] == '5.0.4') {
            $this->optionsSerialized->disableFontAwesome = 0;
            $oldOptions['disableFontAwesome'] = 0;
        }

        if (version_compare($this->version, '5.2.1', '<=')) {
            $oldOptions['isNativeAjaxEnabled'] = 1;
        }
        return $oldOptions;
    }

    // Add settings link on plugin page
    public function addPluginSettingsLink($links) {
        $settingsLink = '<a href="' . admin_url() . 'edit-comments.php?page=' . self::PAGE_SETTINGS . '">' . __('Settings', 'wpdiscuz') . '</a>';
        if (!$this->optionsSerialized->isUsePoMo) {
            $settingsLink .= ' | <a href="' . admin_url() . 'edit-comments.php?page=' . self::PAGE_PHRASES . '">' . __('Phrases', 'wpdiscuz') . '</a>';
        }
        array_unshift($links, $settingsLink);
        return $links;
    }

    public function initCurrentPostType() {
        global $post;
        if ($this->isWpdiscuzLoaded) {
            $this->form = $this->wpdiscuzForm->getForm($post->ID);
            add_filter('comments_template', array(&$this, 'addCommentForm'), 9999999);
        }
    }

    public function addContentModal() {
        if ($this->isWpdiscuzLoaded) {
            $html = "<a id='wpdUserContentInfoAnchor' style='display:none;' rel='#wpdUserContentInfo' data-wpd-lity>wpDiscuz</a>";
            $html .= "<div id='wpdUserContentInfo' style='overflow:auto;background:#FDFDF6;padding:20px;width:600px;max-width:100%;border-radius:6px;' class='lity-hide'></div>";
            echo $html;
        }
    }

    public function getLastVisitCookie() {
        global $post;
        $lastVisit = '';
        if ($this->isWpdiscuzLoaded) {
            $lastVisit = current_time('timestamp');
        }
        return $lastVisit;
    }

    public function addCommentForm($file) {
        $file = dirname(__FILE__) . '/templates/comment/comment-form.php';
        return $file;
    }

    private function getCommentListArgs($postId) {
        $post = get_post($postId);
        $postsAuthors = ($post->comment_count && (!$this->optionsSerialized->disableProfileURLs || !$this->optionsSerialized->authorTitlesShowHide)) ? $this->dbManager->getPostsAuthors() : array();
        $icons = $this->optionsSerialized->votingButtonsIcon ? explode('|', $this->optionsSerialized->votingButtonsIcon) : array('fa-plus', 'fa-minus');
        $currentUser = WpdiscuzHelper::getCurrentUser();
        $currentUserEmail = '';
        $isUserLoggedIn = false;
        if (!empty($currentUser->ID)) {
            $currentUserEmail = $currentUser->user_email;
            $isUserLoggedIn = true;
        } else if (isset($_COOKIE['comment_author_email_' . COOKIEHASH]) && $_COOKIE['comment_author_email_' . COOKIEHASH]) {
            $currentUserEmail = urldecode(trim($_COOKIE['comment_author_email_' . COOKIEHASH]));
        }
        $this->form = $this->wpdiscuzForm->getForm($postId);
        $high_level_user = current_user_can('moderate_comments');
        $can_stick_or_close = $post->post_author == $currentUser->ID;
        $post_permalink = get_permalink($postId);
        $args = array(
            'style' => 'div',
            'echo' => false,
            'isSingle' => false,
            'reverse_top_level' => false,
            'reverse_children' => !$this->optionsSerialized->reverseChildren,
            'post_author' => $post->post_author,
            'posts_authors' => $postsAuthors,
            'voting_icons' => array('like' => $icons[0], 'dislike' => $icons[1]),
            'high_level_user' => $high_level_user,
            'avatar_trackback' => apply_filters('wpdiscuz_avatar_trackback', plugins_url(WPDISCUZ_DIR_NAME . '/assets/img/trackback.png')),
            'wpdiscuz_gravatar_size' => apply_filters('wpdiscuz_gravatar_size', 64),
            'can_stick_or_close' => $can_stick_or_close,
            'user_follows' => $this->dbManager->getUserFollows($currentUserEmail),
            'current_user' => $currentUser,
            'current_user_email' => $currentUserEmail,
            'is_share_enabled' => $this->optionsSerialized->isShareEnabled(),
            'post_permalink' => $post_permalink,
            'can_user_reply' => comments_open($post->ID) && $this->optionsSerialized->wordpressThreadComments && (($this->form ? $this->form->isUserCanComment($currentUser, $postId) : true) || $high_level_user),
            'can_user_follow' => $this->optionsSerialized->isFollowActive && $isUserLoggedIn && !empty($currentUserEmail),
            'can_user_vote' => $currentUser->ID || $this->optionsSerialized->isGuestCanVote,
            'wc_stick_btn' => $this->optionsSerialized->enableStickButton && $high_level_user || $can_stick_or_close ? '<span class="wc_stick_btn wc-cta-button"><span class="wc_stick_text">%s</span></span>' : '',
            'wc_close_btn' => $this->optionsSerialized->enableCloseButton && $high_level_user || $can_stick_or_close ? '<span class="wc_close_btn wc-cta-button"><span class="wc_close_text">%s</span></span>' : '',
            'share_buttons' => '',
            'last_visit' => $this->optionsSerialized->enableLastVisitCookie && isset($_COOKIE[self::COOKIE_LAST_VISIT]) ? $_COOKIE[self::COOKIE_LAST_VISIT] : '',
            'walker' => $this->wpdiscuzWalker,
        );
        if ($this->optionsSerialized->enableFbShare && $this->optionsSerialized->fbAppID) {
            $args['share_buttons'] .= '<span class="wc_fb"><i class="fab fa-facebook-f wpf-cta" aria-hidden="true" title="' . $this->optionsSerialized->phrases['wc_share_facebook'] . '"></i></span>';
        }
        if ($this->optionsSerialized->enableGoogleShare) {
            $args['share_buttons'] .= '<a class="wc_go" target="_blank" href="https://plus.google.com/share?url=' . $post_permalink . '" title="' . $this->optionsSerialized->phrases['wc_share_google'] . '"><i class="fab fa-google wpf-cta" aria-hidden="true"></i></a>';
        }
        if ($this->optionsSerialized->enableVkShare) {
            $args['share_buttons'] .= '<a class="wc_vk" target="_blank" href="http://vk.com/share.php?url=' . $post_permalink . '" title="' . $this->optionsSerialized->phrases['wc_share_vk'] . '"><i class="fab fa-vk wpf-cta" aria-hidden="true"></i></a>';
        }
        if ($this->optionsSerialized->enableOkShare) {
            $args['share_buttons'] .= '<a class="wc_ok" target="_blank" href="http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1&st._surl=' . $post_permalink . '" title=""><i class="fab fa-odnoklassniki wpf-cta" aria-hidden="true"></i></a>';
        }
        return apply_filters('wpdiscuz_comment_list_args', $args);
    }

    public function addNewRoles() {
        global $wp_roles;
        $roles = $wp_roles->roles;
        $roles = apply_filters('editable_roles', $roles);
        foreach ($roles as $roleName => $roleInfo) {
            $this->optionsSerialized->blogRoles[$roleName] = isset($this->optionsSerialized->blogRoles[$roleName]) ? $this->optionsSerialized->blogRoles[$roleName] : '#00B38F';
            if ($roleName == 'administrator') {
                $this->optionsSerialized->phrases['wc_blog_role_' . $roleName] = isset($this->optionsSerialized->phrases['wc_blog_role_' . $roleName]) ? $this->optionsSerialized->phrases['wc_blog_role_' . $roleName] : __('Admin', 'wpdiscuz');
            } elseif ($roleName == 'post_author') {
                $this->optionsSerialized->phrases['wc_blog_role_' . $roleName] = isset($this->optionsSerialized->phrases['wc_blog_role_' . $roleName]) ? $this->optionsSerialized->phrases['wc_blog_role_' . $roleName] : __('Author', 'wpdiscuz');
            } elseif ($roleName == 'editor') {
                $this->optionsSerialized->phrases['wc_blog_role_' . $roleName] = isset($this->optionsSerialized->phrases['wc_blog_role_' . $roleName]) ? $this->optionsSerialized->phrases['wc_blog_role_' . $roleName] : __('Editor', 'wpdiscuz');
            } else {
                $this->optionsSerialized->phrases['wc_blog_role_' . $roleName] = isset($this->optionsSerialized->phrases['wc_blog_role_' . $roleName]) ? $this->optionsSerialized->phrases['wc_blog_role_' . $roleName] : __('Member', 'wpdiscuz');
            }
        }
        $this->optionsSerialized->blogRoles['post_author'] = isset($this->optionsSerialized->blogRoles['post_author']) ? $this->optionsSerialized->blogRoles['post_author'] : '#00B38F';
        $this->optionsSerialized->blogRoles['guest'] = isset($this->optionsSerialized->blogRoles['guest']) ? $this->optionsSerialized->blogRoles['guest'] : '#00B38F';
        $this->optionsSerialized->phrases['wc_blog_role_post_author'] = isset($this->optionsSerialized->phrases['wc_blog_role_post_author']) ? $this->optionsSerialized->phrases['wc_blog_role_post_author'] : __('Author', 'wpdiscuz');
        $this->optionsSerialized->phrases['wc_blog_role_guest'] = isset($this->optionsSerialized->phrases['wc_blog_role_guest']) ? $this->optionsSerialized->phrases['wc_blog_role_guest'] : __('Guest', 'wpdiscuz');
    }

    public function showReplies() {
        $response = array('code' => 0, 'data' => '');
        $postId = isset($_POST['postId']) ? intval($_POST['postId']) : 0;
        $commentId = isset($_POST['commentId']) ? intval($_POST['commentId']) : 0;
        if ($postId) {
            $commentListArgs = $this->getCommentListArgs($postId);
            $cArgs = $this->getDefaultCommentsArgs($postId);
            $cArgs['parent'] = $commentId;
            $cArgs['limit'] = 0;
            $comment = get_comment($commentId);
            $children = $comment->get_children(array(
                'format' => 'flat',
                'status' => $cArgs['status'],
                'orderby' => $cArgs['orderby'],
            ));
            $commentListArgs['wpdiscuz_child_count_' . $comment->comment_ID] = count($children);
            $comments = array_merge(array($comment), $children);
            if ($comments) {
                $response['code'] = 1;
                $response['data'] = wp_list_comments($commentListArgs, $comments);
                $response['callbackFunctions'] = array();
                $response = apply_filters('wpdiscuz_image_callbacks', $response);
            }
        }
        wp_die(json_encode($response));
    }

    public function mostReactedComment() {
        $response = array('code' => 0);
        $postId = isset($_POST['postId']) ? intval($_POST['postId']) : 0;
        if ($postId) {
            $commentId = $this->dbManager->getMostReactedCommentId($postId);
            $comment = get_comment($commentId);
            if ($comment && $comment->comment_post_ID == $postId) {
                $this->commentsArgs = $this->getDefaultCommentsArgs($postId);
                $commentStatusIn = array('1');
                if ($this->commentsArgs['status'] === 'all') {
                    $commentStatusIn[] = '0';
                }
                $parentComment = $this->helperOptimization->getCommentRoot($commentId, $commentStatusIn);
                $tree = $parentComment->get_children(array(
                    'format' => 'flat',
                    'status' => $this->commentsArgs['status'],
                    'orderby' => $this->commentsArgs['orderby']
                ));
                $comments = array_merge(array($parentComment), $tree);
                $commentListArgs = $this->getCommentListArgs($postId);
                $commentListArgs['isSingle'] = true;
                $commentListArgs['new_loaded_class'] = 'wc-new-loaded-comment';
                $response['code'] = 1;
                $response['message'] = wp_list_comments($commentListArgs, $comments);
                $response['commentId'] = $commentId;
                $response['parentCommentID'] = $parentComment->comment_ID;
                $response['callbackFunctions'] = array();
                $response = apply_filters('wpdiscuz_image_callbacks', $response);
            }
        }
        wp_die(json_encode($response));
    }

    public function hottestThread() {
        $response = array('code' => 0);
        $postId = isset($_POST['postId']) ? intval($_POST['postId']) : 0;
        if ($postId) {
            $parentCommentIds = $this->dbManager->getParentCommentsHavingReplies($postId);
            $childCount = 0;
            $hottestCommentId = 0;
            $hottestChildren = array();
            foreach ($parentCommentIds as $parentCommentId) {
                $tree = array();
                $children = $this->dbManager->getHottestTree($parentCommentId);
                $tmpCount = count($children);
                if ($childCount < $tmpCount) {

                    $childCount = $tmpCount;
                    $hottestCommentId = $parentCommentId;
                    $hottestChildren = $children;
                }
            }

            if ($hottestCommentId && $hottestChildren) {
                $this->commentsArgs = $this->getDefaultCommentsArgs($postId);
                $commentStatusIn = array('1');
                if ($this->commentsArgs['status'] === 'all') {
                    $commentStatusIn[] = '0';
                }
                $parentComment = $this->helperOptimization->getCommentRoot($hottestCommentId, $commentStatusIn);
                $tree = $parentComment->get_children(array(
                    'format' => 'flat',
                    'status' => $this->commentsArgs['status'],
                    'orderby' => $this->commentsArgs['orderby']
                ));
                $comments = array_merge(array($parentComment), $tree);
                $commentListArgs = $this->getCommentListArgs($postId);
                $commentListArgs['isSingle'] = true;
                $commentListArgs['new_loaded_class'] = 'wc-new-loaded-comment';
                $response['code'] = 1;
                $response['message'] = wp_list_comments($commentListArgs, $comments);
                $response['commentId'] = $hottestCommentId;
                $response['callbackFunctions'] = array();
                $response = apply_filters('wpdiscuz_image_callbacks', $response);
            }
        }
        wp_die(json_encode($response));
    }

    private function getStickyComments($isPaginate, &$commentList, &$commentListArgs) {
        if (isset($this->commentsArgs['first_load']) && $this->commentsArgs['first_load']) {
            $this->commentsArgs['sticky'] = 1;
            $this->commentsArgs['comment__in'] = '';
            $this->commentsArgs['limit'] = 0;
            if ($isPaginate) {
                $this->commentsArgs['number'] = '';
                $this->commentsArgs['offset'] = '';
                $this->commentsArgs['parent'] = '';
            }
            $this->commentsArgs['caller'] = 'wpdiscuz';
            $this->commentsArgs['type__not_in'] = '';
            $this->commentsArgs['type__in'] = array(self::WPDISCUZ_STICKY_COMMENT);
            $stickyComments = get_comments($this->commentsArgs);
            if ($stickyComments) {
                $allStickyChildren = array();
                foreach ($stickyComments as $stickyComment) {
                    $stickyChildren = $stickyComment->get_children(array(
                        'format' => 'flat',
                        'status' => $this->commentsArgs['status'],
                        'orderby' => $this->commentsArgs['orderby']
                    ));
                    $countStickyChildren = count($stickyChildren);
                    if ($countStickyChildren && $this->optionsSerialized->isLoadOnlyParentComments) {
                        $commentListArgs['wpdiscuz_child_count_' . $stickyComment->comment_ID] = $countStickyChildren;
                    } else {
                        $allStickyChildren = array_merge($allStickyChildren, $stickyChildren);
                    }
                }
                if ($allStickyChildren) {
                    $stickyComments = array_merge($stickyComments, $allStickyChildren);
                }
                $commentList = ($isPaginate && $this->optionsSerialized->wordpressCommentOrder == 'desc') ? array_merge($commentList, $stickyComments) : array_merge($stickyComments, $commentList);
            }
        }
    }

}

$wpdiscuz = wpDiscuz();
