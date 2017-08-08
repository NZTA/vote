<?php

class Vote extends DataObject
{

    /**
     * @var array
     */
    private static $db = [
        'Status' => 'enum(array("Like", "Dislike"))'
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Page' => 'Page',
        'Comment' => 'Comment', // can vote on a Page OR a Comment
        'Member' => 'Member'
    ];
}
