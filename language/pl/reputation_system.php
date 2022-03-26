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
	'RS_DISABLED'				=> 'Przepraszamy, ale administrator wyłączył tą funkcję.',

	'RS_COMMENT'				=> 'Komentarz',
	'RS_COMMENT_OPTIONAL'		=> 'Komentarz (opcjonalnie)',
	'RS_COMMENT_REQUIRED'		=> 'Komentarz (wymagane)',
	'RS_POINTS'					=> 'Punkty',
	'RS_POINTS_SUM'				=> 'punkty',
	'RS_POINTS_TO_GIVE'			=> 'Ile punktów',

	'RS_SORT_BY'				=> 'Sortuj według',
	'RS_DELETE_VOTE'			=> 'Usuń swój głos',
	'RS_POST_BY'				=> 'Autor postu:',
	'RS_POST_GIVE_POSITIVE'		=> 'Oceń post pozytywnie',
	'RS_POST_GIVE_NEGATIVE'		=> 'Oceń post negatywnie',
	'RS_USER_GIVE_POSITIVE'		=> 'Oceń użytkownika pozytywnie',
	'RS_USER_GIVE_NEGATIVE'		=> 'Oceń użytkownika negatywnie',

	'RS_ACTION'					=> 'Czynność',
	'RS_DATE'					=> 'Data',
	'RS_FROM'					=> 'Od',
	'RS_FROM_USER'				=> 'Od użytkownika',
	'RS_POST_COUNT'				=> 'Punktów za post',
	'RS_USER_COUNT'				=> 'Punktów od użytkownika',
	'RS_COUNT'					=> 'Ocen',
	'RS_POSITIVE_COUNT'			=> 'Pytywne',
	'RS_NEGATIVE_COUNT'			=> 'Negatywne',
	'RS_STATS'					=> 'Statystyki',
	'RS_WEEK'					=> 'Ostatni tydzień',
	'RS_MONTH'					=> 'Ostatni miesiąc',
	'RS_6MONTHS'				=> 'Ostatnie 6 miesięcy',
	'RS_POINT'					=> 'Punkt',
	'RS_POINTS_TITLE'			=> [
		1	=> 'Punkt: %d',
		2	=> 'Punkty: %d',
	],
	'RS_POST_DELETE'			=> 'Post usunięty',
	'RS_POWER'					=> 'Siła reputacji',
	'RS_TIME'					=> 'Czas',
	'RS_TO'						=> 'do',
	'RS_TO_USER'				=> 'Do',
	'RS_VOTING_POWER_LEFT'		=> '%1$d total, %2$d available',

	'RS_EMPTY_DATA'				=> 'Brak punktów reputacji.',
	'RS_NA'						=> 'Brak',
	'RS_NO_ID'					=> 'Brak ID',
	'RS_NO_REPUTATION'			=> 'Brak punktu reputacji',

	'RS_POINTS_DELETED'			=> [
		1	=> 'Reputacja została usunięta',
		2	=> 'Reputacje zostały usunięte.',
	],

	'RS_CLEAR_POST'				=> 'Wyczyść reputację postu',
	'RS_CLEAR_POST_CONFIRM'		=> 'Czy na pewno chcesz usunąć punkty przyznane za ten post?',
	'RS_CLEARED_POST'			=> 'Reputacja psotu została usunięta.',
	'RS_CLEAR_USER'				=> 'Wyczyść reputację użytkownika',
	'RS_CLEAR_USER_CONFIRM'		=> 'Czy na pewno chcesz usunąć punkty przyznane temu uzytkownikowi?',
	'RS_CLEARED_USER'			=> 'Reputacja użutkownika została usunięta.',

	'LIST_REPUTATIONS'				=> [
		1	=> '%d reputacji',
		2	=> '%d reputacji',
	],

	'RS_MORE_DETAILS'				=> 'Więcej szczegółów →',

	'RS_POWER_DETAILS'				=> 'Sposób obliczania siły reputacji',
	'RS_POWER_DETAILS_SELF'			=> 'Sposób obliczania siły reputacji',

	'RS_POWER_DETAIL_POSTS'			=> [
		1 => '+1 point per every %1$d post',
		2 => '+1 point per every %1$d posts',
	],
	'RS_POWER_DETAIL_AGE'			=> [
		1 => '+1 point per every %1$d day onboard',
		2 => '+1 point per every %1$d days onboard',
	],
	'RS_POWER_DETAIL_REPUTATION'	=> [
		1 => '±1 point per every ±%1$d reputation points',
		2 => '±1 point per every ±%1$d reputation points',
	],
	'RS_POWER_DETAIL_WARNINGS'		=> [
		1 => '−%1$d point per every warning',
		2 => '−%1$d points per every warning',
	],

	'RS_POWER_DETAIL_MIN'			=> 'Minimalna siła reputacji dla wszystkich użytkowników',
	'RS_POWER_DETAIL_MAX'			=> 'Siła reputacji została ograniczona do maksymalnej wartości',
	'RS_POWER_DETAIL_RESULTING'		=> 'Result',
	'RS_POWER_DETAIL_GROUP_POWER'	=> 'Amoint of voting points is determined by user group power',

	'RS_ANTISPAM_INFO'			=> 'Nie możesz przyznać punktów reputacji tak szybko. Wróć później.',
	'RS_COMMENT_TOO_LONG'		=> 'Za długi komentarz.<br />Maksymalna ilość znaków: %2$s. Twój komentarz: %1$s',
	'RS_NO_COMMENT'				=> 'Musisz uzupełnić pole z komentarzem!',
	'RS_NO_POST'				=> 'Wybrany post nie istnieje',
	'RS_NO_POWER'				=> 'Twoja siła reputacji jest za niska. <br /> At least 1 point is required to vote. <br /> <a href="%1$s" class="rs-explain-vote-points"> How to get voting points </a>',
	'RS_NO_POWER_LEFT'			=> 'Nie posiadasz żadnych punktów do rozdysponowania (%2$s/%1$s). <br /> Poczekaj, aż zostaną uzupełnione. <br /> Renewal occurs in %3$s after your last vote. <br /> <a href="%4$s" class="rs-explain-vote-points"> How to get more voting points </a>',
	'RS_NO_USER_ID'				=> 'Nie ma takiego użytkownika.',
	'RS_POST_RATING'			=> 'Ocenianie postu',
	'RS_RATE_BUTTON'			=> 'Oceń',
	'RS_SAME_POST'				=> 'Dodałeś już punkt reputacji za ten post.',
	'RS_SAME_USER'				=> 'Dodałeś już punkt reputacji temu użytkownikowi.',
	'RS_SELF'					=> 'Nie możesz sam sobie przyznać punktów reputacji.',
	'RS_SELF_POST'				=> 'Nie możesz sam sobie przyznać punktów reputacji.',
	'RS_USER_ANONYMOUS'			=> 'Nie możesz przyznać punktów reputacji Gościowi.',
	'RS_USER_BANNED'			=> 'Nie możesz przyznać punktów reputacji zbanowanemu użytkownikowi.',
	'RS_USER_CANNOT_DELETE'		=> 'Nie masz uprawnień do usunięcia punktu.',
	'RS_USER_DISABLED'			=> 'Nie możesz dodawać punktów reputacji.',
	'RS_USER_CANNOT_RATE'		=> 'Cannot perform operation.',
	'RS_USER_GAP'				=> 'Nie możesz ponownie ocenić tego samego użytkownika. Spróbuj ponownie za %s.',
	'RS_USER_NEGATIVE'			=> 'Nie możesz dodawać negatywnych punktów reputacji.<br />Twoja reputacja musi wynosić co najmniej %s.',
	'RS_USER_RATING'			=> 'Ocenianie użytkownika',
	'RS_VIEW_DISALLOWED'		=> 'Nie możesz przeglądać punktów reputacji.',
	'RS_VOTE_POWER_LEFT_OF_MAX'	=> 'Pozostało %1$d punktów do wykorzystania z %2$d. Maksimum na głos: %3$d',
	'RS_VOTE_SAVED'				=> 'Głos zapisany',
	'RS_WARNING_RATING'			=> 'Uwaga',
	'RS_CLOSE'					=> 'Zamknij',

	'RS_HOURS'					=> [
		1 => '%d godzina',
		2 => '%d godziny',
	],

	'RS_USER_IS_EXCLUDED'		=> '%s’s reputation is immutable and impeccable.',
]);
