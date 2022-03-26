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
	'ACP_REPUTATION_RATE_EXPLAIN'	=> 'Здесь вы можете присудить дополнительные баллы репутации любым пользователям.',

	'RS_ENABLE'						=> 'Включить систему репутации',

	'RS_SYNC'						=> 'Синхронизировать систему репутации',
	'RS_SYNC_EXPLAIN'				=> 'Вы можете синхронизировать баллы репутации после массового удаления сообщений/тем/пользователей, изменения настроек репутации, изменения авторов сообщений, конверсий из других систем. Это может занять время. Вы получите уведомление по окончании. <br /> <strong>Внимание!</strong> Все баллы репутации, не соответствующие настройкам, будут удалены в процессе синхронизации. Рекомендуется создать резервную копию таблицы репутации (БД) перед синхронизацией.',
	'RS_SYNC_REPUTATION_CONFIRM'	=> 'Вы уверены, что хотите начать синхронизацию репутации?',

	'RS_TRUNCATE'					=> 'Очистить систему репутации',
	'RS_TRUNCATE_EXPLAIN'			=> 'Эта процедура полностью удалит все данные. <br /> <strong>Действие необратимо!</strong>',
	'RS_TRUNCATE_CONFIRM'			=> 'Вы уверены, что хотите очистить систему репутации?',
	'RS_TRUNCATE_DONE'				=> 'Система репутации была очищена.',

	'REPUTATION_SETTINGS_CHANGED'	=> '<strong>Изменены настройки системы репутации</strong>',

	// Setting legend
	'ACP_RS_MAIN'					=> 'Основное',
	'ACP_RS_DISPLAY'				=> 'Настройки отображения',
	'ACP_RS_POSTS_RATING'			=> 'Рейтинг сообщений',
	'ACP_RS_USERS_RATING'			=> 'Рейтинг пользователей',
	'ACP_RS_COMMENT'				=> 'Комментарии',
	'ACP_RS_POWER'					=> 'Очки голосования',
	'ACP_RS_TOPLIST'				=> 'Топлист',

	// General
	'RS_NEGATIVE_POINT'				=> 'Разрешить отрицательные отзывы',
	'RS_MIN_REP_NEGATIVE'			=> 'Минимальная репутация для отрицательного отзыва',
	'RS_MIN_REP_NEGATIVE_EXPLAIN'	=> 'Какую репутацию должен иметь пользователь, чтобы оставлять отрицательные отзывы. 0 снимает ограничение.',
	'RS_WARNING'					=> 'Разрешить отрицательную оценку при предупреждениях',
	'RS_WARNING_EXPLAIN'			=> 'Модераторы с соответствующими правами смогут давать отрицательную оценку при предупреждении пользователей.',
	'RS_WARNING_MAX_POWER'			=> 'Макс. кол-во баллов, которое можно отнять при предупреждении',
	'RS_WARNING_MAX_POWER_EXPLAIN'	=> '',
	'RS_MIN_POINT'					=> 'Минимально возможная репутация',
	'RS_MIN_POINT_EXPLAIN'			=> 'По достижении этого значения дальнейшие отрицательные оценки не будут понижать репутацию пользователя. 0 снимает ограничение.',
	'RS_MAX_POINT'					=> 'Максимально возможная репутация',
	'RS_MAX_POINT_EXPLAIN'			=> 'По достижении этого значения дальнейшие положительные оценки не будут повышать репутацию пользователя. 0 снимает ограничение.',
	'RS_PREVENT_OVERRATING'			=> 'Предотвращение переоценки',
	'RS_PREVENT_OVERRATING_EXPLAIN'	=> 'Запретить пользователю оценивать одного и того же пользователя.<br /><em>Пример:</em> если у пользователя A больше 10 отзывов и 85% из них даны пользователем B, B больше не сможет оценивать А, пока процент его отзывов не станет ниже 85%. Чтобы снять это ограничение, установите одно или оба значения в 0.',
	'RS_PREVENT_NUM'				=> 'Общее количество отзывов у пользователя A ≥',
	'RS_PREVENT_PERC'				=> '<br /> и процент отзывов, данных пользователем B, ≥',
	'RS_PER_PAGE'					=> 'Строк на страницу в списках отзывов',
	'RS_DISPLAY_AVATAR'				=> 'Показывать аватары в списках отзывов',
	'RS_POINT_TYPE'					=> 'Как отображать оценки в списках отзывов',
	'RS_POINT_TYPE_EXPLAIN'			=> 'Рекомендуется выбрать значок, если одна оценка всегда равна одному баллу.',
	'RS_POINT_VALUE'				=> 'Как число +N/−N',
	'RS_POINT_IMG'					=> 'Как значок +/−',

	'RS_USERS_TO_EXCLUDE'			=> 'Пользователи с неизменяемой репутацией',
	'RS_USERS_TO_EXCLUDE_EXPLAIN'	=> 'Введите через запятую список ID пользователей, оценивать которых запрещено. Пример: 2,34,567',
	'RS_USERS_TO_EXCLUDE_ERROR'		=> 'Поле «Пользователи с неизменяемой репутацией» может содержать только числа и запятые.',

	'RS_INSTANT_VOTE'				=> 'Мгновенная оценка',
	'RS_INSTANT_VOTE_EXPLAIN'		=> 'После нажатия на кнопку +/− ставить оценку сразу же, не выводя всплывающее окно.',
	'RS_INSTANT_VOTE_TEXT'			=> 'Для этого:<br />
		1. Отключите комментарии.<br />
		2. Отключите очки голосования или поставьте «Максимум очков на одну оценку» = 1.',

	// Post rating
	'RS_POST_RATING'				=> 'Разрешить оценку сообщений',
	'RS_POST_RATING_EXPLAIN'		=> 'Это глобальная настройка. Помимо этого оценку можно включить/отключить для любого форума отдельно на странице управления форумами.',
	'RS_POST_RATING_FIRST_ONLY'		=> 'Первое сообщение темы',
	'RS_ALLOW_REPUTATION_BUTTON'	=> 'Отправить и включить систему репутации на всех форумах',
	'RS_ANTISPAM'					=> 'Антиспам',
	'RS_ANTISPAM_EXPLAIN'			=> 'Запретить пользователям оценивать новые сообщения после того, как они оценили X сообщений в течение Y часов. Чтобы снять это ограничение, установите одно или оба значения в 0.',
	'RS_POSTS'						=> 'Количество оценённых сообщений',
	'RS_HOURS'						=> 'за последние часы',
	'RS_ANTISPAM_METHOD'			=> 'Метод проверки антиспама',
	'RS_ANTISPAM_METHOD_EXPLAIN'	=> 'Метод «Один пользователь» проверяет репутацию, выставленную одному и тому же пользователю. Метод «Все пользователи» проверяет репутацию независимо от того, кто получил баллы.',
	'RS_SAME_USER'					=> 'Один пользователь',
	'RS_ALL_USERS'					=> 'Все пользователи',

	'RS_CONTENT_WIDGET_TYPE'		=> 'Что отображать над текстом сообщения',
	'RS_CONTENT_WIDGET_TYPE_2'		=> 'Рейтинг сообщения и кнопки оценки сообщения +/−',
	'RS_CONTENT_WIDGET_TYPE_1'		=> 'Рейтинг сообщения',
	'RS_CONTENT_WIDGET_TYPE_0'		=> 'Ничего',

	'RS_MINIPROFILE_WIDGET_TYPE'	=> 'Что отображать под аватарой',
	'RS_MINIPROFILE_WIDGET_TYPE_EXPLAIN'	=> '<i>Примечание:</i> При недостаточной ширине экрана (на телефонах) будет отображён только один из этих двух блоков репутации — а именно тот, что содержит кнопки оценки.',
	'RS_MINIPROFILE_WIDGET_TYPE_2'	=> 'Рейтинг пользователя и кнопки оценки сообщения +/−',
	'RS_MINIPROFILE_WIDGET_TYPE_1'	=> 'Рейтинг пользователя',
	'RS_MINIPROFILE_WIDGET_TYPE_0'	=> 'Ничего',

	// 'RS_AUC_MINIPROFILE_DOUBLE_REP'			=> 'В аукционных форумах под аватарой отображать',
	// 'RS_AUC_MINIPROFILE_DOUBLE_REP_EXPLAIN'	=> 'Если включено расширение аукциона.',
	// 'RS_AUC_MINIPROFILE_DOUBLE_REP_0'		=> 'Только аукционную репутацию',
	// 'RS_AUC_MINIPROFILE_DOUBLE_REP_1'		=> 'И аукционную, и общую',

	'RS_WIDGET_TYPE_ERROR'			=> 'Кнопки оценки сообщения могут отображаться либо над текстом, либо под аватарой, но не там и там одновременно.',

	// User rating
	'RS_USER_RATING'				=> 'Разрешить оценку пользователей с их страницы профиля',
	'RS_USER_RATING_GAP'			=> 'Период между оценками',
	'RS_USER_RATING_GAP_EXPLAIN'	=> 'Один пользователь может дать оценку другому не чаще чем раз в N часов. Если поставить 0, пользователи смогут оценивать других только один раз.',

	// Comments
	'RS_ENABLE_COMMENT'				=> 'Разрешить комментарии',
	'RS_ENABLE_COMMENT_EXPLAIN'		=> 'Пользователи смогут добавлять комментарии вместе с рейтингом.',
	'RS_FORCE_COMMENT'				=> 'Обязать пользователя вводить комментарий',
	'RS_COMMENT_NO'					=> 'Нет',
	'RS_COMMENT_POST'				=> 'Да, только при оценке сообщения',
	'RS_COMMENT_USER'				=> 'Да, только при оценке пользователя',
	'RS_COMMENT_BOTH'				=> 'Да, в обоих случаях',
	'RS_COMMEN_LENGTH'				=> 'Максимальная длина комментария',
	'RS_COMMEN_LENGTH_EXPLAIN'		=> '0 снимает ограничение.',

	// Voting points
	'RS_ENABLE_POWER'				=> 'Включить очки голосования',
	'RS_ENABLE_POWER_EXPLAIN'		=> 'Если эта функция отключена, пользователь может ставить оценки без ограничений. Если включена, пользователь, ставя кому-то оценку, тратит очки, количество которых ограничено. У новых пользователей мало очков, у активных и старых пользователей — больше. Чем больше у пользователя очков, тем чаще он может ставить оценки и тем больше баллов может поставить другому пользователю или сообщению. Зарабатывать очки он может несколькими способами, настраиваемыми ниже.',
	'RS_POWER_RENEWAL'				=> 'Время обновления очков',
	'RS_POWER_RENEWAL_EXPLAIN'		=> 'Если пользователь истратил все доступные очки, он должен подождать заданное здесь время, прежде чем сможет ставить оценки снова. Чем больше очков заработал пользователь, тем меньше у него вероятность столкнуться с этим ограничением. 0 снимает ограничение, все пользователи могут ставить оценки без ожидания. <br /> <i>Пример:</i> у пользователя А есть три очка, он поставил три плюса разным сообщениям, теперь у него ноль очков, и он больше не может ставить плюсы, но через N часов у него снова будет три очка.',
	'RS_MIN_POWER'					=> 'Начальное/минимальное количество очков',
	'RS_MIN_POWER_EXPLAIN'			=> 'Количество очков для недавно зарегистрированных, заблокированных, пользователей с низкой репутацией и т.д. <i>Обратите внимание:</i> если поставить 0, новые пользователи не смогут ставить оценки, пока не заработают очки. <br/> Разрешено 0-10. Рекомендовано 1.',
	'RS_MAX_POWER'					=> 'Максимум очков на одну оценку',
	'RS_MAX_POWER_EXPLAIN'			=> 'Если значение больше 1, пользователи смогут выбирать, сколько баллов они хотят дать за один раз. Например, можно давать несколько баллов за наиболее интересные сообщения. На одну оценку пользователь может потратить не более указанного здесь числа, даже если у него миллион очков. <br/> Разрешено 1-20. Рекомендовано 1.',
	// 'RS_MAX_POWER_AUC'				=> 'Максимум очков на одну оценку для репутации продавца/покупателя',

	'RS_TOTAL_POSTS'				=> 'Добавлять очки за количество сообщений',
	'RS_TOTAL_POSTS_EXPLAIN'		=> 'Пользователь получит 1 очко за каждые N сообщений.',
	'RS_MEMBERSHIP_DAYS'			=> 'Добавлять очки по сроку регистрации пользователя',
	'RS_MEMBERSHIP_DAYS_EXPLAIN'	=> 'Пользователь получит 1 очко за каждые N дней стажа.',
	'RS_POWER_REP_POINT'			=> 'Добавлять очки в зависимости от репутации',
	'RS_POWER_REP_POINT_EXPLAIN'	=> 'Пользователь получит 1 очко за каждые N баллов своей репутации. <i>Применяйте с осторожностью:</i> отрицательная репутация отнимает очки, и пользователь с низкой репутацией рискует потерять возможность ставить оценки.',
	'RS_LOSE_POWER_WARN'			=> 'Потеря очков из-за предупреждений',
	'RS_LOSE_POWER_WARN_EXPLAIN'	=> 'Каждое предупреждение отнимает указанное количество очков. Когда предупреждение истечёт, очки восстановятся.</i>',

	// Toplist
	'RS_ENABLE_TOPLIST'				=> 'Включить топлист',
	'RS_ENABLE_TOPLIST_EXPLAIN' 	=> 'Список пользователей с наивысшей репутацией внизу главной страницы.',
	'RS_TOPLIST_DIRECTION'			=> 'Направление списка',
	'RS_TL_HORIZONTAL'				=> 'Горизонтально',
	'RS_TL_VERTICAL'				=> 'Вертикально',
	'RS_TOPLIST_NUM'				=> 'Количество пользователей для отображения',

	// Rate module
	'POINTS_INVALID'				=> 'Поле баллов должно содержать только цифры.',
	'RS_VOTE_SAVED'					=> 'Ваш голос был успешно сохранен',

	'RS_POINTS_TYPE'				=> 'Тип репутации',
	'RS_POINTS_TYPE_COMMON'			=> 'Репутация участника форумов',
	'RS_POINTS_TYPE_BUYER'			=> 'Репутация покупателя',
	'RS_POINTS_TYPE_SELLER'			=> 'Репутация продавца',
	'RS_POINTS'						=> 'Баллы',
	'RS_COMMENT'					=> 'Комментарий',
]);
