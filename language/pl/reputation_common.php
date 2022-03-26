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
	'REPUTATION'					=> 'Reputacja',

	'RS_USER_REPUTATION'			=> 'Reputacja użytkownika',
	'RS_POST_REPUTATION'			=> 'Reputacji za post',
	// 'RS_POST_RATED'				=> 'You have rated this post',
	'RS_RATE_POSITIVE'				=> 'Oceń pozytywnie',
	'RS_RATE_NEGATIVE'				=> 'Oceń negatywnie',

	'NOTIFICATION_TYPE_REPUTATION'		=> 'Ktoś przyznał Ci punkt reputacji',
	'NOTIFICATION_RATE_POST_POSITIVE'	=> '<strong>Otrzymano poztytwny punkt reputacji</strong> od %s za post',
	'NOTIFICATION_RATE_POST_NEGATIVE'	=> '<strong>Otrzymano negatywny punkt reputacji</strong> od %s za post',
	'NOTIFICATION_RATE_USER_POSITIVE'	=> '<strong>Otrzymano poztytwny punkt reputacji</strong> od %s',
	'NOTIFICATION_RATE_USER_NEGATIVE'	=> '<strong>Otrzymano negatywny punkt reputacji</strong> od %s',

	'EXCEPTION_FIELD_MISSING'		=> 'brakuje wymaganego pola "%1$s"',
	'EXCEPTION_INVALID_TYPE'		=> 'typ reputacji nie istnieje "%1$s"',
	'EXCEPTION_OUT_OF_BOUNDS'		=> 'Pole `%1$s` odbiera dane poza jego granicami',

	'RS_TOPLIST'					=> 'Toplista reputacji',
	'RS_NO_USERS'					=> 'brak użytkowników do wyświetlenia',
	'RS_LASTVOTES'					=> 'Reputacja użytkowników',
	'RS_LASTVOTES_TOP'				=> 'Toplista reputacji',

	'RS_USER_REPUTATION_COMMON'		=> 'Reputacja użytkownika',
	'RS_REPUTATION_COMMON'			=> 'Reputacja użytkownika',
]);
