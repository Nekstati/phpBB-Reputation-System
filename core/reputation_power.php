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

class reputation_power implements reputation_power_interface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var string The table we use to store our reputations */
	protected $reputations_table;

	/** @var array Reputation power explanation */
	private $explanation;

	/**
	* Constructor
	* 
	* @param \phpbb\config\config $config	Config object
	* @param \phpbb\db\driver\driver $db	Database object
	* @param string $reputations_table		Name of the table used to store reputations data
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, $reputation_table)
	{
		$this->config = $config;
		$this->db = $db;
		$this->reputation_table = $reputation_table;
	}

	/**
	* Function returns maximum reputation power of one user
	*
	* @param int $posts User posts
	* @param timestamp $regdate User registration date
	* @param int $reputation User reputation
	* @param int $warnings User warnings
	* @param int $user_group_id User group ID
	* @return int User power reputation
	* @access public
	*/
	public function get($posts, $regdate, $reputation, $warnings, $user_group_id)
	{
		$now = time();
		$user_power = [];

		if ($this->config['rs_total_posts'])
			$user_power['FOR_NUMBER_OF_POSTS'] = intval($posts / $this->config['rs_total_posts']);

		if ($this->config['rs_membership_days'])
			$user_power['FOR_USER_AGE'] = intval(intval(($now - $regdate) / 86400) / $this->config['rs_membership_days']);

		if ($this->config['rs_power_rep_point'])
			$user_power['FOR_REPUTATION'] = intval($reputation / $this->config['rs_power_rep_point']);

		if ($this->config['rs_power_lose_warn'] > 0)
			$user_power['FOR_WARNINGS'] = -$warnings * $this->config['rs_power_lose_warn'];

		if (empty($user_power))
			$user_max_power = $this->config['rs_max_power'];
		else
			$user_max_power = array_sum($user_power) + $this->config['rs_min_power'];

		foreach ($user_power as $k => &$v)
			if ($this->config['rs_negative_point'])
				$v = ($v < 0 || $k == 'FOR_WARNINGS') ? '−' . abs($v) : "+$v";

		if ($this->config['rs_min_power'])
			$user_power['MINIMUM_VOTING_POWER'] = $this->config['rs_min_power'];

		if ($user_max_power < $this->config['rs_min_power'])
			$user_max_power = max($this->config['rs_min_power'], $user_max_power);

		if ($this->config['rs_max_power'])
			$user_power['MAXIMUM_VOTING_POWER'] = $this->config['rs_max_power'];

		if ($user_max_power > $this->config['rs_max_power'])
			$user_max_power = min($this->config['rs_max_power'], $user_max_power);

		$user_power['RESULTING_VOTING_POWER'] = $user_max_power;

		if ($user_group_id)
		{
			$sql = 'SELECT group_reputation_power
				FROM ' . GROUPS_TABLE . "
				WHERE group_id = $user_group_id";
			$result = $this->db->sql_query($sql);
			$group_power = (int) $this->db->sql_fetchfield('group_reputation_power');
			$this->db->sql_freeresult($result);

			if (!empty($group_power))
			{
				$user_power = [];
				$user_max_power = $user_power['GROUP_VOTING_POWER'] = $group_power;
			}
		}

		$this->explanation = $user_power;

		return $user_max_power;
	}

	/**
	* Function returns an array explaining structure of the user reputation power
	*
	* @return array User reputation power with the explanation
	* @access public
	*/
	public function explain()
	{
		return $this->explanation;
	}

	/**
	* Function returns a reputation power used by an user
	*
	* @param $user_id User ID
	* @return int Power used
	* @access public
	*/
	public function used($user_id)
	{
		$time = time();
		$power_used = 0;

		if ($this->config['rs_power_renewal'])
		{
			$renewal_timeout = $time - $this->config['rs_power_renewal'] * 3600;

			$sql = 'SELECT reputation_points
				FROM ' . $this->reputation_table . "
				WHERE user_id_from = {$user_id}
					AND reputation_time > $renewal_timeout";
			$result = $this->db->sql_query($sql);

			while ($vote = $this->db->sql_fetchrow($result))
			{
				// How much power a user spent in a specified period of time
				$power_used += abs($vote['reputation_points']);
			}
			$this->db->sql_freeresult($result);
		}

		return $power_used;
	}
}
