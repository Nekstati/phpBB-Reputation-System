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
	'ACP_REPUTATION_RATE_EXPLAIN'		=> 'Tutaj można dodac dodatkowe punkty użytkownikom',

	'RS_ENABLE'						=> 'Włącz System Reputacji',

	'RS_SYNC'						=> 'Synchronizacja Systemu Reputacji',
	'RS_SYNC_EXPLAIN'				=> 'Możesz zsynchronizować System Reputacji po: masowym usunięciu postów/tematów/użytkowników, zmianie ustawień reputacji, zmianie autorów postów, konwersji z innych systemów. Synchronizacja zajmie chwilę, więc proszę być cierpliwym. Po zakończeniu synchronizacji zostaniesz powiadomiony o tym fakcie.<br /><strong>Uwaga!</strong> Podczas synchronizacji zostaną usunięte punkty reputacji, które nie są zgodne z ustawieniami reputacji. Zaleca się wykonanie kopii zapasowej tabeli reputacji przed synchronizacją.',
	'RS_SYNC_REPUTATION_CONFIRM'	=> 'Czy na pewno chcesz zsynchrnizować reputację?',

	'RS_TRUNCATE'				=> 'Wyczyść system reputacji',
	'RS_TRUNCATE_EXPLAIN'		=> 'Ta procedura usuwa wszystkie dane.<br /><strong>Nieodwracalnie!</strong>',
	'RS_TRUNCATE_CONFIRM'		=> 'Czy na pewno chcesz wszystko usunąć?',
	'RS_TRUNCATE_DONE'			=> 'Reputacja została usunięta.',

	'REPUTATION_SETTINGS_CHANGED'	=> '<strong>Zmieniono ustawienia systemu reputacji</strong>',

	// Setting legend
	'ACP_RS_MAIN'			=> 'Główne',
	'ACP_RS_DISPLAY'		=> 'Wyświetlanie',
	'ACP_RS_POSTS_RATING'	=> 'Oceniania postów',
	'ACP_RS_USERS_RATING'	=> 'Oceniania użytkowników',
	'ACP_RS_COMMENT'		=> 'Komentarze',
	'ACP_RS_POWER'			=> 'Siła reputacji',
	'ACP_RS_TOPLIST'		=> 'Toplist',

	// General
	'RS_NEGATIVE_POINT'				=> 'Włącz negatywne punkty',
	'RS_MIN_REP_NEGATIVE'			=> 'Minimalna reputacja dla negatywnych punktów',
	'RS_MIN_REP_NEGATIVE_EXPLAIN'	=> 'Jaką reputację musi posiadać użytkownik, aby móc przyznawać negatywne punkty reputacji. Ustawienie wartość 0 wyłącza tę funckję.',
	'RS_WARNING'					=> 'Włącz ostrzeżenia',
	'RS_WARNING_EXPLAIN'			=> 'Użytkownik ze stosownymi uprawnieniami może przyznawać negatywne punkty podczas wystawiania ostrzeżenia.',
	'RS_WARNING_MAX_POWER'			=> 'Maksymalna siła reputacji przed ostrzeżeniem',
	'RS_WARNING_MAX_POWER_EXPLAIN'	=> 'Maksymalna siła reputacji przed wyświetleniem ostrzeżenia.',
	'RS_MIN_POINT'					=> 'Minimalne punkty',
	'RS_MIN_POINT_EXPLAIN'			=> 'Minimalna liczba punktów, którą użytkownik może otrzymać. Ustawienie na 0 wyłącza tę funkcję.',
	'RS_MAX_POINT'					=> 'Maksymalne punkty',
	'RS_MAX_POINT_EXPLAIN'			=> 'Maksymalna liczba punktów, którą użytkownik może otrzymać. Ustawienie na 0 wyłącza tę funkcję.',
	'RS_PREVENT_OVERRATING'			=> 'Zapobiegaj nabijaniu punktów',
	'RS_PREVENT_OVERRATING_EXPLAIN'	=> 'Blokuj "koleżeńskie" nadawanie reputacji<br /><em>Przykład:</em> użytkownik A ma wiecej niż 10 punktów reputacji i  85% z nich pochodzi od użytkownika B, użytkownik B nie może dawać reputacji użytkownikowi A, dopóki ratio nie spadnie poniżej 85%.<br />Aby wyłączyć te funkcję, wpisz 0 w kazdym polu.',
	'RS_PREVENT_NUM'				=> 'Wszystkie punkty reputacji użytkownika A są równe lub większe niż',
	'RS_PREVENT_PERC'				=> '<br />i udział punktów od użytkownika B jest równy lub większy niż',
	'RS_PER_PAGE'					=> 'Punktów reputacji na stronę',
	'RS_DISPLAY_AVATAR'				=> 'Wyświetlaj awatary',
	'RS_POINT_TYPE'					=> 'Metoda wyśiwetlania punktów',
	'RS_POINT_TYPE_EXPLAIN'			=> 'Punkty reputacji mogą być wyświetlane jako dokładna wartość punktów jaką użytkownicy nadalii lub jako obraz przedstawiający plusa lub minusa dla dodatnich lub ujemnych punktów. Metoda Obraz jest przydatna, jedna ocena zawsze równa się jednemu punktowit.',
	'RS_POINT_VALUE'				=> 'Wartość +N/−N',
	'RS_POINT_IMG'					=> 'Obraz +/−',

	'RS_USERS_TO_EXCLUDE'			=> 'Users with unchangeable reputation',
	'RS_USERS_TO_EXCLUDE_EXPLAIN'	=> 'Enter IDs of users, who cannot be rated. Separate IDs by commas. Example: 2,34,567',
	'RS_USERS_TO_EXCLUDE_ERROR'		=> 'Only digits and commas are allowed in the field “Users with unchangeable reputation”.',

	'RS_INSTANT_VOTE'				=> 'Instant vote',
	'RS_INSTANT_VOTE_EXPLAIN'		=> 'Save vote immediately after pressing +/− button, without any popup window.',
	'RS_INSTANT_VOTE_TEXT'			=> 'To obtain this:<br />
		1. Disable comments below.<br />
		2. Disable voting points below or set “Maximum points spending per vote” = 1.',

	// Post rating
	'RS_POST_RATING'				=> 'Włącz ocenę postu',
	'RS_POST_RATING_EXPLAIN'		=> 'Pozwól użytkownikom oceniać posty innych.<br />Możesz wyłaczyć na niektórych forach.',
	'RS_POST_RATING_FIRST_ONLY'		=> 'First post only',
	'RS_ALLOW_REPUTATION_BUTTON'	=> 'Zapisz i włącz system reputacji na wszystkich forach',
	'RS_ANTISPAM'					=> 'Anty-Spam',
	'RS_ANTISPAM_EXPLAIN'			=> 'Opcja ta uniemożliwia ocenianie postów dla użytkowników, którzy ocenili za dużą liczbę postów w ciągu ostatnich godzin. Ustwienie wartość 0 dla jednego lub obu pół wyłącza tą funkcję.',
	'RS_POSTS'						=> 'Ilość ocenionych postów',
	'RS_HOURS'						=> 'w ciągu ostatnich godzin',
	'RS_ANTISPAM_METHOD'			=> 'Metoda sprawdzania antyspam',
	'RS_ANTISPAM_METHOD_EXPLAIN'	=> 'Metoda wszyscy użytkownicy uwzględnia wszystkie ocenione posty, nie zależnie od tego kto był jego autorem. Metoda ten sam użytownik sprawdza tylko punkty przyznane temu samemu użytownikowi (tzn. użytownik może oceniać posty do woli, ale nie może ocenić postów tego samo użytkownika ponad limit ustawiony powyżej).',
	'RS_SAME_USER'					=> 'Ten sam użytkownik',
	'RS_ALL_USERS'					=> 'Wszyscy użytkownicy',

	'RS_CONTENT_WIDGET_TYPE'		=> 'What to show above post text',
	'RS_CONTENT_WIDGET_TYPE_2'		=> 'Post rating value and post rate buttons +/−',
	'RS_CONTENT_WIDGET_TYPE_1'		=> 'Post rating value',
	'RS_CONTENT_WIDGET_TYPE_0'		=> 'Nothing',

	'RS_MINIPROFILE_WIDGET_TYPE'	=> 'What to show under user avatar',
	'RS_MINIPROFILE_WIDGET_TYPE_EXPLAIN'	=> '<i>Note:</i> On narrow screens (mobile devices) only one of these two reputation blocks is displayed — namely, the one containing the rate buttons.',
	'RS_MINIPROFILE_WIDGET_TYPE_2'	=> 'User rating value and post rate buttons +/−',
	'RS_MINIPROFILE_WIDGET_TYPE_1'	=> 'User rating value',
	'RS_MINIPROFILE_WIDGET_TYPE_0'	=> 'Nothing',
	
	// 'RS_AUC_MINIPROFILE_DOUBLE_REP'			=> 'In auction forums under user avatar show',
	// 'RS_AUC_MINIPROFILE_DOUBLE_REP_EXPLAIN'	=> 'If auction extension enabled.',
	// 'RS_AUC_MINIPROFILE_DOUBLE_REP_0'		=> 'Only seller/buyer reputation',
	// 'RS_AUC_MINIPROFILE_DOUBLE_REP_1'		=> 'Both seller/buyer and forum reputation',

	'RS_WIDGET_TYPE_ERROR'			=> 'Post rate buttons can be displayed either above post text, or below user avatar, but not in both places.',

	// User rating
	'RS_USER_RATING'				=> 'Włącz ocenę użytkownika',
	'RS_USER_RATING_GAP'			=> 'Przerwa w ocenianiu',
	'RS_USER_RATING_GAP_EXPLAIN'	=> 'Okres czasu, w którym użytkownik nie może ocenić tego samego użytkownika. Użytkownicy muszą czekać przez podany okres, aby oddać kolejny głos. Ustawienie wartości na 0 zablokuje tą funkcję i użytkownicy będą mogli ocenić tego samego użytkownika tylko raz.',

	// Comments
	'RS_ENABLE_COMMENT'				=> 'Włącz komentarze',
	'RS_ENABLE_COMMENT_EXPLAIN'		=> 'Jeżeli włączone, użytkownicy będa mogli dodać własny komentarz do przyznawanego punktu.',
	'RS_FORCE_COMMENT'				=> 'Zmuś użytkownika do wpisania komentarza',
	'RS_COMMENT_NO'					=> 'Nie',
	'RS_COMMENT_POST'				=> 'Tylko ocena postu',
	'RS_COMMENT_USER'				=> 'Tylko ocena użytkownika',
	'RS_COMMENT_BOTH'				=> 'Oba (posty i użytkownicy)',
	'RS_COMMEN_LENGTH'				=> 'Długość komentarza',
	'RS_COMMEN_LENGTH_EXPLAIN'		=> 'Ilość znaków jaką może zawierać komentarz. Ustaw na 0 aby wyłaczyć limit.',

	// Voting points
	'RS_ENABLE_POWER'				=> 'Włącz siłę reputacji',
	'RS_ENABLE_POWER_EXPLAIN'		=> 'Siła reputacja jest czymś, co użytkownicy forum mogą gromadzić i wykorzystywać do głosowania. Nowi użytkownicy mają niską siłę reputacji, starzy i pomocni użytkownicy mogą uzyskują większą siłę reputacji. Im większa jest twoja siła, tym więcej możesz głosować w określonym czasie oraz twój wpływ na ocenę postu czy użytkowników jest większy.<br/>Użytkownicy mogą wybrać podczas przyznawania punktów, ile punktów chcą przyznać za danych posty czy też konkretnemu użytkownikowi.',
	'RS_POWER_RENEWAL'				=> 'Czas odnowienia siły',
	'RS_POWER_RENEWAL_EXPLAIN'		=> 'Opcja ta umożliwa kontrolę przyznawania punktów.<br />Każdy punkt siły reputacji zostanie odnowiony po upływie podanego okresu.<br />Sugerowanie ustawienia: 5 godzin.<br />Ustwienie wartość 0 wyłącza tą funkcję.',
	'RS_MIN_POWER'					=> 'Minimalna siła reptuacji',
	'RS_MIN_POWER_EXPLAIN'			=> 'Jest to siła reputacji z jaką będą startowali użytkownicy forum.<br/>Dozwolone: 0-10. Sugerowane: 1.',
	'RS_MAX_POWER'					=> 'Maksymalna siła reputacji',
	'RS_MAX_POWER_EXPLAIN'			=> 'Maksymalna dozwolona siła punktów reputacji na jeden punkt.<br />Użytkownik może wybrać punkty z rozwijanego menu.<br />Dozwolone: 1-20. Sugerowane: 3.',
	// 'RS_MAX_POWER_AUC'				=> 'Maximum points spending per vote for seller/buyer reputation',

	'RS_TOTAL_POSTS'				=> 'Czynnik postów',
	'RS_TOTAL_POSTS_EXPLAIN'		=> 'Użytkownik będzie otrzymywał większą siłę reputacji co każde x postów.',
	'RS_MEMBERSHIP_DAYS'			=> 'Czynnik dni członkostwa',
	'RS_MEMBERSHIP_DAYS_EXPLAIN'	=> 'Użytkownik będzie otrzymywał większą o 1 siłę reputacji co każde x dni.',
	'RS_POWER_REP_POINT'			=> 'Czynnik punktów reputacji',
	'RS_POWER_REP_POINT_EXPLAIN'	=> 'Użytkownik będzie otrzymywał większą siłę reputacji co każde x pkt. reputacji.',
	'RS_LOSE_POWER_WARN'			=> 'Czynnik ostrzeżeń',
	'RS_LOSE_POWER_WARN_EXPLAIN'	=> 'Każde ostrzeżęnie będzie zmniejszało siłę reputacji o x pkt.',

	// Toplist
	'RS_ENABLE_TOPLIST'				=> 'Włącz Toplistę',
	'RS_ENABLE_TOPLIST_EXPLAIN' 	=> 'Na stronie głównej zostanie wyświetlona lista z użytkownikami mającymi najwięcej punktów reputacji.',
	'RS_TOPLIST_DIRECTION'			=> 'Kierunek listy',
	'RS_TL_HORIZONTAL'				=> 'Poziomy',
	'RS_TL_VERTICAL'				=> 'Pionowy',
	'RS_TOPLIST_NUM'				=> 'Użytkowników na Topliście',

	// Rate module
	'POINTS_INVALID'	=> 'Pole punkty musi zawierać tylko liczby.',
	'RS_VOTE_SAVED'		=> 'Twój głos został poprawnie zapisany',
	
	// 'RS_POINTS_TYPE'				=> 'Reputation type',
	// 'RS_POINTS_TYPE_COMMON'			=> 'Forums user reputation',
	// 'RS_POINTS_TYPE_BUYER'			=> 'Buyer reputation',
	// 'RS_POINTS_TYPE_SELLER'			=> 'Seller reputation',
	'RS_POINTS'						=> 'Punkty',
	'RS_COMMENT'					=> 'Komentarz',
]);
