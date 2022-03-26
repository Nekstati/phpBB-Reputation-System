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

class reputation_helper
{
	/**
	* Reputation class 
	*
	* @param $points Rating points
	* @static
	* @access public
	* @return string String value of CSS class for voting placeholder
	*/
	static public function reputation_class($points)
	{
		if ($points > 0) 
		{
			return 'positive' . ($GLOBALS['config']['rs_negative_point'] ? ' signed' : '');
		}
		else if ($points < 0) 
		{
			return 'negative signed';
		}
		else
		{
			return 'neutral';
		}
	}
}
