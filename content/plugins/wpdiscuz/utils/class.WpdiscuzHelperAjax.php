<?php

if (!defined('ABSPATH')) {
    exit();
}

class WpdiscuzHelperAjax implements WpDiscuzConstants {

    private $optionsSerialized;
    private $dbManager;

    public function __construct($optionsSerialized, $dbManager) {
        $this->optionsSerialized = $optionsSerialized;
        $this->dbManager = $dbManager;
        add_action('wp_ajax_wpdiscuzStickComment', array(&$this, 'stickComment'));
        add_action('wp_ajax_wpdiscuzCloseThread', array(&$this, 'closeThread'));
        add_action('wp_ajax_wpdDeactivate', array(&$this, 'deactivate'));
        add_action('wp_ajax_wpdImportSTCR', array(&$this, 'importSTCR'));
        add_filter('wp_update_comment_data', array(&$this, 'commentDataArr'), 10, 3);
    }

    public function stickComment() {
        $response = array('code' => 0, 'data' => '');
        $postId = isset($_POST['postId']) ? intval($_POST['postId']) : 0;
        $commentId = isset($_POST['commentId']) ? intval($_POST['commentId']) : 0;
        if ($postId && $commentId) {
            $comment = get_comment($commentId);
            if (current_user_can('moderate_comments') && $comment && isset($comment->comment_ID) && $comment->comment_ID && !$comment->comment_parent) {
                $commentarr = array('comment_ID' => $commentId);
                if ($comment->comment_type == self::WPDISCUZ_STICKY_COMMENT) {
                    $commentarr['comment_type'] = '';
                    $response['data'] = $this->optionsSerialized->phrases['wc_stick_comment'];
                } else {
                    $commentarr['comment_type'] = self::WPDISCUZ_STICKY_COMMENT;
                    $response['data'] = $this->optionsSerialized->phrases['wc_unstick_comment'];
                }
                $commentarr['wpdiscuz_comment_update'] = true;
                if (wp_update_comment(wp_slash($commentarr))) {
                    $response['code'] = 1;
                }
            }
        }
        wp_die(json_encode($response));
    }

    public function closeThread() {
        $response = array('code' => 0, 'data' => '');
        $postId = isset($_POST['postId']) ? intval($_POST['postId']) : 0;
        $commentId = isset($_POST['commentId']) ? intval($_POST['commentId']) : 0;
        if ($postId && $commentId) {
            $comment = get_comment($commentId);
            if (current_user_can('moderate_comments') && $comment && isset($comment->comment_ID) && $comment->comment_ID && !$comment->comment_parent) {
                $children = $comment->get_children(array(
                    'format' => 'flat',
                    'status' => 'all',
                ));

                if (absint($comment->comment_karma)) {
                    $response['data'] = $this->optionsSerialized->phrases['wc_close_comment'];
                    $response['icon'] = 'fa-unlock';
                } else {
                    $response['data'] = $this->optionsSerialized->phrases['wc_open_comment'];
                    $response['icon'] = 'fa-lock';
                }
                $commentarr = array(
                    'comment_ID' => $comment->comment_ID,
                    'comment_karma' => !(boolval($comment->comment_karma)),
                    'wpdiscuz_comment_update' => true,
                );
                if (wp_update_comment(wp_slash($commentarr))) {
                    $response['code'] = 1;
                    if ($children && is_array($children)) {
                        foreach ($children as $child) {
                            $commentarr['comment_ID'] = $child->comment_ID;
                            wp_update_comment($commentarr);
                        }
                    }
                }
            }
        }
        wp_die(json_encode($response));
    }

    public function deactivate() {
        $response = array('code' => 0);
        $json = filter_input(INPUT_POST, 'deactivateData');
        if ($json) {
            parse_str($json, $data);
            if (isset($data['never_show']) && ($v = intval($data['never_show']))) {
                update_option(self::OPTION_SLUG_DEACTIVATION, $v);
                $response['code'] = 'dismiss_and_deactivate';
            } else if (isset($data['deactivation_reason']) && ($reason = trim($data['deactivation_reason']))) {
                $pluginData = get_plugin_data(WPDISCUZ_DIR_PATH . "/class.WpdiscuzCore.php");
                $to = 'feedback@wpdiscuz.com';
                $subject = '[wpDiscuz Feedback - ' . $pluginData['Version'] . '] - ' . $reason;
                $headers = array();
                $contentType = 'text/html';
                $fromName = apply_filters('wp_mail_from_name', get_option('blogname'));
                $siteUrl = get_site_url();
                $parsedUrl = parse_url($siteUrl);
                $domain = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
                $fromEmail = 'no-reply@' . $domain;
                $headers[] = "Content-Type:  $contentType; charset=UTF-8";
                $headers[] = "From: " . $fromName . " <" . $fromEmail . "> \r\n";
                $message = "<strong>Deactivation reason:</strong> " . $reason . "\r\n" . "<br/>";
                if (isset($data['deactivation_reason_desc']) && ($reasonDesc = trim($data['deactivation_reason_desc']))) {
                    $message .= "<strong>Deactivation reason description:</strong> " . $reasonDesc . "\r\n" . "<br/>";
                }
                $sent = wp_mail($to, $subject, $message, $headers);
                $response['code'] = 'send_and_deactivate';
            }
        }
        wp_die(json_encode($response));
    }

    public function importSTCR() {
        $response = array('progress' => 0);
        $stcrData = isset($_POST['stcrData']) ? $_POST['stcrData'] : '';
        if ($stcrData) {
            parse_str($stcrData, $data);
            $limit = 50;
            $step = isset($data['stcr-step']) ? intval($data['stcr-step']) : 0;
            $stcrSubscriptionsCount = isset($data['stcr-subscriptions-count']) ? intval($data['stcr-subscriptions-count']) : 0;
            $nonce = isset($data['_wpnonce']) ? trim($data['_wpnonce']) : '';
            if (wp_verify_nonce($nonce, 'wc_tools_form') && $stcrSubscriptionsCount) {
                $offset = $limit * $step;
                if ($limit && $offset >= 0) {
                    $subscriptions = $this->dbManager->getStcrSubscriptions($limit, $offset);
                    if ($subscriptions) {
                        $this->dbManager->addStcrSubscriptions($subscriptions);
                        ++$step;
                        $response['step'] = $step;
                        $progress = $offset ? $offset * 100 / $stcrSubscriptionsCount : $limit * 100 / $stcrSubscriptionsCount;
                        $response['progress'] = intval($progress);
                    } else {
                        $response['progress'] = 100;
                    }
                }
            }
        }
        wp_die(json_encode($response));
    }

    public function commentDataArr($data, $comment, $commentarr) {
        if (isset($data['wpdiscuz_comment_update']) && $data['wpdiscuz_comment_update']) {
            $data['comment_date'] = $comment->comment_date;
            $data['comment_date_gmt'] = $comment->comment_date_gmt;
        }
        return $data;
    }

}
