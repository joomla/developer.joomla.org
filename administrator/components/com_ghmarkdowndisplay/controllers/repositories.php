<?php
/**
 * @package     Joomla.DeveloperNetwork
 * @subpackage  com_ghmarkdowndisplay
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Repositories management controller.
 */
class GHMarkdownDisplayControllerRepositories extends AdminController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var  string
	 */
	protected $text_prefix = 'COM_GHMARKDOWNDISPLAY_REPOSITORIES';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  BaseDatabaseModel|boolean  Model object on success; otherwise false on failure.
	 */
	public function getModel($name = 'Repository', $prefix = 'GHMarkdownDisplayModel', $config = ['ignore_request' => true])
	{
		return parent::getModel($name, $prefix, $config);
	}
}
