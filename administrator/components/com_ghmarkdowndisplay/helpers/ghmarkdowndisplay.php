<?php
/**
 * @package     Joomla.DeveloperNetwork
 * @subpackage  com_ghmarkdowndisplay
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;

/**
 * Component helper.
 */
class GHMarkdownDisplayHelper extends ContentHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			Text::_('COM_GHMARKDOWNDISPLAY_SUBMENU_REPOSITORIES'),
			'index.php?option=com_ghmarkdowndisplay&view=repositories',
			$vName === 'repositories'
		);

		JHtmlSidebar::addEntry(
			Text::_('COM_GHMARKDOWNDISPLAY_SUBMENU_SECTIONS'),
			'index.php?option=com_ghmarkdowndisplay&view=sections',
			$vName === 'sections'
		);
	}
}
