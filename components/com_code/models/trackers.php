<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Trackers Model for Joomla Code
 */
class CodeModelTrackers extends JModelLegacy
{
	/**
	 * Get the list of trackers
	 *
	 * @return  array
	 */
	public function getItems()
	{
		// Get the list of active branches.
		$db = $this->getDbo();

		$db->setQuery(
			$db->getQuery(true)
				->select('a.*')
				->from('#__code_trackers AS a')
				->order('a.title ASC')
		);

		return $db->loadObjectList();
	}
}
