<?php
/**
*
* Reputation System
*
* @copyright (c) 2014 Lukasz Kaczynski
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace pico\reputation\notification;

/**
* Reputation notifications class
* This class handles notifications for Reputation System
*
* @package notifications
*/
class rate_user_negative extends rate_user_positive
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'pico.reputation.notification.type.rate_user_negative';
	}

	/**
	* Language key used to output the text
	*
	* @var string
	*/
	protected $language_key = 'NOTIFICATION_RATE_USER_NEGATIVE';
}
