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

class rating_controller
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

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

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	const RS_COMMENT_BOTH = 1;
	const RS_COMMENT_POST = 2;
	const RS_COMMENT_USER = 3;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth $auth						Auth object
	* @param \phpbb\config\config $config				Config object
	* @param \phpbb\controller\helper					Controller helper object
	* @param \phpbb\db\driver\driver $db				Database object
	* @param \phpbb\request\request $request			Request object
	* @param \phpbb\template\template $template			Template object
	* @param \phpbb\user $user							User object
	* @param \pico\reputation\core\reputation_helper	Reputation helper object
	* @param \pico\reputation\core\reputation_manager	Reputation manager object
	* @param \pico\reputation\core\reputation_power		Reputation power object
	* @param string $reputations_table					Name of the table used to store reputations data
	* @param string $root_path							phpBB root path
	* @param string $php_ext							phpEx
	* @return \pico\reputation\controller\rating_controller
	* @access public
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\db\driver\driver_interface $db, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \pico\reputation\core\reputation_helper $reputation_helper, \pico\reputation\core\reputation_manager $reputation_manager, \pico\reputation\core\reputation_power $reputation_power, $reputations_table, $root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->reputation_helper = $reputation_helper;
		$this->reputation_manager = $reputation_manager;
		$this->reputation_power = $reputation_power;
		$this->reputations_table = $reputations_table;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;

		$user->add_lang_ext('pico/reputation', 'reputation_system');
	}

	/**
	* Display the post rating page
	*
	* @param string $mode		Mode taken from the URL 
	* @param int $post_id		Post ID taken from the URL
	* @return Symfony\Component\HttpFoundation\Response A Symfony Response object
	* @access public
	*/
	public function post($mode, $post_id)
	{
		$error = '';
		$is_ajax = $this->request->is_ajax();

		// $auc = $this->request->variable('auc', false);
		$auc = false;

		if (empty($this->config['rs_enable']))
		{
			if ($is_ajax)
			{
				(new \phpbb\json_response)->send(['error_msg' => $this->user->lang('RS_DISABLED')]);
			}

			redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
		}

		$reputation_type_ids = ($auc)
			? [$this->reputation_manager->get_reputation_type_id('auc_post_buyer'), $this->reputation_manager->get_reputation_type_id('auc_post_seller')]
			: [$this->reputation_manager->get_reputation_type_id('post')];

		$sql_array = [
			'SELECT'	=> 'p.forum_id, p.poster_id, p.post_subject, u.user_type, u.username, f.reputation_enabled, r.reputation_id, r.reputation_points, t.topic_first_post_id',
			'FROM'		=> [
				POSTS_TABLE		=> 'p',
				USERS_TABLE		=> 'u',
				FORUMS_TABLE	=> 'f',
				TOPICS_TABLE	=> 't',
			],
			'LEFT_JOIN'	=> [
				[
					'FROM'	=> [$this->reputations_table => 'r'],
					'ON'	=> 'p.post_id = r.reputation_item_id
						AND ' . $this->db->sql_in_set('r.reputation_type_id', $reputation_type_ids) . '
						AND r.user_id_from = ' . $this->user->data['user_id'],
				],
			],
			'WHERE'		=> 'p.post_id = ' . $post_id . '
				AND p.poster_id = u.user_id
				AND p.forum_id = f.forum_id
				AND p.topic_id = t.topic_id',
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			$this->show_error($this->user->lang('RS_NO_POST'));
		}

		if ($this->request->is_set_post('cancel'))
		{
			redirect(append_sid("{$this->root_path}viewtopic.{$this->php_ext}", 'f=' . $row['forum_id'] . '&amp;p=' . $post_id) . '#p' . $post_id);
		}

		$post_rating_enabled = ($this->config['rs_post_rating'] == 2 || ($this->config['rs_post_rating'] == 1 && $post_id == $row['topic_first_post_id']));
		if (!$post_rating_enabled || !$this->config['rs_negative_point'] && $mode == 'negative' || !$row['reputation_enabled'])
		{
			$this->show_error($this->user->lang('RS_DISABLED'), 'viewtopic', $post_id);
		}

		if ($row['user_type'] == USER_IGNORE)
		{
			$this->show_error($this->user->lang('RS_USER_ANONYMOUS'), 'viewtopic', $post_id);
		}

		if ($row['poster_id'] == $this->user->data['user_id'])
		{
			$this->show_error($this->user->lang('RS_SELF_POST'), 'viewtopic', $post_id);
		}

		if (in_array($row['poster_id'], explode(',', $this->config['rs_users_to_exclude'])))
		{
			$this->show_error($this->user->lang('RS_USER_IS_EXCLUDED', $row['username']), 'viewtopic', $post_id);
		}

		// Don't allow to rate same post - only allow to delete the vote added previously
		if ($row['reputation_id'])
		{
			$message = $this->user->lang('RS_SAME_POST', ($row['reputation_points'] > 0 ? '+' : '') . $row['reputation_points']);

			if ($this->auth->acl_get('u_rs_delete') && ($mode == 'negative' ? ($row['reputation_points'] > 0) : ($row['reputation_points'] < 0)))
			{
				$message .= '<br /><a class="reputation-delete" href="' . $this->helper->route('reputation_delete_controller', ['rid' => $row['reputation_id']] + ($auc ? ['auc' => true] : [])) . '">' . $this->user->lang('RS_DELETE_VOTE') . '</a>';
			}

			$this->show_error($message, 'viewtopic', $post_id);
		}

		if (!$this->auth->acl_get('f_rs_rate', $row['forum_id']) || !$this->auth->acl_get('f_rs_rate_negative', $row['forum_id']) && $mode == 'negative' || !$this->auth->acl_get('u_rs_rate_post'))
		{
			$this->show_error($this->user->lang('RS_USER_DISABLED'), 'viewtopic', $post_id);
		}

		// Check if user reputation is enough to give negative points
		if ($this->config['rs_min_rep_negative'] && ($this->user->data['user_reputation'] < $this->config['rs_min_rep_negative']) && $mode == 'negative')
		{
			$this->show_error($this->user->lang('RS_USER_NEGATIVE', $this->config['rs_min_rep_negative']), 'viewtopic', $post_id);
		}

		// Anti-abuse behaviour
		if (!empty($this->config['rs_anti_time']) && !empty($this->config['rs_anti_post']))
		{
			$anti_time = time() - $this->config['rs_anti_time'] * 3600;
			$sql_and = (!$this->config['rs_anti_method']) ? 'AND user_id_to = ' . $row['poster_id'] : '';
			$sql = 'SELECT COUNT(reputation_id) AS reputation_per_day
				FROM ' . $this->reputations_table . '
				WHERE user_id_from = ' . $this->user->data['user_id'] . '
					' . $sql_and . '
					AND ' . $this->db->sql_in_set('reputation_type_id', $reputation_type_ids) . '
					AND reputation_time > ' . $anti_time;
			$result = $this->db->sql_query($sql);
			$anti_row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($anti_row['reputation_per_day'] >= $this->config['rs_anti_post'])
			{
				$this->show_error($this->user->lang('RS_ANTISPAM_INFO'), 'viewtopic', $post_id);
			}
		}

		if ($this->user->check_ban($row['poster_id'], false, false, true))
		{
			$this->show_error($this->user->lang('RS_USER_BANNED'), 'viewtopic', $post_id);
		}

		// Prevent overrating one user by another
		if ($this->reputation_manager->prevent_rating($row['poster_id']))
		{
			$this->show_error($this->user->lang('RS_ANTISPAM_INFO'), 'viewtopic', $post_id);
		}

		$points = $this->request->variable('points', '');
		$comment = $this->request->variable('comment', '', true);

		$submit = false;

		if ($this->request->is_set_post('submit_vote'))
		{
			$submit = true;
		}

		if ($submit && $this->config['rs_enable_comment'])
		{
			if (strlen($comment) > $this->config['rs_comment_max_chars'])
			{
				$submit = false;
				$error = $this->user->lang('RS_COMMENT_TOO_LONG', strlen($comment), $this->config['rs_comment_max_chars']);

				if ($is_ajax)
				{
					(new \phpbb\json_response)->send(['comment_error' => $error]);
				}
			}

			if (($this->config['rs_force_comment'] == self::RS_COMMENT_BOTH || $this->config['rs_force_comment'] == self::RS_COMMENT_POST) && empty($comment))
			{
				$submit = false;
				$error = $this->user->lang('RS_NO_COMMENT');

				if ($is_ajax)
				{
					(new \phpbb\json_response)->send(['comment_error' => $error]);
				}
			}
		}

		// Sumbit vote without showing popup window when the comments and the reputation power are disabled
		if (!$this->config['rs_enable_comment'] && (!$this->config['rs_enable_power'] || $this->config[$auc ? 'rs_max_power_auc' : 'rs_max_power'] == 1))
		{
			$submit = true;
			$points = ($mode == 'negative') ? '-1' : '1';
		}

		if ($this->config['rs_enable_power'])
		{
			$voting_power_pulldown = '';
			$used_power = $this->reputation_power->used($this->user->data['user_id']);
			// $user_reputation_common_plus_auc = $this->user->data['user_reputation'] + $this->user->data['user_reputation_auc_buyer'] + $this->user->data['user_reputation_auc_seller'];
			$user_reputation_common_plus_auc = $this->user->data['user_reputation'];
			$max_voting_power = $this->reputation_power->get($this->user->data['user_posts'], $this->user->data['user_regdate'], $user_reputation_common_plus_auc, $this->user->data['user_warnings'], $this->user->data['group_id']);
			$details_url = $this->helper->route('reputation_explain_vote_points', ['uid' => $this->user->data['user_id']]);

			if ($max_voting_power < 1)
			{
				$this->show_error($this->user->lang('RS_NO_POWER', $details_url), 'viewtopic', $post_id);
			}

			$voting_power_left = $max_voting_power - $used_power;
			$max_voting_allowed = $this->config['rs_power_renewal'] ? min($max_voting_power, $voting_power_left) : $max_voting_power;
			$max_voting_allowed = min($max_voting_allowed, $this->config[$auc ? 'rs_max_power_auc' : 'rs_max_power']);

			if ($voting_power_left <= 0 && $this->config['rs_power_renewal'])
			{
				$message = $this->user->lang('RS_NO_POWER_LEFT', $max_voting_power, $voting_power_left, $this->user->lang('RS_HOURS', (int) $this->config['rs_power_renewal']), $details_url);
				$this->show_error($message, 'viewtopic', $post_id);
			}

			$this->template->assign_vars([
				'RS_POWER_POINTS_LEFT'		=> $this->config['rs_power_renewal'] ? $this->user->lang('RS_VOTE_POWER_LEFT_OF_MAX', min($voting_power_left, $max_voting_power), $max_voting_power, $max_voting_allowed) : '',
				'RS_POWER_PROGRESS_EMPTY'	=> ($this->config['rs_power_renewal'] && $max_voting_power) ? round((($max_voting_power - $voting_power_left) / $max_voting_power) * 100, 0) : '',
			]);

			if ($this->config[$auc ? 'rs_max_power_auc' : 'rs_max_power'] > 1)
			{
				for ($i = 1; $i <= $max_voting_allowed; ++$i)
				{
					if ($mode == 'negative')
					{
						$voting_power_pulldown = '<option value="-' . $i . '">' . '&minus;' . $i . '</option>';
					}
					else
					{
						$voting_power_pulldown = '<option value="' . $i . '">' . '+' . $i . '</option>';
					}

					$this->template->assign_block_vars('reputation', [
						'REPUTATION_POWER'	=> $voting_power_pulldown
					]);
				}
			}
			else
			{
				$points = ($mode == 'negative') ? '-1' : '1';
			}
		}
		else
		{
			$points = ($mode == 'negative') ? '-1' : '1';
		}

		if ($submit)
		{
			// Prevent cheater to break the forum permissions to give negative points or give more points than they can 
			if (!$this->auth->acl_get('f_rs_rate_negative', $row['forum_id']) && $points < 0 || $points < 0 && $this->config['rs_min_rep_negative'] && ($this->user->data['user_reputation'] < $this->config['rs_min_rep_negative']) || $this->config['rs_enable_power'] && (($points > $max_voting_allowed) || ($points < -$max_voting_allowed)))
			{
				$submit = false;
				$error = $this->user->lang('RS_USER_CANNOT_RATE');

				if ($is_ajax)
				{
					(new \phpbb\json_response)->send(['comment_error' => $error]);
				}
			}
		}

		if ($submit && empty($error))
		{
			$data = [
				'user_id_from'			=> $this->user->data['user_id'],
				'user_id_to'			=> $row['poster_id'],
				'reputation_type'		=> 'post',
				'reputation_item_id'	=> $post_id,
				'reputation_points'		=> $points,
				'reputation_comment'	=> $comment,
			];

			try
			{
				$this->reputation_manager->store_reputation($data);
			}
			catch (\Exception $e)
			{
				$error = $e->getMessage();

				if ($is_ajax)
				{
					(new \phpbb\json_response)->send(['error_msg' => $error]);
				}
			}
		}
		if ($submit && empty($error))
		{
			$notification_data = [
				'user_id_to'		=> $row['poster_id'],
				'user_id_from'		=> $this->user->data['user_id'],
				'post_id'			=> $post_id,
				'post_subject'		=> $row['post_subject'],
				'auc'				=> $auc,
			];
			$this->reputation_manager->add_notification('pico.reputation.notification.type.rate_post_' . $mode, $notification_data);

			$post_reputation = $this->reputation_manager->get_post_reputation($post_id);
			$user_reputation = $this->reputation_manager->get_user_reputation($row['poster_id']);

			$message = $this->user->lang('RS_VOTE_SAVED');
			$json_data = [
				'post_id'				=> $post_id,
				'poster_id'				=> $row['poster_id'],
				'post_reputation'		=> $this->format_number($post_reputation),
				'user_reputation'		=> $this->format_number($user_reputation),
				'post_reputation_class'	=> $this->reputation_helper->reputation_class($post_reputation),
				'user_reputation_class'	=> $this->reputation_helper->reputation_class($user_reputation),
				'reputation_vote'		=> ($points > 0) ? 'rated_good' : 'rated_bad',
				'success_msg'			=> $message,
				'auc'					=> $auc,
			];
			$redirect = append_sid("{$this->root_path}viewtopic.{$this->php_ext}", 'f=' . $row['forum_id'] . '&amp;p=' . $post_id) . '#p' . $post_id;
			$this->reputation_manager->response($message, $json_data, $redirect, 'RETURN_TOPIC', $is_ajax);
		}

		$this->template->assign_vars([
			'ERROR_MSG'					=> $error,

			'S_CONFIRM_ACTION'			=> $this->helper->route('reputation_post_rating_controller', ['mode' => $mode, 'post_id' => $post_id] + ($auc ? ['auc' => true] : [])),
			'S_ERROR'					=> (!empty($error)) ? true : false,
			'S_RS_POWER_ENABLE' 		=> $this->config['rs_enable_power'] ? true : false,
			'S_RS_COMMENT_ENABLE'		=> $this->config['rs_enable_comment'] ? true : false,
			'S_RS_COMMENT_REQUIRED'		=> ($this->config['rs_force_comment'] == self::RS_COMMENT_BOTH || $this->config['rs_force_comment'] == self::RS_COMMENT_POST),
			'S_IS_AJAX'					=> $is_ajax,

			'RS_MODE'					=> $mode,
			'RS_AUC'					=> $auc,
		]);

		return $this->helper->render('ratepost.html', $this->user->lang('RS_POST_GIVE_' . strtoupper($mode)));
	}

	/**
	* Display the user rating page
	*
	* @param int $uid	User ID taken from the URL
	* @return Symfony\Component\HttpFoundation\Response A Symfony Response object
	* @access public
	*/
	public function user($mode, $uid)
	{
		$error = '';
		$is_ajax = $this->request->is_ajax();

		// $auc = $this->request->variable('auc', false);
		$auc = false;

		if (empty($this->config['rs_enable']))
		{
			if ($is_ajax)
			{
				(new \phpbb\json_response)->send(['error_msg' => $this->user->lang('RS_DISABLED')]);
			}

			redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
		}

		if (!$this->config['rs_user_rating'] || !$this->auth->acl_get('u_rs_rate'))
		{
			$this->show_error($this->user->lang('RS_DISABLED'));
		}

		$sql = 'SELECT user_id, user_type, username
			FROM ' . USERS_TABLE . "
			WHERE user_id = $uid";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			$this->show_error($this->user->lang('RS_NO_USER_ID'));
		}

		if ($this->request->is_set_post('cancel'))
		{
			redirect(append_sid("memberlist.{$this->php_ext}", 'mode=viewprofile&amp;u=' . $uid));
		}

		if ($row['user_type'] == USER_IGNORE)
		{
			$this->show_error($this->user->lang('RS_USER_ANONYMOUS'));
		}

		if ($row['user_id'] == $this->user->data['user_id'])
		{
			$this->show_error($this->user->lang('RS_SELF'), 'memberlist', $uid);
		}

		if (in_array($row['user_id'], explode(',', $this->config['rs_users_to_exclude'])))
		{
			$this->show_error($this->user->lang('RS_USER_IS_EXCLUDED', $row['username']), 'memberlist', $uid);
		}

		if ($this->user->check_ban($uid, false, false, true))
		{
			$this->show_error($this->user->lang('RS_USER_BANNED'), 'memberlist', $uid);
		}

		$reputation_type_ids = ($auc)
			? [$this->reputation_manager->get_reputation_type_id('auc_user_buyer'), $this->reputation_manager->get_reputation_type_id('auc_user_seller')]
			: [$this->reputation_manager->get_reputation_type_id('user')];

		$sql = 'SELECT reputation_id, reputation_time, reputation_points
			FROM ' . $this->reputations_table . "
			WHERE user_id_to = {$uid}
				AND user_id_from = {$this->user->data['user_id']}
				AND " . $this->db->sql_in_set('reputation_type_id', $reputation_type_ids) . "
			ORDER by reputation_id DESC";
		$result = $this->db->sql_query($sql);
		$check_user = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($check_user && !$this->config['rs_user_rating_gap'])
		{
			$message = $this->user->lang('RS_SAME_USER');

			if ($this->auth->acl_get('u_rs_delete') && ($mode == 'negative' ? ($check_user['reputation_points'] > 0) : ($check_user['reputation_points'] < 0)))
			{
				$message .= '<br /><a class="reputation-delete" href="' . $this->helper->route('reputation_delete_controller', ['rid' => $check_user['reputation_id']] + ($auc ? ['auc' => true] : [])) . '">' . $this->user->lang('RS_DELETE_VOTE') . '</a>';
			}

			$this->show_error($message, 'memberlist', $uid);
		}

		if ($this->config['rs_user_rating_gap'] && (time() < $check_user['reputation_time'] + $this->config['rs_user_rating_gap'] * 3600))
		{
			// Inform user how long he has to wait to rate the user
			$next_vote_time = ($check_user['reputation_time'] + $this->config['rs_user_rating_gap'] * 3600) - time();
			$next_vote_in = '';
			$next_vote_in .= intval($next_vote_time / 86400) ? intval($next_vote_time / 86400) . ' ' . $this->user->lang('DAYS') . ' ' : '';
			$next_vote_in .= intval(($next_vote_time / 3600) % 24)  ? intval(($next_vote_time / 3600) % 24) . ' ' . $this->user->lang('HOURS') . ' ' : '';
			$next_vote_in .= intval(($next_vote_time / 60) % 60) ? intval(($next_vote_time / 60) % 60) . ' ' . $this->user->lang('MINUTES') : '';
			$next_vote_in .= (intval($next_vote_time) < 60) ? intval($next_vote_time) . ' ' . $this->user->lang('SECONDS') : '';

			$this->show_error($this->user->lang('RS_USER_GAP', $next_vote_in), 'memberlist', $uid);
		}

		// Check if user reputation is enough to give negative points
		if ($this->config['rs_min_rep_negative'] && ($this->user->data['user_reputation'] < $this->config['rs_min_rep_negative']) && $mode == 'negative')
		{
			$this->show_error($this->user->lang('RS_USER_NEGATIVE', $this->config['rs_min_rep_negative']), 'memberlist', $uid);
		}

		if ($this->reputation_manager->prevent_rating($uid))
		{
			$this->show_error($this->user->lang('RS_SAME_USER'), 'memberlist', $uid);
		}

		$points = $this->request->variable('points', '');
		$comment = $this->request->variable('comment', '', true);
		$error = '';

		$submit = false;

		if ($this->request->is_set_post('submit_vote'))
		{
			$submit = true;
		}

		if ($submit && $this->config['rs_enable_comment'])
		{
			if (strlen($comment) > $this->config['rs_comment_max_chars'])
			{
				$submit = false;
				$error = $this->user->lang('RS_COMMENT_TOO_LONG', strlen($comment), $this->config['rs_comment_max_chars']);

				if ($is_ajax)
				{
					(new \phpbb\json_response)->send(['comment_error' => $error]);
				}
			}

			if (($this->config['rs_force_comment'] == self::RS_COMMENT_BOTH || $this->config['rs_force_comment'] == self::RS_COMMENT_USER) && empty($comment))
			{
				$submit = false;
				$error = $this->user->lang('RS_NO_COMMENT');

				if ($is_ajax)
				{
					(new \phpbb\json_response)->send(['comment_error' => $error]);
				}
			}
		}

		// Sumbit vote without showing popup window when the comments and the reputation power are disabled
		if (!$this->config['rs_enable_comment'] && (!$this->config['rs_enable_power'] || $this->config[$auc ? 'rs_max_power_auc' : 'rs_max_power'] == 1))
		{
			$submit = true;
			$points = ($mode == 'negative') ? '-1' : '1';
		}

		if ($this->config['rs_enable_power'])
		{
			$voting_power_pulldown = '';
			$used_power = $this->reputation_power->used($this->user->data['user_id']);
			// $user_reputation_common_plus_auc = $this->user->data['user_reputation'] + $this->user->data['user_reputation_auc_buyer'] + $this->user->data['user_reputation_auc_seller'];
			$user_reputation_common_plus_auc = $this->user->data['user_reputation'];
			$max_voting_power = $this->reputation_power->get($this->user->data['user_posts'], $this->user->data['user_regdate'], $user_reputation_common_plus_auc, $this->user->data['user_warnings'], $this->user->data['group_id']);
			$details_url = $this->helper->route('reputation_explain_vote_points', ['uid' => $this->user->data['user_id']]);

			if ($max_voting_power < 1)
			{
				$this->show_error($this->user->lang('RS_NO_POWER', $details_url), 'memberlist', $uid);
			}

			$voting_power_left = $max_voting_power - $used_power;
			$max_voting_allowed = $this->config['rs_power_renewal'] ? min($max_voting_power, $voting_power_left) : $max_voting_power;
			$max_voting_allowed = min($max_voting_allowed, $this->config[$auc ? 'rs_max_power_auc' : 'rs_max_power']);

			if ($voting_power_left <= 0 && $this->config['rs_power_renewal'])
			{
				$message = $this->user->lang('RS_NO_POWER_LEFT', $max_voting_power, $voting_power_left, $this->user->lang('RS_HOURS', (int) $this->config['rs_power_renewal']), $details_url);
				$this->show_error($message, 'memberlist', $uid);
			}

			$this->template->assign_vars([
				'RS_POWER_POINTS_LEFT'		=> $this->config['rs_power_renewal'] ? $this->user->lang('RS_VOTE_POWER_LEFT_OF_MAX', min($voting_power_left, $max_voting_power), $max_voting_power, $max_voting_allowed) : '',
				'RS_POWER_PROGRESS_EMPTY'	=> ($this->config['rs_power_renewal'] && $max_voting_power) ? round((($max_voting_power - $voting_power_left) / $max_voting_power) * 100, 0) : '',
			]);

			if ($this->config[$auc ? 'rs_max_power_auc' : 'rs_max_power'] > 1)
			{
				for ($i = 1; $i <= $max_voting_allowed; ++$i)
				{
					if ($mode == 'negative')
					{
						$voting_power_pulldown = '<option value="-' . $i . '">' . '&minus;' . $i . '</option>';
					}
					else
					{
						$voting_power_pulldown = '<option value="' . $i . '">' . '+' . $i . '</option>';
					}

					$this->template->assign_block_vars('reputation', [
						'REPUTATION_POWER'	=> $voting_power_pulldown
					]);
				}
			}
			else
			{
				$points = ($mode == 'negative') ? '-1' : '1';
			}
		}
		else
		{
			$points = ($mode == 'negative') ? '-1' : '1';
		}

		if ($submit)
		{
			// Prevent cheater to break the forum permissions to give negative points or give more points than they can 
			if (!$this->auth->acl_get('u_rs_rate_negative') && $points < 0 || $points < 0 && $this->config['rs_min_rep_negative'] && ($this->user->data['user_reputation'] < $this->config['rs_min_rep_negative']) || $this->config['rs_enable_power'] && (($points > $max_voting_allowed) || ($points < -$max_voting_allowed)))
			{
				$submit = false;
				$error = $this->user->lang('RS_USER_CANNOT_RATE');

				if ($is_ajax)
				{
					(new \phpbb\json_response)->send(['comment_error' => $error]);
				}
			}
		}

		if ($submit && empty($error))
		{
			$data = [
				'user_id_from'			=> $this->user->data['user_id'],
				'user_id_to'			=> $uid,
				'reputation_type'		=> 'user',
				'reputation_item_id'	=> $uid,
				'reputation_points'		=> $points,
				'reputation_comment'	=> $comment,
			];

			try
			{
				$this->reputation_manager->store_reputation($data);
			}
			catch (\Exception $e)
			{
				$error = $e->getMessage();

				if ($is_ajax)
				{
					(new \phpbb\json_response)->send(['error_msg' => $error]);
				}
			}
		}
		if ($submit && empty($error))
		{
			$notification_data = [
				'user_id_to'	=> $uid,
				'user_id_from'	=> $this->user->data['user_id'],
				'auc'			=> $auc,
			];
			$this->reputation_manager->add_notification('pico.reputation.notification.type.rate_user_' . $mode, $notification_data);

			$message = $this->user->lang('RS_VOTE_SAVED');
			$user_reputation = $this->reputation_manager->get_user_reputation($uid);
			$json_data = [
				'user_id'				=> $uid,
				'user_reputation'		=> $this->format_number($user_reputation),
				'user_reputation_class'	=> $this->reputation_helper->reputation_class($user_reputation),
				'reputation_vote'		=> ($points > 0) ? 'rated_good' : 'rated_bad',
				'success_msg'			=> $message,
				'auc'					=> $auc,
			];
			$redirect = append_sid("memberlist.{$this->php_ext}", 'mode=viewprofile&amp;u=' . $uid);
			$this->reputation_manager->response($message, $json_data, $redirect, 'RETURN_PAGE', $is_ajax);
		}

		$this->template->assign_vars([
			'ERROR_MSG'					=> $error,

			'S_CONFIRM_ACTION'			=> $this->helper->route('reputation_user_rating_controller', ['mode' => $mode, 'uid' => $uid] + ($auc ? ['auc' => true] : [])),
			'S_RS_POWER_ENABLE' 		=> $this->config['rs_enable_power'] ? true : false,
			'S_RS_COMMENT_ENABLE'		=> $this->config['rs_enable_comment'] ? true : false,
			'S_RS_COMMENT_REQUIRED'		=> ($this->config['rs_force_comment'] == self::RS_COMMENT_BOTH || $this->config['rs_force_comment'] == self::RS_COMMENT_USER),
			'S_IS_AJAX'					=> $is_ajax,

			'RS_MODE'					=> $mode,
			'RS_AUC'					=> $auc,
		]);

		return $this->helper->render('rateuser.html', $this->user->lang('RS_USER_GIVE_' . strtoupper($mode)));
	}

	private function show_error($text, $redirect_page, $redirect_item_id)
	{
		if ($redirect_page == 'viewtopic'):
			$redirect_url = append_sid("{$this->root_path}viewtopic.{$this->php_ext}", "p=$redirect_item_id'#p$redirect_item_id");
			$redirect_url_text = 'RETURN_TOPIC';
		elseif ($redirect_page == 'memberlist'):
			$redirect_url = append_sid("memberlist.{$this->php_ext}", "mode=viewprofile&amp;u=$redirect_item_id");
			$redirect_url_text = 'RETURN_PAGE';
		else:
			$redirect_url = append_sid("{$this->root_path}index.{$this->php_ext}");
			$redirect_url_text = 'RETURN_INDEX';
		endif;

		meta_refresh(3, $redirect_url);

		if ($this->request->is_ajax())
		{
			(new \phpbb\json_response)->send(['error_msg' => $text]);
		}
		else
		{
			$text .= '<br /><br />' . $this->user->lang($redirect_url_text, '<a href="' . $redirect_url . '">', '</a>');
			trigger_error($text);
		}
	}

	private function format_number($number)
	{
		return ($this->config['rs_negative_point'] && $number > 0 ? '+' : '') . $number;
	}
}
