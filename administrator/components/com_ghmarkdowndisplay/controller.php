<?php
/**
 * @package     Joomla.DeveloperNetwork
 * @subpackage  com_ghmarkdowndisplay
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

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
	protected $default_view = 'repositories';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  $this
	 */
	public function display($cachable = false, $urlparams = array())
	{
		JLoader::register('GHMarkdownDisplayHelper', __DIR__ . '/helpers/ghmarkdowndisplay.php');

		// Load the submenu.
		GHMarkdownDisplayHelper::addSubmenu($this->input->get('view', $this->default_view));

		return parent::display();
	}
}
