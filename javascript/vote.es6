(() => {

  /**
   * Updates the vote icon of the selected option and updates the count of likes
   * and dislikes for this object.
   *
   * @param {Element} voteEl
   * @param {String} status
   * @param {Integer} numLikes
   * @param {Integer} numDislikes
   *
   * @return void
   */
  const updateVote = (voteEl, status, numLikes, numDislikes) => {
    let links = voteEl.querySelectorAll('.vote__link');
    let selected = voteEl.querySelector(`.vote__link--${status.toLowerCase()}`);
    let likeCount = voteEl.querySelector('.vote__num--like');
    let dislikeCount = voteEl.querySelector('.vote__num--dislike');

    // reset the selected icon
    for (let i = 0; i < links.length; i++) {
      let el = links[i];

      el.classList.remove('vote__link--selected');
    }

    // update the selected icon
    selected.classList.add('vote__link--selected');

    // update counts of likes and dislikes
    if (likeCount) {
      likeCount.innerHTML = numLikes;
    }

    if (dislikeCount) {
      dislikeCount.innerHTML = numDislikes;
    }
  };

  /**
   * Handles the requests for updating our vote object
   *
   * @param  {Element} link
   * @param  {Element} parent
   *
   * @return void
   */
  const castVote = (link, parent) => {
    let url = link.getAttribute('href');
    let dataVote = link.getAttribute('data-vote');
    let dataCommentID = link.getAttribute('data-comment-id');

    fetch(url, {
        method: 'post',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: encodeURI(`vote=${dataVote}&comment_id=${dataCommentID}`)
      })
      .then((response) => {
        if (response.ok) {
          return response.json();
        }
      })
      .then((data) => {
        // updates the selected icon and count of likes and dislikes
        updateVote(parent, data.status, data.numLikes, data.numDislikes);
      })
      .then((data) => {
          parent.classList.remove('vote--processing');
      })
      .catch((err) => {
        let errMessage = parent.querySelector('.vote__message');

        if (errMessage) {
          errMessage.style.display = '';
        }

        parent.classList.remove('vote--processing');
      });
  };

  document.addEventListener('DOMContentLoaded', () => {

    let voteComponents = document.querySelectorAll('.vote');

    if (voteComponents.length === 0) {
      return;
    }

    // Add the event handler for each vote button
    for (let i = 0; i < voteComponents.length; i++) {
      let voteComponent = voteComponents[i];
      let buttons = voteComponent.querySelectorAll('[data-vote]');

      if (buttons.length === 0) {
        return;
      }

      // add event handler for like and dislike buttons
      for (let i = 0; i < buttons.length; i++) {
        let button = buttons[i];

        button.addEventListener('click', (e) => {
          e.preventDefault();

          let link = e.currentTarget;
          let parent = voteComponent;
          let message = parent.querySelector('.vote__message');

          // reset error messages's visibility
          if (message) {
            message.style.display = 'none';
          }

          // prevent the ability to click the same vote icon twice
          if (parent.classList.contains('vote--processing') || link.classList.contains('vote__link--selected')) {
            return false;
          }

          // sets the processing class to the parent for styling if needed
          parent.classList.add('vote--processing');

          // make request to API to like or dislike
          castVote(link, parent);
        });
      }
    }
  });

})();
