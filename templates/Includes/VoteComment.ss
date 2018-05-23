<% with $Comment %>
    <div class="vote<% if $VoteStatusByCurrentUser %> vote--casted<% end_if %>">
        <a data-vote="like" data-comment-id="$ID" href="{$Parent.Link}vote" class="vote__link vote__link--like<% if $VoteStatusByCurrentUser == 'Like' %> vote__link--selected<% end_if %>">
            <i class="i i-thumb-up i--unselected"></i>
            <i class="i i-thumb-up-blue i--selected"></i>
            <span class="vote__num vote__num--like">$LikeCount</span>
        </a>

        <a data-vote="dislike" data-comment-id="$ID" href="{$Parent.Link}vote" class="vote__link vote__link--dislike<% if $VoteStatusByCurrentUser == 'Dislike' %> vote__link--selected<% end_if %>">
            <i class="i i-thumb-down i--unselected"></i>
            <i class="i i-thumb-down-blue i--selected"></i>
            <span class="vote__num vote__num--dislike">$LikeCount</span>
        </a>

        <p class="vote__message theme--error hide">
            There was an error submitting your vote. Please try again later.
        </p>
    </div>
<% end_with %>
