<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Trackers Model for Joomla Code
 */
class CodeModelTrackers extends JModelLegacy
{
	public function getItems()
	{
		// Initialize variables.
		$items = array();

		// Get the list of active branches.
		$this->_db->setQuery(
			'SELECT a.*' .
			' FROM #__code_trackers AS a' .
//			' WHERE a.published = 1' .
			' ORDER BY a.title ASC'
		);
		$items = $this->_db->loadObjectList();

		if ($this->_db->getErrorNum())
		{
			JError::raiseError(500, 'Unable to access resource.');
		}

		return $items;
	}

	public function save($data)
	{
		$table = $this->getTable('Tracker', 'CodeTable');

		return $table->save($data);
	}
}
