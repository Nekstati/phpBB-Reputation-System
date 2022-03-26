<?php
/**
*
* Reputation System
*
* @copyright (c) 2014 Lukasz Kaczynski
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

// To be included in all board pages

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'REPUTATION'					=> 'Reputation',

	'RS_USER_REPUTATION'			=> 'User reputation',
	'RS_POST_REPUTATION'			=> 'Post reputation',
	// 'RS_POST_RATED'				=> 'You have rated this post',
	'RS_RATE_POSITIVE'				=> 'Rate positive',
	'RS_RATE_NEGATIVE'				=> 'Rate negative',

	'NOTIFICATION_TYPE_REPUTATION'		=> 'Someone gives you reputation point',
	'NOTIFICATION_RATE_POST_POSITIVE'	=> '<strong>Rated positively</strong> by %s for post',
	'NOTIFICATION_RATE_POST_NEGATIVE'	=> '<strong>Rated negatively</strong> by %s for post',
	'NOTIFICATION_RATE_USER_POSITIVE'	=> '<strong>Rated positively</strong> by %s',
	'NOTIFICATION_RATE_USER_NEGATIVE'	=> '<strong>Rated negatively</strong> by %s',

	'EXCEPTION_FIELD_MISSING'		=> 'Required field "%1$s" missing.',
	'EXCEPTION_INVALID_TYPE'		=> 'Requested reputation type "%1$s" does not exist.',
	'EXCEPTION_OUT_OF_BOUNDS'		=> 'Invalid value in the field "%1$s".',

	'RS_TOPLIST'					=> 'Users with the highest reputation',
	'RS_NO_USERS'					=> 'No users to display',
	'RS_LASTVOTES'					=> 'Feedback on users',
	'RS_LASTVOTES_TOP'				=> 'Top users',


	//
	// Auction
	//

	'RS_USER_REPUTATION_COMMON'		=> 'User reputation',
	// 'RS_USER_REPUTATION_AUC'		=> 'Seller&thinsp;/&hairsp;buyer reputation',
	'RS_REPUTATION_COMMON'			=> 'User reputation',
	// 'RS_REPUTATION_AUC'				=> 'Seller&thinsp;/&hairsp;buyer reputation',
	// 'RS_REPUTATION_AUC_BUYER'		=> 'Incl. as the buyer',
	// 'RS_REPUTATION_AUC_SELLER'		=> 'Incl. as the seller',

	// 'RS_AUC_USER_ROLE'				=> 'Your role in the deal',
	// 'RS_BUYER'						=> 'buyer',
	// 'RS_SELLER'						=> 'seller',

	// 'RS_AUC_CARD_RED_RATING'		=> 'Red card',
	// 'RS_AUC_CARD_YELLOW_RATING'		=> 'Yellow card',
	// 'RS_AUC_CARD_GREEN_RATING'		=> 'Green card',
	// 'RS_AUC_USER_BUYER_RATING'		=> 'User rated as <span> buyer </span> from the profile',
	// 'RS_AUC_USER_SELLER_RATING'		=> 'User rated as <span> seller </span> from the profile',
	// 'RS_AUC_POST_BUYER_RATING'		=> 'User rated as <span> buyer </span> for the message',
	// 'RS_AUC_POST_SELLER_RATING'		=> 'User rated as <span> seller </span> for the message',

	// 'RS_LASTVOTES_AUC'				=> 'Feedback on sellers&thinsp;/&hairsp;buyers',
	// 'RS_LASTVOTES_TOP_AUC'			=> 'Top sellers / buyers',
]);
