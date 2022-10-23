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

class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/**
	* Constructor
	*
	* @param \phpbb\config\config       $config     Config object
	* @param \phpbb\template\template   $template   Template object
	* @return \pico\reputation\event\main_listener
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\template\template $template)
	{
		$this->config = $config;
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
			'core.common'		=> 'reputation_status',
			'core.user_setup'	=> 'load_language_on_setup',
		];
	}

	/**
	* Check if Reputation System is enabled or disabled
	*
	* @return null
	* @access public
	*/
	public function reputation_status()
	{
		$this->template->assign_vars([
			'S_REPUTATION'		=> $this->config['rs_enable'],
			'RS_SHOW_ZERO_REP'	=> $this->config['rs_show_zero_rep'] === '' ? 1 : $this->config['rs_show_zero_rep'],
		]);
	}

	/**
	* Load common reputation language files during user setup
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function load_language_on_setup($event)
	{
		$event['lang_set_ext'] = array_merge($event['lang_set_ext'], [[
			'ext_name'		=> 'pico/reputation',
			'lang_set'		=> 'reputation_common',
		]]);

		$router = $GLOBALS['phpbb_container']->get('routing.helper');

		$this->template->assign_vars([
			'RS_URL_LASTVOTES'		=> $router->route('reputation_lastvotes_controller'),
			// 'RS_URL_LASTVOTES_AUC'	=> $router->route('reputation_lastvotes_controller', ['auc' => true]),
		]);
	}
}
