<?php

//mimic the actuall admin-ajax
define('DOING_AJAX', true);

if (!isset($_POST['action'])) {
    die('-1');
}

require_once('../../../../../wp-load.php');

header('Content-Type: text/html');
send_nosniff_header();

header('Cache-Control: no-cache');
header('Pragma: no-cache');

$wpdiscuz = wpDiscuz();
$action = esc_attr(trim($_POST['action']));
$allowedActions = array(
    'wpdLoadMoreComments',
    'wpdVoteOnComment',
    'wpdSorting',
    'wpdAddComment',
    'wpdGetSingleComment',
    'wpdCheckNotificationType',
    'wpdRedirect',
    'wpdEditComment',
    'wpdSaveEditedComment',
    'wpdUpdateAutomatically',
    'wpdUpdateOnClick',
    'wpdReadMore',
    'wpdShowReplies',
    'wpdMostReactedComment',
    'wpdHottestThread',
    'wpdGetInfo',
    'wpdGetActivityPage',
    'wpdGetSubscriptionsPage',
    'wpdGetFollowsPage',
    'wpdDeleteComment',
    'wpdCancelSubscription',
    'wpdCancelFollow',
    'wpdEmailDeleteLinks',
    'wpdGuestAction',
    'wpdStickComment',
    'wpdCloseThread',
    'wpdFollowUser',
);

// Load more comments
add_action('wpdiscuz_wpdLoadMoreComments', array($wpdiscuz, 'loadMoreComments'));
add_action('wpdiscuz_nopriv_wpdLoadMoreComments', array($wpdiscuz, 'loadMoreComments'));
// Vote on comments
add_action('wpdiscuz_wpdVoteOnComment', array($wpdiscuz, 'voteOnComment'));
add_action('wpdiscuz_nopriv_wpdVoteOnComment', array($wpdiscuz, 'voteOnComment'));
// Sorting comments
add_action('wpdiscuz_wpdSorting', array($wpdiscuz, 'sorting'));
add_action('wpdiscuz_nopriv_wpdSorting', array($wpdiscuz, 'sorting'));
// Adding comment
add_action('wpdiscuz_wpdAddComment', array($wpdiscuz, 'addComment'));
add_action('wpdiscuz_nopriv_wpdAddComment', array($wpdiscuz, 'addComment'));
// Get single comment
add_action('wpdiscuz_wpdGetSingleComment', array($wpdiscuz, 'getSingleComment'));
add_action('wpdiscuz_nopriv_wpdGetSingleComment', array($wpdiscuz, 'getSingleComment'));
// Get single comment
add_action('wpdiscuz_wpdCheckNotificationType', array($wpdiscuz->helperEmail, 'checkNotificationType'));
add_action('wpdiscuz_nopriv_wpdCheckNotificationType', array($wpdiscuz->helperEmail, 'checkNotificationType'));
// Redirect first commenter
add_action('wpdiscuz_wpdRedirect', array($wpdiscuz, 'redirect'));
add_action('wpdiscuz_nopriv_wpdRedirect', array($wpdiscuz, 'redirect'));
// Edit comment
add_action('wpdiscuz_wpdEditComment', array($wpdiscuz, 'editComment'));
add_action('wpdiscuz_nopriv_wpdEditComment', array($wpdiscuz, 'editComment'));
// Save edited comment
add_action('wpdiscuz_wpdSaveEditedComment', array($wpdiscuz, 'saveEditedComment'));
add_action('wpdiscuz_nopriv_wpdSaveEditedComment', array($wpdiscuz, 'saveEditedComment'));
// Update comment list automatically
add_action('wpdiscuz_wpdUpdateAutomatically', array($wpdiscuz, 'updateAutomatically'));
add_action('wpdiscuz_nopriv_wpdUpdateAutomatically', array($wpdiscuz, 'updateAutomatically'));
// Update comment list manually
add_action('wpdiscuz_wpdUpdateOnClick', array($wpdiscuz, 'updateOnClick'));
add_action('wpdiscuz_nopriv_wpdUpdateOnClick', array($wpdiscuz, 'updateOnClick'));
// Read more comment
add_action('wpdiscuz_wpdReadMore', array($wpdiscuz, 'readMore'));
add_action('wpdiscuz_nopriv_wpdReadMore', array($wpdiscuz, 'readMore'));
// Show Comment Replies
add_action('wpdiscuz_wpdShowReplies', array($wpdiscuz, 'showReplies'));
add_action('wpdiscuz_nopriv_wpdShowReplies', array($wpdiscuz, 'showReplies'));
// Most Reacted Comment
add_action('wpdiscuz_wpdMostReactedComment', array($wpdiscuz, 'mostReactedComment'));
add_action('wpdiscuz_nopriv_wpdMostReactedComment', array($wpdiscuz, 'mostReactedComment'));
// Hottest Comment Thread
add_action('wpdiscuz_wpdHottestThread', array($wpdiscuz, 'hottestThread'));
add_action('wpdiscuz_nopriv_wpdHottestThread', array($wpdiscuz, 'hottestThread'));
// Get user content info
add_action('wpdiscuz_wpdGetInfo', array($wpdiscuz->helper, 'wpdGetInfo'));
add_action('wpdiscuz_nopriv_wpdGetInfo', array($wpdiscuz->helper, 'wpdGetInfo'));
// Get user activity page item
add_action('wpdiscuz_wpdGetActivityPage', array($wpdiscuz->helper, 'getActivityPage'));
add_action('wpdiscuz_nopriv_wpdGetActivityPage', array($wpdiscuz->helper, 'getActivityPage'));
// Get user subscription page item
add_action('wpdiscuz_wpdGetSubscriptionsPage', array($wpdiscuz->helper, 'getSubscriptionsPage'));
add_action('wpdiscuz_nopriv_wpdGetSubscriptionsPage', array($wpdiscuz->helper, 'getSubscriptionsPage'));
// Get user follow page item
add_action('wpdiscuz_wpdGetFollowsPage', array($wpdiscuz->helper, 'getFollowsPage'));
add_action('wpdiscuz_nopriv_wpdGetFollowsPage', array($wpdiscuz->helper, 'getFollowsPage'));
// Delete users' comment
add_action('wpdiscuz_wpdDeleteComment', array($wpdiscuz->helperAjax, 'deleteComment'));
add_action('wpdiscuz_nopriv_wpdDeleteComment', array($wpdiscuz->helperAjax, 'deleteComment'));
// Delete users' subscription
add_action('wpdiscuz_wpdCancelSubscription', array($wpdiscuz->helperAjax, 'deleteSubscription'));
add_action('wpdiscuz_nopriv_wpdCancelSubscription', array($wpdiscuz->helperAjax, 'deleteSubscription'));
// Delete users' follow
add_action('wpdiscuz_wpdCancelFollow', array($wpdiscuz->helperAjax, 'deleteFollow'));
add_action('wpdiscuz_nopriv_wpdCancelFollow', array($wpdiscuz->helperAjax, 'deleteFollow'));
// Email to user the delete links
add_action('wpdiscuz_wpdEmailDeleteLinks', array($wpdiscuz->helperAjax, 'emailDeleteLinks'));
// Guest action
add_action('wpdiscuz_wpdGuestAction', array($wpdiscuz->helperAjax, 'guestAction'));
// Stick comment
add_action('wpdiscuz_wpdStickComment', array($wpdiscuz->helperAjax, 'stickComment'));
// Close comment
add_action('wpdiscuz_wpdCloseThread', array($wpdiscuz->helperAjax, 'closeThread'));
// Follow user
add_action('wpdiscuz_wpdFollowUser', array($wpdiscuz->helperAjax, 'followUser'));

if (in_array($action, $allowedActions)) {
    if (is_user_logged_in()) {
        do_action('wpdiscuz_' . $action);
    } else {
        do_action('wpdiscuz_nopriv_' . $action);
    }
} else {
    die('-1');
}