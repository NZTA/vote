<?php

namespace NZTA\Vote\Models;

use Page;
use SilverStripe\Comments\Model\Comment;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class Vote extends DataObject
{
    /**
     * @var string
     */
    private static $table_name = 'Vote';

    /**
     * @var string
     */
    private static $singular_name = 'Vote';

    /**
     * @var string
     */
    private static $plural_name = 'Votes';

    /**
     * @var array
     */
    private static $db = [
        'Status' => 'Enum("Like, Dislike")',
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Page'    => Page::class,
        'Comment' => Comment::class, // can vote on a Page OR a Comment
        'Member'  => Member::class,
    ];
}
