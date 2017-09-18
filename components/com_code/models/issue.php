<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Issue Model for Joomla Code
 */
class CodeModelIssue extends BaseDatabaseModel
{
	/**
	 * Fetch the comments for a specified issue
	 *
	 * @param   integer  $issueId  JoomlaCode Issue ID to search by, uses the issue from the request if one is not specified
	 *
	 * @return  stdClass
	 */
	public function getComments($issueId = null)
	{
		$issueId = empty($issueId) ? $this->getState('issue.id') : $issueId;

		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*, ' . $query->concatenate(['cu.first_name', $db->quote(' '), 'cu.last_name']) . ' AS commenter_name')
			->from('#__code_tracker_issue_responses AS a')
			->join('LEFT', '#__code_users AS cu ON cu.user_id = a.created_by')
			->where('a.jc_issue_id = ' . (int) $issueId)
			->order('a.created_date ASC');

		$db->setQuery($query);

		try
		{
			return $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$this->setError(Text::sprintf('COM_CODE_ERROR_FETCHING_COMMENTS', $issueId));
		}
	}

	/**
	 * Fetch the commits for a specified issue
	 *
	 * @param   integer  $issueId  JoomlaCode Issue ID to search by, uses the issue from the request if one is not specified
	 *
	 * @return  stdClass
	 */
	public function getCommits($issueId = null)
	{
		$issueId = empty($issueId) ? $this->getState('issue.id') : $issueId;

		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*, ' . $query->concatenate(['cu.first_name', $db->quote(' '), 'cu.last_name']) . ' AS committer_name')
			->from('#__code_tracker_issue_commits AS a')
			->join('LEFT', '#__code_users AS cu ON cu.user_id = a.created_by')
			->where('a.jc_issue_id = ' . (int) $issueId)
			->order('a.created_date ASC');

		$db->setQuery($query);

		try
		{
			return $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$this->setError(Text::sprintf('COM_CODE_ERROR_FETCHING_COMMITS', $issueId));
		}
	}

	/**
	 * Fetch the specified issue
	 *
	 * @param   integer  $issueId  JoomlaCode Issue ID to search by, uses the issue from the request if one is not specified
	 *
	 * @return  stdClass
	 */
	public function getItem($issueId = null)
	{
		$issueId = empty($issueId) ? $this->getState('issue.id') : $issueId;

		$db = $this->getDbo();

		$query = $db->getQuery(true);
		$query->select(
			'a.*, s.state_id AS state, s.title AS status_name, '
			. $query->concatenate(['cu.first_name', $db->quote(' '), 'cu.last_name']) . ' AS created_by_name'
		)
			->from('#__code_tracker_issues AS a')
			->join('LEFT', '#__code_tracker_status AS s on s.jc_status_id = a.status')
			->join('LEFT', '#__code_users AS cu on cu.user_id = a.created_by')
			->where('a.jc_issue_id = ' . (int) $issueId);

		$db->setQuery($query);

		try
		{
			$data = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			$this->setError(Text::sprintf('COM_CODE_ERROR_FETCHING_ISSUE', $issueId));
		}

		if (empty($data))
		{
			JError::raiseError(404, Text::_('COM_CODE_ERROR_ISSUE_NOT_FOUND'));
		}

		return $data;
	}

	/**
	 * Fetch the tags for a specified issue
	 *
	 * @param   integer  $issueId  JoomlaCode Issue ID to search by, uses the issue from the request if one is not specified
	 *
	 * @return  stdClass
	 */
	public function getTags($issueId = null)
	{
		$issueId = empty($issueId) ? $this->getState('issue.id') : $issueId;

		$db = $this->getDbo();

		$subQuery = $db->getQuery(true)
			->select('tag_id')
			->from('#__code_tracker_issue_tag_map')
			->where('issue_id = ' . $issueId);

		$db->setQuery(
			$db->getQuery(true)
				->select('tag')
				->from('#__code_tags')
				->where('tag_id IN (' . (string) $subQuery . ')')
				->order('tag ASC')
		);

		try
		{
			return $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$this->setError(Text::sprintf('COM_CODE_ERROR_FETCHING_TAGS', $issueId));
		}
	}

	/**
	 * Fetch the tracker for a specified issue
	 *
	 * @param   integer  $issueId  JoomlaCode Issue ID to search by, uses the issue from the request if one is not specified
	 *
	 * @return  stdClass
	 */
	public function getTracker($issueId = null)
	{
		$issueId = empty($issueId) ? $this->getState('issue.id') : $issueId;

		$item = $this->getItem($issueId);

		$db = $this->getDbo();

		$db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from('#__code_trackers')
				->where('jc_tracker_id = ' . (int) $item->tracker_id)
		);

		try
		{
			return $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			$this->setError(Text::sprintf('COM_CODE_ERROR_FETCHING_TRACKER', $issueId));
		}
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @note    Calling getState in this method will result in recursion.
	 */
	protected function populateState()
	{
		// Set the JoomlaCode issue ID from the request.
		$this->setState('issue.id', Factory::getApplication()->input->getUint('issue_id'));
	}
}
