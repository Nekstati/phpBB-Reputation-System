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
	'ACL_CAT_REPUTATION'		=> 'Reputation',

	'ACL_A_REPUTATION'			=> 'Can manage reputation settings',

	'ACL_M_RS_MODERATE'			=> 'Can moderate reputation votes',
	'ACL_M_RS_RATE'				=> 'Can award additional reputation points',

	'ACL_U_RS_DELETE'			=> 'Can delete given votes',
	'ACL_U_RS_RATE'				=> 'Can rate other users',
	'ACL_U_RS_RATE_NEGATIVE'	=> 'Can negatively rate other users <br /> <em>User has to be able to rate other users before he/she can negatively rate other users.</em>',
	'ACL_U_RS_RATE_POST'		=> 'Can rate posts made by other users',
	'ACL_U_RS_VIEW'				=> 'Can view reputation',

	'ACL_F_RS_RATE'				=> 'Can rate posts made by other users',
	'ACL_F_RS_RATE_NEGATIVE'	=> 'Can negatively rate posts made by other users <br /> <em>User has to be able to rate posts before he/she can negatively rate posts made by other users.</em>',
]);
