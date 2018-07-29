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
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Component base controller.
 */
class GHMarkdownDisplayController extends BaseController
{
	/**
	 * The default view.
	 *
	 * @var  string
	 */
	protected $default_view = 'document';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  $this
	 */
	public function display($cachable = false, $urlparams = [])
	{
		// Set the default view name and format from the Request.
		$vName = $this->input->get('view', $this->default_view);
		$this->input->set('view', $vName);

		/*
		 * Handle a case where the document view is loaded with a repository ID but no item ID,
		 * generally this should be from a menu item and this will prevent duplicate URLs.
		 */
		if ($vName === 'document' && $this->input->getUint('id') === null)
		{
			/** @var GHMarkdownDisplayModelDocument $model */
			$model = $this->getModel('Document');
			$item  = $model->getItem();

			// If no item is given, the view will handle errors
			if ($item !== false)
			{
				// Redirect to the canonical document URL, use the application to specify 301 since the controller API doesn't support this
				Factory::getApplication()->redirect(
					Route::_('index.php?option=com_ghmarkdowndisplay&view=document&id=' . $item->id . '&repository=' . $item->repository_id),
					301
				);

				// Generally not needed since redirect shuts down the process, but ensures the below code never runs
				return $this;
			}
		}

		$cachable = !Factory::getUser()->guest;

		$urlparams = [
			'id'         => 'UINT',
			'repository' => 'UINT',
			'print'      => 'BOOLEAN',
			'lang'       => 'CMD',
			'Itemid'     => 'UINT',
		];

		return parent::display($cachable, $urlparams);
	}
}
