<?php

if (!defined('ABSPATH')) {
    exit();
}

class WpdiscuzOptimizationHelper implements WpDiscuzConstants {

    private $optionsSerialized;
    private $dbManager;
    private $emailHelper;
    private $wpdiscuzForm;

    public function __construct($optionsSerialized, $dbManager, $emailHelper, $wpdiscuzForm) {
        $this->optionsSerialized = $optionsSerialized;
        $this->dbManager = $dbManager;
        $this->emailHelper = $emailHelper;
        $this->wpdiscuzForm = $wpdiscuzForm;
        add_action('comment_post', array(&$this, 'updateStatistics'), 11, 1);
        add_action('delete_comment', array(&$this, 'updateStatistics'), 11, 2);
        add_action('deleted_comment', array(&$this, 'cleanCommentRelatedRows'));
    }

    /**
     * recursively get new comments tree
     * return array of comments' ids
     */
    public function getTreeByParentId($commentId, &$tree) {
        $children = $this->dbManager->getCommentsByParentId($commentId);
        if ($children && is_array($children)) {
            foreach ($children as $child) {
                if (!in_array($child, $tree)) {
                    $tree[] = $child;
                    $this->getTreeByParentId($child, $tree);
                }
            }
        }
        return $tree;
    }

    public function isReplyInAuthorTree($commentId, $authorComments) {
        $comment = get_comment($commentId);
        if (in_array($comment->comment_parent, $authorComments)) {
            return true;
        }
        if ($comment->comment_parent) {
            return $this->isReplyInAuthorTree($comment->comment_parent, $authorComments);
        } else {
            return false;
        }
    }

    /**
     * add new comment id in comment meta if status is approved
     * @param type $newStatus the comment new status
     * @param type $oldStatus the comment old status
     * @param type $comment current comment object
     */
    public function statusEventHandler($newStatus, $oldStatus, $comment) {
        $this->updateStatistics($comment->comment_ID, $comment);
        if ($newStatus != $oldStatus && $newStatus == 'approved') {
            $this->notifyOnApprove($comment);
            if ($this->optionsSerialized->isNotifyOnCommentApprove) {
                $this->emailHelper->notifyOnApproving($comment);
            }
        }
    }

    /**
     * get the current comment root comment
     * @param type $commentId the current comment id
     * @return type comment
     */
    public function getCommentRoot($commentId) {
        $comment = get_comment($commentId);
        if ($comment && $comment->comment_parent) {
            return $this->getCommentRoot($comment->comment_parent);
        } else {
            return $comment;
        }
    }

    public function getCommentDepth($commentId, &$depth = 1) {
        $comment = get_comment($commentId);
        if ($comment->comment_parent && ($depth < $this->optionsSerialized->wordpressThreadCommentsDepth)) {
            $depth++;
            return $this->getCommentDepth($comment->comment_parent, $depth);
        } else {
            return $depth;
        }
    }

    private function notifyOnApprove($comment) {
        $postId = $comment->comment_post_ID;
        $commentId = $comment->comment_ID;
        $email = $comment->comment_author_email;
        $parentComment = get_comment($comment->comment_parent);
        $this->emailHelper->notifyPostSubscribers($postId, $commentId, $email);
        if ($parentComment) {
            $parentCommentEmail = $parentComment->comment_author_email;
            if ($parentCommentEmail != $email) {
                $this->emailHelper->notifyAllCommentSubscribers($postId, $commentId, $email);
                $this->emailHelper->notifyCommentSubscribers($parentComment->comment_ID, $commentId, $email);
            }
        }
    }

    public function removeVoteData() {
        if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'remove_vote_data') && current_user_can('manage_options')) {
            $this->dbManager->removeVotes();
            wp_redirect(admin_url('edit-comments.php?page=' . self::PAGE_SETTINGS));
        }
    }

    public function resetPhrases() {
        if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'reset_phrases_nonce') && current_user_can('manage_options')) {
            $this->dbManager->deletePhrases();
            wp_redirect(admin_url('edit-comments.php?page=' . self::PAGE_PHRASES));
        }
    }

    public function cleanCommentRelatedRows($commentId) {
        $this->dbManager->deleteSubscriptions($commentId);
        $this->dbManager->deleteVotes($commentId);
    }

    public function updateStatistics($commentId, $comment = null) {
        if (!$comment && $commentId) {
            $comment = get_comment($commentId);
        }
        if ($comment) {
            $authorsCount = $this->dbManager->getAuthorsCount($comment->comment_post_ID, false);
            set_transient(self::TRS_AUTHORS_COUNT . $comment->comment_post_ID, $authorsCount);
            $followers = $this->dbManager->getAllSubscriptionsCount($comment->comment_post_ID, false);
            set_transient(self::TRS_FOLLOWERS_COUNT . $comment->comment_post_ID, $followers);
            $repliesCount = $this->dbManager->getRepliesCount($comment->comment_post_ID, false);
            set_transient(self::TRS_REPLIES_COUNT . $comment->comment_post_ID, $repliesCount);
            $threadsCount = $this->dbManager->getThreadsCount($comment->comment_post_ID, false);
            set_transient(self::TRS_THREADS_COUNT . $comment->comment_post_ID, $threadsCount);
            $authorsLimit = apply_filters('wpdiscuz_recent_authors_limit', 5);
            $recentAuthors = $this->dbManager->getRecentAuthors($comment->comment_post_ID, $authorsLimit, false);
            set_transient(self::TRS_RECENT_AUTHORS . $comment->comment_post_ID, $recentAuthors);
        }
    }

}
