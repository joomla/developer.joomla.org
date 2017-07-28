<?php
/**
 * Joomla! Stat Charts
 *
 * @copyright  Copyright (C) 2015 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

defined('_JEXEC') or die;

/**
 * Helper class for the Joomla! charts module
 *
 * @since  1.0
 */
class JoomlaStatChartsHelper
{
	/**
	 * Array containing the colors to use in charts
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $colors = [
		1 => '#5aa426',
		2 => '#89c764',
		3 => '#fc8f30',
		4 => '#fec34d',
		5 => '#e42626',
		6 => '#f27042',
		7 => '#0d6dab',
		8 => '#75bee9',
		9 => '#25304f',
		10 => '#4076a2',
		11 => '#2383c6',
		12 => '#91c5ea',
		13 => '#433e42',
		14 => '#999999',
		15 => '#c3c3c3',
		16 => '#ececec',
	];

	/**
	 * Array containing the keys for the used colors for this instance
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $usedColors = [];

	/**
	 * Retrieves a color for the chart
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getColor()
	{
		// First, check if all the colors have been used
		if (count($this->colors) === count($this->usedColors))
		{
			$this->usedColors = [];
		}

		$found = false;

		while (!$found)
		{
			$key = rand(1, count($this->colors));

			if (!array_key_exists($key, $this->usedColors))
			{
				// Get the color to return
				$color = $this->colors[$key];

				// Breaks the loop
				$found = true;

				// Flags the color as used
				$this->usedColors[$key] = true;
			}
		}

		return $color;
	}
}
