<?php

namespace NZTA\Vote\Models;

use Page;
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

    /**
     * @inheritDoc
     *
     * IF the comment module exists, then via configuration (yml)
     * Comment support is added as 'Comment' => Comment::class
     */
    private static $has_one = [
        'Page'    => Page::class,
        'Member'  => Member::class,
    ];
}
