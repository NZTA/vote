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
    public function getLikeCount()
    {
        $votes = $this->owner->Votes();

        if ($votes->count()) {
            return $votes
                ->filter([
                    'Status'    => 'Like',
                    'CommentID' => '0',
                ])
                ->count();
        }

        return null;
    }

    public function commentLikeCount($commentID)
    {
        $votes = $this->owner->Votes();

        if ($votes->count()) {
            return $votes
                ->filter([
                    'Status'    => 'Like',
                    'CommentID' => $commentID,
                ])
                ->count();
        }

        return null;
    }
}
