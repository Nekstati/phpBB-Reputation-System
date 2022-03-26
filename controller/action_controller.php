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

class action_controller
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\user */
	protected $user;

	/** @var \pico\reputation\core\reputation_helper */
	protected $reputation_helper;

	/** @var \pico\reputation\core\reputation_manager */
	protected $reputation_manager;

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
	* @param \phpbb\controller\helper					Controller helper object
	* @param \phpbb\db\driver\driver $db				Database object
	* @param \phpbb\request\request $request			Request object
	* @param \phpbb\user $user							User object
	* @param \pico\reputation\core\reputation_helper	Reputation helper object
	* @param \pico\reputation\core\reputation_manager	Reputation manager object
	* @param string $reputations_table					Name of the table used to store reputations data
	* @param string $root_path							phpBB root path
	* @param string $php_ext							phpEx
	* @return \pico\reputation\controller\rating_controller
	* @access public
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\controller\helper $helper, \phpbb\db\driver\driver_interface $db, \phpbb\request\request $request, \phpbb\user $user, \pico\reputation\core\reputation_helper $reputation_helper, \pico\reputation\core\reputation_manager $reputation_manager, $reputations_table, $reputation_types_table, $root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->db = $db;
		$this->helper = $helper;
		$this->request = $request;
		$this->user = $user;
		$this->reputation_helper = $reputation_helper;
		$this->reputation_manager = $reputation_manager;
		$this->reputations_table = $reputations_table;
		$this->reputation_types_table = $reputation_types_table;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;

		$user->add_lang_ext('pico/reputation', 'reputation_system');
	}

	/**
	* Delete reputation page/action
	*
	* @param int $rid	Reputation ID taken from the URL
	* @return null
	* @access public
	*/
	public function delete($rid)
	{
		$is_ajax = $this->request->is_ajax();
		$submit = false;

		// $auc = $this->request->variable('auc', false);
		$auc = false;
		$target_type_names = ($auc)
			? ['auc_post_buyer', 'auc_post_seller']
			: ['post'];
		$post_type_ids = [];
		foreach ($target_type_names as $name)
			$post_type_ids[$name] = $this->reputation_manager->get_reputation_type_id($name);

		$sql_array = [
			'SELECT'	=> 'r.*, rt.reputation_type_name, p.post_id, uf.username AS username_from, ut.username AS username_to',
			'FROM'		=> [
				$this->reputations_table => 'r',
				$this->reputation_types_table => 'rt',
			],
			'LEFT_JOIN'	=> [
				[
					'FROM'	=> [POSTS_TABLE => 'p'],
					'ON'	=> 'p.post_id = r.reputation_item_id
						AND ' . $this->db->sql_in_set('r.reputation_type_id', $post_type_ids),
				],
				[
					'FROM'	=> [USERS_TABLE => 'uf'],
					'ON'	=> 'r.user_id_from = uf.user_id ',
				],
				[
					'FROM'	=> [USERS_TABLE => 'ut'],
					'ON'	=> 'r.user_id_to = ut.user_id ',
				],
			],
			'WHERE'	=> 'r.reputation_id = ' . $rid . '
				AND rt.reputation_type_id = r.reputation_type_id',
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (empty($row))
		{
			$message = $this->user->lang('RS_NO_REPUTATION');
			$json_data = [
				'error_msg' => $message,
			];
			$redirect = append_sid("{$this->root_path}index.{$this->php_ext}");
			$redirect_text = 'RETURN_INDEX';
			$this->reputation_manager->response($message, $json_data, $redirect, $redirect_text, $is_ajax);
		}

		$redirect = ($this->request->server('HTTP_REFERER', '')) ?: $this->helper->route('reputation_details_controller', ['uid' => $row['user_id_to']] + ($auc ? ['auc' => true] : []));

		if ($this->request->is_set_post('cancel'))
		{
			redirect($redirect);
		}

		if ($this->auth->acl_gets('m_rs_moderate') || (($row['user_id_from'] == $this->user->data['user_id']) && $this->auth->acl_get('u_rs_delete')))
		{
			$submit = true;
		}
		else
		{
			$message = $this->user->lang('RS_USER_CANNOT_DELETE');
			$json_data = [
				'error_msg' => $message,
			];
			$redirect_text = 'RETURN_PAGE';
			$this->reputation_manager->response($message, $json_data, $redirect, $redirect_text, $is_ajax);
		}

		if ($submit)
		{
			try
			{
				$this->reputation_manager->delete_reputation($row);
			}
			catch (\Exception $e)
			{
				trigger_error($e->getMessage());
			}

			$user_reputation = $this->reputation_manager->get_user_reputation($row['user_id_to']);
			$remainder = 0;

			// When deleting own vote from userprofile page, we need to know is this our only vote or not, prior to set proper rated_good/rated_bad class.
			if (strpos($this->request->server('HTTP_REFERER', ''), 'memberlist.php?mode=viewprofile') !== false && $row['user_id_from'] == $this->user->data['user_id'])
			{
				$user_type_ids = ($auc)
					? [$this->reputation_manager->get_reputation_type_id('auc_user_buyer'), $this->reputation_manager->get_reputation_type_id('auc_user_seller')]
					: [$this->reputation_manager->get_reputation_type_id('user')];

				$sql = 'SELECT SUM(reputation_points) AS sum
					FROM ' . $this->reputations_table . '
					WHERE ' . $this->db->sql_in_set('reputation_type_id', $user_type_ids) . "
					AND user_id_from = {$this->user->data['user_id']}
					AND user_id_to = {$row['user_id_to']}";
				$result = $this->db->sql_query($sql);
				$remainder = $this->db->sql_fetchfield('sum');
				$this->db->sql_freeresult($result);
			}

			$message = $this->user->lang('RS_POINTS_DELETED', 1);
			$json_data = [
				'rid'					=> $rid,
				'poster_id'				=> $row['user_id_to'],
				'user_reputation'		=> $this->format_number($user_reputation),
				'user_reputation_class'	=> $this->reputation_helper->reputation_class($user_reputation),
				'is_own_single_vote'	=> ($row['user_id_from'] == $this->user->data['user_id'] && !$remainder),
				'auc'					=> $auc,
			];

			if (isset($row['post_id']))
			{
				$post_reputation = $this->reputation_manager->get_post_reputation($row['post_id']);

				$json_data = array_merge($json_data, [
					'post_id'				=> $row['post_id'],
					'post_reputation'		=> $this->format_number($post_reputation),
					'post_reputation_class'	=> $this->reputation_helper->reputation_class($post_reputation),
				]);
			}

			$redirect_text = 'RETURN_PAGE';
			$this->reputation_manager->response($message, $json_data, $redirect, $redirect_text, $is_ajax);
		}
	}

	/**
	* Clear post reputation
	*
	* @param int $post_id	Post ID
	* @return null
	* @access public
	*/
	public function clear_post($post_id)
	{
		$is_ajax = $this->request->is_ajax();
		$submit = false;

		// $auc = $this->request->variable('auc', false);
		$auc = false;
		$target_type_names = ($auc)
			? ['auc_post_buyer', 'auc_post_seller']
			: ['post'];
		$post_type_ids = [];
		foreach ($target_type_names as $name)
			$post_type_ids[$name] = $this->reputation_manager->get_reputation_type_id($name);

		$sql_array = [
			'SELECT'	=> 'r.*, p.post_subject, p.post_reputation, ut.username AS username_to',
			'FROM'		=> [
				$this->reputations_table => 'r',
				POSTS_TABLE => 'p',
			],
			'LEFT_JOIN'	=> [
				[
					'FROM'	=> [USERS_TABLE => 'ut'],
					'ON'	=> 'r.user_id_to = ut.user_id ',
				],
			],
			'WHERE'	=> 'r.reputation_item_id = ' . $post_id . '
				AND ' . $this->db->sql_in_set('r.reputation_type_id', $post_type_ids) . '
				AND p.post_id = r.reputation_item_id',
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		if (empty($rows))
		{
			$message = $this->user->lang('RS_NO_REPUTATION');
			$json_data = [
				'error_msg' => $message,
			];
			$redirect = append_sid("{$this->root_path}index.{$this->php_ext}");
			$redirect_text = 'RETURN_INDEX';

			$this->reputation_manager->response($message, $json_data, $redirect, $redirect_text, $is_ajax);
		}

		$redirect = $this->helper->route('reputation_post_details_controller', ['post_id' => $post_id] + ($auc ? ['auc' => true] : []));

		if ($this->request->is_set_post('cancel'))
		{
			redirect($redirect);
		}

		$redirect_text = 'RETURN_PAGE';

		if ($this->auth->acl_gets('m_rs_moderate'))
		{
			if ($is_ajax)
			{
				$submit = true;
			}
			else
			{
				$s_hidden_fields = build_hidden_fields(['p' => $post_id] + ($auc ? ['auc' => true] : []));

				if (confirm_box(true))
				{
					$submit = true;
				}
				else
				{
					confirm_box(false, $this->user->lang('RS_CLEAR_POST_CONFIRM'), $s_hidden_fields);
				}
			}
		}
		else
		{
			$message = $this->user->lang('RS_USER_CANNOT_DELETE');
			$json_data = [
				'error_msg' => $message,
			];

			$this->reputation_manager->response($message, $json_data, $redirect, $redirect_text, $is_ajax);
		}

		if ($submit)
		{
			try
			{
				$this->reputation_manager->clear_post_reputation($post_id, $rows);
			}
			catch (\Exception $e)
			{
				trigger_error($e->getMessage());
			}

			$user_reputation = $this->reputation_manager->get_user_reputation($rows[0]['user_id_to']);

			$message = $this->user->lang('RS_CLEARED_POST');
			$json_data = [
				'clear_post'			=> true,
				'post_id'				=> $post_id,
				'poster_id'				=> $rows[0]['user_id_to'],
				'user_reputation'		=> $this->format_number($user_reputation),
				'post_reputation'		=> 0,
				'user_reputation_class'	=> $this->reputation_helper->reputation_class($user_reputation),
				'post_reputation_class'	=> 'neutral',
				'auc'					=> $auc,
			];

			$this->reputation_manager->response($message, $json_data, $redirect, $redirect_text, $is_ajax);
		}
	}

	/**
	* Clear user reputation
	*
	* @param int $uid	User ID
	* @return null
	* @access public
	*/
	public function clear_user($uid)
	{
		$is_ajax = $this->request->is_ajax();
		$submit = false;

		$auc = $this->request->variable('auc', false);
		$auc = false;
		// $target_type_names = ['post', 'auc_post_buyer', 'auc_post_seller'];
		$target_type_names = ['post'];
		$post_type_ids = [];
		foreach ($target_type_names as $name)
			$post_type_ids[$name] = $this->reputation_manager->get_reputation_type_id($name);

		$sql_array = [
			'SELECT'	=> 'r.*, ut.user_reputation, ut.username AS username_to',
			'FROM'		=> [
				USERS_TABLE => 'ut',
			],
			'LEFT_JOIN'	=> [
				[
					'FROM'	=> [$this->reputations_table => 'r'],
					'ON'	=> 'r.user_id_to = ut.user_id ',
				],
			],
			'WHERE'	=> 'ut.user_id = ' . $uid
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (empty($row))
		{
			$message = $this->user->lang('RS_NO_REPUTATION');
			$json_data = [
				'error_msg' => $message,
			];
			$redirect = append_sid("{$this->root_path}index.{$this->php_ext}");
			$redirect_text = 'RETURN_INDEX';

			$this->reputation_manager->response($message, $json_data, $redirect, $redirect_text, $is_ajax);
		}

		$redirect = $this->helper->route('reputation_details_controller', ['uid' => $uid] + ($auc ? ['auc' => true] : []));

		if ($this->request->is_set_post('cancel'))
		{
			redirect($redirect);
		}

		$post_ids = [];

		$sql = 'SELECT reputation_item_id
			FROM ' . $this->reputations_table . "
			WHERE user_id_to = {$uid}
				AND " . $this->db->sql_in_set('reputation_type_id', $post_type_ids) . "
			GROUP BY reputation_item_id";
		$result = $this->db->sql_query($sql);

		while ($post_row = $this->db->sql_fetchrow($result))
		{
			$post_ids[] = $post_row['reputation_item_id'];
		}
		$this->db->sql_freeresult($result);

		$redirect_text = 'RETURN_PAGE';

		if ($this->auth->acl_gets('m_rs_moderate'))
		{
			if ($is_ajax)
			{
				$submit = true;
			}
			else
			{
				$s_hidden_fields = build_hidden_fields(['u' => $uid] + ($auc ? ['auc' => true] : []));

				if (confirm_box(true))
				{
					$submit = true;
				}
				else
				{
					confirm_box(false, $this->user->lang('RS_CLEAR_USER_CONFIRM'), $s_hidden_fields);
				}
			}
		}
		else
		{
			$message = $this->user->lang('RS_USER_CANNOT_DELETE');
			$json_data = [
				'error_msg' => $message,
			];

			$this->reputation_manager->response($message, $json_data, $redirect, $redirect_text, $is_ajax);
		}

		if ($submit)
		{
			try
			{
				$this->reputation_manager->clear_user_reputation($uid, $row, $post_ids);
			}
			catch (\Exception $e)
			{
				trigger_error($e->getMessage());
			}

			$message = $this->user->lang('RS_CLEARED_USER');
			$json_data = [
				'clear_user'			=> true,
				'post_ids'				=> $post_ids,
				'poster_id'				=> $uid,
				'user_reputation'		=> 0,
				'post_reputation'		=> 0,
				'user_reputation_class'	=> 'neutral',
				'post_reputation_class'	=> 'neutral',
				'auc'					=> $auc,
			];

			$this->reputation_manager->response($message, $json_data, $redirect, $redirect_text, $is_ajax);
		}
	}

	private function format_number($number)
	{
		return ($GLOBALS['config']['rs_negative_point'] && $number > 0 ? '+' : '') . $number;
	}
}
