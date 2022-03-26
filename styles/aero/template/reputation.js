/**
* @package Reputation System
* @copyright (c) 2014 Pico
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

var reputation = {};
reputation.requestSent = false;

(function ($) {  // Avoid conflicts with other libraries

var $popup = $('#reputation-popup');

// Close error messages and other non-interactive popups by clicking on them
$popup.click(function(e) {
	if ($popup.find('.rs-message').length) {
		$popup.dissolve();
	}
});

// Rate user
$('a.rate-bad-icon, a.rate-good-icon', '.section-memberlist .rs-widget').click(function(event) {
	reputation.show_popup(this.href, event, 'rate_user');
});

// Rate post
$('a.rate-bad-icon, a.rate-good-icon', '.section-viewtopic .rs-widget').click(function(event) {
	reputation.show_popup(this.href, event, 'rate_post');
});

// Display reputation details
$('a.post-reputation').click(function(event) {
	reputation.show_popup(this.href, event, 'details');
});

// Display vote points explain
$('a.rs-explain-vote-points').click(function(event) {
	reputation.show_popup(this.href, event, 'default');
});
$popup.on('click', 'a.rs-explain-vote-points', function(event) {
	reputation.show_popup(this.href, event, 'explain');
});

// Save vote
$popup.on('click', '[name=submit]', function(event) {
	event.stopPropagation();
	event.preventDefault();
	reputation.submit_action(this.form.action, this.form.dataset.rate);
});

// Cancel rating
$popup.on('click', '[name=cancel]', function(event) {
	event.stopPropagation();
	event.preventDefault();
	$popup.dissolve();
});

// Sort votes by
$popup.on('click', 'a.sort_order', function(event) {
	event.stopPropagation();
	event.preventDefault();
	reputation.sort_order_by(this.href);
});

// Delete vote
$popup.on('click', '.reputation-delete', function(event) {
	event.stopPropagation();
	event.preventDefault();
	reputation.submit_action(this.href, 'delete_vote');
});

// Clear reputation
$popup.on('click', '.reputation-clear', function(event) {
	event.stopPropagation();
	event.preventDefault();

	if (confirm(this.dataset.langConfirm)) {
		reputation.submit_action(this.href, this.dataset.mode);
	}
});


$.extend($popup, {
	reveal: () => {
		$(document).on('keydown.phpbb.alert', (e) => {
			if (e.keyCode === 13 || e.keyCode === 27) { // Enter or Escape
				phpbb.alert.close($popup, true);
				e.preventDefault();
				e.stopPropagation();
			}
		});
		phpbb.alert.open($popup);
		return $popup;
	},
	dissolve: () => {
		phpbb.alert.close($popup, true);
		return $popup;
	}
});


reputation.show_popup = function(href, event, mode) {
	event.stopPropagation();
	event.preventDefault();

	if (reputation.requestSent) {
		return;
	}

	reputation.requestSent = true;

	$.ajax({
		url: href,
		dataType: 'html',
		beforeSend: function() {
			$popup.dissolve();
		},
		success: function(data) {
			if (data.substr(0, 1) == '{') {
				reputation.response(jQuery.parseJSON(data), mode);
			}
			else {
				$popup.empty().append(data).reveal();
			}
		},
		complete: function() {
			reputation.requestSent = false;
		}
	});
}


reputation.submit_action = function(href, mode) {
	switch (mode) {
		case 'rate_post':
		case 'rate_user':
			data = $('form', $popup).serialize();
		break;

		default:
			data = '';
		break;
	}

	$.ajax({
		url: href,
		data: data,
		dataType: 'json',
		type: 'POST',
		success: function(r) {
			reputation.response(r, mode);
		}
	});
}


reputation.response = function(data, mode) {
	var auc = (data.auc) ? '-auc' : '';
	var error = data.error_msg || data.MESSAGE_TEXT || null;

	if (error) {
		$popup.empty().append('<div class="rs-message">' + (error.includes('<h3') ? '' : `<h3>${$popup.attr('data-title')}</h3>`) + error + '</div>').reveal();
	}
	else if (data.comment_error) {
		$('.error', $popup).detach();
		$('.comment', $popup).append('<dl class="error"><span>' + data.comment_error + '</span></dl>');
	}
	else switch (mode) {
		case 'rate_post':
			var post_id = data.post_id;
			var poster_id = data.poster_id;

			$popup.dissolve();
			$(`.rs-widget[data-post=${post_id}]`).removeClass('rated_good rated_bad').addClass(data.reputation_vote);
			$(`.rs-widget[data-post=${post_id}] .post-reputation`).text(data.post_reputation).removeClass('neutral negative positive signed').addClass(data.post_reputation_class);
			$(`.rs-widget[data-user${auc}=${poster_id}] .post-reputation`).text(data.user_reputation).removeClass('neutral negative positive signed').addClass(data.user_reputation_class);
		break;

		case 'rate_user':
			var poster_id = data.user_id;

			$popup.dissolve();
			$(`.rs-widget[data-user${auc}=${poster_id}]`).removeClass('rated_good rated_bad').addClass(data.reputation_vote);
			$(`.rs-widget[data-user${auc}=${poster_id}] .post-reputation`).text(data.user_reputation).removeClass('neutral negative positive signed').addClass(data.user_reputation_class);
		break;

		case 'delete_vote':
			var rid = data.rid;
			var post_id = data.post_id;
			var poster_id = data.poster_id;

			$('#rs-list-item-' + rid).hide(100, function() {
				$(this).detach();
				if ($('.reputation-list-item').length == 0) {
					$popup.dissolve();
				}
			});

			if (!post_id && data.is_own_single_vote)
				$(`.rs-widget[data-user${auc}=${poster_id}]`).removeClass('rated_good rated_bad');
			if (post_id && data.is_own_single_vote)
				$(`.rs-widget[data-post=${post_id}]`).removeClass('rated_good rated_bad');
			if (post_id)
				$(`.rs-widget[data-post=${post_id}] .post-reputation`).text(data.post_reputation).removeClass('neutral negative positive signed').addClass(data.post_reputation_class);
			$(`.rs-widget[data-user${auc}=${poster_id}] .post-reputation`).text(data.user_reputation).removeClass('neutral negative positive signed').addClass(data.user_reputation_class);

			if ($popup.find('.rs-message').length) {
				$popup.dissolve();
			}
		break;

		case 'clear_post':
			if (!data.clear_post) break;

			var post_id = data.post_id;
			var poster_id = data.poster_id;

			$('.reputation-list-item').slideUp(100, function() {
				$popup.dissolve();
				$(`.rs-widget[data-post=${post_id}]`).removeClass('rated_good rated_bad');
				$(`.rs-widget[data-post=${post_id}] .post-reputation`).text(data.post_reputation).removeClass('neutral negative positive signed').addClass(data.post_reputation_class);
				$(`.rs-widget[data-user${auc}=${poster_id}] .post-reputation`).text(data.user_reputation).removeClass('neutral negative positive signed').addClass(data.user_reputation_class);
			});
		break;

		case 'clear_user':
			if (!data.clear_user) break;

			var post_ids = data.post_ids;
			var poster_id = data.poster_id;

			$('.reputation-list-item').slideUp(100, function() {
				$popup.dissolve();

				$.each(post_ids, function(i, post_id) {
					$(`.rs-widget[data-post=${post_id}]`).removeClass('rated_good rated_bad');
					$(`.rs-widget[data-post=${post_id}] .post-reputation`).text(data.post_reputation).removeClass('neutral negative positive signed').addClass(data.post_reputation_class);
				});

				$(`.rs-widget[data-user=${poster_id}]`).removeClass('rated_good rated_bad');
				$(`.rs-widget[data-user=${poster_id}] .post-reputation`).text(data.user_reputation).removeClass('neutral negative positive signed').addClass(data.user_reputation_class);

				$(`.rs-widget[data-user-auc=${poster_id}]`).removeClass('rated_good rated_bad');
				$(`.rs-widget[data-user-auc=${poster_id}] .post-reputation`).text(data.user_reputation).removeClass('neutral negative positive signed').addClass(data.user_reputation_class);
			});
		break;
	}
}


reputation.sort_order_by = function(href) {
	$.ajax({
		url: href,
		dataType: 'html',
		success: function(s) {
			$popup.empty().append(s);
		}
	});
}

})(jQuery); // Avoid conflicts with other libraries
