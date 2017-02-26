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
		// Create the link
		$link = 'index.php?option=com_code&view=issue&issue_id=' . $id;

		if ($item = self::findItem())
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
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
		// Create the link
		$link = 'index.php?option=com_code&view=tracker&tracker_id=' . $id;

		if ($item = self::findItem())
		{
			$link .= '&Itemid=' . $item;
		}

		return $link;
	}

	/**
	 * Find a menu item ID.
	 *
	 * @return  mixed  The ID found or null otherwise.
	 */
	private static function findItem()
	{
		static $itemId;

		if ($itemId)
		{
			return $itemId;
		}

		$menus = JFactory::getApplication()->getMenu('site');
		$items = $menus->getItems(['component_id'], [JComponentHelper::getComponent('com_code')->id]);

		// There will only be one com_code menu item set up
		if (count($items))
		{
			return $itemId = $items[0]->id;
		}

		// Check if the active menuitem matches the requested language
		$active = $menus->getActive();

		if ($active
			&& $active->component == 'com_code'
			&& ($language == '*' || in_array($active->language, ['*', $language]) || !JLanguageMultilang::isEnabled())
		)
		{
			return $itemId = $active->id;
		}

		// If not found, return language specific home link
		$default = $menus->getDefault($language);

		return $itemId = !empty($default->id) ? $default->id : null;
	}
}
