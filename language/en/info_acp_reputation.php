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
	'ACP_REPUTATION_SYSTEM'			=> 'Reputation system',
	'ACP_REPUTATION_OVERVIEW'		=> 'Overview',
	'ACP_REPUTATION_SETTINGS'		=> 'Settings',
	'ACP_REPUTATION_RATE'			=> 'Rate',
	'ACP_REPUTATION_SYNC'			=> 'Synchronise',

	'RS_FORUM_REPUTATION'			=> 'Enable post rating (reputation)',
	'RS_FORUM_REPUTATION_EXPLAIN'	=> 'Allow members to rate posts in that forum.',

	'RS_GROUP_POWER'				=> 'Group voting points',
	'RS_GROUP_POWER_EXPLAIN'		=> 'If this field is filled, the amount of voting points of each member will be overwritten and will not be based on their posts etc.',

	'LOG_USER_REPUTATION_CLEARED'	=> '<strong>Cleared user reputation</strong> <br /> User: %1$s',
	'LOG_REPUTATION_SYNC'			=> '<strong>Reputation system resynchronised</strong>',
	'LOG_REPUTATION_TRUNCATE'		=> '<strong>Cleared reputations</strong>',
	'REPUTATION_SETTINGS_CHANGED'	=> '<strong>Altered reputation system settings</strong>',
]);
