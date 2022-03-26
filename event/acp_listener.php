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

class acp_listener implements EventSubscriberInterface
{
	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/**
	* Constructor
	*
	* @param \phpbb\request\request     $request    Request object
	* @param \phpbb\template\template   $template   Template object
	* @return \pico\reputation\event\acp_listener
	* @access public
	*/
	public function __construct(\phpbb\request\request $request, \phpbb\template\template $template)
	{
		$this->request = $request;
		$this->template = $template;
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
			'core.acp_manage_forums_request_data'		=> 'forum_reputation_request',
			'core.acp_manage_forums_initialise_data'	=> 'forum_initialise_reputation',
			'core.acp_manage_forums_display_form'		=> 'forum_display_reputation',
			'core.acp_manage_group_request_data'		=> 'group_request_data',
			'core.acp_manage_group_initialise_data'		=> 'group_initialise_data',
			'core.acp_manage_group_display_form'		=> 'group_display_form',
			'core.permissions'							=> 'add_reputation_permissions',
		];
	}

	/**
	* Add reputation forum request data
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function forum_reputation_request($event)
	{
		$event['forum_data'] += ['reputation_enabled' => $this->request->variable('reputation_enabled', 0)];
	}

	/**
	* Initialise reputation data while creating a new forum
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function forum_initialise_reputation($event)
	{
		if ($event['action'] == 'add')
		{
			$event['forum_data'] += ['reputation_enabled' => true];
		}
	}

	/**
	* Assign reputation data to forum template
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function forum_display_reputation($event)
	{
		$event['template_data'] += ['S_ENABLE_REPUTATION' => $event['forum_data']['reputation_enabled']];
	}

	/**
	* Add reputation group request data
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function group_request_data($event)
	{
		$event['submit_ary'] += ['reputation_power' => $this->request->variable('group_reputation_power', 0)];
		$event['validation_checks'] += ['reputation_power' => ['num', false, 0, 999]];
	}

	/**
	* Add group test variable
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function group_initialise_data($event)
	{
		$event['test_variables'] += ['reputation_power' => 'int'];
	}

	/**
	* Assign reputation data to group template
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function group_display_form($event)
	{
		$group_row = $event['group_row'];

		$this->template->assign_vars([
			'GROUP_REPUTATION_POWER' => (isset($group_row['group_reputation_power'])) ? $group_row['group_reputation_power'] : 0,
		]);
	}

	/**
	* Add reputation permissions
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function add_reputation_permissions($event)
	{
		$event['categories'] += ['reputation' => 'ACL_CAT_REPUTATION'];

		$event['permissions'] += [
			'a_reputation'			=> ['lang' => 'ACL_A_REPUTATION', 'cat' => 'misc'],

			'f_rs_rate'				=> ['lang' => 'ACL_F_RS_RATE', 'cat' => 'reputation'],
			'f_rs_rate_negative'	=> ['lang' => 'ACL_F_RS_RATE_NEGATIVE', 'cat' => 'reputation'],

			'm_rs_moderate'			=> ['lang' => 'ACL_M_RS_MODERATE', 'cat' => 'reputation'],
			'm_rs_rate'				=> ['lang' => 'ACL_M_RS_RATE', 'cat' => 'reputation'],

			'u_rs_rate'				=> ['lang' => 'ACL_U_RS_RATE', 'cat' => 'reputation'],
			'u_rs_rate_negative'	=> ['lang' => 'ACL_U_RS_RATE_NEGATIVE', 'cat' => 'reputation'],
			'u_rs_view'				=> ['lang' => 'ACL_U_RS_VIEW', 'cat' => 'reputation'],
			'u_rs_rate_post'		=> ['lang' => 'ACL_U_RS_RATE_POST', 'cat' => 'reputation'],
			'u_rs_delete'			=> ['lang' => 'ACL_U_RS_DELETE', 'cat' => 'reputation'],
		];
	}
}
