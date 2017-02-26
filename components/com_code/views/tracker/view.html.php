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
 * The HTML Joomla Code tracker view.
 */
class CodeViewTracker extends JViewLegacy
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
		// Load the necessary helper class
		$this->loadHelper('Select');

		// Populate basic variables
		$this->state  = $this->get('State');
		$this->item   = $this->get('Item');
		$this->items  = $this->get('Items');
		$this->page   = $this->get('Pagination');
		$this->user   = JFactory::getUser();
		$this->params = JFactory::getApplication()->getParams('com_code');

		// Priorities map, from integer to string
		$this->priorities = CodeHelperSelect::getPrioritiesRaw();

		// URL to submit the form to
		$id     = JFactory::getApplication()->input->getUint('Itemid', 0);
		$itemid = $id ? '&Itemid=' . (int) $id : '';

		$this->formURL = JRoute::_(
			'index.php?option=com_code&view=tracker&tracker_id=' . $this->item->jc_tracker_id . $itemid
		);

		// Ordering
		$this->order     = $this->getModel()->getState('list.ordering', 'issue_id');
		$this->order_Dir = $this->getModel()->getState('list.direction', 'desc');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
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
		$app = JFactory::getApplication();

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

		$id = (int) @$menu->query['tracker_id'];

		// If the menu item does not concern this tracker
		if ($menu && ($menu->query['option'] != 'com_code' || $menu->query['view'] != 'tracker' || $id != $this->item->jc_tracker_id))
		{
			$title = $this->item->title;

			$app->getPathway()->addItem($this->item->title, JRoute::_('index.php?option=com_code&view=tracker&tracker_id=' . $this->item->jc_tracker_id));
		}

		// Check for empty title and add site name if param is set
		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		if (empty($title))
		{
			$title = $this->item->title;
		}

		$this->document->setTitle($title);

		if ($this->item->description)
		{
			$this->document->setDescription($this->item->description);
		}
		elseif (!$this->item->description && $this->params->get('menu-meta_description'))
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
