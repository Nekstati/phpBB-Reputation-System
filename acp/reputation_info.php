<?php
/**
*
* Reputation System
*
* @copyright (c) 2014 Lukasz Kaczynski
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace pico\reputation\acp;

class reputation_info
{
	function module()
	{
		return [
			'filename'	=> '\pico\reputation\acp\reputation_module',
			'title'		=> 'ACP_REPUTATION_SYSTEM',
			'modes'		=> [
				'overview'		=> ['title' => 'ACP_REPUTATION_OVERVIEW', 'auth' => 'ext_pico/reputation && acl_a_reputation', 'cat' => ['ACP_REPUTATION_SYSTEM']],
				'settings'		=> ['title' => 'ACP_REPUTATION_SETTINGS', 'auth' => 'ext_pico/reputation && acl_a_reputation', 'cat' => ['ACP_REPUTATION_SYSTEM']],
				'rate'			=> ['title' => 'ACP_REPUTATION_RATE',     'auth' => 'ext_pico/reputation && acl_a_reputation', 'cat' => ['ACP_REPUTATION_SYSTEM']],
				'sync'			=> ['title' => 'ACP_REPUTATION_SYNC',     'auth' => 'ext_pico/reputation && acl_a_reputation', 'cat' => ['ACP_REPUTATION_SYSTEM'], 'display' => false],
			],
		];
	}
}
