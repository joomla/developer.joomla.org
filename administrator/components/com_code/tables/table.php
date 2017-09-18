<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

/**
 * Code component base table object.
 */
class CodeTable extends Table
{
	/**
	 * Column name for the legacy lookup
	 *
	 * @var  string
	 */
	protected $_legacyLookup;

	/**
	 * Method to load a data object by its legacy ID
	 *
	 * @param   integer  $legacyId  The user ID to load
	 *
	 * @return  boolean  True on success
	 */
	public function loadByLegacyId($legacyId)
	{
		$db = $this->getDbo();

		// Look up the user id based on the legacy id.
		$db->setQuery(
			$db->getQuery(true)
				->select($this->_tbl_key)
				->from($this->_tbl)
				->where($this->_legacyLookup . ' = ' . (int) $legacyId)
		);

		$itemId = (int) $db->loadResult();

		if ($itemId)
		{
			return $this->load($itemId);
		}

		return false;
	}
}
