<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Trackers Model for Joomla Code
 */
class CodeModelTrackers extends BaseDatabaseModel
{
	public function getItems()
	{
		// Initialize variables.
		$items = [];
		$db    = $this->getDbo();

		// Get the list of active branches.
		try
		{
			return $db->setQuery(
				$db->getQuery(true)
					->select('a.*')
					->from('#__code_trackers', 'a')
					->order('a.title ASC')
			)->loadObjectList();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			JError::raiseError(500, 'Unable to access resource.');
		}
	}

	public function save($data)
	{
		/** @var CodeTableTracker $table */
		$table = $this->getTable('Tracker', 'CodeTable');

		return $table->save($data);
	}
}
