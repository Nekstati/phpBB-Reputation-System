<?php
/**
*
* Reputation System
*
* @copyright (c) 2014 Lukasz Kaczynski
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace pico\reputation\controller;

class details_controller
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \pico\reputation\core\reputation_helper */
	protected $reputation_helper;

	/** @var \pico\reputation\core\reputation_manager */
	protected $reputation_manager;

	/** @ \pico\reputation\core\reputation_power */
	protected $reputation_power;

	/** @var string The table we use to store our reputations */
	protected $reputations_table;

	/** @var string The database table the reputation types are stored */
	protected $reputation_types_table;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth $auth						Auth object
	* @param \phpbb\config\config $config				Config object
	* @param \phpbb\controller\helper					Controller helper object
	* @param \phpbb\db\driver\driver $db				Database object
	* @param \phpbb\pagination $pagination				Pagination object
	* @param \phpbb\request\request $request			Request object
	* @param \phpbb\template\template $template			Template object
	* @param \phpbb\user $user							User object
	* @param \pico\reputation\core\reputation_helper	Reputation helper object
	* @param \pico\reputation\core\reputation_manager	Reputation manager object
	* @param \pico\reputation\core\reputation_power		Reputation power object
	* @param string $reputations_table					Name of the table used to store reputations data
	* @param string $reputation_types_table				Name of the table used to store reputation types data
	* @param string $root_path							phpBB root path
	* @param string $php_ext							phpEx
	* @return \pico\reputation\controller\details_controller
	* @access public
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\db\driver\driver_interface $db, \phpbb\pagination $pagination, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \pico\reputation\core\reputation_helper $reputation_helper, \pico\reputation\core\reputation_manager $reputation_manager, \pico\reputation\core\reputation_power $reputation_power, $reputations_table, $reputation_types_table, $root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
		$this->pagination = $pagination;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->reputation_helper = $reputation_helper;
		$this->reputation_manager = $reputation_manager;
		$this->reputation_power = $reputation_power;
		$this->reputations_table = $reputations_table;
		$this->reputation_types_table = $reputation_types_table;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;

		$user->add_lang_ext('pico/reputation', 'reputation_system');
	}

	/**
	* Main reputation details controller 
	*
	* @param int $uid			User ID taken from the URL
	* @param string $sort_key	Sort key: id|username|time|point|action (default: id)
	* @param string $sort_dir	Sort direction: dsc|asc (descending|ascending) (default: dsc)
	* @param int $page			Page number taken from the URL
	* @return Symfony\Component\HttpFoundation\Response A Symfony Response object
	* @access public
	*/
	public function details($uid, $sort_key, $sort_dir, $page)
	{
		if (!$this->auth->acl_get('u_rs_view'))
		{
			$meta_info = append_sid("{$this->root_path}index.{$this->php_ext}", "");
			$message = $this->user->lang['RS_VIEW_DISALLOWED'] . '<br /><br />' . $this->user->lang('RETURN_INDEX', '<a href="' . append_sid("{$this->root_path}index.{$this->php_ext}", "") . '">', '</a>');
			meta_refresh(3, $meta_info);
			trigger_error($message);
		}

		$sql = 'SELECT *
			FROM ' . USERS_TABLE . "
			WHERE user_type <> 2
				AND user_id = $uid";
		$result = $this->db->sql_query($sql);
		$user_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);        

		if (empty($user_row))
		{
			$meta_info = append_sid("{$this->root_path}index.{$this->php_ext}", "");
			$message = $this->user->lang['RS_NO_USER_ID'] . '<br /><br />' . $this->user->lang('RETURN_INDEX', '<a href="' . append_sid("{$this->root_path}index.{$this->php_ext}", "") . '">', '</a>');
			meta_refresh(3, $meta_info);
			trigger_error($message);
		}

		// $auc = $this->request->variable('auc', false);
		$auc = false;
		$target_type_names = ($auc)
			? ['auc_post_buyer', 'auc_post_seller', 'auc_user_buyer', 'auc_user_seller']
			// : ['post', 'user', 'warning', 'auc_card_red', 'auc_card_yellow', 'auc_card_green'];
			: ['post', 'user', 'warning'];
		$rep_type_ids = [];
		foreach ($target_type_names as $name)
			$rep_type_ids[$name] = $this->reputation_manager->get_reputation_type_id($name);
		$rep_type_names = array_flip($rep_type_ids);

		$user_type_ids = ($auc)
			? [$rep_type_ids['auc_user_buyer'], $rep_type_ids['auc_user_seller']]
			: [$rep_type_ids['user']];
		$post_type_ids = ($auc)
			? [$rep_type_ids['auc_post_buyer'], $rep_type_ids['auc_post_seller']]
			: [$rep_type_ids['post']];

		$auc_param = ($auc) ? ['auc' => true] : [];

		// For the rep power. Rep power is not divided between auction rep and common rep.
		// $user_reputation_common_plus_auc = $user_row['user_reputation'] + $user_row['user_reputation_auc_buyer'] + $user_row['user_reputation_auc_seller'];
		$user_reputation_common_plus_auc = $user_row['user_reputation'];

		if ($auc)
		{
			$user_row['user_reputation'] = $user_row['user_reputation_auc_buyer'] + $user_row['user_reputation_auc_seller'];
		}

		$sort_key_sql = [
			'username'	=> 'u.username_clean',
			'time'		=> 'r.reputation_time',
			'point'		=> 'r.reputation_points',
			'action'	=> 'r.reputation_type_id',
		];
		$order_by = $sort_key_sql[$sort_key] . ' ' . (($sort_dir == 'dsc') ? 'DESC' : 'ASC');
		$start = ($page - 1) * $this->config['rs_per_page'];

		$sql_array = [
			'SELECT'	=> 'r.*, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, p.post_id, p.forum_id, p.post_subject, p.post_visibility',
			'FROM'		=> [
				$this->reputations_table => 'r',
			],
			'LEFT_JOIN' => [
				[
					'FROM'	=> [USERS_TABLE => 'u'],
					'ON'	=> 'u.user_id = r.user_id_from',
				],
				[
					'FROM'	=> [POSTS_TABLE => 'p'],
					'ON'	=> 'p.post_id = r.reputation_item_id',
				],
			],
			'WHERE'		=> 'r.user_id_to = ' . $uid . '
				AND ' . $this->db->sql_in_set('r.reputation_type_id', $rep_type_ids),
			'ORDER_BY'	=> $order_by,
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, $this->config['rs_per_page'], $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('reputation', [
				'ID'			=> $row['reputation_id'],
				'USERNAME'		=> get_username_string('full', $row['user_id_from'], $row['username'], $row['user_colour']),
				'ACTION'		=> $this->user->lang('RS_' . strtoupper($rep_type_names[$row['reputation_type_id']]) . '_RATING'),
				'AVATAR'		=> phpbb_get_user_avatar($row),
				'TIME'			=> $this->user->format_date($row['reputation_time']),
				'COMMENT'		=> $row['reputation_comment'],
				'POINTS'		=> $this->format_number($row['reputation_points']),
				'POINTS_CLASS'	=> $this->reputation_helper->reputation_class($row['reputation_points']),
				'POINTS_TITLE'	=> $this->format_number($row['reputation_points']) . $this->user->lang('RS_POINTS_TITLE', abs($row['reputation_points'])),

				'U_DELETE'		=> $this->helper->route('reputation_delete_controller', ['rid' => $row['reputation_id']] + $auc_param),

				'S_COMMENT'		=> !empty($row['reputation_comment']),
				'S_DELETE'		=> ($this->auth->acl_get('m_rs_moderate') || ($row['user_id_from'] == $this->user->data['user_id'] && $this->auth->acl_get('u_rs_delete'))) ? true : false,
			]);

			if (in_array($row['reputation_type_id'], $post_type_ids))
			{
				$this->reputation_manager->generate_post_link($row);
			}
		}
		$this->db->sql_freeresult($result);

		if (!function_exists('phpbb_get_user_rank'))
		{
			include $this->root_path . 'includes/functions_display.' . $this->php_ext;
		}
		$user_rank_data = phpbb_get_user_rank($user_row, $user_row['user_posts']);

		$positive_count = $negative_count = 0;
		$positive_sum = $negative_sum = 0;
		$positive_week = $negative_week = 0;
		$positive_month = $negative_month = 0;
		$positive_6months = $negative_6months = 0;
		$post_count = $user_count = 0;

		$last_week = time() - 604800;
		$last_month = time() - 2678400;
		$last_6months = time() - 16070400;

		$sql = 'SELECT r.reputation_time, r.reputation_type_id, r.reputation_points
			FROM ' . $this->reputations_table . ' r
			LEFT JOIN ' . POSTS_TABLE . " p
				ON (p.post_id = r.reputation_item_id)
			WHERE r.user_id_to = $uid
			AND " . $this->db->sql_in_set('r.reputation_type_id', $rep_type_ids);
		$result = $this->db->sql_query($sql);
		$total_reps = $this->db->sql_affectedrows();

		while ($reputation_vote = $this->db->sql_fetchrow($result))
		{
			if ($reputation_vote['reputation_points'] > 0)
			{
				$positive_count++;
				$positive_sum += $reputation_vote['reputation_points'];
				if ($reputation_vote['reputation_time'] >= $last_week)
				{
					$positive_week++;
				}
				if ($reputation_vote['reputation_time'] >= $last_month)
				{
					$positive_month++;
				}
				if ($reputation_vote['reputation_time'] >= $last_6months)
				{
					$positive_6months++;
				}
			}
			else if ($reputation_vote['reputation_points'] < 0)
			{
				$negative_count++;
				$negative_sum += $reputation_vote['reputation_points'];
				if ($reputation_vote['reputation_time'] >= $last_week)
				{
					$negative_week++;
				}
				if ($reputation_vote['reputation_time'] >= $last_month)
				{
					$negative_month++;
				}
				if ($reputation_vote['reputation_time'] >= $last_6months)
				{
					$negative_6months++;
				}
			}

			if (in_array($reputation_vote['reputation_type_id'], $post_type_ids))
			{
				$post_count += $reputation_vote['reputation_points'];
			}
			else if (in_array($reputation_vote['reputation_type_id'], $user_type_ids))
			{
				$user_count += $reputation_vote['reputation_points'];
			}
		}
		$this->db->sql_freeresult($result);

		if ($this->config['rs_enable_power'])
		{
			$used_power = $this->reputation_power->used($user_row['user_id']);
			$user_max_voting_power = $this->reputation_power->get($user_row['user_posts'], $user_row['user_regdate'], $user_reputation_common_plus_auc, $user_row['user_warnings'], $user_row['group_id']);
			$voting_power_left = '';

			if ($this->config['rs_power_renewal'])
			{
				$voting_power_left = $user_max_voting_power - $used_power;

				if ($voting_power_left < 0)
				{
					$voting_power_left = 0;
				}
			}

			$this->template->assign_vars([
				'RS_POWER'					=> $user_max_voting_power,
				'RS_POWER_LEFT'				=> $this->config['rs_power_renewal'] ? $this->user->lang('RS_VOTING_POWER_LEFT', $user_max_voting_power, min($voting_power_left, $user_max_voting_power)) : '',
			]);
		}

		$this->pagination->generate_template_pagination(
			[
				'routes'	=> 'reputation_details_controller',
				'params'	=> [
					'uid'		=> $uid,
					'sort_key'	=> $sort_key,
					'sort_dir'	=> $sort_dir,
				] + $auc_param,
			],
			'pagination',
			'page',
			$total_reps,
			$this->config['rs_per_page'],
			$start
		);

		$this->template->assign_vars([
			'USER_ID'			=> $user_row['user_id'],
			'USERNAME'			=> get_username_string('username', $user_row['user_id'], $user_row['username'], $user_row['user_colour'], true),
			'USERNAME_FULL'		=> get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
			'REPUTATION'		=> $this->format_number($user_row['user_reputation']),

			'REPUTATION_AUC_BUYER'	=> ($auc) ? $this->format_number($user_row['user_reputation_auc_buyer']) : null,
			'REPUTATION_AUC_SELLER'	=> ($auc) ? $this->format_number($user_row['user_reputation_auc_seller']) : null,

			'AVATAR_IMG'		=> phpbb_get_user_avatar($user_row),
			'RANK_IMG'			=> $user_rank_data['img'],
			'RANK_IMG_SRC'		=> $user_rank_data['img_src'],
			'RANK_TITLE'		=> $user_rank_data['title'],
			'REPUTATION_CLASS'	=> $this->reputation_helper->reputation_class($user_row['user_reputation']),

			'PAGE_NUMBER'		=> $this->pagination->on_page($total_reps, $this->config['rs_per_page'], $start),
			'TOTAL_REPS'		=> $this->user->lang('LIST_REPUTATIONS', (int) $total_reps),

			'U_SORT_USERNAME'	=> $this->helper->route('reputation_details_controller', ['uid' => $uid, 'sort_key' => 'username', 'sort_dir' => ($sort_key == 'username' && $sort_dir == 'asc') ? 'dsc' : 'asc'] + $auc_param),
			'U_SORT_TIME'		=> $this->helper->route('reputation_details_controller', ['uid' => $uid, 'sort_key' => 'time', 'sort_dir' => ($sort_key == 'time' && $sort_dir == 'asc') ? 'dsc' : 'asc'] + $auc_param),
			'U_SORT_POINT'		=> ($this->config['rs_max_power'] == 1) ? '' : $this->helper->route('reputation_details_controller', ['uid' => $uid, 'sort_key' => 'point', 'sort_dir' => ($sort_key == 'point' && $sort_dir == 'asc') ? 'dsc' : 'asc'] + $auc_param),
			'U_SORT_ACTION'		=> $this->helper->route('reputation_details_controller', ['uid' => $uid, 'sort_key' => 'action', 'sort_dir' => ($sort_key == 'action' && $sort_dir == 'asc') ? 'dsc' : 'asc'] + $auc_param),
			'SORT_KEY'			=> $sort_key,
			'SORT_DIR'			=> $sort_dir,

			'U_CLEAR'			=> $this->helper->route('reputation_clear_user_controller', ['uid' =>  $uid] + $auc_param),
			'U_EXPLAIN_POWER'	=> ($this->config['rs_enable_power'] && ($uid == $this->user->data['user_id'] || $this->auth->acl_get('u_rs_view')))
				? $this->helper->route('reputation_explain_vote_points', ['uid' => $uid]) : '',

			'RS_URL_USER_REP'		=> $this->helper->route('reputation_details_controller', ['uid' => $uid]),
			'RS_URL_USER_REP_AUC'	=> $this->helper->route('reputation_details_controller', ['uid' => $uid, 'auc' => true]),

			'POST_COUNT'		=> $this->format_number($post_count),
			'USER_COUNT'		=> $this->format_number($user_count),
			'POSITIVE_COUNT'	=> $positive_count,
			'POSITIVE_SUM'		=> $this->format_number($positive_sum),
			'POSITIVE_WEEK'		=> "+$positive_week",
			'POSITIVE_MONTH'	=> "+$positive_month",
			'POSITIVE_6MONTHS'	=> "+$positive_6months",
			'NEGATIVE_COUNT'	=> $negative_count,
			'NEGATIVE_SUM'		=> $this->format_number($negative_sum),
			'NEGATIVE_WEEK'		=> "−$negative_week",
			'NEGATIVE_MONTH'	=> "−$negative_month",
			'NEGATIVE_6MONTHS'	=> "−$negative_6months",

			'S_RS_POST_RATING' 	=> $this->config['rs_post_rating'] ? true : false,
			'S_RS_USER_RATING' 	=> $this->config['rs_user_rating'] ? true : false,
			'S_RS_AVATAR'		=> $this->config['rs_display_avatar'] ? true : false,
			'S_RS_COMMENT'		=> $this->config['rs_enable_comment'] ? true : false,
			'S_RS_NEGATIVE'		=> $this->config['rs_negative_point'] ? true : false,
			'S_RS_POWER_ENABLE'	=> $this->config['rs_enable_power'] ? true : false,
			'S_CLEAR'			=> $this->auth->acl_gets('m_rs_moderate') ? true : false,

			'IS_MULTIPOINT_MODE'	=> ($this->config[$auc ? 'rs_max_power_auc' : 'rs_max_power'] > 1),

			'RS_USER_REPUTATION_TITLE'	=> $this->user->lang('RS_USER_REPUTATION' . ($auc ? '_AUC' : '')),
			'RS_AUC'					=> $auc,
		]);

		return $this->helper->render('details.html', $this->user->lang('RS_USER_REPUTATION' . ($auc ? '_AUC' : '')));
	}

	/**
	* Post details controller
	*
	* @param int $post_id		Post ID taken from the URL
	* @param string $sort_key	Sort key: id|username|time|point
	* @param string $sort_dir	Sort direction: dsc|asc (descending|ascending)
	* @return Symfony\Component\HttpFoundation\Response A Symfony Response object
	* @access public
	*/
	public function postdetails($post_id, $sort_key, $sort_dir)
	{
		$is_ajax = $this->request->is_ajax();

		if (empty($this->config['rs_enable']))
		{
			if ($is_ajax)
			{
				$json_data = [
					'error_msg' => $this->user->lang('RS_DISABLED'),
				];
				(new \phpbb\json_response)->send($json_data);
			}

			redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
		}

		$sql_array = [
			'SELECT'	=> 'p.forum_id, p.poster_id, p.post_subject, u.username, u.user_colour',
			'FROM'		=> [
				POSTS_TABLE => 'p',
				USERS_TABLE => 'u'
			],
			'WHERE'		=> 'p.post_id = ' . $post_id . '
				AND p.poster_id = u.user_id',
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$post_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (empty($post_row))
		{
			$message = $this->user->lang('RS_NO_POST');
			$json_data = [
				'error_msg' => $message,
			];
			$redirect = append_sid("{$this->root_path}index.{$this->php_ext}");
			$redirect_text = 'RETURN_INDEX';

			$this->reputation_manager->response($message, $json_data, $redirect, $redirect_text, $is_ajax);
		}

		if (!$this->auth->acl_get('u_rs_view'))
		{
			$message = $this->user->lang('RS_VIEW_DISALLOWED');
			$json_data = [
				'error_msg' => $message,
			];
			$redirect = append_sid("{$this->root_path}viewtopic.{$this->php_ext}", 'f=' . $post_row['forum_id'] . '&amp;p=' . $post_id) . '#p' . $post_id;
			$redirect_text = 'RETURN_PAGE';

			$this->reputation_manager->response($message, $json_data, $redirect, $redirect_text, $is_ajax);
		}

		$auc = $this->request->variable('auc', false);
		$target_type_names = ($auc)
			? ['auc_post_buyer', 'auc_post_seller']
			: ['post'];
		$rep_type_ids = [];
		foreach ($target_type_names as $name)
			$rep_type_ids[$name] = $this->reputation_manager->get_reputation_type_id($name);

		$auc_param = ($auc) ? ['auc' => true] : [];

		$sort_key_sql = [
			'username'	=> 'u.username_clean',
			'time'		=> 'r.reputation_time',
			'point'		=> 'r.reputation_points',
		];
		$order_by = $sort_key_sql[$sort_key] . ' ' . (($sort_dir == 'dsc') ? 'DESC' : 'ASC');

		$sql_array = [
			'SELECT'	=> 'r.* , u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height',
			'FROM'		=> [$this->reputations_table => 'r'],
			'LEFT_JOIN' => [
				[
					'FROM'	=> [USERS_TABLE => 'u'],
					'ON'	=> 'u.user_id = r.user_id_from',
				],
			],
			'WHERE'	=> 'r.reputation_item_id = ' . $post_id . '
				AND ' . $this->db->sql_in_set('r.reputation_type_id', $rep_type_ids),
			'ORDER_BY'	=> $order_by,
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('reputation', [
				'ID'			=> $row['reputation_id'],
				'USERNAME'		=> get_username_string('full', $row['user_id_from'], $row['username'], $row['user_colour']),
				'AVATAR'		=> phpbb_get_user_avatar($row),
				'TIME'			=> $this->user->format_date($row['reputation_time']),
				'COMMENT'		=> $row['reputation_comment'],
				'POINTS'		=> $this->format_number($row['reputation_points']),
				'POINTS_CLASS'	=> $this->reputation_helper->reputation_class($row['reputation_points']),
				'POINTS_TITLE'	=> $this->format_number($row['reputation_points']) . $this->user->lang('RS_POINTS_TITLE', abs($row['reputation_points'])),

				'U_DELETE'		=> $this->helper->route('reputation_delete_controller', ['rid' => $row['reputation_id']] + $auc_param),

				'S_COMMENT'		=> !empty($row['reputation_comment']),
				'S_DELETE'		=> ($this->auth->acl_get('m_rs_moderate') || ($row['user_id_from'] == $this->user->data['user_id'] && $this->auth->acl_get('u_rs_delete'))) ? true : false,
			]);
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'POST_ID'		=> $post_id,
			'POST_SUBJECT'	=> $post_row['post_subject'],
			'POST_AUTHOR'	=> get_username_string('full', $post_row['poster_id'], $post_row['username'], $post_row['user_colour']),
			'POST_URL'		=> append_sid("{$this->root_path}viewtopic.php", "p=$post_id#p$post_id"),

			'U_SORT_USERNAME'	=> $this->helper->route('reputation_post_details_controller', ['post_id' => $post_id, 'sort_key' => 'username', 'sort_dir' => ($sort_key == 'username' && $sort_dir == 'asc') ? 'dsc' : 'asc'] + $auc_param),
			'U_SORT_TIME'		=> $this->helper->route('reputation_post_details_controller', ['post_id' => $post_id, 'sort_key' => 'time', 'sort_dir' => ($sort_key == 'time' && $sort_dir == 'asc') ? 'dsc' : 'asc'] + $auc_param),
			'U_SORT_POINT'		=> ($this->config['rs_max_power'] == 1) ? '' : $this->helper->route('reputation_post_details_controller', ['post_id' => $post_id, 'sort_key' => 'point', 'sort_dir' => ($sort_key == 'point' && $sort_dir == 'asc') ? 'dsc' : 'asc'] + $auc_param),
			'SORT_KEY'			=> $sort_key,
			'SORT_DIR'			=> $sort_dir,

			'U_CLEAR'				=> $this->helper->route('reputation_clear_post_controller', ['post_id' =>  $post_id] + $auc_param),

			'S_RS_AVATAR'		=> $this->config['rs_display_avatar'] ? true : false,
			'S_RS_COMMENT'		=> $this->config['rs_enable_comment'] ? true : false,
			'S_RS_POINTS_IMG'	=> $this->config['rs_point_type'] ? true : false,
			'S_CLEAR'			=> $this->auth->acl_gets('m_rs_moderate') ? true : false,
			'S_IS_AJAX'			=> $is_ajax ? true : false,
		]);

		$this->template->append_var('BODY_CLASS', 'rs-details-body');

		return $this->helper->render('postdetails.html', $this->user->lang('RS_POST_REPUTATION'));
	}

	/**
	* User details controller
	*
	* @param int $uid			User ID taken from the URL
	* @param string $sort_key	Sort key: id|username|time|point|action (default: id)
	* @param string $sort_dir	Sort direction: dsc|asc (descending|ascending) (default: dsc)
	* @return Symfony\Component\HttpFoundation\Response A Symfony Response object
	* @access public
	*/
	public function userdetails($uid, $sort_key, $sort_dir)
	{
		$is_ajax = $this->request->is_ajax();

		if (empty($this->config['rs_enable']))
		{
			if ($is_ajax)
			{
				$json_data = [
					'error_msg' => $this->user->lang('RS_DISABLED'),
				];
				(new \phpbb\json_response)->send($json_data);
			}

			redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
		}

		$sql = 'SELECT user_id, username, user_colour
			FROM ' . USERS_TABLE . '
			WHERE user_type <> 2
				AND user_id =' . (int) $uid;
		$result = $this->db->sql_query($sql);
		$user_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (empty($user_row))
		{
			$message = $this->user->lang('RS_NO_USER_ID');
			$json_data = [
				'error_msg' => $message,
			];
			$redirect = append_sid("{$this->root_path}index.{$this->php_ext}");
			$redirect_text = 'RETURN_INDEX';

			$this->reputation_manager->response($message, $json_data, $redirect, $redirect_text, $is_ajax);
		}

		if (!$this->auth->acl_get('u_rs_view'))
		{
			$message = $this->user->lang('RS_VIEW_DISALLOWED');
			$json_data = [
				'error_msg' => $message,
			];
			$redirect = append_sid("memberlist.{$this->php_ext}", 'mode=viewprofile&amp;u=' . $uid);
			$redirect_text = 'RETURN_PAGE';

			$this->reputation_manager->response($message, $json_data, $redirect, $redirect_text, $is_ajax);
		}

		$sort_key_sql = [
			'username'	=> 'u.username_clean',
			'time'		=> 'r.reputation_time',
			'point'		=> 'r.reputation_points',
			'action'	=> 'r.reputation_type_id',
		];
		$order_by = $sort_key_sql[$sort_key] . ' ' . (($sort_dir == 'dsc') ? 'DESC' : 'ASC');

		// $auc = $this->request->variable('auc', false);
		$auc = false;
		$target_type_names = ($auc)
			? ['auc_post_buyer', 'auc_post_seller', 'auc_user_buyer', 'auc_user_seller']
			// : ['post', 'user', 'warning', 'auc_card_red', 'auc_card_yellow', 'auc_card_green'];
			: ['post', 'user', 'warning'];
		$rep_type_ids = [];
		foreach ($target_type_names as $name)
			$rep_type_ids[$name] = $this->reputation_manager->get_reputation_type_id($name);
		$rep_type_names = array_flip($rep_type_ids);

		$post_type_ids = ($auc)
			? [$rep_type_ids['auc_post_buyer'], $rep_type_ids['auc_post_seller']]
			: [$rep_type_ids['post']];

		$auc_param = ($auc) ? ['auc' => true] : [];

		$sql_array = [
			'SELECT'	=> 'r.*, u.username, u.user_colour, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, p.post_id, p.forum_id, p.post_subject, p.post_visibility',
			'FROM'		=> [
				$this->reputations_table => 'r',
			],
			'LEFT_JOIN' => [
				[
					'FROM'	=> [USERS_TABLE => 'u'],
					'ON'	=> 'u.user_id = r.user_id_from',
				],
				[
					'FROM'	=> [POSTS_TABLE => 'p'],
					'ON'	=> 'p.post_id = r.reputation_item_id',
				],
			],
			'WHERE'		=> 'r.user_id_to = ' . $uid . '
				AND ' . $this->db->sql_in_set('r.reputation_type_id', $rep_type_ids),
			'ORDER_BY'	=> $order_by,
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('reputation', [
				'ID'			=> $row['reputation_id'],
				'USERNAME'		=> get_username_string('full', $row['user_id_from'], $row['username'], $row['user_colour']),
				'ACTION'		=> $this->user->lang('RS_' . strtoupper($rep_type_names[$row['reputation_type_id']]) . '_RATING'),
				'AVATAR'		=> phpbb_get_user_avatar($row),
				'TIME'			=> $this->user->format_date($row['reputation_time']),
				'COMMENT'		=> $row['reputation_comment'],
				'POINTS'		=> $this->format_number($row['reputation_points']),
				'POINTS_CLASS'	=> $this->reputation_helper->reputation_class($row['reputation_points']),
				'POINTS_TITLE'	=> $this->format_number($row['reputation_points']) . $this->user->lang('RS_POINTS_TITLE', abs($row['reputation_points'])),

				'U_DELETE'		=> $this->helper->route('reputation_delete_controller', ['rid' => $row['reputation_id']] + $auc_param),

				'S_COMMENT'		=> !empty($row['reputation_comment']),
				'S_DELETE'		=> ($this->auth->acl_get('m_rs_moderate') || ($row['user_id_from'] == $this->user->data['user_id'] && $this->auth->acl_get('u_rs_delete'))) ? true : false,
			]);

			if (in_array($row['reputation_type_id'], $post_type_ids))
			{
				$this->reputation_manager->generate_post_link($row);
			}
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'USER_ID'			=> $uid,
			'USERNAME'			=> get_username_string('username', $user_row['user_id'], $user_row['username'], $user_row['user_colour'], true),
			'USERNAME_FULL'		=> get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),

			'U_USER_DETAILS'	=> $this->helper->route('reputation_details_controller', ['uid' => $uid] + $auc_param),
			'U_SORT_USERNAME'	=> $this->helper->route('reputation_user_details_controller', ['uid' => $uid, 'sort_key' => 'username', 'sort_dir' => ($sort_key == 'username' && $sort_dir == 'asc') ? 'dsc' : 'asc'] + $auc_param),
			'U_SORT_TIME'		=> $this->helper->route('reputation_user_details_controller', ['uid' => $uid, 'sort_key' => 'time', 'sort_dir' => ($sort_key == 'time' && $sort_dir == 'asc') ? 'dsc' : 'asc'] + $auc_param),
			'U_SORT_POINT'		=> ($this->config['rs_max_power'] == 1) ? '' : $this->helper->route('reputation_user_details_controller', ['uid' => $uid, 'sort_key' => 'point', 'sort_dir' => ($sort_key == 'point' && $sort_dir == 'asc') ? 'dsc' : 'asc'] + $auc_param),
			'U_SORT_ACTION'		=> $this->helper->route('reputation_user_details_controller', ['uid' => $uid, 'sort_key' => 'action', 'sort_dir' => ($sort_key == 'action' && $sort_dir == 'asc') ? 'dsc' : 'asc'] + $auc_param),
			'SORT_KEY'			=> $sort_key,
			'SORT_DIR'			=> $sort_dir,

			'U_CLEAR'				=> $this->helper->route('reputation_clear_user_controller', ['uid' =>  $uid] + $auc_param),

			'RS_USER_REPUTATION_TITLE'	=> $this->user->lang('RS_USER_REPUTATION' . ($auc ? '_AUC' : '')),

			'S_RS_AVATAR'		=> $this->config['rs_display_avatar'] ? true : false,
			'S_RS_COMMENT'		=> $this->config['rs_enable_comment'] ? true : false,
			'S_RS_POINTS_IMG'	=> $this->config['rs_point_type'] ? true : false,
			'S_CLEAR'			=> $this->auth->acl_gets('m_rs_moderate') ? true : false,
			'S_IS_AJAX'			=> $is_ajax ? true : false,

			'IS_REF_VIEWTOPIC'	=> strpos($this->request->server('HTTP_REFERER', ''), 'viewtopic.php') !== false,
		]);

		$this->template->append_var('BODY_CLASS', 'rs-details-body');

		return $this->helper->render('userdetails.html', $this->user->lang('RS_USER_REPUTATION' . ($auc ? '_AUC' : ''), $user_row['username']));
	}

	public function lastvotes($sort_key, $sort_dir, $page)
	{
		if (!$this->auth->acl_get('u_rs_view'))
		{
			$meta_info = append_sid("{$this->root_path}index.{$this->php_ext}", "");
			$message = $this->user->lang['RS_VIEW_DISALLOWED'] . '<br /><br />' . $this->user->lang('RETURN_INDEX', '<a href="' . append_sid("{$this->root_path}index.{$this->php_ext}", "") . '">', '</a>');
			meta_refresh(3, $meta_info);
			trigger_error($message);
		}

		// $auc = $this->request->variable('auc', false);
		$auc = false;
		$target_type_names = ($auc)
			? ['auc_post_buyer', 'auc_post_seller', 'auc_user_buyer', 'auc_user_seller']
			// : ['post', 'user', 'warning', 'auc_card_red', 'auc_card_yellow', 'auc_card_green'];
			: ['post', 'user', 'warning'];
		$rep_type_ids = [];
		foreach ($target_type_names as $name)
			$rep_type_ids[$name] = $this->reputation_manager->get_reputation_type_id($name);
		$rep_type_names = array_flip($rep_type_ids);

		$post_type_ids = ($auc)
			? [$rep_type_ids['auc_post_buyer'], $rep_type_ids['auc_post_seller']]
			: [$rep_type_ids['post']];

		$auc_param = ($auc) ? ['auc' => true] : [];

		$sort_key_sql = [
			'username_from'	=> 'uf.username_clean',
			'username_to'	=> 'ut.username_clean',
			'time'		=> 'r.reputation_time',
			'point'		=> 'r.reputation_points',
			'action'	=> 'r.reputation_type_id',
		];
		$order_by = $sort_key_sql[$sort_key] . ' ' . (($sort_dir == 'dsc') ? 'DESC' : 'ASC');
		$start = ($page - 1) * $this->config['rs_per_page'];

		$sql_array = [
			'SELECT'	=> 'r.*, p.post_id, p.forum_id, p.post_subject, p.post_visibility',
			'FROM'		=> [
				$this->reputations_table => 'r',
			],
			'LEFT_JOIN' => [
				[
					'FROM'	=> [USERS_TABLE => 'uf'],
					'ON'	=> 'uf.user_id = r.user_id_from',
				],
				[
					'FROM'	=> [USERS_TABLE => 'ut'],
					'ON'	=> 'ut.user_id = r.user_id_to',
				],
				[
					'FROM'	=> [POSTS_TABLE => 'p'],
					'ON'	=> 'p.post_id = r.reputation_item_id',
				],
			],
			'WHERE'		=> $this->db->sql_in_set('r.reputation_type_id', $rep_type_ids),
			'ORDER_BY'	=> $order_by,
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, $this->config['rs_per_page'], $start);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$tops = [];

		if ($auc)
		{
			$sql = 'SELECT user_id, user_reputation_auc_buyer, user_reputation_auc_seller
				FROM ' . USERS_TABLE . '
				WHERE user_type <> ' . USER_IGNORE . '
				AND (user_reputation_auc_buyer + user_reputation_auc_seller) > 0
				ORDER BY user_reputation_auc_buyer DESC, user_reputation_auc_seller DESC';
			$result = $this->db->sql_query_limit($sql, 25);
			$tops = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);
		}
		else
		{
			$sql = 'SELECT user_id, user_reputation
				FROM ' . USERS_TABLE . '
				WHERE user_type <> ' . USER_IGNORE . '
				AND user_reputation > 0
				ORDER BY user_reputation DESC';
			$result = $this->db->sql_query_limit($sql, 25);
			$tops = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);
		}

		$uids_from_and_to = array_column($rows, 'user_id_to', 'user_id_to') + array_column($rows, 'user_id_from', 'user_id_from') + array_column($tops, 'user_id', 'user_id');
		$user_loader = $GLOBALS['phpbb_container']->get('user_loader');
		$user_loader->load_users($uids_from_and_to);

		foreach ($rows as $row)
		{
			$this->template->assign_block_vars('reputation', [
				'ID'			=> $row['reputation_id'],
				'USERNAME_FROM'	=> $user_loader->get_username($row['user_id_from'], 'full'),
				'USERNAME_TO'	=> $user_loader->get_username($row['user_id_to'], 'full'),
				'ACTION'		=> $this->user->lang('RS_' . strtoupper($rep_type_names[$row['reputation_type_id']]) . '_RATING'),
				'AVATAR_FROM'	=> $user_loader->get_avatar($row['user_id_from']),
				'AVATAR_TO'		=> $user_loader->get_avatar($row['user_id_to']),
				'TIME'			=> $this->user->format_date($row['reputation_time']),
				'COMMENT'		=> $row['reputation_comment'],
				'POINTS'		=> $this->format_number($row['reputation_points']),
				'POINTS_CLASS'	=> $this->reputation_helper->reputation_class($row['reputation_points']),
				'POINTS_TITLE'	=> $this->format_number($row['reputation_points']) . $this->user->lang('RS_POINTS_TITLE', abs($row['reputation_points'])),

				'U_DELETE'		=> ($this->auth->acl_get('m_rs_moderate') || ($row['user_id_from'] == $this->user->data['user_id'] && $this->auth->acl_get('u_rs_delete')))
					? $this->helper->route('reputation_delete_controller', ['rid' => $row['reputation_id']] + $auc_param) : '',
			]);

			if (in_array($row['reputation_type_id'], $post_type_ids))
			{
				$this->reputation_manager->generate_post_link($row);
			}
		}

		foreach ($tops as $row)
		{
			if ($auc)
			{
				$row['user_reputation'] = $row['user_reputation_auc_buyer'] + $row['user_reputation_auc_seller'];
			}

			$this->template->assign_block_vars('toplist', [
				'USERNAME'		=> $user_loader->get_username($row['user_id'], 'full'),
				'AVATAR'		=> $user_loader->get_avatar($row['user_id']),
				'POINTS'		=> $this->format_number($row['user_reputation']),
				'POINTS_CLASS'	=> $this->reputation_helper->reputation_class($row['user_reputation']),
			]);
		}

		$sql = 'SELECT COUNT(reputation_id) AS total_reps
			FROM ' . $this->reputations_table . "
			WHERE " . $this->db->sql_in_set('reputation_type_id', $rep_type_ids);
		$result = $this->db->sql_query($sql);
		$total_reps = (int) $this->db->sql_fetchfield('total_reps');
		$this->db->sql_freeresult($result);

		$this->pagination->generate_template_pagination(
			[
				'routes'	=> 'reputation_lastvotes_controller',
				'params'	=> [
					'sort_key'	=> $sort_key,
					'sort_dir'	=> $sort_dir,
				] + $auc_param,
			],
			'pagination',
			'page',
			$total_reps,
			$this->config['rs_per_page'],
			$start
		);

		$this->template->assign_vars([
			'PAGE_NUMBER'		=> $this->pagination->on_page($total_reps, $this->config['rs_per_page'], $start),
			'TOTAL_REPS'		=> $this->user->lang('LIST_REPUTATIONS', (int) $total_reps),

			'U_SORT_USERNAME_FROM'	=> $this->helper->route('reputation_lastvotes_controller', ['sort_key' => 'username_from', 'sort_dir' => ($sort_key == 'username_from' && $sort_dir == 'asc') ? 'dsc' : 'asc'] + $auc_param),
			'U_SORT_USERNAME_TO'	=> $this->helper->route('reputation_lastvotes_controller', ['sort_key' => 'username_to', 'sort_dir' => ($sort_key == 'username_to' && $sort_dir == 'asc') ? 'dsc' : 'asc'] + $auc_param),
			'U_SORT_TIME'		=> $this->helper->route('reputation_lastvotes_controller', ['sort_key' => 'time', 'sort_dir' => ($sort_key == 'time' && $sort_dir == 'asc') ? 'dsc' : 'asc'] + $auc_param),
			'U_SORT_POINT'		=> ($this->config['rs_max_power'] == 1) ? '' : $this->helper->route('reputation_lastvotes_controller', ['sort_key' => 'point', 'sort_dir' => ($sort_key == 'point' && $sort_dir == 'asc') ? 'dsc' : 'asc'] + $auc_param),
			'U_SORT_ACTION'		=> $this->helper->route('reputation_lastvotes_controller', ['sort_key' => 'action', 'sort_dir' => ($sort_key == 'action' && $sort_dir == 'asc') ? 'dsc' : 'asc'] + $auc_param),
			'SORT_KEY'			=> $sort_key,
			'SORT_DIR'			=> $sort_dir,

			'S_RS_AVATAR'		=> $this->config['rs_display_avatar'] ? true : false,
			'S_RS_COMMENT'		=> $this->config['rs_enable_comment'] ? true : false,
			'S_RS_POINTS_IMG'	=> $this->config['rs_point_type'] ? true : false,

			'RS_LASTVOTES_TITLE'		=> $this->user->lang('RS_LASTVOTES' . ($auc ? '_AUC' : '')),
			'RS_LASTVOTES_TOP_TITLE'	=> $this->user->lang('RS_LASTVOTES_TOP' . ($auc ? '_AUC' : '')),
			'RS_AUC'					=> $auc,
		]);

		return $this->helper->render('lastvotes.html', $this->user->lang('RS_LASTVOTES' . ($auc ? '_AUC' : '')));
	}

	public function explain_vote_points($uid)
	{
		if (!$this->config['rs_enable_power'])
		{
			trigger_error('RS_DISABLED');
		}
		if ($uid != $this->user->data['user_id'] && !$this->auth->acl_get('u_rs_view'))
		{
			trigger_error('RS_VIEW_DISALLOWED');
		}

		if ($uid == $this->user->data['user_id'])
		{
			$data = $this->user->data;
		}
		else
		{
			$sql = 'SELECT *
				FROM ' . USERS_TABLE . '
				WHERE user_type <> ' . USER_IGNORE . "
				AND user_id = $uid";
			$result = $this->db->sql_query($sql);
			$data = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);        

			if (empty($data))
			{
				trigger_error('RS_NO_USER_ID');
			}
		}

		// $user_reputation_common_plus_auc = $data['user_reputation'] + $data['user_reputation_auc_buyer'] + $data['user_reputation_auc_seller'];
		$user_reputation_common_plus_auc = $data['user_reputation'];
		$this->reputation_power->get($data['user_posts'], $data['user_regdate'], $user_reputation_common_plus_auc, $data['user_warnings'], $data['group_id']);
		$explain = $this->reputation_power->explain();
		$info = [];

		if (isset($explain['GROUP_VOTING_POWER']))
			$info[] = ['RS_POWER_DETAIL_GROUP_POWER', $explain['GROUP_VOTING_POWER']];

		if (isset($explain['FOR_USER_AGE']))
			$info[] = [$this->user->lang('RS_POWER_DETAIL_AGE', (int) $this->config['rs_membership_days']), $explain['FOR_USER_AGE']];

		if (isset($explain['FOR_NUMBER_OF_POSTS']))
			$info[] = [$this->user->lang('RS_POWER_DETAIL_POSTS', (int) $this->config['rs_total_posts']), $explain['FOR_NUMBER_OF_POSTS']];

		if (isset($explain['FOR_REPUTATION']))
			$info[] = [$this->user->lang('RS_POWER_DETAIL_REPUTATION', (int) $this->config['rs_power_rep_point']), $explain['FOR_REPUTATION']];

		if (isset($explain['FOR_WARNINGS']))
			$info[] = [$this->user->lang('RS_POWER_DETAIL_WARNINGS', (int) $this->config['rs_power_lose_warn']), $explain['FOR_WARNINGS']];

		if (isset($explain['MINIMUM_VOTING_POWER']))
			$info[] = [$this->user->lang('RS_POWER_DETAIL_MIN'), $explain['MINIMUM_VOTING_POWER']];

		if (isset($explain['MAXIMUM_VOTING_POWER']))
			$info[] = [$this->user->lang('RS_POWER_DETAIL_MAX'), $explain['MAXIMUM_VOTING_POWER']];

		if (isset($explain['RESULTING_VOTING_POWER']))
			$info[] = [$this->user->lang('RS_POWER_DETAIL_RESULTING'), $explain['RESULTING_VOTING_POWER']];

		$html = '<table class="rs-explain-vote-points-table">';
		foreach ($info as $i)
			$html .= "<tr><td>{$i[0]}</td><td style=\"font-weight: bold\">{$i[1]}</td></tr>";
		$html .= '</table>';

		$title = ($uid == $this->user->data['user_id'])
			? $this->user->lang('RS_POWER_DETAILS_SELF')
			: $this->user->lang('RS_POWER_DETAILS', get_username_string('username', $data['user_id'], $data['username']));

		if ($this->request->is_ajax())
		{
			echo "<h3>$title</h3><div class=\"rs-message\">$html</div>";
			garbage_collection();
			exit_handler();
		}

		$this->template->assign_vars([
			'TITLE_H2'			=> '',
			'TITLE_H3'			=> $title,
			'CONTAINER_CLASS'	=> 'panel',
			'HTML'				=> $html,
		]);
		$this->template->append_var('BODY_CLASS', 'rs-details-body');

		page_header($title);
		$this->template->set_filenames(['body' => '@pico_reputation/message.html']);
		page_footer(true, false, false);

		return new \Symfony\Component\HttpFoundation\Response($this->template->assign_display('body'));
	}

	private function format_number($number)
	{
		return ($this->config['rs_negative_point'] && $number > 0 ? '+' : '') . $number;
	}
}
