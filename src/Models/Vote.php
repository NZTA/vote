<?php

namespace NZTA\Vote\Models;

use Page;
use SilverStripe\Comments\Model\Comment;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class Vote extends DataObject
{
    private static $table_name = 'Vote';

    private static $singular_name = 'Vote';

    private static $plural_name = 'Votes';

    private static $db = [
        'Status' => 'Enum("Like, Dislike", "Like")',
    ];

    private static $has_one = [
        'Page'    => Page::class,
        'Comment' => Comment::class, // can vote on a Page OR a Comment
        'Member'  => Member::class,
    ];
}
