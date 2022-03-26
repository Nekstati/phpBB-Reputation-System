<?php
/**
*
* Reputation System
*
* @copyright (c) 2014 Lukasz Kaczynski
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace pico\reputation;

class ext extends \phpbb\extension\base
{
	protected $notification_types = [
		'pico.reputation.notification.type.rate_post_positive',
		'pico.reputation.notification.type.rate_post_negative',
		'pico.reputation.notification.type.rate_user_positive',
		'pico.reputation.notification.type.rate_user_negative',
	];

	function enable_step($old_state)
	{
		$is_first_step = ($old_state === false);

		if ($is_first_step)
		{
			$notifier = $this->container->get('notification_manager');
			foreach ($this->notification_types as $type)
			{
				$notifier->enable_notifications($type);
			}
			return 'notifications';
		}

		return parent::enable_step($old_state);
	}

	function disable_step($old_state)
	{
		$is_first_step = ($old_state === false);

		if ($is_first_step)
		{
			$notifier = $this->container->get('notification_manager');
			foreach ($this->notification_types as $type)
			{
				$notifier->disable_notifications($type);
			}
			return 'notifications';
		}

		return parent::disable_step($old_state);
	}

	function purge_step($old_state)
	{
		$is_first_step = ($old_state === false);

		if ($is_first_step)
		{
			$notifier = $this->container->get('notification_manager');
			foreach ($this->notification_types as $type)
			{
				$notifier->purge_notifications($type);
			}
			return 'notifications';
		}

		return parent::purge_step($old_state);
	}
}
