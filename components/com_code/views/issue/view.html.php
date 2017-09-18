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
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Router\Route;

/**
 * The HTML Joomla Code issue view.
 */
class CodeViewIssue extends HtmlView
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		/** @var CodeModelIssue $model */
		$model = $this->getModel('Issue');

		$this->state    = $model->getState();
		$this->item     = $model->getItem();
		$this->tags     = $model->getTags();
		$this->commits  = $model->getCommits();
		$this->comments = $model->getComments();
		$this->tracker  = $model->getTracker();
		$this->user     = Factory::getUser();
		$this->params   = Factory::getApplication()->getParams('com_code');

		// Check for errors.
		if (count($errors = $model->getErrors()))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		$this->prepareDocument();

		return parent::display($tpl);
	}

	/**
	 * Prepares the document.
	 *
	 * @return  void.
	 */
	protected function prepareDocument()
	{
		$app = Factory::getApplication();

		// Because the application sets a default page title, we need to get it from the menu item itself
		$menu = $app->getMenu()->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', $this->item->title);
		}

		$title = $this->params->get('page_title', '');

		$id = (int) @$menu->query['issue_id'];

		// If the menu item does not concern this issue
		if ($menu && ($menu->query['option'] != 'com_code' || $menu->query['view'] != 'issue' || $id != $this->item->jc_issue_id))
		{
			$pathway = $app->getPathway();
			$title   = '[#' . $this->item->jc_issue_id . '] - ' . $this->item->title;

			$pathway->addItem($this->tracker->title, Route::_('index.php?option=com_code&view=tracker&tracker_id=' . $this->tracker->jc_tracker_id));
			$pathway->addItem($this->item->title, Route::_('index.php?option=com_code&view=issue&issue_id=' . $this->item->jc_issue_id));
		}

		// Check for empty title and add site name if param is set
		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		if (empty($title))
		{
			$title = '[#' . $this->item->jc_issue_id . '] - ' . $this->item->title;
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
