<?php

namespace NZTA\Vote\Extensions;

use NZTA\Vote\Models\Vote;
use SilverStripe\Comments\Model\Comment;
use SilverStripe\Core\Extension;

class VoteExtension extends Extension
{
    private static $has_many = [
        'Votes' => Vote::class,
    ];

    /**
     * Helper function that gets the number of likes on a given Page.
     *
     * @return int|null
     */
    public function getLikeCount(): ?int
    {
        return $this->commentLikeCount(0);
    }

    public function commentLikeCount($commentID): ?int
    {
        $filter = ['Status' => 'Like'];
        if (class_exists(Comment::class)) {
            $filter['CommentID'] = (int)$commentID;
        }
        return $this->owner->Votes()->filter($filter)->count() ?: null;
    }
}
