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
	'MCP_RS_ADD_WARNING'			=> 'Punkty reputacji dla ostrzeżenia',
	'MCP_RS_ADD_WARNING_EXPLAIN'	=> 'Można nadać ujemne punkty reputacji użytkownikowi za złe zachowanie itp. Zadziała tylko wtedy, gdy zaznaczysz pole poniżej.',
	'MCP_RS_ADD_REPUTATION'			=> 'Dodaj reputację',

	'MCP_RS_POINTS'	=> [
		1	=> '-%d punkt',
		2	=> '-%d punktów',
	],
	'RS_POINTS'						=> 'Punkty',
	'RS_COMMENT'					=> 'Komentarz',
]);
