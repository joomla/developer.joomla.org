<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Tracker Model for Joomla Code
 */
class CodeModelTracker extends JModelItem
{
	/**
	 * Model context string.
	 *
	 * @var  string
	 */
	protected $_context = 'com_code.tracker';

	/**
	 * A list of issues for this tracker.
	 *
	 * @var  array|boolean
	 */
	protected $issues;

	/**
	 * The pagination object.
	 *
	 * @var  JPagination
	 */
	protected $pagination;

	/**
	 * Method to get article data.
	 *
	 * @param   integer  $pk  The id of the tracker.
	 *
	 * @return  mixed  Menu item data object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('tracker.id');

		// Initialize the memory storage array.
		if ($this->_item === null)
		{
			$this->_item = [];
		}

		if (!isset($this->_item[$pk]))
		{
			try
			{
				// Get a database and query object.
				$db    = $this->getDbo();
				$query = $db->getQuery(true);

				// Select the fields from the main table.
				$query->select($this->getState('item.select', 'a.*'));
				$query->from('#__code_trackers AS a');

				// Get only the row by primary key.
				$query->where('a.jc_tracker_id = ' . (int) $pk);

				// Filter by published state.
				$published = $this->getState('filter.published');
				$archived  = $this->getState('filter.archived');

				if (is_numeric($published))
				{
					$query->where('(a.state = ' . (int) $published . ' OR a.state =' . (int) $archived . ')');
				}

				// Get the data object from the database.
				$db->setQuery($query);
				$data = $db->loadObject();

				if (empty($data))
				{
					JError::raiseError(404, JText::_('COM_CODE_ERROR_TRACKER_NOT_FOUND'));
				}

				// Check for published state if filter set.
				if (((is_numeric($published)) || (is_numeric($archived))) && (($data->state != $published) && ($data->state != $archived)))
				{
					JError::raiseError(404, JText::_('COM_CODE_ERROR_TRACKER_NOT_FOUND'));
				}

				// Setup the options Registry object.
				$options       = new Registry($data->options);
				$data->options = clone $this->getState('options');
				$data->options->merge($options);

				// Setup the metadata Registry object.
				$data->metadata = new Registry($data->metadata);

				$this->_item[$pk] = $data;
			}
			catch (Exception $e)
			{
				$this->setError($e);
				$this->_item[$pk] = false;
			}
		}

		return $this->_item[$pk];
	}


	/**
	 * Get the issues for a tracker
	 *
	 * @param   integer  $pk  The tracker ID
	 *
	 * @return  mixed  An array of articles or false if an error occurs.
	 */
	public function getItems($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('tracker.id');

		if ($this->issues === null)
		{
			/** @var CodeModelIssues $model */
			$model = JModelLegacy::getInstance('Issues', 'CodeModel', ['ignore_request' => true]);

			$model->setState('options', $this->getState('options'));
			$model->setState('filter.tracker_id', $pk);
			$model->setState('filter.search', $this->getState('filter.search'));
			$model->setState('list.start', $this->getState('list.start'));
			$model->setState('list.ordering', $this->getState('list.ordering', 'issue_id'));
			$model->setState('list.direction', $this->getState('list.direction', 'DESC'));
			$model->setState('list.limit', $this->getState('list.limit'));
			$model->setState('list.filter', $this->getState('list.filter'));

			$model->setState('filter.state', $this->getState('issue.state'));
			$model->setState('filter.status_id', $this->getState('issue.status_id'));
			$model->setState('filter.status_id_include', $this->getState('issue.status_id_include'));
			$model->setState('filter.tag_id', $this->getState('issue.tag_id'));
			$model->setState('filter.tag_id_include', $this->getState('issue.tag_id_include'));
			$model->setState('filter.submitter_name', $this->getState('issue.submitter_name'));
			$model->setState('filter.submitter_id', $this->getState('issue.submitter_id'));
			$model->setState('filter.submitter_id_include', $this->getState('issue.submitter_id_include'));
			$model->setState('filter.closer_name', $this->getState('issue.closer_name'));
			$model->setState('filter.closer_id', $this->getState('issue.closer_id'));
			$model->setState('filter.closer_id_include', $this->getState('issue.closer_id_include'));
			$model->setState('filter.date_field', $this->getState('issue.date_field'));
			$model->setState('filter.date_filtering', $this->getState('issue.date_filtering'));
			$model->setState('filter.start_date_range', $this->getState('issue.start_date_range'));
			$model->setState('filter.end_date_range', $this->getState('issue.end_date_range'));
			$model->setState('filter.relative_date', $this->getState('issue.relative_date'));

			$this->issues = $model->getItems();

			if ($this->issues === false)
			{
				$this->setError($model->getError());
			}

			$this->pagination = $model->getPagination();
		}

		return $this->issues;
	}

	/**
	 * Method to get a JPagination object for the data set.
	 *
	 * @return  JPagination
	 */
	public function getPagination()
	{
		$this->getItems();

		return $this->pagination;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('site');

		// Set the JoomlaCode tracker ID from the request.
		$pk = $app->input->getInt('tracker_id');
		$this->setState('tracker.id', $pk);

		// Prefix for use with getUserStateFromRequest when getting issue filters
		$issueStatePrefix = 'com_code.tracker.' . $pk . ':' . $app->input->getInt('Itemid', 0) . '.issue.';

		// Get the list ID for use with getUserStateFromRequest
		$listId = $pk . ':' . $app->input->getInt('Itemid', 0);

		// Load the component/page options from the application.
		$this->setState('options', $app->getParams('com_code'));

		// Set the state filter.
		//$this->setState('filter.state', 1);

		// Set the optional filter search string text.
		$this->setState('filter.search', $app->getUserStateFromRequest('com_code.tracker.' . $listId . '.issue.search', 'search', null, 'string'));

		// Set the issue filters
		$this->setState('issue.state', $app->getUserStateFromRequest($issueStatePrefix . 'state', 'filter_state', null, 'int'));

		$this->setState('issue.status_id', $app->getUserStateFromRequest($issueStatePrefix . 'status_id', 'filter_status_id', null, 'uint'));

		$this->setState(
			'issue.status_id_include', $app->getUserStateFromRequest($issueStatePrefix . 'status_id_include', 'filter_status_id_include', '=', 'uint')
		);

		$this->setState('issue.tag_id', $app->getUserStateFromRequest($issueStatePrefix . 'tag_id', 'filter_tag_id', null, 'array'));

		$this->setState(
			'issue.tag_id_include', $app->getUserStateFromRequest($issueStatePrefix . 'tag_id_include', 'filter_tag_id_include', '=', 'uint')
		);

		$this->setState(
			'issue.submitter_name', $app->getUserStateFromRequest($issueStatePrefix . 'submitter_name', 'filter_submitter_name', null, 'string')
		);

		$this->setState('issue.submitter_id', $app->getUserStateFromRequest($issueStatePrefix . 'submitter_id', 'filter_submitter_id', null, 'uint'));

		$this->setState(
			'issue.submitter_id_include',
			$app->getUserStateFromRequest($issueStatePrefix . 'submitter_id_include', 'filter_submitter_id_include', null, 'uint')
		);

		$this->setState('issue.closer_name', $app->getUserStateFromRequest($issueStatePrefix . 'closer_name', 'filter_closer_name', null, 'string'));
		$this->setState('issue.closer_id', $app->getUserStateFromRequest($issueStatePrefix . 'closer_id', 'filter_closer_id', null, 'uint'));

		$this->setState(
			'issue.closer_id_include',
			$app->getUserStateFromRequest($issueStatePrefix . 'closer_id_include', 'filter_closer_id_include', null, 'uint')
		);

		$this->setState('issue.date_field', $app->getUserStateFromRequest($issueStatePrefix . 'date_field', 'filter_date_field', null, 'cmd'));

		$this->setState(
			'issue.date_filtering', $app->getUserStateFromRequest($issueStatePrefix . 'date_filtering', 'filter_date_filtering', null, 'cmd')
		);

		$this->setState(
			'issue.start_date_range',
			$app->getUserStateFromRequest($issueStatePrefix . 'start_date_range', 'filter_start_date_range', null, 'string')
		);

		$this->setState(
			'issue.end_date_range', $app->getUserStateFromRequest($issueStatePrefix . 'end_date_range', 'filter_end_date_range', null, 'string')
		);

		$this->setState(
			'issue.relative_date', $app->getUserStateFromRequest($issueStatePrefix . 'relative_date', 'filter_relative_date', null, 'int')
		);

		// Set the tracker filter.
		//$this->setState('filter.tracker_id', 1);
		//$this->setState('filter.tracker_id_include', 1);

		// Set the tag filter.
		//$this->setState('filter.tag_id', 1);
		//$this->setState('filter.tag_id_include', 1);

		// Set the submitter filter.
		//$this->setState('filter.submitter_id', 1);
		//$this->setState('filter.submitter_id_include', 1);

		// Set the closer filter.
		//$this->setState('filter.closer_id', 1);
		//$this->setState('filter.closer_id_include', 1);

		// Set the date filters.
		//$this->setState('filter.date_filtering', null);
		//$this->setState('filter.date_field', null);
		//$this->setState('filter.start_date_range', null);
		//$this->setState('filter.end_date_range', null);
		//$this->setState('filter.relative_date', null);

		// Load the list options from the request.
		$this->setState('list.start', $app->input->getInt('limitstart', 0));

		$this->setState(
			'list.ordering',
			$app->getUserStateFromRequest('com_code.tracker.' . $listId . '.filter_order', 'filter_order', 'a.modified_date', 'string')
		);

		$this->setState(
			'list.direction', $app->getUserStateFromRequest('com_code.tracker.' . $listId . '.filter_order_Dir', 'filter_order_Dir', 'DESC', 'cmd')
		);

		$this->setState(
			'list.limit', $app->getUserStateFromRequest('com_code.tracker.' . $listId . '.limit', 'limit', $app->get('list_limit'), 'uint')
		);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string  $id  A prefix for the store id.
	 *
	 * @return	string  A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('tracker.id');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.tag_id');
		$id .= ':' . $this->getState('filter.tag_id_include');
		$id .= ':' . $this->getState('filter.submitter_id');
		$id .= ':' . $this->getState('filter.submitter_id_include');
		$id .= ':' . $this->getState('filter.closer_id');
		$id .= ':' . $this->getState('filter.closer_id_include');
		$id .= ':' . $this->getState('filter.date_filtering');
		$id .= ':' . $this->getState('filter.date_field');
		$id .= ':' . $this->getState('filter.start_date_range');
		$id .= ':' . $this->getState('filter.end_date_range');
		$id .= ':' . $this->getState('filter.relative_date');
		$id .= ':' . $this->getState('filter.search');

		return parent::getStoreId($id);
	}
}
