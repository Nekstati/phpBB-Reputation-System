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
	'ACP_REPUTATION_SYSTEM'			=> 'Система репутации',
	'ACP_REPUTATION_OVERVIEW'		=> 'Общие',
	'ACP_REPUTATION_SETTINGS'		=> 'Настройки',
	'ACP_REPUTATION_RATE'			=> 'Оценить',
	'ACP_REPUTATION_SYNC'			=> 'Синхронизировать',

	'RS_FORUM_REPUTATION'			=> 'Включить рейтинг сообщений (репутацию)',
	'RS_FORUM_REPUTATION_EXPLAIN'	=> 'Разрешить пользователям оценивать сообщения в этом форуме.',

	'RS_GROUP_POWER'				=> 'Очки голосования группы',
	'RS_GROUP_POWER_EXPLAIN'		=> 'Если это поле заполнено, количество очков голосования у каждого члена группы будет равно этому значению, независисмо от других критериев, установленных в настройках системы репутации (количество сообщений пользователя и т.д.).',

	'LOG_USER_REPUTATION_CLEARED'	=> '<strong>Обнулена репутация пользователя</strong> <br /> Пользователь: %1$s',
	'LOG_REPUTATION_SYNC'			=> '<strong>Система репутации синхронизирована</strong>',
	'LOG_REPUTATION_TRUNCATE'		=> '<strong>Удалены все записи в системе репутации</strong>',
	'REPUTATION_SETTINGS_CHANGED'	=> '<strong>Изменены настройки системы репутации</strong>',
]);
