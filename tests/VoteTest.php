<?php

class VoteTest extends FunctionalTest
{

    /**
     * @var string
     */
    protected static $fixture_file = './VoteTest.yml';

    /**
     * @var array
     */
    protected $requiredExtensions = [
        'Page_Controller' => [
            'VoteControllerExtension'
        ]
    ];

    public function testVote()
    {
        // Ensure vote has been added to the comment
        $comment = $this->objFromFixture('Comment', 'Comment1');
        $commentID = $comment->ID;

        $postData = [
            'comment_id' => $commentID,
            'vote'       => 'Like',
        ];

        $controller = new Page_Controller();
        $request = new SS_HTTPRequest('POST', 'vote', '', $postData);
        $response = $controller->vote();

        // Ensure accept only ajax requests
        $this->assertEquals(400, $response->getstatusCode());
        $this->assertEquals('The request needs to be post AJAX.', $response->getStatusDescription());

        // Adding ajax headers
        $request->addHeader('X-Requested-With', 'XMLHttpRequest');
        $controller->setRequest($request);
        $response = $controller->vote();

        // Ensure logged users can only vote and accept the ajax request
        $this->assertEquals(400, $response->getstatusCode());
        $this->assertEquals('Sorry, you need to login to vote.', $response->getStatusDescription());

        // Login as Member1
        $member = $this->objFromFixture('Member', 'Member1');
        $this->logInAs($member);

        $response = $controller->vote();
        $responseBody = json_decode($response->getBody());

        // Ensure getting success response
        $this->assertEquals(200, $response->getstatusCode());

        // Ensure getting the correct response body
        $this->assertEquals(1, $responseBody->numLikes);

        // Ensure vote saved to database with under this $member
        $vote = Vote::get()->filter(
            [
                'MemberID'  => $member->ID,
                'CommentID' => $commentID
            ]
        )->first();

        // Asserts vote and data
        $this->assertTrue($vote instanceof Vote);
        $this->assertEquals('Like', $vote->Status);
    }
}
