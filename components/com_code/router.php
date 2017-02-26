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
 * Routing class from com_code
 *
 * @since  4.0
 */
class CodeRouter extends JComponentRouterBase
{
	/**
	 * Build the route for the com_code component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   4.0
	 */
	public function build(&$query)
	{
		// Initialize variables.
		$segments = [];

		// We need a menu item.  Either the one specified in the query, or the current active one if none specified
		if (empty($query['Itemid']))
		{
			$menuItem      = $this->menu->getActive();
			$menuItemGiven = false;
		}
		else
		{
			$menuItem      = $this->menu->getItem($query['Itemid']);
			$menuItemGiven = true;
		}

		// Check again
		if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_code')
		{
			$menuItemGiven = false;
			unset($query['Itemid']);
		}

		if (!isset($query['view']))
		{
			// We need to have a view in the query or it is an invalid URL
			return $segments;
		}

		$view = $query['view'];
		unset($query['view']);

		switch ($view)
		{
			case 'issue':
				$segments[] = 'issue-' . $query['issue_id'];
				unset($query['issue_id']);

				break;

			case 'tracker':
				$segments[] = 'tracker-' . $query['tracker_id'];
				unset($query['tracker_id']);

				break;

			default:
				break;
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array &$segments The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   4.0
	 */
	public function parse(&$segments)
	{
		// Initialize variables.
		$vars = [];

		// If no segments exist then we are at the tracker list
		if (empty($segments))
		{
			$vars['view'] = 'trackers';

			return $vars;
		}

		// Get the item from the segment
		$segment = array_shift($segments);
		list ($view, $jcItemId) = explode('-', $segment);

		$db = JFactory::getDbo();

		$vars['view'] = $view;

		// Move forward based on view
		switch ($view)
		{
			case 'tracker':
				// Search the database for the appropriate tracker.
				$db->setQuery(
					$db->getQuery(true)
						->select('tracker_id')
						->from('#__code_trackers')
						->where('jc_tracker_id = ' . (int) $jcItemId)
					, 0, 1
				);
				$trackerId = (int) $db->loadResult();

				// If the tracker isn't found throw a 404.
				if (!$trackerId)
				{
					throw new InvalidArgumentException('Tracker not found.', 404);
				}

				// We're on a valid tracker, finish up the processing
				$vars['tracker_id'] = $jcItemId;

				break;

			case 'issue';
				// Search the database for the appropriate issue.
				$db->setQuery(
					$db->getQuery(true)
						->select('issue_id')
						->from('#__code_tracker_issues')
						->where('jc_issue_id = ' . (int) $jcItemId)
					, 0, 1
				);
				$issueId = (int) $db->loadResult();

				// If the issue isn't found throw a 404.
				if (!$issueId)
				{
					throw new InvalidArgumentException('Issue not found.', 404);
				}

				// We're on a valid issue, finish up the processing
				$vars['issue_id'] = $jcItemId;

				break;

			default:
				// Unsupported resource
				throw new InvalidArgumentException('Resource not found.', 404);
		}

		return $vars;
	}
}
