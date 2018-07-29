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

/**
 * Repository view class
 */
class GHMarkdownDisplayViewRepository extends HtmlView
{
	/**
	 * The form object
	 *
	 * @var  Form
	 */
	protected $form;

	/**
	 * The item record
	 *
	 * @var  CMSObject
	 */
	protected $item;

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
		// Initialise variables.
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');
		$this->form  = $this->get('Form');

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
		Factory::getApplication('administrator')->input->set('hidemainmenu', true);

		$user       = Factory::getUser();
		$userId     = $user->id;
		$isNew      = $this->item->id == 0;
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

		JToolbarHelper::title(
			Text::_('COM_GHMARKDOWNDISPLAY_VIEW_REPOSITORY_' . ($checkedOut ? 'VIEW_REPOSITORY' : ($isNew ? 'ADD_REPOSITORY' : 'EDIT_REPOSITORY'))),
			'file-2'
		);

		if ($isNew)
		{
			JToolbarHelper::apply('repository.apply');
			JToolbarHelper::save('repository.save');
			JToolbarHelper::save2new('repository.save2new');
			JToolbarHelper::cancel('repository.cancel');
		}
		else
		{
			if (!$checkedOut)
			{
				JToolbarHelper::apply('repository.apply');
				JToolbarHelper::save('repository.save');
				JToolbarHelper::save2new('repository.save2new');
			}

			JToolbarHelper::save2copy('repository.save2copy');
			JToolbarHelper::cancel('repository.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
