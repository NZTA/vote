<?php

namespace NZTA\Vote\Tests;

use NZTA\Vote\Extensions\VoteControllerExtension;
use NZTA\Vote\Extensions\VoteExtension;
use NZTA\Vote\Models\Vote;
use Page;
use PageController;
use SilverStripe\Comments\Model\Comment;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;

class VoteTest extends SapphireTest
{
    protected static $fixture_file = './VoteTest.yml';

    protected static $required_extensions = [
        PageController::class => [VoteControllerExtension::class],
        Page::class => [VoteExtension::class],
    ];

    protected ?PageController $controller;

    public function setUp(): void
    {
        parent::setUp();
        $this->logOut();
        $page = $this->objFromFixture(Page::class, 'Page1');
        $this->controller = PageController::create($page);
    }

    public function testVoteErrorsOnBadHttpMethod()
    {
        try {
            $response = $this->controller->vote(); // null request
        } catch (HTTPResponse_Exception $e) {
            $response = $e->getResponse();
        }

        // Ensure accept only POST requests
        $this->assertSame(405, $response->getstatusCode());
        $this->assertSame('Method Not Allowed', $response->getStatusDescription());
        $this->assertSame('POST', $response->getHeader('Allow'));
        $this->assertSame('Only HTTP POST requests are accepted', $response->getBody());
    }

    public function testOnlyAuthenticatedMembersCanVote()
    {
        $request = new HTTPRequest('POST', 'vote', '', ['vote' => 'Like', 'comment_id' => 0]);
        $this->controller->setRequest($request);
        try {
            $response = $this->controller->vote();
        } catch (HTTPResponse_Exception $e) {
            $response = $e->getResponse();
        }

        // Ensure logged users can only vote
        $this->assertEquals(403, $response->getstatusCode());
        $this->assertEquals('Forbidden', $response->getStatusDescription());
        $this->assertSame('Log in to vote', $response->getBody());
    }

    public function testBadVotesAreRejected()
    {
        $member = $this->objFromFixture(Member::class, 'Member1');
        $this->logInAs($member);
        $request = new HTTPRequest('POST', 'vote', '', ['vote' => 'Upvote', 'comment_id' => 0]);
        $this->controller->setRequest($request);
        try {
            $response = $this->controller->vote();
        } catch (HTTPResponse_Exception $e) {
            $response = $e->getResponse();
        }

        // Ensure logged users can only vote
        $this->assertEquals(400, $response->getstatusCode());
        $this->assertEquals('Bad Request', $response->getStatusDescription());
        $this->assertSame('Invalid vote', $response->getBody());
    }

    public function testVotesOnBadCommentsAreRejected()
    {
        $member = $this->objFromFixture(Member::class, 'Member1');
        $this->logInAs($member);
        $request = new HTTPRequest('POST', 'vote', '', ['vote' => 'Like', 'comment_id' => 9001]);
        $this->controller->setRequest($request);
        try {
            $response = $this->controller->vote();
        } catch (HTTPResponse_Exception $e) {
            $response = $e->getResponse();
        }

        // Ensure logged users can only vote
        $this->assertEquals(400, $response->getstatusCode());
        $this->assertEquals('Bad Request', $response->getStatusDescription());
        $this->assertSame('Invalid comment', $response->getBody());
    }

    public function testVoteOnComment()
    {
        $member = $this->objFromFixture(Member::class, 'Member1');
        $this->logInAs($member);
        $comment = $this->objFromFixture(Comment::class, 'Comment1');
        $request = new HTTPRequest('POST', 'vote', '', ['vote' => 'Like', 'comment_id' => $comment->ID]);
        $this->controller->setRequest($request);

        $response = $this->controller->vote();

        $this->assertEquals(200, $response->getstatusCode());
        $responseBody = json_decode($response->getBody());
        $this->assertEquals(1, $responseBody->numLikes);
        $vote = Vote::get()->filter(['MemberID'  => $member->ID, 'CommentID' => $comment->ID])->first();
        $this->assertInstanceOf(Vote::class, $vote, 'Vote should have been saved');
        $this->assertEquals('Like', $vote->Status);
    }

    public function testVoteOnPage()
    {
        $member = $this->objFromFixture(Member::class, 'Member1');
        $this->logInAs($member);
        $request = new HTTPRequest('POST', 'vote', '', ['vote' => 'Like', 'comment_id' => 0]);
        $this->controller->setRequest($request);

        $response = $this->controller->vote();

        $this->assertEquals(200, $response->getstatusCode());
        $responseBody = json_decode($response->getBody());
        $this->assertEquals(1, $responseBody->numLikes);
        $vote = Vote::get()->filter(['MemberID'  => $member->ID, 'CommentID' => 0])->first();
        $this->assertInstanceOf(Vote::class, $vote, 'Vote should have been saved');
        $this->assertEquals('Like', $vote->Status);
    }
}
