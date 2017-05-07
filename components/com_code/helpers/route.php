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
 * Code Component Route Helper.
 */
abstract class CodeHelperRoute
{
	/**
	 * Get the issue route.
	 *
	 * @param   integer $id The ID of the issue.
	 *
	 * @return  string  The issue route.
	 */
	public static function getIssueRoute($id)
	{
		return 'index.php?option=com_code&view=issue&issue_id=' . $id;
	}

	/**
	 * Get the tracker route.
	 *
	 * @param   integer $id The ID of the tracker.
	 *
	 * @return  string  The tracker route.
	 */
	public static function getTrackerRoute($id)
	{
		return 'index.php?option=com_code&view=tracker&tracker_id=' . $id;
	}
}
