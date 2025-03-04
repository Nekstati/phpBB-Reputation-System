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
	'RS_DISABLED'				=> 'Администратор отключил эту функцию.',

	'RS_COMMENT'				=> 'Комментарий',
	'RS_COMMENT_OPTIONAL'		=> 'Комментарий (необязательно)',
	'RS_COMMENT_REQUIRED'		=> 'Комментарий (обязательно)',
	'RS_POINTS'					=> 'Баллы',
	'RS_POINTS_SUM'				=> 'баллов',
	'RS_POINTS_TO_GIVE'			=> 'Сколько баллов дать',

	'RS_SORT_BY'				=> 'Сортировать по',
	'RS_DELETE_VOTE'			=> 'Удалить вашу оценку',
	'RS_POST_BY'				=> 'Автор сообщения:',
	'RS_POST_GIVE_POSITIVE'		=> 'Плюс за сообщение',
	'RS_POST_GIVE_NEGATIVE'		=> 'Минус за сообщение',
	'RS_USER_GIVE_POSITIVE'		=> 'Плюс пользователю',
	'RS_USER_GIVE_NEGATIVE'		=> 'Минус пользователю',

	'RS_ACTION'					=> 'Тип',
	'RS_DATE'					=> 'Дата',
	'RS_FROM'					=> 'От',
	'RS_FROM_USER'				=> 'От кого',
	'RS_POST_COUNT'				=> 'Баллов за сообщения',
	'RS_USER_COUNT'				=> 'Баллов из профиля',
	'RS_COUNT'					=> 'Оценок',
	'RS_POSITIVE_COUNT'			=> 'Положительных оценок',
	'RS_NEGATIVE_COUNT'			=> 'Отрицательных оценок',
	'RS_STATS'					=> 'Статистика полученных оценок',
	'RS_WEEK'					=> 'Последняя неделя',
	'RS_MONTH'					=> 'Последний месяц',
	'RS_6MONTHS'				=> 'Последние 6 месяцев',
	'RS_POINT'					=> 'Балл',
	'RS_POINTS_TITLE'			=> [
		1	=> ' балл',
		2	=> ' балла',
		3	=> ' баллов',
	],
	'RS_POST_DELETE'			=> 'Сообщение удалено',
	'RS_POWER'					=> 'Очки голосования',
	'RS_TIME'					=> 'Время',
	'RS_TO'						=> 'кому',
	'RS_TO_USER'				=> 'Кому',
	'RS_VOTING_POWER_LEFT'		=> 'Всего %1$d, доступно %2$d',

	'RS_EMPTY_DATA'				=> 'Нет оценок.',
	'RS_NA'						=> 'н/д',
	'RS_NO_ID'					=> 'Нет ID',
	'RS_NO_REPUTATION'			=> 'Нет данных репутации.',

	'RS_POINTS_DELETED'			=> [
		1	=> 'Оценка удалена.',
		2	=> 'Оценки удалены.',
	],

	'RS_CLEAR_POST'				=> 'Удалить всё',
	'RS_CLEAR_POST_CONFIRM'		=> 'Вы действительно хотите удалить все оценки этого сообщения?',
	'RS_CLEARED_POST'			=> 'Все оценки сообщения удалены.',
	'RS_CLEAR_USER'				=> 'Обнулить',
	'RS_CLEAR_USER_CONFIRM'		=> 'Вы действительно хотите обнулить репутацию этого пользователя?',
	'RS_CLEARED_USER'			=> 'Репутация пользователя обнулена.',

	'LIST_REPUTATIONS'				=> [
		1	=> '%d оценка',
		2	=> '%d оценки',
		3	=> '%d оценок',
	],

	'RS_MORE_DETAILS'				=> 'Подробнее',

	'RS_POWER_DETAILS'				=> 'Как начисляются очки голосования %1$s',
	'RS_POWER_DETAILS_SELF'			=> 'Как вам начисляются очки голосования',

	'RS_POWER_DETAIL_POSTS'			=> [
		1 => '+1 очко за каждое %1$d сообщение',
		2 => '+1 очко за каждые %1$d сообщения',
		3 => '+1 очко за каждые %1$d сообщений',
	],
	'RS_POWER_DETAIL_AGE'			=> [
		1 => '+1 очко за каждый %1$d день стажа',
		2 => '+1 очко за каждые %1$d дня стажа',
		3 => '+1 очко за каждые %1$d дней стажа',
	],
	'RS_POWER_DETAIL_REPUTATION'	=> [
		1 => '±1 очко за каждый ±%1$d балл репутации',
		2 => '±1 очко за каждые ±%1$d балла репутации',
		3 => '±1 очко за каждые ±%1$d баллов репутации',
	],
	'RS_POWER_DETAIL_WARNINGS'		=> [
		1 => '−%1$d очко за каждое предупреждение',
		2 => '−%1$d очка за каждое предупреждение',
		3 => '−%1$d очков за каждое предупреждение',
	],

	'RS_POWER_DETAIL_MIN'			=> 'Нижний лимит для всех пользователей',
	'RS_POWER_DETAIL_MAX'			=> 'Верхний лимит для всех пользователей',
	'RS_POWER_DETAIL_RESULTING'		=> 'Итого',
	'RS_POWER_DETAIL_GROUP_POWER'	=> 'Количество очков голосования определено параметрами группы пользователя',

	'RS_ANTISPAM_INFO'			=> 'Нельзя так часто ставить оценки. Повторите попытку позже.',
	'RS_COMMENT_TOO_LONG'		=> 'Ваш комментарий слишком длинный: %1$s символов. <br /> Разрешённый максимум: %2$s.',
	'RS_NO_COMMENT'				=> 'Пожалуйста, напишите комментарий.',
	'RS_NO_POST'				=> 'Такое сообщение отсутствует.',
	'RS_NO_POWER'				=> 'У вас нет очков голосования. <br /> Надо иметь хотя бы одно очко, чтобы ставить оценки. <br /> <a href="%1$s" class="rs-explain-vote-points"> Как начисляются очки </a>',
	'RS_NO_POWER_LEFT'			=> 'Вы истратили все доступные очки голосования (%2$s/%1$s). <br /> Подождите, пока они регенерируют. <br /> Регенерация происходит спустя %3$s после последней оценки. <br /> <a href="%4$s"  class="rs-explain-vote-points"> Как получить больше очков </a>',
	'RS_NO_USER_ID'				=> 'Запрошенный пользователь не существует.',
	'RS_POST_RATING'			=> 'Оценка за сообщение',
	'RS_RATE_BUTTON'			=> 'Сохранить',
	'RS_SAME_POST'				=> 'Вы уже поставили %s за это сообщение.',
	'RS_SAME_USER'				=> 'Вы уже оценили этого пользователя.',
	'RS_SELF'					=> 'Нельзя ставить оценки самому себе.',
	'RS_SELF_POST'				=> 'Нельзя оценивать свои сообщения.',
	'RS_USER_ANONYMOUS'			=> 'Нельзя оценивать анонимных пользователей.',
	'RS_USER_BANNED'			=> 'Нельзя оценивать заблокированных пользователей.',
	'RS_USER_CANNOT_DELETE'		=> 'У вас недостаточно прав для удаления этой оценки.',
	'RS_USER_DISABLED'			=> 'Вам не разрешено ставить оценки.',
	'RS_USER_CANNOT_RATE'		=> 'Невозможно выполнить операцию.',
	'RS_USER_GAP'				=> 'Нельзя ставить оценку пользователю так часто. <br /> Попытайтесь снова через %s.',
	'RS_USER_NEGATIVE'			=> 'Чтобы ставить отрицательные оценки, <br /> ваша репутация должна быть выше %s.',
	'RS_USER_RATING'			=> 'Оценка из профиля',
	'RS_VIEW_DISALLOWED'		=> 'Вам не разрешено просматривать детали репутации.',
	'RS_VOTE_POWER_LEFT_OF_MAX'	=> 'Сколько баллов у вас в запасе: %1$d из %2$d',
	'RS_VOTE_SAVED'				=> 'Оценка сохранена',
	'RS_WARNING_RATING'			=> 'Предупреждение от модератора',
	'RS_CLOSE'					=> 'Закрыть',

	'RS_HOURS'					=> [
		1 => '%d час',
		2 => '%d часа',
		3 => '%d часов',
	],

	'RS_USER_IS_EXCLUDED'		=> 'Репутация пользователя %s неизменна и безупречна.',
]);
