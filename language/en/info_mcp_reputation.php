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
	'MCP_RS_ADD_WARNING'			=> 'Reputation points for warning',
	'MCP_RS_ADD_WARNING_EXPLAIN'	=> 'You can give negative reputation points to this user for a bad behaviour etc. This will only work if you have checked the checkbox below.',
	'MCP_RS_ADD_REPUTATION'			=> 'Add reputation',

	'MCP_RS_POINTS'	=> [
		1	=> '-%d point',
		2	=> '-%d points',
	],
	'RS_POINTS'						=> 'Points',
	'RS_COMMENT'					=> 'Comment',
]);
