<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/table.php';

/**
 * Code tracker issue status table object.
 */
class CodeTableTrackerStatus extends CodeTable
{
	/**
	 * {@inheritdoc}
	 */
	protected $_legacyLookup = 'jc_status_id';

	/**
	 * Class constructor.
	 *
	 * @param	JDatabaseDriver  $db  A database connector object.
	 */
	public function __construct($db)
	{
		parent::__construct('#__code_tracker_status', 'status_id', $db);
	}
}
