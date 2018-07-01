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
 * Table interface class for the #__ghmarkdowndisplay_documents table
 *
 * @property   integer  $id                Item ID (primary key)
 * @property   integer  $section_id        Pseudo-foreign key to the #__ghmarkdowndisplay_sections table
 * @property   string   $name              The name of the document
 * @property   string   $alias             The alias of the document
 * @property   string   $file              The path to the file within the repository
 * @property   integer  $ordering          The item order
 * @property   integer  $published         The publishing state of the item
 * @property   integer  $checked_out       User ID who has checked out the item for editing
 * @property   string   $checked_out_time  The time the item was checked out for editing
 */
class GHMarkdownDisplayTableDocument extends Table
{
	/**
	 * The class constructor.
	 *
	 * @param   JDatabaseDriver  $db  JDatabaseDriver connector object.
	 */
	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__ghmarkdowndisplay_documents', 'id', $db);
	}
}
