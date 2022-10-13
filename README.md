# Vote

Provides the ability to vote on a Page OR a Comment.

## Features

 * Able to like or unlike a page, member or comment.

## Installation

    composer require nzta/vote

To get work vote module in all the pages, You need to add this to your config.yml file.

```
Page:
  extensions:
    - NZTA\Vote\Extensions\VoteExtension

PageController:
  extensions:
    - NZTA\Vote\Extensions\VoteControllerExtension
```