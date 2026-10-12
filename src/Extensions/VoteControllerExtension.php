<?php

namespace NZTA\Vote\Extensions;

use NZTA\Vote\Models\Vote;
use SilverStripe\Comments\Model\Comment;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Core\Extension;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * @extends Extension<\SilverStripe\CMS\Controllers\ContentController>
 */
class VoteControllerExtension extends Extension
{
    private static $allowed_actions = [
        'vote',
    ];

    /**
     * Returns a json encoded string containing the number of likes and dislikes
     * for the specified vote object.
     *
     * @return HTTPResponse
     * @throws HTTPResponse_Exception
     */
    public function vote()
    {
        $request = $this->getOwner()->getRequest();

        if (!$request->isPOST()) {
            try {
                // allow hooks to run
                $this->getOwner()->httpError(405, 'Only HTTP POST requests are accepted');
            } catch (HTTPResponse_Exception $e) {
                // set required header
                $e->getResponse()->addHeader('Allow', 'POST');
                throw $e;
            }
        }

        // a commentID of 0 means the vote is not for a comment
        $commentID = (int)($request->postVar('comment_id') ?? 0);
        $status = $request->postVar('vote');

        // Get all votes to count the amount of likes and dislikes for this object (lazy loaded later)
        $votes = $this->getOwner()->data()->Votes();

        // checks if user has already voted and returns error message if so
        if (class_exists(Comment::class)) {
            $this->voteByCurrentUser($status, $commentID);
            $votes = $votes->filter('CommentID', $commentID);
        } else {
            $this->voteByCurrentUser($status, 0);
        }

        $response = $this->getOwner()->getResponse();
        $response->addHeader('Content-Type', 'application/json');
        $body = json_encode([
            'status' => $status,
            'numLikes' => $votes->filter('Status', 'Like')->count(),
            'numDislikes' => $votes->filter('Status', 'Dislike')->count(),
        ]);
        if (!$body) {
            $body = '{"status":"error"}';
            $response->setStatusCode(500);
        }
        $response->setBody($body);

        return $response;
    }

    /**
     * Sends a vote as the current user
     *
     * @param string $status
     * @param int $commentID
     *
     * @return null
     */
    protected function voteByCurrentUser(string $status, int $commentID)
    {
        return $this->castVote(Security::getCurrentUser(), $status, $commentID);
    }

    /**
     * Creates a Vote object for the currently logged in member, and sets the status to the provided value
     *
     * @param Member|null $member
     * @param string $status
     * @param integer $commentID
     *
     * @return null
     * @throws HTTPResponse_Exception
     */
    private function castVote(?Member $member, string $status, int $commentID = 0)
    {
        // check if there is a logged in member
        if (!$member) {
            return $this->getOwner()->httpError(403, _t(self::class . '.ERROR_BAD_USER', 'Log in to vote'));
        }

        $voteDetails = ['MemberID' => $member->ID];
        if (class_exists(Comment::class)) {
            $voteDetails['CommentID'] = $commentID;
        }

        $vote = Vote::create($voteDetails);

        // validate status data
        $availableStatuses = $vote->dbObject('Status')->enumValues();
        if (!in_array($status, $availableStatuses)) {
            return $this->getOwner()->httpError(400, _t(self::class . '.ERROR_BAD_STATUS', 'Invalid vote'));
        }

        // validate comment data
        if (
            $commentID !== 0
            && (!class_exists(Comment::class) || !Comment::get()->byID($commentID))
        ) {
            return $this->getOwner()->httpError(400, _t(self::class . '.ERROR_BAD_COMMENT', 'Invalid comment'));
        }

        $votesForPage = $this->getOwner()->data()->Votes();

        // check whether the member has already voted
        $hasVoted = $votesForPage->filter($voteDetails)->first();

        // set or update status
        $vote = $hasVoted ?? $vote;
        $vote->Status = $status;

        if ($hasVoted) {
            $vote->write();
        } else {
            $votesForPage->add($vote); // performs a write
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
    public function VoteStatus(?Member $member): ?string
    {
        return !$member ? null : $this->getOwner()->data()
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
    public function VoteStatusByCurrentUser(): ?string
    {
        return $this->getOwner()->VoteStatus(Security::getCurrentUser());
    }
}
