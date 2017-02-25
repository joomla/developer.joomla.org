<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Finder.Tracker_Issues
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('FinderIndexerAdapter', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php');

/**
 * Smart Search adapter for com_code issues.
 */
class PlgFinderTracker_Issues extends FinderIndexerAdapter
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var  boolean
	 */
	protected $autoloadLanguage = true;

	/**
	 * The plugin identifier.
	 *
	 * @var  string
	 */
	protected $context = 'Tracker_Issues';

	/**
	 * The extension name.
	 *
	 * @var  string
	 */
	protected $extension = 'com_code';

	/**
	 * The sublayout to use when rendering the results.
	 *
	 * @var  string
	 */
	protected $layout = 'issue';

	/**
	 * The type of content that the adapter indexes.
	 *
	 * @var  string
	 */
	protected $type_title = 'Tracker Issue';

	/**
	 * The table name.
	 *
	 * @var  string
	 */
	protected $table = '#__code_tracker_issues';

	/**
	 * Method to update index data on category access level changes
	 *
	 * @param   JTable  $row  A JTable object
	 *
	 * @return  void
	 */
	protected function categoryAccessChange($row)
	{
		// The issue tracker is static, do nothing
	}

	/**
	 * Method to update index data on category access level changes
	 *
	 * @param   array    $pks    A list of primary key ids of the content that has changed state.
	 * @param   integer  $value  The value of the state that the content has been changed to.
	 *
	 * @return  void
	 */
	protected function categoryStateChange($pks, $value)
	{
		// The issue tracker is static, do nothing
	}

	/**
	 * Method to get the SQL query used to retrieve the list of content items.
	 *
	 * @param   JDatabaseQuery  $sql  A JDatabaseQuery object or null.
	 *
	 * @return  JDatabaseQuery  A database object.
	 */
	protected function getListQuery($sql = null)
	{
		// Check if we can use the supplied SQL query.
		$sql = $sql instanceof JDatabaseQuery ? $sql : $this->db->getQuery(true);

		return $sql->select($this->db->quoteName('a.issue_id'))
			->select($this->db->quoteName('a.jc_issue_id'))
			->select($this->db->quoteName('a.title'))
			->select($this->db->quoteName('a.description', 'summary'))
			->select('1 AS state')
			->select($this->db->quoteName('a.created_date', 'start_date'))
			->select($this->db->quote('') . ' AS author')
			->select($this->db->quote('*') . ' AS language')
			->select('0 AS publish_start_date')
			->select('0 AS publish_end_date')
			->select('1 AS access')
			->from($this->db->quoteName($this->table, 'a'));
	}

	/**
	 * Method to get a SQL query to load the published and access states for
	 * an article and category.
	 *
	 * @return  JDatabaseQuery  A database object.
	 */
	protected function getStateQuery()
	{
		$query = $this->db->getQuery(true);

		// Issue ID
		$query->select('a.issue_id')
			->from($this->table . ' AS a');

		return $query;
	}

	/**
	 * Method to get the URL for the item. The URL is how we look up the link
	 * in the Finder index.
	 *
	 * @param   integer  $id         The id of the item.
	 * @param   string   $extension  The extension the category is in.
	 * @param   string   $view       The view for the URL.
	 *
	 * @return  string  The URL of the item.
	 */
	protected function getUrl($id, $extension, $view)
	{
		return 'index.php?option=' . $extension . '&view=' . $view . '&issue_id=' . $id;
	}

	/**
	 * Method to index an item. The item must be a FinderIndexerResult object.
	 *
	 * @param   FinderIndexerResult  $item  The item to index as an FinderIndexerResult object.
	 *
	 * @return  void
	 *
	 * @throws  Exception on database error.
	 */
	protected function index(FinderIndexerResult $item)
	{
		// Check if the extension is enabled
		if (JComponentHelper::isEnabled($this->extension) == false)
		{
			return;
		}

		// Build the necessary route and path information.
		$item->url   = $this->getUrl($item->jc_issue_id, $this->extension, $this->layout);
		$item->route = CodeHelperRoute::getIssueRoute($item->jc_issue_id);
		$item->path  = FinderIndexerHelper::getContentPath($item->route);

		// Set the language.
		$item->language = FinderIndexerHelper::getDefaultLanguage();

		// Add the metadata.
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'link');

		// Add the taxonomy data.
		$item->addTaxonomy('Type', $this->type_title);
		$item->addTaxonomy('Language', $item->language);

		// Get content extras.
		FinderIndexerHelper::getContentExtras($item);

		$this->indexer->index($item);
	}

	/**
	 * Method to update index data on access level changes
	 *
	 * @param   JTable  $row  A JTable object
	 *
	 * @return  void
	 */
	protected function itemAccessChange($row)
	{
		// The issue tracker is static, do nothing
	}

	/**
	 * Method to update index data on published state changes
	 *
	 * @param   array    $pks    A list of primary key ids of the content that has changed state.
	 * @param   integer  $value  The value of the state that the content has been changed to.
	 *
	 * @return  void
	 */
	protected function itemStateChange($pks, $value)
	{
		// The issue tracker is static, do nothing
	}

	/**
	 * Method to update index data when a plugin is disabled
	 *
	 * @param   array  $pks  A list of primary key ids of the content that has changed state.
	 *
	 * @return  void
	 */
	protected function pluginDisable($pks)
	{
		// Since multiple plugins may be disabled at a time, we need to check first
		// that we're handling the appropriate one for the context
		foreach ($pks as $pk)
		{
			if ($this->getPluginType($pk) == strtolower($this->context))
			{
				// Get all of the items to unindex them
				$query = clone $this->getStateQuery();
				$this->db->setQuery($query);
				$items = $this->db->loadColumn();

				// Remove each item
				foreach ($items as $item)
				{
					$this->remove($item);
				}
			}
		}
	}

	/**
	 * Method to setup the indexer to be run.
	 *
	 * @return  boolean  True on success.
	 */
	protected function setup()
	{
		// Load dependent classes.
		JLoader::register('CodeHelperRoute', JPATH_SITE . '/components/com_code/helpers/route.php');

		return true;
	}
}
