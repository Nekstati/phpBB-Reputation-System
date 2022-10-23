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
	'ACP_REPUTATION_RATE_EXPLAIN'	=> 'Here you can award additional reputation points to any users.',

	'RS_ENABLE'						=> 'Enable reputation system',

	'RS_SYNC'						=> 'Synchronise reputation system',
	'RS_SYNC_EXPLAIN'				=> 'You can synchronise reputation points after a mass removal of posts/topics/users, changing reputation settings, changing post authors, conversions from others systems. This may take a while. You will be notified when the process is completed. <br /> <strong>Warning!</strong> All reputation points that do not match the reputation settings will be deleted during synchronization. It is recommended to make backup of the reputation table (DB) before synchronisation.',
	'RS_SYNC_REPUTATION_CONFIRM'	=> 'Are you sure you wish to synchronise reputations?',

	'RS_TRUNCATE'					=> 'Clear reputation system',
	'RS_TRUNCATE_EXPLAIN'			=> 'This procedure completely removes all data. <br /> <strong>Action is not reversible!</strong>',
	'RS_TRUNCATE_CONFIRM'			=> 'Are you sure you wish to clear reputation system?',
	'RS_TRUNCATE_DONE'				=> 'Reputations were cleared.',

	'REPUTATION_SETTINGS_CHANGED'	=> '<strong>Altered reputation system settings</strong>',

	// Setting legend
	'ACP_RS_MAIN'					=> 'General',
	'ACP_RS_DISPLAY'				=> 'Display settings',
	'ACP_RS_POSTS_RATING'			=> 'Post rating',
	'ACP_RS_USERS_RATING'			=> 'User rating',
	'ACP_RS_COMMENT'				=> 'Comments',
	'ACP_RS_POWER'					=> 'Voting points',
	'ACP_RS_TOPLIST'				=> 'Toplist',
	'ACP_RS_PENALTY'				=> 'Penalty for inactivity',

	// General
	'RS_NEGATIVE_POINT'				=> 'Allow negative points',
	'RS_MIN_REP_NEGATIVE'			=> 'Minimum reputation for negative voting',
	'RS_MIN_REP_NEGATIVE_EXPLAIN'	=> 'How much reputation is required to give negative points. Setting the value to 0 disables this behaviour.',
	'RS_WARNING'					=> 'Enable warnings',
	'RS_WARNING_EXPLAIN'			=> 'Users with proper permissions can give negative points when warning users.',
	'RS_WARNING_MAX_POWER'			=> 'Maximum reputation points for warnings',
	'RS_WARNING_MAX_POWER_EXPLAIN'	=> 'Maximum negative points that can be given when warning user.',
	'RS_MIN_POINT'					=> 'Minimum points',
	'RS_MIN_POINT_EXPLAIN'			=> 'Limits the minimum reputation points a user can have. Setting the value to 0 disables this behaviour.',
	'RS_MAX_POINT'					=> 'Maximum points',
	'RS_MAX_POINT_EXPLAIN'			=> 'Limits the maximum reputation points a user can receive. Setting the value to 0 disables this behaviour.',
	'RS_PREVENT_OVERRATING'			=> 'Prevent overrating',
	'RS_PREVENT_OVERRATING_EXPLAIN'	=> 'Block users from rating the same user. <br /> <em>Example:</em> if user A has more than 10 votes and 85% of them come from user B, user B can not rate that user until his votes ratio is higher than 85%. <br /> To disable this feature set one or both values to 0.',
	'RS_PREVENT_NUM'				=> 'Total votes received by user A ≥',
	'RS_PREVENT_PERC'				=> '<br /> and ratio of user B votes ≥',
	'RS_PER_PAGE'					=> 'Votes per page in reputation tables',
	'RS_DISPLAY_AVATAR'				=> 'Display avatars',
	'RS_POINT_TYPE'					=> 'Method for displaying points',
	'RS_POINT_TYPE_EXPLAIN'			=> 'Viewing reputation points can be displayed as either the exact value of points a user gave or as an icon showing a plus or minus. The “Icon” method is useful if you set up reputation points so that one rating always equals to one point.',
	'RS_POINT_VALUE'				=> 'Number +N/−N',
	'RS_POINT_IMG'					=> 'Icon +/−',

	'RS_USERS_TO_EXCLUDE'			=> 'Users with unchangeable reputation',
	'RS_USERS_TO_EXCLUDE_EXPLAIN'	=> 'Enter IDs of users, who cannot be rated. Separate IDs by commas. Example: 2,34,567',
	'RS_USERS_TO_EXCLUDE_ERROR'		=> 'Only digits and commas are allowed in the field “Users with unchangeable reputation”.',

	'RS_INSTANT_VOTE'				=> 'Instant vote',
	'RS_INSTANT_VOTE_EXPLAIN'		=> 'Save vote immediately after pressing +/− button, without any popup window.',
	'RS_INSTANT_VOTE_TEXT'			=> 'To obtain this:<br />
		1. Disable comments below.<br />
		2. Disable voting points below or set “Maximum points spending per vote” = 1.',

	// Post rating
	'RS_POST_RATING'				=> 'Enable post rating',
	'RS_POST_RATING_EXPLAIN'		=> 'This is a global setting. Besides, you can enable or disable reputation for each forum separately (see forums management page).',
	'RS_POST_RATING_FIRST_ONLY'		=> 'First post only',
	'RS_ALLOW_REPUTATION_BUTTON'	=> 'Submit and enable reputation system in all forums',
	'RS_ANTISPAM'					=> 'Anti-spam',
	'RS_ANTISPAM_EXPLAIN'			=> 'Block users from rating any more posts after they have rated the defined number of posts within the defined number of hours. To disable this feature set one or both values to 0.',
	'RS_POSTS'						=> 'Number of rated posts',
	'RS_HOURS'						=> 'in the last hours',
	'RS_ANTISPAM_METHOD'			=> 'Anti-spam check method',
	'RS_ANTISPAM_METHOD_EXPLAIN'	=> '“Same user” method checks reputation given to the same user. “All users” method checks reputation regardless of who received points.',
	'RS_SAME_USER'					=> 'Same user',
	'RS_ALL_USERS'					=> 'All users',

	'RS_CONTENT_WIDGET_TYPE'		=> 'What to show above post text',
	'RS_CONTENT_WIDGET_TYPE_2'		=> 'Post rating value and post rate buttons +/−',
	'RS_CONTENT_WIDGET_TYPE_1'		=> 'Post rating value',
	'RS_CONTENT_WIDGET_TYPE_0'		=> 'Nothing',

	'RS_MINIPROFILE_WIDGET_TYPE'	=> 'What to show under user avatar',
	'RS_MINIPROFILE_WIDGET_TYPE_EXPLAIN'	=> '<i>Note:</i> On narrow screens (mobile devices) only one of these two reputation blocks is displayed — namely, the one containing the rate buttons.',
	'RS_MINIPROFILE_WIDGET_TYPE_2'	=> 'User rating value and post rate buttons +/−',
	'RS_MINIPROFILE_WIDGET_TYPE_1'	=> 'User rating value',
	'RS_MINIPROFILE_WIDGET_TYPE_0'	=> 'Nothing',
	
	// 'RS_AUC_MINIPROFILE_DOUBLE_REP'			=> 'In auction forums under user avatar show',
	// 'RS_AUC_MINIPROFILE_DOUBLE_REP_EXPLAIN'	=> 'If auction extension enabled.',
	// 'RS_AUC_MINIPROFILE_DOUBLE_REP_0'		=> 'Only seller/buyer reputation',
	// 'RS_AUC_MINIPROFILE_DOUBLE_REP_1'		=> 'Both seller/buyer and forum reputation',

	'RS_WIDGET_TYPE_ERROR'			=> 'Post rate buttons can be displayed either above post text, or below user avatar, but not in both places.',

	'RS_SHOW_ZERO_REP'				=> 'If user or post rating = 0',
	'RS_SHOW_ZERO_REP_1'			=> 'Show',
	'RS_SHOW_ZERO_REP_0'			=> 'Hide',

	// User rating
	'RS_USER_RATING'				=> 'Allow rating of users from their profile page',
	'RS_USER_RATING_GAP'			=> 'Voting gap',
	'RS_USER_RATING_GAP_EXPLAIN'	=> 'Time period a user must wait before they can give another rating to a user they have already rated. Setting the value to 0 disables this behaviour and users can rate other users once each.',

	// Comments
	'RS_ENABLE_COMMENT'				=> 'Enable comments',
	'RS_ENABLE_COMMENT_EXPLAIN'		=> 'When enabled, users will be able to add a personal comment with their rating.',
	'RS_FORCE_COMMENT'				=> 'Force user to enter comment',
	'RS_COMMENT_NO'					=> 'No',
	'RS_COMMENT_POST'				=> 'Yes, only post ratings',
	'RS_COMMENT_USER'				=> 'Yes, only user ratings',
	'RS_COMMENT_BOTH'				=> 'Yes, both user and post ratings',
	'RS_COMMEN_LENGTH'				=> 'Max comment length',
	'RS_COMMEN_LENGTH_EXPLAIN'		=> 'Set to 0 for unlimited characters.',

	// Voting points
	'RS_ENABLE_POWER'				=> 'Enable voting points',
	'RS_ENABLE_POWER_EXPLAIN'		=> 'If this function is disabled, user can rate posts/users without restrictions. If enabled, user spends his voting points per each voting. New users have less voting points, active and veteran users gain more ones. The more points user has, the more he can vote during a specified period of time and the more influence he can have on the rating of another user or post. Additional voting points can be gained by the methods that you can set up below.',
	'RS_POWER_RENEWAL'				=> 'Voting points renewal time',
	'RS_POWER_RENEWAL_EXPLAIN'		=> 'This controls how users can spend earned points. <br/> If you set this option, users must wait for the given time interval before they can vote again. The more voting points a user has, the more points they can spend in the set time. <br /> Setting the value to 0 disables this behaviour and users can vote without waiting.',
	'RS_MIN_POWER'					=> 'Starting/minimum voting points',
	'RS_MIN_POWER_EXPLAIN'			=> 'This is how much voting points newly registered users, banned users and users with low reputation or other criteria have. Users can’t go below this minimum. <br/> Allowed 0-10. Recommended 1.',
	'RS_MAX_POWER'					=> 'Maximum points spending per vote',
	'RS_MAX_POWER_EXPLAIN'			=> 'Maximum amount of points that a user can spend per vote. Even if a user has millions of points, they’ll still be limited by this maximum number when voting. <br/> Allowed 1-20. Recommended 1.',
	// 'RS_MAX_POWER_AUC'				=> 'Maximum points spending per vote for seller/buyer reputation',

	'RS_TOTAL_POSTS'				=> 'Gain points with number of posts',
	'RS_TOTAL_POSTS_EXPLAIN'		=> 'User will gain 1 voting point every X number of posts set here.',
	'RS_MEMBERSHIP_DAYS'			=> 'Gain points with length of the user’s membership',
	'RS_MEMBERSHIP_DAYS_EXPLAIN'	=> 'User will gain 1 voting point every X number of days set here',
	'RS_POWER_REP_POINT'			=> 'Gain points with the user’s reputation',
	'RS_POWER_REP_POINT_EXPLAIN'	=> 'User will gain 1 voting point every X number of reputation points they earn set here.',
	'RS_LOSE_POWER_WARN'			=> 'Lose points with warnings',
	'RS_LOSE_POWER_WARN_EXPLAIN'	=> 'Each warning decreases voting points by this amount of points. Warnings expire in accordance with the settings in General -> Board Configuration -> Board settings',

	// Toplist
	'RS_ENABLE_TOPLIST'				=> 'Enable toplist',
	'RS_ENABLE_TOPLIST_EXPLAIN' 	=> 'Display a list of users with the highest reputation on the index page.',
	'RS_TOPLIST_DIRECTION'			=> 'Direction of list',
	'RS_TL_HORIZONTAL'				=> 'Horizontal',
	'RS_TL_VERTICAL'				=> 'Vertical',
	'RS_TOPLIST_NUM'				=> 'Number of users to display',

	// Penalty
	'RS_PENALTY_ON'					=> 'Decrease user reputation after long inactivity',
	'RS_PENALTY_ON_EXPLAIN'			=> 'Example: say we have set 30 days and 1 point here. Now, if some user ignores our website for a long time, his reputation will decrease by 1 point every 30 days. But, if he logins in 29th day, nothing will decrease. Nothing will decrease also in case if reputation already equals to zero or to the “Minimum points” parameter. If reputation decreases, the user receives an email notification.',
	'RS_PENALTY_DAYS'				=> 'After how many days we decrease the reputation',
	'RS_PENALTY_POINTS'				=> 'By how many points we decrease the reputation',
	'RS_PENALTY_POINTS_UNIT'		=> 'points',
	'RS_PENALTY_GROUPS'				=> 'Which groups are affected',
	'RS_PENALTY_GROUPS_EXPLAIN'		=> 'User is affected if he is a member of one of the groups selected here and <i>that group is his default group</i>. For example, administrators are members of “Registered users”, but if you select only “Registered users”, administrators are not affected since their default group is “Administrators”.<br /><br />
		Hold CTRL key to select multiple groups.',
	'RS_ERR_TOO_MANY_GROUPS'		=> 'Too many groups selected in “Penalty for inactivity” section.',

	// Rate module
	'POINTS_INVALID'				=> 'Points field has to contain only numbers.',
	'RS_VOTE_SAVED'					=> 'Your vote has been saved successfully',
	
	'RS_POINTS_TYPE'				=> 'Reputation type',
	'RS_POINTS_TYPE_COMMON'			=> 'Forums user reputation',
	'RS_POINTS_TYPE_BUYER'			=> 'Buyer reputation',
	'RS_POINTS_TYPE_SELLER'			=> 'Seller reputation',
	'RS_POINTS'						=> 'Points',
	'RS_COMMENT'					=> 'Comment',
	'RS_CHANGE_POINTS_ONLY'			=> 'Don’t store as the asmin’s vote, just change the value',
]);
