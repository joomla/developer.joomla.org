<?php
/**
 * @package     Joomla.BugSquad
 * @subpackage  com_trackerstats
 *
 * @copyright   Copyright (C) 2011 Mark Dexter. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Gets the data for the release notes menu item.
 */
class TrackerstatsModelReleasenotes extends ListModel
{
	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db           = $this->getDbo();
		$query        = $db->getQuery(true);
		$subQuery     = $db->getQuery(true);
		$includeRaw   = $this->state->params->get('include_issues', null);
		$excludeRaw   = $this->state->params->get('exclude_issues', null);
		$includeArray = explode(',', $includeRaw);
		$excludeArray = explode(',', $excludeRaw);
		$includeArray = ArrayHelper::toInteger($includeArray);
		$excludeArray = ArrayHelper::toInteger($excludeArray);

		$subQuery->select('it.issue_id AS issue_id, it.tag_id AS tag_id, t.tag AS tag')
			->join('LEFT', '#__code_tags AS t ON t.tag_id = it.tag_id')
			->from('#__code_tracker_issue_tag_map AS it')
			->where('it.tag_id IN (6,10,16,20,24,26,28,51,52,55,62,65,67,74,75,78,79,82,83,87,92,93,105,107,112,115)')
			->group('it.issue_id, it.tag_id, t.tag');

		// Select required fields from the categories.
		$query->select('CASE WHEN ISNULL(m.tag) THEN ' . $db->quote('None') . ' ELSE m.tag END as category');
		$query->select('i.title, i.jc_issue_id, i.close_date');

		$query->from('#__code_tracker_issues AS i');
		$query->join('LEFT', '(' . (string) $subQuery . ') AS m ON i.jc_issue_id = m.issue_id');

		$query->where(
			'((DATE(close_date) BETWEEN ' . $db->quote(substr($this->state->params->get('start_date'), 0, 10))
			. ' AND ' . $db->quote(substr($this->state->params->get('end_date'), 0, 10)) . ')'
			. ' OR (i.jc_issue_id IN (' . implode(',', $includeArray) . ')))'
		);

		// Join the status table to get the status name
		$query->select('s.title AS status_name');
		$query->join('LEFT', '#__code_tracker_status AS s ON i.status = s.jc_status_id');

		// Filter on merged items from the trackers
		$query->where('(s.title LIKE ' . $db->quote('%Fixed in SVN%') . ' OR s.title LIKE ' . $db->quote('%Implemented in trunk%') . ')');

		// Exclude explicitly listed trackers
		$query->where('i.jc_issue_id NOT IN (' . implode(',', $excludeArray) . ')');

		if ($this->state->get('list.filter'))
		{
			$query->where('i.title LIKE ' . $db->quote('%' . $this->state->get('list.filter') . '%'));
		}

		$query->order('CASE WHEN ISNULL(m.tag) THEN ' . $db->quote('None') . ' ELSE m.tag END ASC');
		$query->order('i.jc_issue_id ASC');

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app    = Factory::getApplication('site');
		$jinput = $app->input;

		$params     = $app->getParams();
		$menuParams = new Registry;

		if ($menu = $app->getMenu()->getActive())
		{
			$menuParams->loadString($menu->getParams());
		}

		$mergedParams = clone $menuParams;
		$mergedParams->merge($params);
		$this->setState('params', $mergedParams);

		// Optional filter text
		$this->setState('list.filter', $jinput->getString('filter-search'));

		$value = $app->input->get('limit', $app->get('list_limit', 0), 'uint');
		$this->setState('list.limit', $value);

		$value = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $value);
	}
}
