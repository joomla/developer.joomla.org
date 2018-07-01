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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

// Access check.
if (!Factory::getUser()->authorise('core.manage', 'com_ghmarkdowndisplay'))
{
	return JError::raiseWarning(404, Text::_('JERROR_ALERTNOAUTHOR'));
}

$controller = BaseController::getInstance('GHMarkdownDisplay');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
