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
	'ACP_REPUTATION_SYSTEM'			=> 'System Reputacji',
	'ACP_REPUTATION_OVERVIEW'		=> 'Informacje',
	'ACP_REPUTATION_SETTINGS'		=> 'Ustawienia reputacji',
	'ACP_REPUTATION_RATE'			=> 'Oceń',
	'ACP_REPUTATION_SYNC'			=> 'Synchronizuj',

	'RS_FORUM_REPUTATION'			=> 'Włącz ocenianie postów (reputację)',
	'RS_FORUM_REPUTATION_EXPLAIN'	=> 'Zezwól użytkownikom na ocenę postów. Możesz wybrać, czy ocenianie postu ma wpływać na reputację użytkownika.',

	'RS_GROUP_POWER'				=> 'Siła reputacji grupy',
	'RS_GROUP_POWER_EXPLAIN'		=> 'Jeśli to pole jest wypełnione, siła reputacji członków zostaną zastąpione i nie będą oparte na postach itp.',

	'LOG_USER_REPUTATION_CLEARED'	=> '<strong>Usunięto reputację użytkownika:</strong><br />User: %1$s',
	'LOG_REPUTATION_SYNC'			=> '<strong>System reputacji zsynchronizowany</strong>',
	'LOG_REPUTATION_TRUNCATE'		=> '<strong>Reputacja wyczyszczona</strong>',
	'REPUTATION_SETTINGS_CHANGED'	=> '<strong>Zmieniono ustawienia systemu reputacji</strong>',
]);
