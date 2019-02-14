<?php

/** COMMENTS WALKER */
class WpdiscuzWalker extends Walker_Comment implements WpDiscuzConstants {

    private $helper;
    private $helperOptimization;
    private $dbManager;
    private $optionsSerialized;

    public function __construct($helper, $helperOptimization, $dbManager, $optionsSerialized) {
        $this->helper = $helper;
        $this->helperOptimization = $helperOptimization;
        $this->dbManager = $dbManager;
        $this->optionsSerialized = $optionsSerialized;
    }

    /** START_EL */
    public function start_el(&$output, $comment, $depth = 0, $args = array(), $id = 0) {
        $depth++;
        $GLOBALS['comment_depth'] = $depth;
        $GLOBALS['comment'] = $comment;
        // BEGIN
        $commentOutput = '';
        $depth = isset($args['addComment']) ? $args['addComment'] : $depth;
        $uniqueId = $comment->comment_ID . '_' . $comment->comment_parent;
        $commentWrapperClass = array('wc-comment');
        $isClosed = $comment->comment_karma;
        $commentIcons = '';
        if ($comment->comment_type == self::WPDISCUZ_STICKY_COMMENT) {
            $commentWrapperClass[] = 'wc-sticky-comment';
            $commentIcons .= '<i class="fas fa-thumbtack wpd-sticky" aria-hidden="true" title="' . $this->optionsSerialized->phrases['wc_sticky_comment_icon_title'] . '"></i>';
            $stickText = $this->optionsSerialized->phrases['wc_unstick_comment'];
        } else {
            $stickText = $this->optionsSerialized->phrases['wc_stick_comment'];
        }

        if ($isClosed) {
            $commentWrapperClass[] = 'wc-closed-comment';
            $commentIcons .= '<i class="fas fa-lock wpd-closed" aria-hidden="true" title="' . $this->optionsSerialized->phrases['wc_closed_comment_icon_title'] . '"></i>';
            $closeText = $this->optionsSerialized->phrases['wc_open_comment'];
        } else {
            $closeText = $this->optionsSerialized->phrases['wc_close_comment'];
        }

        $comment->comment_content = apply_filters('wpdiscuz_before_comment_text', $comment->comment_content, $comment);
        if ($this->optionsSerialized->enableImageConversion) {
            $comment->comment_content = $this->helper->makeClickable($comment->comment_content);
        }

        $comment->comment_content = apply_filters('comment_text', $comment->comment_content, $comment, $args);
        $commentReadMoreLimit = $this->optionsSerialized->commentReadMoreLimit;
        if (strstr($comment->comment_content, '[/spoiler]')) {
            $commentReadMoreLimit = 0;
            $comment->comment_content = WpdiscuzHelper::spoiler($comment->comment_content);
        }
        if ($commentReadMoreLimit && count(explode(' ', strip_tags($comment->comment_content))) > $commentReadMoreLimit) {
            $comment->comment_content = WpdiscuzHelper::getCommentExcerpt($comment->comment_content, $uniqueId, $this->optionsSerialized);
        }
        $comment->comment_content .= $comment->comment_approved === '0' ? '<p class="wc_held_for_moderate">' . $this->optionsSerialized->phrases['wc_held_for_moderate'] . '</p>' : '';

        if (isset($args['new_loaded_class'])) {
            $commentWrapperClass[] = $args['new_loaded_class'];
            if ($args['isSingle']) {
                $commentWrapperClass[] = 'wpdiscuz_single';
            } else {
                $depth = $this->helperOptimization->getCommentDepth($comment->comment_ID);
            }
        }

        if ($this->optionsSerialized->wordpressIsPaginate) {
            $commentLink = get_comment_link($comment);
        } else {
            $commentLink = $args['post_permalink'] . '#comment-' . $comment->comment_ID;
            if (!empty($args['last_visit'])&& !empty($args['current_user_email']) && strtotime($comment->comment_date) > $args['last_visit'] && $args['current_user_email'] != $comment->comment_author_email) {
                $commentWrapperClass[] = 'wc-new-loaded-comment';
            }
        }

        $userKey = $comment->user_id . '_' . $comment->comment_author_email . '_' . $comment->comment_author;
        if (isset($_SESSION['wpdiscuz_users'][$userKey])) {
            $user = $_SESSION['wpdiscuz_users'][$userKey];
        } else {
            $user = array();
            $user['user'] = '';
            if ($comment->user_id) {
                $user['user'] = get_user_by('id', $comment->user_id);
            } else if ($this->optionsSerialized->isUserByEmail) {
                $user['user'] = get_user_by('email', $comment->comment_author_email);
            }
            $user['commentAuthorUrl'] = ('http://' == $comment->comment_author_url) ? '' : $comment->comment_author_url;
            $user['commentAuthorUrl'] = apply_filters('get_comment_author_url', $user['commentAuthorUrl'], $comment->comment_ID, $comment);
            if ($user['user']) {
                $user['commentAuthorUrl'] = $user['commentAuthorUrl'] ? $user['commentAuthorUrl'] : $user['user']->user_url;
                $user['authorName'] = $user['user']->display_name ? $user['user']->display_name : $comment->comment_author;
                $authorAvatarField = $user['user']->ID;
                $gravatarUserId = $user['user']->ID;
                $gravatarUserEmail = $user['user']->user_email;
                $user['profileUrl'] = in_array($user['user']->ID, $args['posts_authors']) ? get_author_posts_url($user['user']->ID) : '';
                if ($user['user']->ID == $args['post_author']) {
                    $user['authorClass'] = 'wc-blog-user wc-blog-post_author';
                    $user['author_title'] = $this->optionsSerialized->phrases['wc_blog_role_post_author'];
                } else {
                    $user['authorClass'] = 'wc-blog-guest';
                    $user['author_title'] = $this->optionsSerialized->phrases['wc_blog_role_guest'];
                    if ($this->optionsSerialized->blogRoles) {
                        if ($user['user']->roles && is_array($user['user']->roles)) {
                            foreach ($user['user']->roles as $role) {
                                if (isset($this->optionsSerialized->blogRoles[$role])) {
                                    $user['authorClass'] = 'wc-blog-user wc-blog-' . $role;
                                    $rolePhrase = isset($this->optionsSerialized->phrases['wc_blog_role_' . $role]) ? $this->optionsSerialized->phrases['wc_blog_role_' . $role] : '';
                                    $user['author_title'] = apply_filters('wpdiscuz_user_label', $rolePhrase, $user['user']);
                                    break;
                                }
                            }
                        }
                    }
                }
            } else {
                $user['authorName'] = $comment->comment_author ? $comment->comment_author : $this->optionsSerialized->phrases['wc_anonymous'];
                $authorAvatarField = $comment->comment_author_email;
                $gravatarUserId = 0;
                $gravatarUserEmail = $comment->comment_author_email;
                $user['profileUrl'] = '';
                $user['authorClass'] = 'wc-blog-guest';
                $user['author_title'] = $this->optionsSerialized->phrases['wc_blog_role_guest'];
            }
            $user['authorName'] = apply_filters('wpdiscuz_comment_author', $user['authorName'], $comment);
            $user['profileUrl'] = apply_filters('wpdiscuz_profile_url', $user['profileUrl'], $user['user']);
            if ($this->optionsSerialized->wordpressShowAvatars) {
                $authorAvatarField = apply_filters('wpdiscuz_author_avatar_field', $authorAvatarField, $comment, $user['user'], $user['profileUrl']);
                $user['gravatarArgs'] = array(
                    'wpdiscuz_gravatar_field' => $authorAvatarField,
                    'wpdiscuz_gravatar_size' => $args['wpdiscuz_gravatar_size'],
                    'wpdiscuz_gravatar_user_id' => $gravatarUserId,
                    'wpdiscuz_gravatar_user_email' => $gravatarUserEmail,
                    'wpdiscuz_current_user' => $user['user'],
                );
                $user['avatar'] = get_avatar($user['gravatarArgs']['wpdiscuz_gravatar_field'], $user['gravatarArgs']['wpdiscuz_gravatar_size'], '', $user['authorName'], $user['gravatarArgs']);
            }
            if (!$this->optionsSerialized->disableProfileURLs) {
                if ($user['profileUrl']) {
                    $attributes = apply_filters('wpdiscuz_avatar_link_attributes', array('href' => $user['profileUrl'], 'target' => '_blank'));
                    if ($attributes && is_array($attributes)) {
                        $attributesHtml = "";
                        foreach ($attributes as $attribute => $value) {
                            $attributesHtml .= "$attribute='{$value}' ";
                        }
                        $attributesHtml = trim($attributesHtml);
                        $user['authorAvatarSprintf'] = "<a $attributesHtml>%s</a>";
                    } else {
                        $user['authorAvatarSprintf'] = "<a href='{$user['profileUrl']}' target='_blank'>%s</a>";
                    }
                }

                if (($href = $user['commentAuthorUrl']) || ($href = $user['profileUrl'])) {
                    $attributes = apply_filters('wpdiscuz_author_link_attributes', array('href' => $href, 'rel' => 'nofollow', 'target' => '_blank'));
                    if ($attributes && is_array($attributes)) {
                        $attributesHtml = "";
                        foreach ($attributes as $attribute => $value) {
                            $attributesHtml .= "$attribute='{$value}' ";
                        }
                        $attributesHtml = trim($attributesHtml);
                        $user['authorName'] = "<a $attributesHtml>{$user['authorName']}</a>";
                    } else {
                        $user['authorName'] = "<a rel='nofollow' href='$href' target='_blank'>{$user['authorName']}</a>";
                    }
                }
            }
            $_SESSION['wpdiscuz_users'][$userKey] = $user;
        }

        if ($comment->comment_parent && $this->optionsSerialized->wordpressThreadComments) {
            $commentWrapperClass[] = 'wc-reply';
        }

        $trackOrPingback = $comment->comment_type == 'pingback' || $comment->comment_type == 'trackback';

        $commentWrapperClass[] = $user['authorClass'];
        $commentWrapperClass[] = 'wc_comment_level-' . $depth;
        $commentWrapperClass = apply_filters('wpdiscuz_comment_wrap_classes', $commentWrapperClass, $comment);
        $wrapperClass = implode(' ', $commentWrapperClass);

        // begin printing comment template
        $commentOutput .= '<div id="wc-comm-' . $uniqueId . '" class="' . $wrapperClass . '">';
        if ($this->optionsSerialized->wordpressShowAvatars) {
            $authorAvatar = $trackOrPingback ? '<img class="avatar avatar-' . $user['gravatarArgs']['wpdiscuz_gravatar_size'] . ' photo" width="' . $user['gravatarArgs']['wpdiscuz_gravatar_size'] . '" height="' . $user['gravatarArgs']['wpdiscuz_gravatar_size'] . '" src="' . $args['avatar_trackback'] . '" alt="trackback">' : $user['avatar'];
            if (isset($user['authorAvatarSprintf'])) {
                $authorAvatar = sprintf($user['authorAvatarSprintf'], $authorAvatar);
            }
            $commentLeftClass = apply_filters('wpdiscuz_comment_left_class', '');
            $commentOutput .= '<div class="wc-comment-left ' . $commentLeftClass . '"><div class="wpd-xborder"></div>' . $authorAvatar;
            if (!$this->optionsSerialized->authorTitlesShowHide && !$trackOrPingback) {
                $user['author_title'] = apply_filters('wpdiscuz_author_title', $user['author_title'], $comment);
                $commentOutput .= '<div class="' . $user['authorClass'] . ' wc-comment-label">' . '<span>' . $user['author_title'] . '</span>' . '</div>';
            }
            $commentOutput .= apply_filters('wpdiscuz_after_label', '', $comment);
            $commentOutput .= '</div>';
            $commentRightClass = '';
        } else {
            $commentRightClass = ' wc-hide-avatar';
        }

        $commentOutput .= '<div id="comment-' . $comment->comment_ID . '" class="wc-comment-right' . $commentRightClass . '">';
        $commentOutput .= '<div class="wc-comment-header">';
        $uNameClasses = apply_filters('wpdiscuz_username_classes', '');
        $user['authorName'] .= apply_filters('wpdiscuz_after_comment_author', '', $comment, $user['user']);

        $commentOutput .= '<div class="wc-comment-author ' . $uNameClasses . '">' . $user['authorName'] . '</div>';
        if ($args['can_user_follow'] && $args['current_user_email'] != $comment->comment_author_email) {
            if (is_array($args['user_follows']) && in_array($comment->comment_author_email, $args['user_follows'])) {
                $followClass = 'wc-unfollow wc-follow-active';
                $followTip = $this->optionsSerialized->phrases['wc_unfollow_user'];
            } else {
                $followClass = 'wc-follow';
                $followTip = $this->optionsSerialized->phrases['wc_follow_user'];
            }
            $commentOutput .= '<div class="wc-follow-link wpd-tooltip-right wc_not_clicked ' . $followClass . '">';
            $commentOutput .= '<i class="fas fa-rss" aria-hidden="true"></i>';
            $commentOutput .= '<wpdtip>' . $followTip . '</wpdtip>';
            $commentOutput .= '</div>';
        }

        $commentOutput .= '<div class="wc-comment-link">' . $commentIcons;
        $commentOutput .= apply_filters('wpdiscuz_comment_type_icon', '', $comment, $user['user'], $args['current_user']);

        if ($args['is_share_enabled']) {
            $commentOutput .= '<div class="wc-share-link wpf-cta wpd-tooltip-right"><i class="fas fa-share-alt" aria-hidden="true" title="' . $this->optionsSerialized->phrases['wc_share_text'] . '" ></i>';
            $commentOutput .= '<wpdtip>';
            $commentOutput .= $this->optionsSerialized->enableTwitterShare ? '<a class="wc_tw" target="_blank" href="https://twitter.com/intent/tweet?text=' . $this->helper->getTwitterShareContent($comment->comment_content, $commentLink) . '&url=' . urlencode($commentLink) . '" title="' . $this->optionsSerialized->phrases['wc_share_twitter'] . '"><i class="fab fa-twitter wpf-cta" aria-hidden="true"></i></a>' : '';
            $commentOutput .= $args['share_buttons'];
            $commentOutput .= '</wpdtip></div>';
        }

        $commentOutput .= apply_filters('wpdiscuz_before_comment_link', '', $comment, $user['user'], $args['current_user']);

        if (!$this->optionsSerialized->showHideCommentLink) {
            $commentOutput .= apply_filters('wpdiscuz_comment_link_img', '<span class="wc-comment-img-link-wrap"><i class="fas fa-link wc-comment-img-link wpf-cta" data-comment-url="' . $commentLink . '" aria-hidden="true"></i></span>', $comment);
        }

        $commentOutput .= apply_filters('wpdiscuz_after_comment_link', '', $comment, $user['user'], $args['current_user']);

        $commentOutput .= '</div>';
        $commentOutput .= '<div class="wpdiscuz_clear"></div>';
        $commentOutput .= '</div>';

        $commentOutput .= apply_filters('wpdiscuz_comment_text', '<div class="wc-comment-text">' . $comment->comment_content . '</div>', $comment, $args);
        $commentOutput .= apply_filters('wpdiscuz_after_comment_text', '', $comment);
        $commentOutput .= '<div class="wc-comment-footer">';
        $commentOutput .= '<div class="wc-footer-left">';
        if (!$this->optionsSerialized->votingButtonsShowHide) {
            if ($args['can_user_vote']) {
                $voteClass = ' wc_vote wc_not_clicked';
                $voteUp = $this->optionsSerialized->phrases['wc_vote_up'];
                $voteDown = $this->optionsSerialized->phrases['wc_vote_down'];
            } else {
                $voteClass = '';
                $voteUp = $this->optionsSerialized->phrases['wc_login_to_vote'];
                $voteDown = $this->optionsSerialized->phrases['wc_login_to_vote'];
            }
            $voteUpClasses = apply_filters('wpdiscuz_vote_up_icon_classes', array('fas', $args['voting_icons']['like'], 'wc-vote-img-up'), $comment, $args['current_user']);
            $voteDownClasses = apply_filters('wpdiscuz_vote_down_icon_classes', array('fas', $args['voting_icons']['dislike'], 'wc-vote-img-down'), $comment, $args['current_user']);
            if ($this->optionsSerialized->votingButtonsStyle) {
                $votesArr = $this->dbManager->getVotes($comment->comment_ID);
                if ($votesArr && count($votesArr) == 1) {
                    $like = 0;
                    $dislike = 0;
                } else {
                    $like = isset($votesArr[0]) ? intval($votesArr[0]) : 0;
                    $dislike = isset($votesArr[1]) ? intval($votesArr[1]) : 0;
                }
                $commentOutput .= '<span class="wc-vote-link wc-up wc-separate' . $voteClass . '">';
                $commentOutput .= '<i class="' . implode(' ', $voteUpClasses) . '"></i><span>' . $voteUp . '</span>';
                $commentOutput .= '</span>';
                $commentOutput .= '<span class="wc-vote-result wc-vote-result-like' . (($like) ? ' wc-positive' : '') . '">' . $like . '</span>';
                $commentOutput .= '<span class="wc-vote-result wc-vote-result-dislike' . (($dislike) ? ' wc-negative' : '') . '">' . $dislike . '</span>';
                $commentOutput .= '<span class="wc-vote-link wc-down wc-separate' . $voteClass . '">';
                $commentOutput .= '<i class="' . implode(' ', $voteDownClasses) . '"></i><span>' . $voteDown . '</span>';
                $commentOutput .= '</span>';
            } else {
                $voteCount = get_comment_meta($comment->comment_ID, WpdiscuzCore::META_KEY_VOTES, true);
                $commentOutput .= '<span class="wc-vote-link wc-up' . $voteClass . '">';
                $commentOutput .= '<i class="' . implode(' ', $voteUpClasses) . '"></i><span>' . $voteUp . '</span>';
                $commentOutput .= '</span>';
                $commentOutput .= '<span class="wc-vote-result">' . intval($voteCount) . '</span>';
                $commentOutput .= '<span class="wc-vote-link wc-down' . $voteClass . '">';
                $commentOutput .= '<i class="' . implode(' ', $voteDownClasses) . '"></i><span>' . $voteDown . '</span>';
                $commentOutput .= '</span>&nbsp;';
            }
        }

        if (!$isClosed) {
            if ($args['can_user_reply']) {
                $commentOutput .= '<span class="wc-reply-button wc-cta-button" title="' . $this->optionsSerialized->phrases['wc_reply_text'] . '"><i class="far fa-comments" aria-hidden="true"></i> ' . $this->optionsSerialized->phrases['wc_reply_text'] . '</span>';
            }

            if ($args['high_level_user'] || ($this->helper->isCommentEditable($comment) && $this->helper->canUserEditComment($comment, $args['current_user'], $args))) {
                $commentOutput .= '<span class="wc_editable_comment wc-cta-button"> ' . $this->optionsSerialized->phrases['wc_edit_text'] . '</span>';
                $commentOutput .= '<span class="wc_cancel_edit wc-cta-button-x"> ' . $this->optionsSerialized->phrases['wc_comment_edit_cancel_button'] . '</span>';
            }
        }

        $commentOutput .= apply_filters('wpdiscuz_comment_buttons', '', $comment, $user['user'], $args['current_user']);
        if ($comment->comment_parent == 0) {
            $commentOutput .= sprintf($args['wc_stick_btn'], $stickText);
            $commentOutput .= sprintf($args['wc_close_btn'], $closeText);
        }
        $commentOutput .= '</div>';
        $commentOutput .= '<div class="wc-footer-right">';

        if (!$this->optionsSerialized->hideCommentDate) {
            $commentOutput .= '<div class="wc-comment-date"><i class="far fa-clock" aria-hidden="true"></i>' . ($this->optionsSerialized->simpleCommentDate ? get_comment_date($this->optionsSerialized->wordpressDateFormat . ' ' . $this->optionsSerialized->wordpressTimeFormat, $comment->comment_ID) : $this->helper->dateDiff($comment->comment_date_gmt)) . '</div>';
        }
        if ($this->optionsSerialized->wordpressThreadComments && $depth < $this->optionsSerialized->wordpressThreadCommentsDepth) {
            $commentOutput .= '<div class="wc-toggle">';
            if (isset($args['wpdiscuz_child_count_' . $comment->comment_ID])) {
                $countChildren = $args['wpdiscuz_child_count_' . $comment->comment_ID];
                if ($countChildren) {
                    $commentOutput .= '<a href="#" title="' . $this->optionsSerialized->phrases['wc_show_replies_text'] . '">';
                    $commentOutput .= '<span class="wcsep">|</span> <span class="wpdiscuz-children"><span class="wpdiscuz-children-button-text">' . $this->optionsSerialized->phrases['wc_show_replies_text'] . '</span> (<span class="wpdiscuz-children-count">' . $countChildren . '</span>)</span> ';
                    $commentOutput .= '<i class="fas fa-chevron-down wpdiscuz-show-replies"></i>';
                    $commentOutput .= '</a>';
                }
            } else {
                $commentOutput .= $comment->get_children() ? '<i class="fas fa-chevron-up" title="' . $this->optionsSerialized->phrases['wc_hide_replies_text'] . '"></i>' : '';
            }
            $commentOutput .= '</div>';
        }
        $commentOutput .= '</div>';
        $commentOutput .= '<div class="wpdiscuz_clear"></div>';
        $commentOutput .= '</div>';
        $commentOutput .= '</div>';
        $commentOutput .= '<div class="wpdiscuz-comment-message"></div>';
        $commentOutput .= '<div id="wpdiscuz_form_anchor-' . $uniqueId . '"  class="wpdiscuz_clear"></div>';
        $output .= apply_filters('wpdiscuz_comment_end', $commentOutput, $comment, $depth, $args);
    }

    public function end_el(&$output, $comment, $depth = 0, $args = array()) {
        $output = apply_filters('wpdiscuz_thread_end', $output, $comment, $depth, $args);
        $output .= '</div>';
        return $output;
    }

}
