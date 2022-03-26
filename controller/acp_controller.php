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

class acp_controller implements acp_interface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \pico\reputation\core\reputation_manager */
	protected $reputation_manager;

	/** @var string The table we use to store our reputations */
	protected $reputations_table;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	/** @var string Admin root path */
	protected $phpbb_admin_path;

	/** @var string phpEx */
	protected $php_ext;

	/** string Custom form action */
	protected $u_action;

	/** array New config objects */
	protected $new_config;

	/**
	* Constructor
	*
	* @param \phpbb\config\config $config				Config object
	* @param \phpbb\db\driver\driver $db				Database object
	* @param \phpbb\request\request $request			Request object
	* @param \phpbb\template\template $template			Template object
	* @param \phpbb\user $user							User object
	* @param \pico\reputation\core\reputation_manager	Reputation manager object
	* @param string $reputations_table					Name of the table used to store reputations data
	* @param string $phpbb_root_path					phpBB Root path
	* @param string $relative_admin_path				Relative admin root path
	* @param string $php_ext							PHP Extension
	* @return \pico\reputation\controller\acp_controller
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \pico\reputation\core\reputation_manager $reputation_manager, $reputations_table, $phpbb_root_path, $relative_admin_path, $php_ext)
	{
		$this->config = $config;
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->reputation_manager = $reputation_manager;
		$this->reputations_table = $reputations_table;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpbb_admin_path = $this->phpbb_root_path . $relative_admin_path;
		$this->php_ext = $php_ext;
	}

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}

	/**
	* Display reputation overview
	*
	* @return null
	* @access public
	*/
	public function display_overview()
	{
		add_form_key('overview');

		$errors = [];

		$action = $this->request->variable('action', '');

		if (!confirm_box(true))
		{
			$confirm = false;

			switch ($action)
			{
				case 'sync':
					$confirm = true;
					$confirm_lang = 'RS_SYNC_REPUTATION_CONFIRM';
				break;

				case 'truncate':
					$confirm = true;
					$confirm_lang = 'RS_TRUNCATE_CONFIRM';
				break;
			}

			if ($confirm)
			{
				confirm_box(false, $this->user->lang($confirm_lang), build_hidden_fields(['action'	=> $action]));
			}
		}
		else
		{
			switch ($action)
			{
				case 'sync':
					$this->config->set('rs_sync_step', 1, true);

					// Get sync module ID
					$sql = 'SELECT module_id
						FROM ' . MODULES_TABLE . "
						WHERE module_basename LIKE '%reputation%'
							AND module_mode = 'sync'";
					$result = $this->db->sql_query($sql);
					$sync_module_id = (int) $this->db->sql_fetchfield('module_id');
					$this->db->sql_freeresult($result);

					// Redirect to hidden sync module
					redirect(append_sid("{$this->phpbb_admin_path}index.{$this->php_ext}", "i={$sync_module_id}&amp;mode=sync"));
				break;

				case 'truncate':
					$this->db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_reputation = 0');
					$this->db->sql_query('UPDATE ' . POSTS_TABLE . ' SET post_reputation = 0');
					$this->db->sql_query('TRUNCATE ' . $this->reputations_table);

					add_log('admin', 'LOG_REPUTATION_TRUNCATE');

					if ($this->request->is_ajax())
					{
						trigger_error('RS_TRUNCATE_DONE');
					}
				break;
			}
		}

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('overview'))
			{
				$errors[] = $this->user->lang('FORM_INVALID');
			}

			if (empty($errors))
			{
				$this->config->set('rs_enable', $this->request->variable('reputation_enable', 0));
			}

			add_log('admin', 'REPUTATION_SETTINGS_CHANGED');
			meta_refresh(2, $this->u_action);
			trigger_error($this->user->lang('REPUTATION_SETTINGS_CHANGED') . adm_back_link($this->u_action));
		}

		$this->template->assign_vars([
			'S_ERROR'		=> (sizeof($errors)) ? true : false,
			'ERROR_MSG'		=> implode('<br />', $errors),
			'S_REPUTATION_ENABLED'	=> $this->config['rs_enable'] ? true : false,
			'S_FOUNDER'				=> ($this->user->data['user_type'] == USER_FOUNDER) ? true : false,
			'U_ACTION'		=> $this->u_action
		]);
	}

	/**
	* Manage the options a user can configure for this extension
	*
	* @return null
	* @access public
	*/
	public function manage_options()
	{
		add_form_key('manage_settings');
		$this->new_config = $this->config;
		$method = new setting_methods($this->user, $this->new_config);

		$display_vars = [
			'legend1'				=> ['lang' => 'ACP_RS_MAIN'],
			'rs_negative_point'		=> ['lang' => 'RS_NEGATIVE_POINT', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => false],
			'rs_min_rep_negative'	=> ['lang' => 'RS_MIN_REP_NEGATIVE', 'validate' => 'int', 'type' => 'number', 'explain' => true],
			'rs_warning'			=> ['lang' => 'RS_WARNING', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true],
			'rs_max_power_warning'	=> ['lang' => 'RS_WARNING_MAX_POWER', 'validate' => 'int:1', 'type' => 'number:1', 'explain' => true],
			'rs_min_point'			=> ['lang' => 'RS_MIN_POINT', 'validate' => 'int::0', 'type' => 'number::0', 'explain' => true],
			'rs_max_point'			=> ['lang' => 'RS_MAX_POINT', 'validate' => 'int:0', 'type' => 'number:0', 'explain' => true],
			'rs_prevent_perc'		=> ['lang' => 'RS_PREVENT_OVERRATING', 'validate' => 'int:0:99', 'type' => 'false', 'method' => 'false', 'explain' => false],
			'rs_prevent_num'		=> ['lang' => 'RS_PREVENT_OVERRATING', 'validate' => 'int:0', 'type' => 'custom:0:99', 'function' => [$method, 'overrating'], 'explain' => true, 'append' => ' %'],
			'rs_users_to_exclude'	=> ['lang' => 'RS_USERS_TO_EXCLUDE', 'validate' => 'string', 'type' => 'text:0:255', 'explain' => true],
			'rs_instant_vote'		=> ['lang' => 'RS_INSTANT_VOTE', 'validate' => 'none', 'type' => 'custom', 'function' => [$method, 'instant_vote'], 'explain' => true],

			'legend2'				=> ['lang' => 'ACP_RS_DISPLAY'],
			'rs_per_page'			=> ['lang' => 'RS_PER_PAGE', 'validate' => 'int:1', 'type' => 'number:1', 'explain' => false],
			'rs_display_avatar'		=> ['lang' => 'RS_DISPLAY_AVATAR', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true],
			'rs_point_type'			=> ['lang' => 'RS_POINT_TYPE', 'validate' => 'bool', 'type' => 'custom', 'function' => [$method, 'point_type'], 'explain' => true],
			'rs_content_widget_type'		=> ['lang' => 'RS_CONTENT_WIDGET_TYPE', 'validate' => 'int:0:2', 'type' => 'custom', 'function' => [$method, 'rs_content_widget_type'], 'explain' => false],
			'rs_miniprofile_widget_type'	=> ['lang' => 'RS_MINIPROFILE_WIDGET_TYPE', 'validate' => 'int:0:2', 'type' => 'custom', 'function' => [$method, 'rs_miniprofile_widget_type'], 'explain' => true],
			// 'rs_auc_miniprofile_double_rep'	=> ['lang' => 'RS_AUC_MINIPROFILE_DOUBLE_REP', 'validate' => 'int:0:1', 'type' => 'custom', 'function' => [$method, 'rs_auc_miniprofile_double_rep'], 'explain' => true],

			'legend3'				=> ['lang' => 'ACP_RS_POSTS_RATING'],
			'rs_post_rating'		=> ['lang' => 'RS_POST_RATING', 'validate' => 'int:0:2', 'type' => 'custom', 'function' => [$method, 'post_rating'], 'explain' => true],
			'rs_anti_time'			=> ['lang' => 'RS_ANTISPAM', 'validate' => 'int:0:180', 'type' => false, 'method' => false, 'explain' => false],
			'rs_anti_post'			=> ['lang' => 'RS_ANTISPAM', 'validate' => 'int:0', 'type' => 'custom:0:180', 'function' => [$method, 'antispam'], 'explain' => true],
			'rs_anti_method'		=> ['lang' => 'RS_ANTISPAM_METHOD', 'validate' => 'bool', 'type' => 'custom', 'function' => [$method, 'antimethod'], 'explain' => true],

			'legend4'				=> ['lang' => 'ACP_RS_USERS_RATING'],
			'rs_user_rating'		=> ['lang' => 'RS_USER_RATING', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => false],
			'rs_user_rating_gap'	=> ['lang' => 'RS_USER_RATING_GAP', 'validate' => 'int:0', 'type' => 'number:0', 'explain' => true, 'append' => ' ' . $this->user->lang['HOURS']],

			'legend5'				=> ['lang' => 'ACP_RS_COMMENT'],
			'rs_enable_comment'		=> ['lang' => 'RS_ENABLE_COMMENT', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true],
			'rs_force_comment'		=> ['lang' => 'RS_FORCE_COMMENT', 'validate' => 'int:0:3', 'type' => 'custom', 'function' => [$method, 'select_comment'], 'explain' => false],
			'rs_comment_max_chars'	=> ['lang' => 'RS_COMMEN_LENGTH', 'validate' => 'int:0', 'type' => 'number:0', 'explain' => true],

			'legend6'				=> ['lang' => 'ACP_RS_POWER'],
			'rs_enable_power'		=> ['lang' => 'RS_ENABLE_POWER', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true],
			'rs_power_renewal'		=> ['lang' => 'RS_POWER_RENEWAL', 'validate' => 'int:0', 'type' => 'number:0', 'explain' => true, 'append' => ' ' . $this->user->lang['HOURS']],
			'rs_min_power'			=> ['lang' => 'RS_MIN_POWER', 'validate' => 'int:0:10', 'type' => 'number:0:10', 'explain' => true],
			'rs_max_power'			=> ['lang' => 'RS_MAX_POWER', 'validate' => 'int:1:20', 'type' => 'number:1:20', 'explain' => true],
			// 'rs_max_power_auc'		=> ['lang' => 'RS_MAX_POWER_AUC', 'validate' => 'int:1:20', 'type' => 'number:1:20', 'explain' => false],
			// 'rs_power_explain'		=> ['lang' => 'RS_POWER_EXPLAIN', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true],
			'rs_total_posts'		=> ['lang' => 'RS_TOTAL_POSTS', 'validate' => 'int:0', 'type' => 'number:0', 'explain' => true],
			'rs_membership_days'	=> ['lang' => 'RS_MEMBERSHIP_DAYS', 'validate' => 'int:0', 'type' => 'number:0', 'explain' => true],
			'rs_power_rep_point'	=> ['lang' => 'RS_POWER_REP_POINT', 'validate' => 'int:0', 'type' => 'number:0', 'explain' => true],
			'rs_power_lose_warn'	=> ['lang' => 'RS_LOSE_POWER_WARN', 'validate' => 'int:0', 'type' => 'number:0', 'explain' => true],

			'legend7'				=> ['lang' => 'ACP_RS_TOPLIST'],
			'rs_enable_toplist'		=> ['lang' => 'RS_ENABLE_TOPLIST', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true],
			'rs_toplist_direction'	=> ['lang' => 'RS_TOPLIST_DIRECTION', 'validate' => 'bool', 'type' => 'custom', 'function' => [$method, 'toplist_direction'], 'explain' => false],
			'rs_toplist_num'		=> ['lang' => 'RS_TOPLIST_NUM', 'validate' => 'int:1', 'type' => 'number:1', 'explain' => false],
		];

		$cfg_array = $this->request->is_set_post('config') ? utf8_normalize_nfc(request_var('config', ['' => ''], true)) : $this->new_config;
		$errors = [];

		validate_config_vars($display_vars, $cfg_array, $errors);

		$submit = ($this->request->is_set_post('submit') || $this->request->is_set_post('enable_forums_reputation')) ? true : false;

		if ($submit && !check_form_key('manage_settings'))
		{
			$errors[] = $this->user->lang('FORM_INVALID');
		}

		if ($submit && $cfg_array['rs_content_widget_type'] + $cfg_array['rs_miniprofile_widget_type'] > 3)
		{
			$errors[] = $this->user->lang('RS_WIDGET_TYPE_ERROR');
		}

		$cfg_array['rs_users_to_exclude'] = str_replace(' ', '', $cfg_array['rs_users_to_exclude']);
		if ($submit && preg_match('/[^0-9^,]/', $cfg_array['rs_users_to_exclude']))
		{
			$errors[] = $this->user->lang('RS_USERS_TO_EXCLUDE_ERROR');
		}

		if (sizeof($errors))
		{
			$submit = false;
		}

		foreach ($display_vars as $config_name => $null)
		{
			if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
			{
				continue;
			}

			if ($config_name == 'rs_instant_vote')
			{
				continue;
			}

			$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];

			if ($submit)
			{
				$this->config->set($config_name, $config_value);

				if ($config_name == 'rs_post_rating' && $this->request->is_set_post('enable_forums_reputation'))
				{
					$this->enable_forums_reputation();
				}
			}
		}

		if ($submit)
		{
			add_log('admin', 'REPUTATION_SETTINGS_CHANGED');
			meta_refresh(2, $this->u_action);
			trigger_error($this->user->lang('REPUTATION_SETTINGS_CHANGED') . adm_back_link($this->u_action));
		}

		$this->template->assign_vars([
			'S_ERROR'				=> (sizeof($errors)) ? true : false,
			'ERROR_MSG'				=> implode('<br />', $errors),
			'U_ACTION'				=> $this->u_action
		]);

		foreach ($display_vars as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$this->template->assign_block_vars('options', [
					'S_LEGEND'		=> true,
					'LEGEND'		=> $this->user->lang($vars['lang']),
				]);

				continue;
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain']))
			{
				$l_explain = (isset($this->user->lang[$vars['lang_explain']])) ? $this->user->lang($vars['lang_explain']) : $vars['lang_explain'];
			}
			else if ($vars['explain'])
			{
				$l_explain = (isset($this->user->lang[$vars['lang'] . '_EXPLAIN'])) ? $this->user->lang($vars['lang'] . '_EXPLAIN') : '';
			}

			$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$this->template->assign_block_vars('options', [
				'KEY'			=> $config_key,
				'TITLE'			=> (isset($this->user->lang[$vars['lang']])) ? $this->user->lang($vars['lang']) : $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content,
			]);

			unset($display_vars['vars'][$config_key]);
		}
	}

	/**
	* Enable reputation in all forums
	*
	* @return null
	* @access private
	*/
	private function enable_forums_reputation()
	{
		$sql = 'UPDATE ' . FORUMS_TABLE . "
			SET reputation_enabled = 1";
		$this->db->sql_query($sql);
	}

	/**
	* Rate user
	*
	* @return null
	* @access public
	*/
	public function rate_user()
	{
		add_form_key('rate');

		// $this->user->add_lang_ext('pico/reputation', 'reputation_common');

		$submit = $this->request->is_set_post('submit');
		$username = $this->request->variable('username', '', true);
		$points = $this->request->variable('points', '');
		$comment = $this->request->variable('comment', '', true);
		$errors = [];

		if ($submit)
		{
			if (!check_form_key('rate'))
			{
				$errors[] = $this->user->lang('FORM_INVALID');
			}

			$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . "
				WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";
			$result = $this->db->sql_query($sql);
			$user_id_to = (int) $this->db->sql_fetchfield('user_id');
			$this->db->sql_freeresult($result);

			if (!$user_id_to)
			{
				$errors[] = $this->user->lang('NO_USER');
			}

			if (!is_numeric($points))
			{
				$errors[] = $this->user->lang('POINTS_INVALID');
			}
		}

		if ($submit && empty($errors))
		{
			$data = [
				'user_id_from'			=> $this->user->data['user_id'],
				'user_id_to'			=> $user_id_to,
				'reputation_type'		=> 'user',
				'reputation_item_id'	=> $user_id_to,
				'reputation_points'		=> $points,
				'reputation_comment'	=> $comment,
			];

			try
			{
				$this->reputation_manager->store_reputation($data);
				meta_refresh(2, $this->u_action);
				trigger_error($this->user->lang('RS_VOTE_SAVED'). adm_back_link($this->u_action));
			}
			catch (\Exception $e)
			{
				$errors[] = $e->getMessage();
			}
		}

		$this->template->assign_vars([
			'S_ERROR'				=> (sizeof($errors)) ? true : false,
			'ERROR_MSG'				=> implode('<br />', $errors),
			'U_ACTION'			=> $this->u_action,
			'U_FIND_USERNAME'	=> append_sid("{$this->phpbb_root_path}memberlist.{$this->php_ext}", 'mode=searchuser&amp;form=rate&amp;field=username&amp;select_single=true'),
			'RS_USERNAME'	=> $username,
			'RS_POINTS'		=> $points,
			'RS_COMMENT'	=> $comment,
		]);
	}
}

/**
* Setting methods class 
*
* This class contains all additional methods which are called
*	and used to display and set reputation settings
*/
class setting_methods
{
	/** @var \phpbb\user */
	protected $user;

	/** array New config objects */
	protected $new_config;

	/**
	* Constructor
	*
	* @param \phpbb\user $user				User object
	* @param $new_config					Config object
	* @return class setting_methods
	* @access public
	*/
	public function __construct(\phpbb\user $user, $new_config)
	{
		$this->user = $user;
		$this->new_config = $new_config;
	}

	/**
	* Enable/disable post rating
	*/
	function post_rating($value, $key)
	{
		$radio_ary = [2 => 'YES', 1 => 'RS_POST_RATING_FIRST_ONLY', 0 => 'NO'];

		$option = h_radio('config[rs_post_rating]', $radio_ary, $value, 'post_rating', $key);
		$option .= '<br /><input class="button2" type="submit" id="enable_forums_reputation" name="enable_forums_reputation" value="' . $this->user->lang('RS_ALLOW_REPUTATION_BUTTON') . '" style="margin-top: 8px" />';

		return $option;
	}

	/**
	* Select reputation points display method
	*/
	function point_type($value, $key)
	{
		$radio_ary = [
			0	=> 'RS_POINT_VALUE',
			1	=> 'RS_POINT_IMG',
		];

		$radio_text = h_radio('config[rs_point_type]', $radio_ary, $value, 'rs_point_type', $key);

		return $radio_text;
	}

	/**
	* Select reputation overrating method
	*/
	function overrating($value, $key = '')
	{
		return $this->user->lang('RS_PREVENT_NUM') . '&nbsp;<input id="' . $key . '" type="number" min="0" name="config[rs_prevent_num]" value="' . $value . '" /> ' . $this->user->lang('RS_PREVENT_PERC') . '&nbsp;<input type="number" min="0" max="99" name="config[rs_prevent_perc]" value="' . $this->new_config['rs_prevent_perc'] . '" />';
	}

	/**
	* Select reputation overrating method
	*/
	function antimethod($value, $key)
	{
		$radio_ary = [
			0	=> 'RS_SAME_USER',
			1	=> 'RS_ALL_USERS',
		];

		$radio_text = h_radio('config[rs_anti_method]', $radio_ary, $value, 'rs_anti_method', $key, '<br />');

		return $radio_text;
	}

	/**
	* Select reputation anti-spam method
	*/
	function antispam($value, $key = '')
	{
		return $this->user->lang('RS_POSTS') . '&nbsp;<input id="' . $key . '" type="number" min="0" name="config[rs_anti_post]" value="' . $value . '" /> ' . $this->user->lang('RS_HOURS') . '&nbsp;<input type="number" min="0" name="config[rs_anti_time]" value="' . $this->new_config['rs_anti_time'] . '" />';
	}

	/**
	* Select comment method
	*/
	function select_comment($value, $key)
	{
		$radio_ary = [
			0	=> 'RS_COMMENT_NO',
			2	=> 'RS_COMMENT_POST',
			3	=> 'RS_COMMENT_USER',
			1	=> 'RS_COMMENT_BOTH',
		];

		$radio_text = h_radio('config[rs_force_comment]', $radio_ary, $value, 'rs_force_comment', $key, '<br />');

		return $radio_text;
	}

	/**
	* Select toplist direction
	*/
	function toplist_direction($value, $key)
	{
		$radio_ary = [
			0	=> 'RS_TL_HORIZONTAL',
			1	=> 'RS_TL_VERTICAL',
		];

		$radio_text = h_radio('config[rs_toplist_direction]', $radio_ary, $value, 'rs_toplist_direction', $key);

		return $radio_text;
	}

	function rs_content_widget_type($value, $key)
	{
		$radio_ary = [
			2	=> 'RS_CONTENT_WIDGET_TYPE_2',
			1	=> 'RS_CONTENT_WIDGET_TYPE_1',
			0	=> 'RS_CONTENT_WIDGET_TYPE_0',
		];

		$radio_text = h_radio('config[rs_content_widget_type]', $radio_ary, $value, 'rs_content_widget_type', $key, '<br />');

		return $radio_text;
	}

	function rs_miniprofile_widget_type($value, $key)
	{
		$radio_ary = [
			2	=> 'RS_MINIPROFILE_WIDGET_TYPE_2',
			1	=> 'RS_MINIPROFILE_WIDGET_TYPE_1',
			0	=> 'RS_MINIPROFILE_WIDGET_TYPE_0',
		];

		$radio_text = h_radio('config[rs_miniprofile_widget_type]', $radio_ary, $value, 'rs_miniprofile_widget_type', $key, '<br />');

		return $radio_text;
	}

	function rs_auc_miniprofile_double_rep($value, $key)
	{
		$radio_ary = [
			0	=> 'RS_AUC_MINIPROFILE_DOUBLE_REP_0',
			1	=> 'RS_AUC_MINIPROFILE_DOUBLE_REP_1',
		];

		$radio_text = h_radio('config[rs_auc_miniprofile_double_rep]', $radio_ary, $value, 'rs_auc_miniprofile_double_rep', $key, '<br />');

		return $radio_text;
	}

	function instant_vote()
	{
		return $this->user->lang('RS_INSTANT_VOTE_TEXT');
	}
}
