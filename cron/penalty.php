<?php

namespace pico\reputation\cron;

class penalty extends \phpbb\cron\task\base
{
	protected $config;
	protected $db;
	protected $user;
	protected $root_path;
	protected $time_threshold;

	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, $root_path)
	{
		$this->config = $config;
		$this->db = $db;
		$this->user = $user;
		$this->root_path = $root_path;

		$this->time_threshold = time() - $this->config['rs_penalty_days'] * 24 * 3600;
	}

	public function is_runnable()
	{
		return $this->config['rs_penalty_on'] && $this->config['rs_penalty_points'] > 0;
	}

	public function should_run()
	{
		return $this->config['rs_penalty_cron_last_run'] < $this->time_threshold;
	}

	public function run()
	{
		$this->config->set('rs_penalty_cron_last_run', time());

		$groups = json_decode($this->config['rs_penalty_groups']);
		$min_rep = $this->config['rs_min_point'] ?: 0;

		if (!count($groups))
			return;

		$sql = "
			SELECT user_id, username, user_email, user_lastvisit, user_lang, user_reputation
			FROM " . USERS_TABLE . "
			WHERE " . $this->db->sql_in_set('user_type', [USER_NORMAL, USER_FOUNDER]) . "
				AND " . $this->db->sql_in_set('group_id', $groups) . "
				AND user_reputation > $min_rep
			ORDER BY user_id";
		$result = $this->db->sql_query($sql);
		$users = array_column($this->db->sql_fetchrowset($result), null, 'user_id');
		$this->db->sql_freeresult($result);

		if (!count($users))
			return;

		$sql = "
			SELECT session_user_id, MAX(session_time) AS session_time
			FROM " . SESSIONS_TABLE . "
			WHERE session_time < {$this->time_threshold}
				AND " . $this->db->sql_in_set('session_user_id', array_keys($users)) . "
			GROUP BY session_user_id";
		$result = $this->db->sql_query($sql);
		$session_times = array_column($this->db->sql_fetchrowset($result), 'session_time', 'session_user_id');
		$this->db->sql_freeresult($result);

		$points = $this->config['rs_penalty_points'];
		$low_rep_threshold = $min_rep + $points;

		foreach ($users as $id => &$user) {
			$last_visit = $session_times[$id] ?? $user['user_lastvisit'];
			if ($last_visit == 0 || $last_visit >= $this->time_threshold) {
				unset($users[$id]);
				continue;
			}
			$user['rep_diff'] = $user['user_reputation'] < $low_rep_threshold ? $user['user_reputation'] - $min_rep : $points;
		}

		if (!count($users))
			return;

		$sql = "
			UPDATE " . USERS_TABLE . "
			SET user_reputation = (CASE WHEN user_reputation < $low_rep_threshold THEN $min_rep ELSE user_reputation - $points END)
			WHERE " . $this->db->sql_in_set('user_id', array_keys($users));
		$this->db->sql_query($sql);

		$this->email($users);
	}

	private function email($users)
	{
		if (!count($users) || !$this->config['email_enable'])
			return;

		if (!class_exists('messenger'))
			include "{$this->root_path}includes/functions_messenger.php";

		$messenger = new \messenger(false);

		foreach ($users as $user) {
			$messenger->template('@pico_reputation/penalty', $user['user_lang']);
			$messenger->to($user['user_email'], $user['username']);
			$messenger->assign_vars([
				'USERNAME'		=> $user['username'],
				'POINTS'		=> $user['rep_diff'],
				'DAYS'			=> $this->config['rs_penalty_days'],
			]);
			$messenger->send(NOTIFY_EMAIL);
		}

		$messenger->save_queue();
		unset($messenger);
	}
}
