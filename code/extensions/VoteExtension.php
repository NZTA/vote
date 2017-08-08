<?php

class VoteExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $has_many = [
        'Votes' => 'Vote'
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
                    'Status' => 'Like',
                    'CommentID' => '0'
                ])
                ->count();
        }

        return null;
    }

}
