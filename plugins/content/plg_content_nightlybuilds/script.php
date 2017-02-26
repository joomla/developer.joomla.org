<?php
/**
 * Joomla! Nightly Builds
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

/**
 * Installation class to perform additional changes during install/uninstall/update
 *
 * @since  1.0
 */
class PlgContentNightlyBuildsScript extends JInstallerScript
{
	/**
	 * Extension script constructor.
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		$this->minimumJoomla = '3.6';
		$this->minimumPhp    = '5.6';
	}
}
