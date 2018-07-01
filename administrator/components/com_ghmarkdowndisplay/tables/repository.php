<?php
/**
 * @package     Joomla.DeveloperNetwork
 * @subpackage  com_ghmarkdowndisplay
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

/**
 * Table interface class for the #__ghmarkdowndisplay_repositories table
 *
 * @property   integer  $id                Item ID (primary key)
 * @property   string   $name              The name of the repository
 * @property   string   $repository_owner  The GitHub account which is the owner of the repository
 * @property   string   $repository_name   The GitHub repository name
 * @property   string   $repository_path   The path within the repository which contains the documentation
 * @property   integer  $published         The publishing state of the item
 * @property   integer  $checked_out       User ID who has checked out the item for editing
 * @property   string   $checked_out_time  The time the item was checked out for editing
 */
class GHMarkdownDisplayTableRepository extends Table
{
	/**
	 * The class constructor.
	 *
	 * @param   JDatabaseDriver  $db  JDatabaseDriver connector object.
	 */
	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__ghmarkdowndisplay_repositories', 'id', $db);
	}
}
