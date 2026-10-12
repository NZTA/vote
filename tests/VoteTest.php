<?php

namespace NZTA\Vote\Tests;

use NZTA\Vote\Extensions\VoteControllerExtension;
use NZTA\Vote\Extensions\VoteExtension;
use NZTA\Vote\Models\Vote;
use Override;
use Page;
use PageController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Comments\Extensions\CommentsExtension;
use SilverStripe\Comments\Model\Comment;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;

class VoteTest extends SapphireTest
{
    #[Override]
    protected static $fixture_file = ['./VoteTest.yml'];

    #[Override]
    protected static $required_extensions = [
        PageController::class => [VoteControllerExtension::class],
        Page::class => [VoteExtension::class],
    ];

    protected static $illegal_extensions = [
        SiteTree::class => [CommentsExtension::class],
    ];

    /**
     * @var PageController&VoteControllerExtension
     */
    protected ?PageController $controller;

    public function setUp(): void
    {
        parent::setUp();
        $this->logOut();
        $page = $this->objFromFixture(Page::class, 'Page1');
        $this->controller = new PageController($page);
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
        $request = new HTTPRequest('POST', 'vote', [], ['vote' => 'Like']);
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
        $request = new HTTPRequest('POST', 'vote', [], ['vote' => 'Upvote']);
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

    public function testCommentParametersHaveNoAffectWhenCommentsDontExist()
    {
        if (class_exists(Comment::class)) {
            $this->markTestSkipped('Comments exist - Don\'t test that comment IDs shouldn\'t matter when they do');
        }
        $member = $this->objFromFixture(Member::class, 'Member1');
        $this->logInAs($member);
        $request = new HTTPRequest('POST', 'vote', [], ['vote' => 'Like', 'comment_id' => 9001]);
        $this->controller->setRequest($request);
        try {
            $response = $this->controller->vote();
        } catch (HTTPResponse_Exception $e) {
            $response = $e->getResponse();
        }

        // Ensure logged users can only vote
        $this->assertEquals(200, $response->getstatusCode());
        $this->assertEquals('OK', $response->getStatusDescription());
        $this->assertSame('Like', json_decode($response->getBody())?->status);
    }

    public function testVoteOnPage()
    {
        $member = $this->objFromFixture(Member::class, 'Member1');
        $this->logInAs($member);
        $request = new HTTPRequest('POST', 'vote', [], ['vote' => 'Like']);
        $this->controller->setRequest($request);

        $response = $this->controller->vote();

        $this->assertEquals(200, $response->getstatusCode());
        $responseBody = json_decode($response->getBody());
        $this->assertEquals(1, $responseBody->numLikes);
        $vote = Vote::get()->filter(['MemberID'  => $member->ID])->first();
        $this->assertInstanceOf(Vote::class, $vote, 'Vote should have been saved');
        $this->assertEquals('Like', $vote->Status);
    }
}
