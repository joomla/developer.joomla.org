<?php
/**
 * @package     Joomla.DeveloperNetwork
 * @subpackage  com_ghmarkdowndisplay
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Cache\Exception\CacheExceptionInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Version;
use Joomla\Registry\Registry;

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
		$db = $this->getDbo();

		if (!$pk)
		{
			// The menu item configuration defines a repository without a document, if a repository ID is given try to find a PK
			$repoId = (int) $this->getState($this->getName() . '.repository_id');

			if ($repoId === 0)
			{
				$this->setError(Text::_('COM_GHMARKDOWNDISPLAY_ERROR_DOCUMENT_ID_REQUIRED'));

				return false;
			}

			$pk = $db->setQuery(
				$db->getQuery(true)
					->select('a.id')
					->from('#__ghmarkdowndisplay_documents AS a')
					->join('LEFT', '#__ghmarkdowndisplay_sections AS s ON s.id = a.section_id')
					->join('LEFT', '#__ghmarkdowndisplay_repositories AS r ON r.id = s.repository_id')
					->where('r.id = ' . (int) $repoId)
					->order('a.ordering ASC'),
				0,
				1
			)->loadResult();
		}

		$query = $db->getQuery(true)
			->select(
				[
					'a.*',
					's.name AS section_name',
					'r.id AS repository_id',
					'r.repository_owner AS repository_owner',
					'r.repository_name AS repository_name',
				]
			)
			->from('#__ghmarkdowndisplay_documents AS a')
			->join('LEFT', '#__ghmarkdowndisplay_sections AS s ON s.id = a.section_id')
			->join('LEFT', '#__ghmarkdowndisplay_repositories AS r ON r.id = s.repository_id')
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
	 * Get the markdown document as its rendered HTML
	 *
	 * @param   object  $document  The document object as built by this class' getItem method
	 *
	 * @return  string|boolean  Rendered document on success, false on failure.
	 */
	public function getRenderedDocument($document)
	{
		/** @var \Joomla\CMS\Cache\Controller\CallbackController $cache */
		$cache = Factory::getCache('com_ghmarkdowndisplay');

		// Caching should always be enabled here unless in debug mode
		$cache->setCaching(!JDEBUG);

		// Cache this for one day
		$cache->setLifeTime(1440);

		$handler = function ($document)
		{
			$componentParams = ComponentHelper::getParams('com_ghmarkdowndisplay');

			$options = new Registry;

			// Set a user agent for the request
			$options->set('userAgent', 'GitHubMarkdownDisplay/1.0');

			// If an API token is set in the params, use it for authentication
			if ($componentParams->get('github_token', ''))
			{
				$options->set('headers', ['Authorization' => 'token ' . $componentParams->get('github_token', '')]);
			}
			// Set the username and password if set in the params
			elseif ($componentParams->get('github_user', '') && $componentParams->get('github_password'))
			{
				$options->set('api.username', $componentParams->get('github_user', ''));
				$options->set('api.password', $componentParams->get('github_password', ''));
			}

			$version = new Version;

			if ($version->isCompatible('4.0.0')) {
				JLoader::register(GHMarkdownGithubClient::class, JPATH_COMPONENT . '/github/client.php');
				$github = new GHMarkdownGithubClient($options);
				$data = $github->getRepositoryContents($document->repository_owner, $document->repository_name, ltrim($document->file, '/'));
			} else {
				$github = new JGithub($options);
				$data = $github->repositories->contents->get($document->repository_owner, $document->repository_name, ltrim($document->file, '/'));
			}

			switch ($data->encoding)
			{
				case 'base64':
					$contents = base64_decode($data->content);

					break;

				default:
					$this->setError(Text::sprintf('COM_GHMARKDOWNDISPLAY_ERROR_UNSUPPORTED_ENCODING', $data->encoding));

					return false;
			}

			if ($version->isCompatible('4.0.0')) {
				$html = $github->renderMarkdown($contents, 'gfm', $document->repository_owner . '/' . $document->repository_name);
			} else {
				$html = $github->markdown->render($contents, 'gfm', $document->repository_owner . '/' . $document->repository_name);
			}

			// Convert plain tables to Bootstrap to suit our theming
			$html = str_replace('<table>', '<table class="table table-condensed table-striped">', $html);

			// TODO: Postprocessing links
			return $html;
		};

		try
		{
			if (JDEBUG)
			{
				return $handler($document);
			}

			return $cache->get($handler, [$document], 'com_ghmarkdowndisplay.document.' . $document->id);
		}
		catch (CacheExceptionInterface $e)
		{
			return $handler($document);
		}
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
