<?php
/**
*
* Reputation System
*
* @copyright (c) 2014 Lukasz Kaczynski
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace pico\reputation\core;

class reputation_manager
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string The table we use to store our reputations */
	protected $reputations_table;

	/** @var string The database table the reputation types are stored */
	protected $reputation_types_table;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/** @var int Reputation identifier */
	private $reputation_id;

	private $auc, $auc_user_role;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth               $auth                   Auth object
	* @param \phpbb\cache\service           $cache                  Cache object
	* @param \phpbb\config\config           $config                 Config object
	* @param \phpbb\db\driver\driver        $db                     Database object
	* @param \phpbb\log\log\                $log                    Log object
	* @param \phpbb\template\template       $template               Template object
	* @param \phpbb\notification\manager    $notification_manager   Notification object
	* @param \phpbb\user                    $user                   User object
	* @param string                         $reputations_table      Name of the table used to store reputations data
	* @param string                         $reputation_types_table Name of the table used to store reputation types data
	* @param string                         $root_path              phpBB root path
	* @param string                         $php_ext                phpEx
	* @return \pico\reputation\core\reputation_manager
	* @access public
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\log\log $log, \phpbb\notification\manager $notification_manager, \phpbb\template\template $template, \phpbb\user $user, $reputations_table, $reputation_types_table, $root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->config = $config;
		$this->db = $db;
		$this->log = $log;
		$this->notification_manager = $notification_manager;
		$this->template = $template;
		$this->user = $user;
		$this->reputations_table = $reputations_table;
		$this->reputation_types_table = $reputation_types_table;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;

		// $this->auc = $GLOBALS['request']->variable('auc', false);
		$this->auc = false;
		// $this->auc_user_role = $GLOBALS['request']->variable('rs-auc-user-role', '');
		$this->auc_user_role = '';
		if (!in_array($this->auc_user_role, ['buyer', 'seller']))
		{
			$this->auc_user_role = '';
		}
		if ($this->auc_user_role != '')
		{
			$this->auc = true;
		}
	}

	/**
	* Get the reputation types
	*
	* @return array Reputation types
	* @access public
	*/
	public function get_reputation_types()
	{
		$reputation_type_ids = $this->cache->get('reputation_type_ids');

		if ($reputation_type_ids === false)
		{
			$reputation_type_ids = [];

			$sql = 'SELECT *
				FROM ' . $this->reputation_types_table;
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$reputation_type_ids[(int) $row['reputation_type_id']] = (string) $row['reputation_type_name'];
			}
			$this->db->sql_freeresult($result);

			$this->cache->put('reputation_type_ids', $reputation_type_ids);
		}

		return $reputation_type_ids;
	}

	/**
	* Get reputation type id from string
	*
	* @param string $type_string
	* @return int $type_id
	* @access public
	*/
	public function get_reputation_type_id($type_string)
	{
		$types = $this->get_reputation_types();
		$type_id = array_search($type_string, $types);

		if (empty($type_id))
		{
			throw new \Exception($this->user->lang('EXCEPTION_INVALID_TYPE', $type_string));
		}

		return $type_id;
	}

	/**
	* The main function for recording reputation vote.
	*
	* @param array $data Reputation data
	* @access public
	* @return null
	*/
	public function store_reputation($data)
	{
		$data['reputation_time'] = time();

		$required_fields = [
			'user_id_from'			=> 'integer',
			'user_id_to'			=> 'integer',
			'reputation_time'		=> 'integer',
			'reputation_type'		=> 'string',
			'reputation_item_id'	=> 'integer',
			'reputation_points'		=> 'integer',
			'reputation_comment'	=> 'string', 
		];

		foreach ($required_fields as $field => $type)
		{
			if (!isset($data[$field]))
			{
				throw new \Exception($this->user->lang('EXCEPTION_FIELD_MISSING', $field));
			}

			settype($data[$field], $type);
		}

		if ($data['reputation_points'] == 0)
		{
			throw new \Exception($this->user->lang('EXCEPTION_OUT_OF_BOUNDS', 'reputation_points'));
		}

		if ($this->auc_user_role && in_array($data['reputation_type'], ['post', 'user']))
		{
			$data['reputation_type'] = "auc_{$data['reputation_type']}_{$this->auc_user_role}";
		}

		$data['reputation_type_id'] = $this->get_reputation_type_id($data['reputation_type']);

		$validate_unsigned = [
			'user_id_from',
			'user_id_to',
			'reputation_time',
			'reputation_type_id',
			'reputation_item_id',
		];

		foreach ($validate_unsigned as $field)
		{
			if ($data[$field] < 0)
			{
				throw new \Exception($this->user->lang('EXCEPTION_OUT_OF_BOUNDS', $field));
			}
		}

		if (in_array($data['reputation_type'], ['post', 'auc_post_buyer', 'auc_post_seller']))
		{
			$sql = 'UPDATE ' . POSTS_TABLE . "
				SET post_reputation = post_reputation + {$data['reputation_points']}
				WHERE post_id = {$data['reputation_item_id']}";
			$this->db->sql_query($sql);
		}

		$user_rep_field = ($this->auc_user_role) ? "user_reputation_auc_{$this->auc_user_role}" : 'user_reputation';
		$sql = 'UPDATE ' . USERS_TABLE . "
			SET $user_rep_field = $user_rep_field + {$data['reputation_points']}
			WHERE user_id = {$data['user_id_to']}";
		$this->db->sql_query($sql);

		if ($this->config['rs_max_point'] || $this->config['rs_min_point'])
		{
			$this->check_max_min($data['user_id_to']);
		}

		if (isset($_POST['rs_acp_change_points_only']) && defined('ADMIN_START'))
			return;

		// Unset reputation type - it is not stored in DB
		unset($data['reputation_type']);

		$sql = 'INSERT INTO ' . $this->reputations_table . ' ' . $this->db->sql_build_array('INSERT', $data);
		$this->db->sql_query($sql);

		unset($this->reputation_id);
		$this->reputation_id = $this->db->sql_nextid();
	}

	/**
	* Check user reputation
	* 
	* If it is higher than allowed, decrease it to maximum.
	* If it is lower than allowed, increase it to minimum.
	*
	* @param int $user_id User ID
	* @access public
	* @return null
	*/
	private function check_max_min($user_id)
	{
		$user_rep_field = ($this->auc_user_role) ? "user_reputation_auc_{$this->auc_user_role}" : 'user_reputation';

		// $sql = 'SELECT user_reputation, user_reputation_auc_buyer, user_reputation_auc_seller
			// FROM ' . USERS_TABLE . '
			// WHERE user_id = ' . $user_id;
		// $result = $this->db->sql_query($sql);
		// $row = $this->db->sql_fetchrow($result);
		// $this->db->sql_freeresult($result);
		$sql = 'SELECT user_reputation
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . $user_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($this->config['rs_max_point'] != 0 && ($row[$user_rep_field] > $this->config['rs_max_point']))
		{
			$sql = 'UPDATE ' . USERS_TABLE . "
				SET $user_rep_field = {$this->config['rs_max_point']}
				WHERE user_id = $user_id";
			$this->db->sql_query($sql);
		}

		if ($this->config['rs_min_point'] != 0 && ($row[$user_rep_field] < $this->config['rs_min_point']))
		{
			$sql = 'UPDATE ' . USERS_TABLE . "
				SET $user_rep_field = {$this->config['rs_min_point']}
				WHERE user_id = $user_id";
			$this->db->sql_query($sql);
		}
	}

	/**
	* Notify user about reputation
	*
	* @param string $notification_type_name Notification type name
	* @param array $data Notification data
	* @access public
	* @return null
	*/
	public function add_notification($notification_type_name, $data)
	{
		$data = array_merge(
			['reputation_id' => $this->reputation_id],
			$data
		);
		$this->notification_manager->add_notifications($notification_type_name, $data);
	}

	/**
	* Response method for displaying reputation messages
	*
	* @param string $message Message
	* @param array $json_data Json data for ajax request
	* @param string $redirect_link Redirect link
	* @param string $redirect_text Redirect text
	* @param bool $is_ajax Ajax request
	* @access public
	* @return string
	*/
	public function response($message, $json_data, $redirect_link, $redirect_text, $is_ajax = false)
	{
		meta_refresh(3, $redirect_link);

		if ($is_ajax)
		{
			(new \phpbb\json_response)->send($json_data);
		}
		else
		{
			$message .= '<br /><br />' . $this->user->lang($redirect_text, '<a href="' . $redirect_link . '">', '</a>');
			trigger_error($message);
		}
	}

	/**
	* Return post reputation
	*
	* @param int $post_id Post ID
	* @access public
	* @return int post reputation
	*/
	public function get_post_reputation($post_id)
	{
		$sql = 'SELECT post_reputation
			FROM ' . POSTS_TABLE . "
			WHERE post_id = $post_id";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row['post_reputation'];
	}

	/**
	* Return user reputation
	*
	* @param int $user_id User ID
	* @access public
	* @return int user reputation
	*/
	public function get_user_reputation($user_id)
	{
		// $sql = 'SELECT user_reputation, user_reputation_auc_buyer, user_reputation_auc_seller
			// FROM ' . USERS_TABLE . "
			// WHERE user_id = $user_id";
		// $result = $this->db->sql_query($sql);
		// $row = $this->db->sql_fetchrow($result);
		// $this->db->sql_freeresult($result);
		$sql = 'SELECT user_reputation
			FROM ' . USERS_TABLE . "
			WHERE user_id = $user_id";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return ($this->auc) ? ($row['user_reputation_auc_buyer'] + $row['user_reputation_auc_seller']) : $row['user_reputation'];
	}

	/**
	* Prevent overrating one user by another user
	*
	* @param int $user_id User ID
	* @access public
	* @return bool
	*/
	public function prevent_rating($user_id)
	{
		if (!$this->config['rs_prevent_num'] || !$this->config['rs_prevent_perc'])
		{
			return false;
		}

		$total_reps = $same_user = 0;

		$target_type_names = ($this->auc)
			? ['auc_post_buyer', 'auc_post_seller', 'auc_user_buyer', 'auc_user_seller']
			: ['post', 'user'];
		$rep_type_ids = [];
		foreach ($target_type_names as $name)
			$rep_type_ids[$name] = $this->get_reputation_type_id($name);

		$sql = 'SELECT user_id_from
			FROM ' . $this->reputations_table . "
			WHERE user_id_to = {$user_id}
				AND " . $this->db->sql_in_set('reputation_type_id', $rep_type_ids);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$total_reps++;

			if ($row['user_id_from'] == $this->user->data['user_id'])
			{
				$same_user++;
			}
		}
		$this->db->sql_freeresult($result);

		if (($total_reps >= $this->config['rs_prevent_num']) && ($same_user / $total_reps * 100 >= $this->config['rs_prevent_perc']))
		{
			return true;
		}

		return false;
	}

	/**
	* Generet post URL
	*
	* @param array $row Array with data
	* @access public
	* @return null
	*/
	public function generate_post_link($row)
	{
		if (isset($row['post_id']) && $row['post_visibility'] != ITEM_DELETED)
		{
			$post_subject = $row['post_subject'] ?: $this->user->lang('POST');
			$post_url = ($this->auth->acl_get('f_read', $row['forum_id']))
				? append_sid("{$this->root_path}viewtopic.{$this->php_ext}", 'f=' . $row['forum_id'] . '&amp;p=' . $row['post_id'] . '#p' . $row['post_id'])
				: '';
		}
		else
		{
			$post_subject = $this->user->lang('RS_POST_DELETE');
			$post_url = '';
		}

		$this->template->assign_block_vars('reputation.post', [
			'POST_SUBJECT'	=> $post_subject,
			'U_POST'		=> $post_url,
		]);
	}

	/**
	* Delete single reputation
	*
	* @param array $data Reputation data
	* @access public
	* @return null
	*/
	public function delete_reputation($data)
	{
		$required_fields = [
			'user_id_from',
			'user_id_to',
			'reputation_item_id',
			'reputation_points',
			'reputation_type_name',
		];

		foreach ($required_fields as $field)
		{
			if (!isset($data[$field]))
			{
				throw new \Exception($this->user->lang('EXCEPTION_FIELD_MISSING', $field));
			}
		}

		if (in_array($data['reputation_type_name'], ['post', 'auc_post_buyer', 'auc_post_seller']))
		{
			$sql = 'UPDATE ' . POSTS_TABLE . "
				SET post_reputation = post_reputation - {$data['reputation_points']}
				WHERE post_id = {$data['reputation_item_id']}";
			$this->db->sql_query($sql);
		}

		$sql = 'DELETE FROM ' . $this->reputations_table . "
			WHERE reputation_id = {$data['reputation_id']}";
		$this->db->sql_query($sql);

		$this->notification_manager->delete_notifications('pico.reputation.notification.type.rate_post_negative', $data['reputation_id']);
		$this->notification_manager->delete_notifications('pico.reputation.notification.type.rate_post_positive', $data['reputation_id']);
		$this->notification_manager->delete_notifications('pico.reputation.notification.type.rate_user_negative', $data['reputation_id']);
		$this->notification_manager->delete_notifications('pico.reputation.notification.type.rate_user_positive', $data['reputation_id']);

		$user_rep_field = (in_array($data['reputation_type_name'], ['auc_post_buyer', 'auc_user_buyer']))
			? 'user_reputation_auc_buyer'
			: ((in_array($data['reputation_type_name'], ['auc_post_seller', 'auc_user_seller']))
				? 'user_reputation_auc_seller'
				: 'user_reputation');

		$sql = 'UPDATE ' . USERS_TABLE . "
			SET $user_rep_field = $user_rep_field - {$data['reputation_points']}
			WHERE user_id = {$data['user_id_to']}";
		$this->db->sql_query($sql);

		if ($this->config['rs_max_point'] || $this->config['rs_min_point'])
		{
			$this->check_max_min($data['user_id_to']);
		}

		// $this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_REPUTATION_DELETED', false, [
			// 'user_id_from'	=> (isset($data['username_from'])) ? $data['username_from'] : $data['user_id_from'],
			// 'user_id_to'	=> (isset($data['username_to'])) ? $data['username_to'] : $data['user_id_to'],
			// 'points'		=> $data['reputation_points'],
			// 'type_name'		=> $data['reputation_type_name'],
			// 'item_id'		=> $data['reputation_item_id'],
		// ]);
	}

	/**
	* Clear post reputation
	*
	* @param int $post_id Post id
	* @param array $data Reputation data
	* @access public
	* @return null
	*/
	public function clear_post_reputation($post_id, $data)
	{
		$required_fields = [
			'user_id_to',
			'reputation_item_id',
			'reputation_type_id',
			'post_reputation',
		];

		foreach ($required_fields as $field)
		{
			if (!isset($data[0][$field]))
			{
				throw new \Exception($this->user->lang('EXCEPTION_FIELD_MISSING', $field));
			}
		}

		$sql = 'UPDATE ' . POSTS_TABLE . "
			SET post_reputation = 0
			WHERE post_id = {$post_id}";
		$this->db->sql_query($sql);

		$rep_type_names = $this->get_reputation_types();
		$rep_type_ids = array_flip($rep_type_names);
		$points = array_fill_keys($rep_type_names, 0);
		foreach ($data as $d)
			$points[$rep_type_names[$d['reputation_type_id']]] += $d['reputation_points'];

		// $sql = 'UPDATE ' . USERS_TABLE . "
			// SET user_reputation = user_reputation - {$points['post']},
				// user_reputation_auc_buyer = user_reputation_auc_buyer - {$points['auc_post_buyer']},
				// user_reputation_auc_seller = user_reputation_auc_seller - {$points['auc_post_seller']}
			// WHERE user_id = {$data[0]['user_id_to']}";
		// $this->db->sql_query($sql);
		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_reputation = user_reputation - {$points['post']}
			WHERE user_id = {$data[0]['user_id_to']}";
		$this->db->sql_query($sql);

		if ($this->config['rs_max_point'] || $this->config['rs_min_point'])
		{
			$this->check_max_min($data[0]['user_id_to']);
		}

		// $sql = 'DELETE FROM ' . $this->reputations_table . "
			// WHERE reputation_item_id = {$post_id}
				// AND " . $this->db->sql_in_set('reputation_type_id', [$rep_type_ids['post'], $rep_type_ids['auc_post_buyer'], $rep_type_ids['auc_post_seller']]);
		// $this->db->sql_query($sql);
		$sql = 'DELETE FROM ' . $this->reputations_table . "
			WHERE reputation_item_id = {$post_id}
				AND " . $this->db->sql_in_set('reputation_type_id', [$rep_type_ids['post']]);
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . NOTIFICATIONS_TABLE . '
			WHERE ' . $this->db->sql_in_set('notification_type_id', [
				$this->notification_manager->get_notification_type_id('pico.reputation.notification.type.rate_post_negative'),
				$this->notification_manager->get_notification_type_id('pico.reputation.notification.type.rate_post_positive'),
			]) . '
			AND item_parent_id = ' . $post_id;
		$this->db->sql_query($sql);

		// $this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_POST_REPUTATION_CLEARED', false, [
			// 'user_id_to'	=> (isset($data['username_to'])) ? $data['username_to'] : $data['user_id_to'],
			// 'post_subject'	=> (isset($data['post_subject'])) ? $data['post_subject'] : $data['reputation_item_id'],
		// ]);
	}

	/**
	* Clear user reputation
	*
	* @param int $user_id User id
	* @param array $data Reputation data
	* @param arrat $post_ids Post IDs
	* @access public
	* @return null
	*/
	public function clear_user_reputation($user_id, $data, $post_ids)
	{
		$required_fields = [
			'user_reputation',
		];

		foreach ($required_fields as $field)
		{
			if (!isset($data[$field]))
			{
				throw new \Exception($this->user->lang('EXCEPTION_FIELD_MISSING', $field));
			}
		}

		// $sql = 'UPDATE ' . USERS_TABLE . "
			// SET user_reputation = 0,
				// user_reputation_auc_buyer = 0,
				// user_reputation_auc_seller = 0
			// WHERE user_id = {$user_id}";
		// $this->db->sql_query($sql);
		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_reputation = 0
			WHERE user_id = {$user_id}";
		$this->db->sql_query($sql);

		if (sizeof($post_ids))
		{
			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET post_reputation = 0
				WHERE ' . $this->db->sql_in_set('post_id', $post_ids, false, true);
			$this->db->sql_query($sql);
		}

		$sql = 'DELETE FROM ' . $this->reputations_table . "
			WHERE user_id_to = {$user_id}";
		$this->db->sql_query($sql);

		$sql = 'DELETE FROM ' . NOTIFICATIONS_TABLE . '
			WHERE ' . $this->db->sql_in_set('notification_type_id', [
				$this->notification_manager->get_notification_type_id('pico.reputation.notification.type.rate_user_negative'),
				$this->notification_manager->get_notification_type_id('pico.reputation.notification.type.rate_user_positive'),
				$this->notification_manager->get_notification_type_id('pico.reputation.notification.type.rate_post_negative'),
				$this->notification_manager->get_notification_type_id('pico.reputation.notification.type.rate_post_positive'),
			]) . '
			AND user_id = ' . $user_id;
		$this->db->sql_query($sql);

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_REPUTATION_CLEARED', false, [
			'user_id_to'	=> (isset($data['username_to'])) ? $data['username_to'] : $data['user_id_to'],
		]);
	}
}
