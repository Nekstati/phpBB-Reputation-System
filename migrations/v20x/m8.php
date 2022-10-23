<?php
/**
*
* Reputation System
*
* @copyright (c) 2014 Lukasz Kaczynski
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace pico\reputation\migrations\v20x;

class m8 extends \phpbb\db\migration\container_aware_migration
{
	// public function effectively_installed()
	// {
	// }

	static public function depends_on()
	{
		return ['\pico\reputation\migrations\v20x\m7'];
	}

	public function update_data()
	{
		return [
			['config.add', ['rs_penalty_on', 0]],
			['config.add', ['rs_penalty_days', 30]],
			['config.add', ['rs_penalty_points', 1]],
			['config.add', ['rs_penalty_groups', '[]']],
			['config.add', ['rs_penalty_cron_last_run', 0, true]],
		];
	}
}
