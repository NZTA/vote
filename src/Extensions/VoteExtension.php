<?php

namespace NZTA\Vote\Extensions;

use NZTA\Vote\Models\Vote;
use SilverStripe\ORM\DataExtension;

class VoteExtension extends DataExtension
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
        return $this->owner->Votes()->filter(['Status' => 'Like', 'CommentID' => $commentID])->count() ?: null;
    }
}
