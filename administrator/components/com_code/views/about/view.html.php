<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * View to show the about screen.
 */
class CodeViewAbout extends HtmlView
{
	/**
	 * The necessary HTML to display the sidebar
	 *
	 * @var  string
	 */
	protected $sidebar;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		CodeHelper::addSubmenu('about');

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 */
	protected function addToolbar()
	{
		$canDo = CodeHelper::getActions('com_code');

		JToolbarHelper::title(Text::_('COM_CODE_ABOUT_TITLE'), 'code');

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::preferences('com_code');
		}
	}
}
