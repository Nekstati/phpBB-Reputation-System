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
	'MCP_RS_ADD_WARNING'			=> 'Понизить репутацию за предупреждение',
	'MCP_RS_ADD_WARNING_EXPLAIN'	=> 'Вы можете понизить репутацию этого пользователя за нарушение правил и т.д. Это сработает, только если вы поставите галочку ниже.',
	'MCP_RS_ADD_REPUTATION'			=> 'Понизить репутацию',
	'MCP_RS_POINTS'	=> [
		1	=> '-%d балл',
		2	=> '-%d балла',
		3	=> '-%d баллов',
	],
	'RS_POINTS'						=> 'Баллы',
	'RS_COMMENT'					=> 'Комментарий',
]);
