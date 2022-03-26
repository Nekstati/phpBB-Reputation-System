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

class memberlist_listener implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth           $auth       Auth object
	* @param \phpbb\config\config       $config     Config object
	* @param \phpbb\controller\helper   $helper     Controller helper object
	* @return \pico\reputation\event\memberlist_listener
	* @access public
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\controller\helper $helper)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->helper = $helper;
		$this->user = $GLOBALS['user'];
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
			'core.memberlist_prepare_profile_data'	=> 'prepare_user_reputation_data',
		];
	}

	/**
	* Display user reputation on user profile page
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function prepare_user_reputation_data($event)
	{
		if (!$this->config['rs_enable'] || request_var('mode', '') != 'viewprofile')
		{
			return;
		}

		$data = $event['data'];

		if ($this->user->data['user_id'] == $data['user_id'])
		{
			$user_vote_class = 'own';
			// $auc_user_vote_class = 'own';
		}
		else
		{
			$rep_mgr = $GLOBALS['phpbb_container']->get('pico.reputation.manager');
			$rep_table = $GLOBALS['phpbb_container']->getParameter('tables.reputations');

			$user_type_id = $rep_mgr->get_reputation_type_id('user');
			$sql = "SELECT SUM(reputation_points) AS sum
				FROM $rep_table
				WHERE reputation_type_id = $user_type_id
				AND user_id_from = {$this->user->data['user_id']}
				AND user_id_to = {$data['user_id']}";
			$result = $this->db->sql_query($sql);
			$sum = $this->db->sql_fetchfield('sum');
			$this->db->sql_freeresult($result);
			$user_vote_class = $sum ? ($sum > 0 ? 'rated_good' : 'rated_bad') : '';

			// $auc_user_type_ids = [$rep_mgr->get_reputation_type_id('auc_user_buyer'), $rep_mgr->get_reputation_type_id('auc_user_seller')];
			// $sql = "SELECT SUM(reputation_points) AS auc_sum
				// FROM $rep_table
				// WHERE " . $this->db->sql_in_set('reputation_type_id', $auc_user_type_ids) . "
				// AND user_id_from = {$this->user->data['user_id']}
				// AND user_id_to = {$data['user_id']}";
			// $result = $this->db->sql_query($sql);
			// $auc_sum = $this->db->sql_fetchfield('auc_sum');
			// $this->db->sql_freeresult($result);
			// $auc_user_vote_class = $auc_sum ? ($auc_sum > 0 ? 'rated_good' : 'rated_bad') : '';
		}

		// $user_rep_auc = $data['user_reputation_auc_buyer'] + $data['user_reputation_auc_seller'];

		$event['template_data'] += [
			'S_VIEW_REPUTATION'			=> ($this->auth->acl_get('u_rs_view')) ? true : false,
			'S_RATE_USER'				=> ($this->config['rs_user_rating'] && $this->auth->acl_get('u_rs_rate')) ? true : false,
			'S_RATE_USER_NEGATIVE'		=> ($this->config['rs_negative_point'] && $this->auth->acl_get('u_rs_rate_negative')) ? true : false,

			'U_RATE_USER_POSITIVE'		=> $this->helper->route('reputation_user_rating_controller', ['mode' => 'positive', 'uid' => $data['user_id']]),
			'U_RATE_USER_NEGATIVE'		=> $this->helper->route('reputation_user_rating_controller', ['mode' => 'negative', 'uid' => $data['user_id']]),
			'U_VIEW_USER_REPUTATION'	=> $this->helper->route('reputation_user_details_controller', ['uid' => $data['user_id']]),
			'USER_REPUTATION'			=> $this->format_number($data['user_reputation']),
			'USER_REPUTATION_CLASS'		=> $GLOBALS['phpbb_container']->get('pico.reputation.helper')->reputation_class($data['user_reputation']),
			'USER_VOTE_CLASS'			=> $user_vote_class,

			// 'U_RATE_USER_POSITIVE_AUC'		=> $this->helper->route('reputation_user_rating_controller', ['mode' => 'positive', 'uid' => $data['user_id'], 'auc' => true]),
			// 'U_RATE_USER_NEGATIVE_AUC'		=> $this->helper->route('reputation_user_rating_controller', ['mode' => 'negative', 'uid' => $data['user_id'], 'auc' => true]),
			// 'U_VIEW_USER_REPUTATION_AUC'	=> $this->helper->route('reputation_user_details_controller', ['uid' => $data['user_id'], 'auc' => true]),
			// 'USER_REPUTATION_AUC'			=> $this->format_number($user_rep_auc),
			// 'USER_REPUTATION_CLASS_AUC'		=> $GLOBALS['phpbb_container']->get('pico.reputation.helper')->reputation_class($user_rep_auc),
			// 'USER_VOTE_CLASS_AUC'			=> $auc_user_vote_class,

			'RS_USER_ID'				=> $data['user_id'],

			'S_REPUTATION'				=> true,
		];
	}

	private function format_number($number)
	{
		return ($this->config['rs_negative_point'] && $number > 0 ? '+' : '') . $number;
	}
}
