<?php
/**
*
* Reputation System
*
* @copyright (c) 2014 Lukasz Kaczynski
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'RS_DISABLED'				=> 'Administrator has disabled this feature.',

	'RS_COMMENT'				=> 'Comment',
	'RS_COMMENT_OPTIONAL'		=> 'Comment (optional)',
	'RS_COMMENT_REQUIRED'		=> 'Comment (required)',
	'RS_POINTS'					=> 'Points',
	'RS_POINTS_SUM'				=> 'points',
	'RS_POINTS_TO_GIVE'			=> 'How many points',

	'RS_SORT_BY'				=> 'Sort by',
	'RS_DELETE_VOTE'			=> 'Delete your vote',
	'RS_POST_BY'				=> 'Post author:',
	'RS_POST_GIVE_POSITIVE'		=> 'Rate post positive',
	'RS_POST_GIVE_NEGATIVE'		=> 'Rate post negative',
	'RS_USER_GIVE_POSITIVE'		=> 'Rate user positive',
	'RS_USER_GIVE_NEGATIVE'		=> 'Rate user negative',

	'RS_ACTION'					=> 'Type',
	'RS_DATE'					=> 'Date',
	'RS_FROM'					=> 'From',
	'RS_FROM_USER'				=> 'From user',
	'RS_POST_COUNT'				=> 'Points for posts',
	'RS_USER_COUNT'				=> 'Points from user profile',
	'RS_COUNT'					=> 'Votes',
	'RS_POSITIVE_COUNT'			=> 'Positive votes',
	'RS_NEGATIVE_COUNT'			=> 'Negative votes',
	'RS_STATS'					=> 'Statistics of received points',
	'RS_WEEK'					=> 'Last week',
	'RS_MONTH'					=> 'Last month',
	'RS_6MONTHS'				=> 'Last 6 months',
	'RS_POINT'					=> 'Point',
	'RS_POINTS_TITLE'			=> [
		1	=> ' point',
		2	=> ' points',
	],
	'RS_POST_DELETE'			=> 'Post deleted',
	'RS_POWER'					=> 'Voting points',
	'RS_TIME'					=> 'Time',
	'RS_TO'						=> 'to',
	'RS_TO_USER'				=> 'To',
	'RS_VOTING_POWER_LEFT'		=> '%1$d total, %2$d available',

	'RS_EMPTY_DATA'				=> 'There are no votes.',
	'RS_NA'						=> 'n/a',
	'RS_NO_ID'					=> 'No ID',
	'RS_NO_REPUTATION'			=> 'There is no reputation data.',

	'RS_POINTS_DELETED'			=> [
		1	=> 'The vote has been deleted.',
		2	=> 'The votes have been deleted.',
	],

	'RS_CLEAR_POST'				=> 'Clear post rating',
	'RS_CLEAR_POST_CONFIRM'		=> 'Do you really want to delete all votes given to that post?',
	'RS_CLEARED_POST'			=> 'The post rating has been cleared.',
	'RS_CLEAR_USER'				=> 'Clear user reputation',
	'RS_CLEAR_USER_CONFIRM'		=> 'Do you really want to delete all votes given to that user?',
	'RS_CLEARED_USER'			=> 'The user reputation has been cleared.',

	'LIST_REPUTATIONS'				=> [
		1	=> '%d vote',
		2	=> '%d votes',
	],

	'RS_MORE_DETAILS'				=> 'More details →',

	'RS_POWER_DETAILS'				=> 'How %1$s’s voting points should be calculated',
	'RS_POWER_DETAILS_SELF'			=> 'How your voting points should be calculated',

	'RS_POWER_DETAIL_POSTS'			=> [
		1 => '+1 point per every %1$d post',
		2 => '+1 point per every %1$d posts',
	],
	'RS_POWER_DETAIL_AGE'			=> [
		1 => '+1 point per every %1$d day onboard',
		2 => '+1 point per every %1$d days onboard',
	],
	'RS_POWER_DETAIL_REPUTATION'	=> [
		1 => '±1 point per every ±%1$d reputation points',
		2 => '±1 point per every ±%1$d reputation points',
	],
	'RS_POWER_DETAIL_WARNINGS'		=> [
		1 => '−%1$d point per every warning',
		2 => '−%1$d points per every warning',
	],

	'RS_POWER_DETAIL_MIN'			=> 'Minimum allowed voting points for all users',
	'RS_POWER_DETAIL_MAX'			=> 'Maximun allowed voting points for all users',
	'RS_POWER_DETAIL_RESULTING'		=> 'Result',
	'RS_POWER_DETAIL_GROUP_POWER'	=> 'Amoint of voting points is determined by user group power',

	'RS_ANTISPAM_INFO'			=> 'You cannot rate the post so soon. You may try again later.',
	'RS_COMMENT_TOO_LONG'		=> 'Your comment contains %1$s characters and is too long. <br /> The maximum allowed characters: %2$s.',
	'RS_NO_COMMENT'				=> 'Please enter a comment.',
	'RS_NO_POST'				=> 'There is no such post.',
	'RS_NO_POWER'				=> 'You have no voting points. <br /> At least 1 point is required to vote. <br /> <a href="%1$s" class="rs-explain-vote-points"> How to get voting points </a>',
	'RS_NO_POWER_LEFT'			=> 'You have spent all available voting points (%2$s/%1$s). <br /> Wait until they renew. <br /> Renewal occurs in %3$s after your last vote. <br /> <a href="%4$s" class="rs-explain-vote-points"> How to get more voting points </a>',
	'RS_NO_USER_ID'				=> 'The requested user does not exist.',
	'RS_POST_RATING'			=> 'Rating post',
	'RS_RATE_BUTTON'			=> 'Rate',
	'RS_SAME_POST'				=> 'You have already given this post %s points.',
	'RS_SAME_USER'				=> 'You have already rated this user.',
	'RS_SELF'					=> 'You cannot rate yourself',
	'RS_SELF_POST'				=> 'You cannot rate your own posts.',
	'RS_USER_ANONYMOUS'			=> 'You are not allowed to rate anonymous users.',
	'RS_USER_BANNED'			=> 'You are not allowed to rate banned users.',
	'RS_USER_CANNOT_DELETE'		=> 'You do not have permission to delete that vote.',
	'RS_USER_DISABLED'			=> 'You are not allowed to rate.',
	'RS_USER_CANNOT_RATE'		=> 'Cannot perform operation.',
	'RS_USER_GAP'				=> 'You cannot rate the same user so soon. You can try again in %s.',
	'RS_USER_NEGATIVE'			=> 'You are not allowed to rate negatively. <br /> Your reputation has to be higher than %s.',
	'RS_USER_RATING'			=> 'Rating user',
	'RS_VIEW_DISALLOWED'		=> 'You are not allowed to view reputation details.',
	'RS_VOTE_POWER_LEFT_OF_MAX'	=> '%1$d voting points left of %2$d',
	'RS_VOTE_SAVED'				=> 'Vote saved',
	'RS_WARNING_RATING'			=> 'Warning from the moderator',
	'RS_CLOSE'					=> 'Close',

	'RS_HOURS'					=> [
		1 => '%d hour',
		2 => '%d hours',
	],

	'RS_USER_IS_EXCLUDED'		=> '%s’s reputation is immutable and impeccable.',
]);
