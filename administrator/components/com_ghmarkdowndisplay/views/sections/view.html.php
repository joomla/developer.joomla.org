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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Pagination\Pagination;

/**
 * Sections view class
 */
class GHMarkdownDisplayViewSections extends HtmlView
{
	/**
	 * The active search tools filters
	 *
	 * @var   array
	 * @note  Must be public to be accessed from the search tools layout
	 */
	public $activeFilters;

	/**
	 * Form instance containing the search tools filter form
	 *
	 * @var   Form
	 * @note  Must be public to be accessed from the search tools layout
	 */
	public $filterForm;

	/**
	 * The items to display
	 *
	 * @var  array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  Pagination
	 */
	protected $pagination;

	/**
	 * The state information
	 *
	 * @var  CMSObject
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @see     HtmlView::loadTemplate()
	 * @throws  Exception
	 */
	public function display($tpl = null)
	{
		// Initialise variables
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(Text::_('COM_GHMARKDOWNDISPLAY_VIEW_SECTIONS'), 'file-2');

		JToolbarHelper::addNew('section.add');
		JToolbarHelper::editList('section.edit');

		JToolbarHelper::publish('sections.publish', 'JTOOLBAR_PUBLISH', true);
		JToolbarHelper::unpublish('sections.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		JToolbarHelper::checkin('sections.checkin');

		if ($this->state->get('filter.published') == -2)
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'sections.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		else
		{
			JToolbarHelper::trash('sections.trash');
		}

		if (Factory::getUser()->authorise('core.admin', 'com_ghmarkdowndisplay'))
		{
			JToolbarHelper::preferences('com_ghmarkdowndisplay');
		}
	}
}
