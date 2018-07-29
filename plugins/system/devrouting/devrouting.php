<?php
/**
 * Joomla! Developer Network
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;

/**
 * Plugin for extra processing of routing on the Joomla! Developer Network
 */
class PlgSystemDevRouting extends CMSPlugin
{
	/**
	 * Application object
	 *
	 * @var  CMSApplication
	 */
	protected $app;

	/**
	 * Database driver
	 *
	 * @var  JDatabaseDriver
	 */
	protected $db;

	/**
	 * Listener for the `onAfterRoute` event
	 *
	 * @return  void
	 */
	public function onAfterRoute()
	{
		// Only for frontend
		if (!$this->app->isClient('site'))
		{
			return;
		}

		// Only for the GitHub component
		if ($this->app->input->get('option') !== 'com_ghmarkdowndisplay')
		{
			return;
		}

		// Only if an ID hasn't been set
		if ($this->app->input->getUint('id', 0) !== 0)
		{
			return;
		}

		$repoId = $this->app->input->getUint('repository');

		// Get the first document from the first section in the repo which is published
		$sectionQuery = $this->db->getQuery(true)
			->select('id')
			->from('#__ghmarkdowndisplay_sections')
			->where('repository_id = ' . $repoId)
			->order('ordering ASC');

		if (Factory::getUser()->guest)
		{
			$sectionQuery->where('published = 1');
		}

		$sectionId = (int) $this->db->setQuery($sectionQuery, 0, 1)->loadResult();

		// If we don't have an ID let this pass through and the component can handle the error
		if (!$sectionId)
		{
			return;
		}

		$documentQuery = $this->db->getQuery(true)
			->select('id')
			->from('#__ghmarkdowndisplay_documents')
			->where('section_id = ' . $sectionId)
			->order('ordering ASC');

		if (Factory::getUser()->guest)
		{
			$documentQuery->where('published = 1');
		}

		$documentId = (int) $this->db->setQuery($documentQuery, 0, 1)->loadResult();

		// If we don't have an ID let this pass through and the component can handle the error
		if (!$documentId)
		{
			return;
		}

		$this->app->redirect(Route::_('index.php?option=com_ghmarkdowndisplay&view=document&repository=' . $repoId . '&id=' . $documentId), 301);
	}
}
