<div class="vote<% if $VoteStatusByCurrentUser %> vote--casted<% end_if %>">
    <div>
        <% if $VoteTitle %>
            <p class="vote__title">$VoteTitle</p>
        <% end_if %>
        <a data-vote="Like" data-comment-id="0" href="{$Link}vote" class="vote__link vote__link--like<% if $VoteStatusByCurrentUser == 'Like' %> vote__link--selected<% end_if %>">
            <i class="i i-thumb-up i--unselected"></i>
            <i class="i i-thumb-up-blue i--selected"></i>
            <span class="vote__num vote__num--like">$Votes.Filter('Status', 'Like').Filter('CommentID', '0').Count</span>
        </a>

        <a data-vote="Dislike" data-comment-id="$CommentID" href="{$Link}vote" class="vote__link vote__link--dislike<% if $VoteStatusByCurrentUser == 'Dislike' %> vote__link--selected<% end_if %>">
            <i class="i i-thumb-down i--unselected"></i>
            <i class="i i-thumb-down-blue i--selected"></i>
            <span class="vote__num vote__num--dislike">$Votes.Filter('Status', 'Dislike').Filter('CommentID', '0').Count</span>
        </a>
    </div>

    <p class="vote__message theme--error hide">
        There was error submitting your vote. Please try again later.
    </p>
</div>
