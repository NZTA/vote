# Vote

## Introduction

Provides the ability to vote on a Page.
If the comments module is present, then votes can also be added to comments.

## Installation

```bash
composer require nzta/vote
```

Add the vote extensions to pages where desired.
E.g. to enable globally (on all pages), configure such as below:

```yml
Page:
  extensions:
    - NZTA\Vote\Extensions\VoteExtension

PageController:
  extensions:
    - NZTA\Vote\Extensions\VoteControllerExtension
```

### Comment support
If the comments module is present (`silverstripe/comments`) then comment support is automatic for comments that exist
on a configured page (per the controller extension as above). See _config/comment_support.yml for detail.
