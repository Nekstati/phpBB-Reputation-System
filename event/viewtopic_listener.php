<?php
/**
*
* Reputation System
*
* @copyright (c) 2014 Lukasz Kaczynski
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace pico\reputation\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class viewtopic_listener implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \pico\reputation\core\reputation_helper */
	protected $reputation_helper;

	/** @var string The table we use to store reputations */
	protected $reputations_table;

	protected $in_auction;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth                           $auth               Auth object
	* @param \phpbb\config\config                       $config             Config object
	* @param \phpbb\controller\helper                   $helper             Controller helper object
	* @param \phpbb\template\template                   $template           Template object
	* @param \phpbb\user                                $user               User object
	* @param \pico\reputation\core\reputation_helper    $reputation_helper  Reputation helper object
	* @param string                                     $reputations_table  Name of the table used to store reputations data
	* @return \pico\reputation\event\viewtopic_listener
	* @access public
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, \pico\reputation\core\reputation_helper $reputation_helper,  $reputations_table)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->reputation_helper = $reputation_helper;
		$this->reputations_table = $reputations_table;

		$this->db = $GLOBALS['db'];
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return [
			'core.viewtopic_assign_template_vars_before'	=> 'assign_reputation',
			'core.viewtopic_get_post_data'					=> 'modify_sql_array',
			'core.viewtopic_post_rowset_data'				=> 'post_rowset_reputation_data',
			'core.viewtopic_cache_guest_data'				=> 'cache_reputation_data',
			'core.viewtopic_cache_user_data'				=> 'cache_reputation_data',
			'core.viewtopic_modify_post_row'				=> 'post_row_reputation',
		];
	}

	/**
	* Add global template var for reputation in forums
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function assign_reputation($event)
	{
		if ($this->config['rs_enable'])
		{
			$topic_data = $event['topic_data'];

			// $auc_forumlist = (isset($this->config['auc_forumlist'])) ? json_decode($this->config['auc_forumlist']) : [];
			// $this->in_auction = in_array($event['forum_id'], $auc_forumlist);
			$this->in_auction = false;

			// Post rating is not allowed in the global announcements
			// because there is no option to set proper permissions for such topics
			$this->template->assign_vars([
				'S_FORUM_REPUTATION'			=> ($topic_data['reputation_enabled'] && $this->config['rs_post_rating'] && ($topic_data['topic_type'] != POST_GLOBAL)) ? true : false,

				'RS_CONTENT_WIDGET_TYPE'		=> $this->config['rs_content_widget_type'],
				'RS_MINIPROFILE_WIDGET_TYPE'	=> $this->config['rs_miniprofile_widget_type'],

				'RS_AUC'						=> $this->in_auction,
				// 'RS_AUC_MINIPROFILE_DOUBLE_REP'	=> $this->config['rs_auc_miniprofile_double_rep'],
			]);
		}
	}

	/**
	* Modify sql array by adding reputations table
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function modify_sql_array($event)
	{
		if ($this->config['rs_enable'] && $this->config['rs_post_rating'])
		{
			$rep_mgr = $GLOBALS['phpbb_container']->get('pico.reputation.manager');
			// $post_type_ids = [$rep_mgr->get_reputation_type_id('post'), $rep_mgr->get_reputation_type_id('auc_post_buyer'), $rep_mgr->get_reputation_type_id('auc_post_seller')];
			$post_type_ids = [$rep_mgr->get_reputation_type_id('post')];

			$sql_ary = $event['sql_ary'];

			$sql_ary['LEFT_JOIN'][] = [
				'FROM'	=> [$this->reputations_table => 'r'],
				'ON'	=> 'r.reputation_item_id = p.post_id
					AND ' . $this->db->sql_in_set('r.reputation_type_id', $post_type_ids) . '
					AND r.user_id_from = ' . $this->user->data['user_id'],
			];
			$sql_ary['SELECT'] .= ', r.reputation_id, r.reputation_points';

			$event['sql_ary'] = $sql_ary;
		}
	}

	/**
	* Add reputation data to rowset
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function post_rowset_reputation_data($event)
	{
		if ($this->config['rs_enable'] && $this->config['rs_post_rating'])
		{
			$event['rowset_data'] += [
				'post_reputation'	=> $event['row']['post_reputation'],
				'user_voted'		=> $event['row']['reputation_id'],
				'reputation_points'	=> $event['row']['reputation_points'],
			];
		}
	}

	/**
	* Add guest user's and user's data to display their reputation
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function cache_reputation_data($event)
	{
		if ($this->config['rs_enable'] && $this->config['rs_post_rating'])
		{
			$event['user_cache_data'] += [
				'user_reputation'				=> $event['row']['user_reputation'],
				// 'user_reputation_auc_buyer'		=> $event['row']['user_reputation_auc_buyer'],
				// 'user_reputation_auc_seller'	=> $event['row']['user_reputation_auc_seller'],
			];
		}
	}

	/**
	* Add post row data for displaying reputation
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function post_row_reputation($event)
	{
		if (!$this->config['rs_enable'])
		{
			return;
		}

		if ($this->config['rs_post_rating'] == 2 || ($this->config['rs_post_rating'] == 1 && $event['post_row']['S_FIRST_POST']))
		{
			$row = $event['row'];
			$poster = $event['user_poster_data'];
			$post_id = $row['post_id'];
			$poster_id = $event['poster_id'];

			if ($this->user->data['user_id'] == $poster_id)
			{
				$post_vote_class = 'own';
			}
			else
			{
				$post_vote_class = $row['user_voted'] ? (($row['reputation_points'] > 0) ? 'rated_good' : 'rated_bad') : '';
			}

			$poster_rep = ($this->in_auction) ? ($poster['user_reputation_auc_buyer'] + $poster['user_reputation_auc_seller']) : $poster['user_reputation'];
			$poster_rep_common = $poster['user_reputation'];
			$auc_param = ($this->in_auction) ? ['auc' => true] : [];

			$event['post_row'] += [
				'S_VIEW_REPUTATION'			=> ($this->auth->acl_get('u_rs_view')) ? true : false,
				'S_RATE_POST'				=> ($this->auth->acl_get('f_rs_rate', $row['forum_id']) && $this->auth->acl_get('u_rs_rate_post') && $poster_id != ANONYMOUS) ? true : false,
				'S_RATE_POST_NEGATIVE'		=> ($this->auth->acl_get('f_rs_rate_negative', $row['forum_id']) && $this->config['rs_negative_point']) ? true : false,

				'U_RATE_POST_POSITIVE'		=> $this->helper->route('reputation_post_rating_controller', ['mode' => 'positive', 'post_id' => $post_id] + $auc_param),
				'U_RATE_POST_NEGATIVE'		=> $this->helper->route('reputation_post_rating_controller', ['mode' => 'negative', 'post_id' => $post_id] + $auc_param),
				'U_VIEW_POST_REPUTATION'	=> $this->helper->route('reputation_post_details_controller', ['post_id' => $post_id] + $auc_param),
				'POST_REPUTATION'			=> $this->format_number($row['post_reputation']),
				'POST_REPUTATION_CLASS'		=> $this->reputation_helper->reputation_class($row['post_reputation']),
				'POST_VOTE_CLASS'			=> $post_vote_class,

				'U_VIEW_USER_REPUTATION'	=> $this->helper->route('reputation_user_details_controller', ['uid' => $poster_id] + $auc_param),
				'USER_REPUTATION'			=> $this->format_number($poster_rep),
				'USER_REPUTATION_CLASS'		=> $this->reputation_helper->reputation_class($poster_rep),

				'U_VIEW_USER_REPUTATION_COMMON'		=> $this->helper->route('reputation_user_details_controller', ['uid' => $poster_id]),
				'USER_REPUTATION_COMMON'			=> $this->format_number($poster['user_reputation']),
				'USER_REPUTATION_CLASS_COMMON'		=> $this->reputation_helper->reputation_class($poster['user_reputation']),
			];
		}
	}

	private function format_number($number)
	{
		return ($this->config['rs_negative_point'] && $number > 0 ? '+' : '') . $number;
	}
}
