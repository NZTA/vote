<?php

namespace NZTA\Vote\Extensions;

use NZTA\Vote\Models\Vote;
use SilverStripe\Comments\Model\Comment;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Core\Extension;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

class VoteControllerExtension extends Extension
{
    private static $allowed_actions = [
        'vote',
    ];

    /**
     * Returns a json encoded string containing the number of likes and dislikes
     * for the specified vote object.
     *
     * @return string
     * @throws HTTPResponse_Exception
     */
    public function vote()
    {
        $request = $this->owner->getRequest();

        if (!$request->isPOST()) {
            try {
                // allow hooks to run
                $this->owner->httpError(405, 'Only HTTP POST requests are accepted');
            } catch (HTTPResponse_Exception $e) {
                // set required header
                $e->getResponse()->addHeader('Allow', 'POST');
                throw $e;
            }
        }

        // a commentID of 0 means the vote is not for a comment
        $commentID = $request->postVar('comment_id');
        $status = $request->postVar('vote');

        // checks if user has already voted and returns error message if so
        $this->voteByCurrentUser($status, $commentID);

        // Get all votes to count the amount of likes and dislikes for this object
        $votes = $this->owner->data()->Votes()->filter('CommentID', $commentID);

        $response = $this->owner->getResponse();
        $response->addHeader('Content-Type', 'application/json');
        $response->setBody(json_encode([
            'status' => $status,
            'numLikes' => $votes->filter('Status', 'Like')->count(),
            'numDislikes' => $votes->filter('Status', 'Dislike')->count(),
        ]));

        return $response;
    }

    /**
     * Sends a vote as the current user
     *
     * @param string $status
     * @param integer $commentID
     *
     * @return null
     */
    protected function voteByCurrentUser($status, $commentID)
    {
        return $this->castVote(Security::getCurrentUser(), $status, (int)$commentID);
    }

    /**
     * Creates a Vote object for the currently logged in member, and sets the status to the provided value
     *
     * @param Member|null $member
     * @param string $status
     * @param integer $commentID
     *
     * @return string|null
     * @throws HTTPResponse_Exception
     */
    private function castVote(?Member $member, string $status, int $commentID)
    {
        // check if there is a logged in member
        if (!$member) {
            return $this->owner->httpError(403, _t(self::class . '.ERROR_BAD_USER', 'Log in to vote'));
        }

        $voteDetails = [
            'MemberID' => $member->ID,
            'CommentID' => $commentID,
        ];

        $vote = Vote::create($voteDetails);

        // validate status data
        $availableStatuses = $vote->dbObject('Status')->enumValues();

        if (!in_array($status, $availableStatuses)) {
            return $this->owner->httpError(400, _t(self::class . '.ERROR_BAD_STATUS', 'Invalid vote'));
        }

        // validate comment data
        if ($commentID !== 0 && !Comment::get()->byID($commentID)) {
            return $this->owner->httpError(400, _t(self::class . '.ERROR_BAD_COMMENT', 'Invalid comment'));
        }



        $votesForPage = $this->owner->data();

        // check whether the member has already voted
        $hasVoted = $votesForPage->Votes()->filter($voteDetails)->first();

        // set or update status
        $vote = $hasVoted ?? $vote;
        $vote->Status = $status;

        if ($hasVoted) {
            $vote->write();
        } else {
            $votesForPage->Votes()->add($vote); // performs a write
        }

        return null;
    }

    /**
     * Gets the status of the vote - like|dislike
     *
     * @param Member|null $member
     *
     * @return string|null
     */
    public function VoteStatus($member)
    {
        return !$member ? null : $this->owner->data()
            ->Votes()
            ->filter('MemberID', $member->ID)
            ->first()
            ?->Status;
    }

    /**
     * Gets the status of the vote based on the current user
     *
     * @return string|null
     */
    public function VoteStatusByCurrentUser()
    {
        return $this->owner->VoteStatus(Security::getCurrentUser());
    }
}
