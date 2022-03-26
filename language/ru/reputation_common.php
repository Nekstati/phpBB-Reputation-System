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
	'REPUTATION'					=> 'Репутация',

	'RS_USER_REPUTATION'			=> 'Репутация пользователя',
	'RS_POST_REPUTATION'			=> 'Рейтинг сообщения',
	// 'RS_POST_RATED'				=> 'Вы уже оценили это сообщение',
	'RS_RATE_POSITIVE'				=> 'Поставить плюс',
	'RS_RATE_NEGATIVE'				=> 'Поставить минус',

	'NOTIFICATION_TYPE_REPUTATION'		=> 'Кто-то поставил вам плюс или минус',
	'NOTIFICATION_RATE_POST_POSITIVE'	=> '%s поставил вам <b>плюс</b> за сообщение:',
	'NOTIFICATION_RATE_POST_NEGATIVE'	=> '%s поставил вам <b>минус</b> за сообщение:',
	'NOTIFICATION_RATE_USER_POSITIVE'	=> '%s поставил вам <b>плюс</b>.',
	'NOTIFICATION_RATE_USER_NEGATIVE'	=> '%s поставил вам <b>минус</b>.',

	'EXCEPTION_FIELD_MISSING'		=> 'Отсутствует обязательное поле "%1$s".',
	'EXCEPTION_INVALID_TYPE'		=> 'Запрошенный тип репутации "%1$s" не существует.',
	'EXCEPTION_OUT_OF_BOUNDS'		=> 'Недопустимое значение в поле "%1$s".',

	'RS_TOPLIST'					=> 'Пользователи с наивысшей репутацией',
	'RS_NO_USERS'					=> 'Нет пользователей',
	'RS_LASTVOTES'					=> 'Отзывы о пользователях',
	'RS_LASTVOTES_TOP'				=> 'Топ пользователей',


	//
	// Auction
	//

	'RS_USER_REPUTATION_COMMON'		=> 'Репутация пользователя',
	// 'RS_USER_REPUTATION_AUC'		=> 'Репутация продавца&thinsp;/&hairsp;покупателя',
	'RS_REPUTATION_COMMON'			=> 'Репутация пользователя',
	// 'RS_REPUTATION_AUC'				=> 'Продавца&thinsp;/&hairsp;покупателя',
	// 'RS_REPUTATION_AUC_BUYER'		=> 'В т.ч. как покупателя',
	// 'RS_REPUTATION_AUC_SELLER'		=> 'В т.ч. как продавца',

	// 'RS_AUC_USER_ROLE'				=> 'Ваша роль в сделке',
	// 'RS_BUYER'						=> 'покупатель',
	// 'RS_SELLER'						=> 'продавец',

	// 'RS_AUC_CARD_RED_RATING'		=> 'Красная карточка',
	// 'RS_AUC_CARD_YELLOW_RATING'		=> 'Жёлтая карточка',
	// 'RS_AUC_CARD_GREEN_RATING'		=> 'Зелёная карточка',
	// 'RS_AUC_USER_BUYER_RATING'		=> 'Оценка <span> покупателю </span> из профиля',
	// 'RS_AUC_USER_SELLER_RATING'		=> 'Оценка <span> продавцу </span> из профиля',
	// 'RS_AUC_POST_BUYER_RATING'		=> 'Оценка <span> покупателю </span> за сообщение',
	// 'RS_AUC_POST_SELLER_RATING'		=> 'Оценка <span> продавцу </span> за сообщение',

	// 'RS_LASTVOTES_AUC'				=> 'Отзывы о продавцах&thinsp;/&hairsp;покупателях',
	// 'RS_LASTVOTES_TOP_AUC'			=> 'Топ продавцов / покупателей',
]);
