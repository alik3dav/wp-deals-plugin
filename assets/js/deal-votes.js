(function () {
	'use strict';

	if (typeof dealsVotes === 'undefined') {
		return;
	}

	var components = document.querySelectorAll('[data-deal-vote-component]');
	if (!components.length) {
		return;
	}

	var updateComponent = function (component, payload) {
		var upButton = component.querySelector('[data-vote-type="1"]');
		var downButton = component.querySelector('[data-vote-type="-1"]');
		var scoreNode = component.querySelector('[data-vote-score]');
		var countsNode = component.querySelector('[data-vote-counts]');
		var currentVote = Number(payload.current_user_vote || 0);

		component.dataset.currentVote = String(currentVote);

		if (scoreNode) {
			scoreNode.textContent = String(payload.score);
		}

		if (countsNode) {
			countsNode.dataset.upvotes = String(payload.upvotes);
			countsNode.dataset.downvotes = String(payload.downvotes);
			countsNode.textContent = payload.upvotes + ' up / ' + payload.downvotes + ' down';
		}

		if (upButton) {
			upButton.classList.toggle('is-active', currentVote === 1);
		}

		if (downButton) {
			downButton.classList.toggle('is-active', currentVote === -1);
		}
	};

	var setError = function (component, message) {
		var errorNode = component.querySelector('[data-vote-error]');
		if (!errorNode) {
			return;
		}

		errorNode.textContent = message;
		errorNode.hidden = !message;
	};

	var setPending = function (component, pending) {
		component.classList.toggle('is-pending', pending);
		component.querySelectorAll('[data-vote-type]').forEach(function (button) {
			button.disabled = pending || !dealsVotes.isLoggedIn;
		});
	};

	components.forEach(function (component) {
		component.addEventListener('click', function (event) {
			var button = event.target.closest('[data-vote-type]');
			if (!button || !component.contains(button)) {
				return;
			}

			event.preventDefault();

			if (!dealsVotes.isLoggedIn) {
				setError(component, dealsVotes.loginMessage || 'Please log in to vote.');
				return;
			}

			setError(component, '');
			setPending(component, true);

			var payload = {
				deal_id: Number(component.dataset.dealId),
				vote_type: Number(button.dataset.voteType)
			};

			window.fetch(dealsVotes.restUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': dealsVotes.nonce
				},
				credentials: 'same-origin',
				body: JSON.stringify(payload)
			}).then(function (response) {
				if (!response.ok) {
					return response.json().then(function (data) {
						var message = dealsVotes.errorMessage || 'Unable to process vote. Please try again.';
						if (data && data.message) {
							message = data.message;
						}
						throw new Error(message);
					});
				}
				return response.json();
			}).then(function (data) {
				updateComponent(component, data);
			}).catch(function (error) {
				setError(component, error.message || dealsVotes.errorMessage || 'Unable to process vote. Please try again.');
			}).finally(function () {
				setPending(component, false);
			});
		});
	});
})();
