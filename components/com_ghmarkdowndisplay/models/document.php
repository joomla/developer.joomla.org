<?php
/**
 * @package     Joomla.DeveloperNetwork
 * @subpackage  com_ghmarkdowndisplay
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;

/**
 * Document model class.
 */
class GHMarkdownDisplayModelDocument extends ItemModel
{
	/**
	 * Get the navigational data for a document's repository.
	 *
	 * @param   integer  $repositoryId  The repository ID of the document to process
	 *
	 * @return  array|boolean  Array on success, false on failure.
	 */
	public function getDocumentNavigation($repositoryId = null)
	{
		$repositoryId = (!empty($repositoryId)) ? $repositoryId : (int) $this->getState($this->getName() . '.repository_id');

		if (!$repositoryId)
		{
			$this->setError(Text::_('COM_GHMARKDOWNDISPLAY_ERROR_REPOSITORY_ID_REQUIRED'));

			return false;
		}

		$db = $this->getDbo();

		$sectionQuery = $db->getQuery(true)
			->select(['id', 'repository_id', 'name'])
			->from('#__ghmarkdowndisplay_sections')
			->where('repository_id = ' . (int) $repositoryId)
			->order('ordering ASC');

		if (Factory::getUser()->guest)
		{
			$sectionQuery->where('published = 1');
		}

		$sections = $db->setQuery($sectionQuery)->loadAssocList('id');

		$documentQuery = $db->getQuery(true)
			->select(['id', 'section_id', 'name'])
			->from('#__ghmarkdowndisplay_documents')
			->where('section_id IN (' . implode(',', array_keys($sections)) . ')')
			->order('ordering ASC');

		if (Factory::getUser()->guest)
		{
			$documentQuery->where('published = 1');
		}

		$documents = $db->setQuery($documentQuery)->loadAssocList('id');

		return compact('sections', 'documents');
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  object|boolean  Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');

		if (!$pk)
		{
			$this->setError(Text::_('COM_GHMARKDOWNDISPLAY_ERROR_DOCUMENT_ID_REQUIRED'));

			return false;
		}

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select(
				[
					'a.*',
					's.name AS section_name',
				]
			)
			->from('#__ghmarkdowndisplay_documents AS a')
			->join('LEFT', '#__ghmarkdowndisplay_sections AS s ON s.id = a.section_id')
			->where('a.id = ' . (int) $pk);

		if (Factory::getUser()->guest)
		{
			$query->where('a.published = 1');
		}

		$document = $db->setQuery($query)->loadObject();

		if ($document === null)
		{
			$this->setError(Text::_('COM_GHMARKDOWNDISPLAY_ERROR_DOCUMENT_NOT_FOUND'));

			return false;
		}

		return $document;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 */
	protected function populateState()
	{
		$app = Factory::getApplication();

		// Load state from the request.
		$this->setState($this->getName() . '.id', $app->input->getUint('id'));
		$this->setState($this->getName() . '.repository_id', $app->input->getUint('repository'));

		$this->setState('params', $app->getParams('com_ghmarkdowndisplay'));
	}
}
