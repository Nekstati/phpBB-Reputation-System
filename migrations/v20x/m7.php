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

class m7 extends \phpbb\db\migration\container_aware_migration
{
	// public function effectively_installed()
	// {
	// }

	static public function depends_on()
	{
		return ['\pico\reputation\migrations\v10x\m2_initial_data'];
	}

	public function update_data()
	{
		return [
			['config.add', ['rs_content_widget_type', '0']],
			['config.add', ['rs_miniprofile_widget_type', '2']],
			['config.add', ['rs_users_to_exclude', '']],
			['config.add', ['rs_show_zero_rep', '1']],
			['config.remove', ['rs_power_explain']],
			['config.update', ['rs_min_point', min($this->config['rs_min_point'], 0)]], // Now only negative or zero value is allowed
			['config.update', ['rs_post_rating', $this->config['rs_post_rating'] == 1 ? 2 : 0]], // Now 1 means "First post only"
			['config.update', ['rs_user_rating_gap', $this->config['rs_user_rating_gap'] * 24]], // Now in hours
			['custom', [[&$this, 'remove_old_notification_type']]],
		];
	}

	public function remove_old_notification_type()
	{
		$this->container->get('notification_manager')->purge_notifications('pico.reputation.notification.type.rate_user');
		// Replaced by 'rate_user_positive' & 'rate_user_negative'
	}
}
