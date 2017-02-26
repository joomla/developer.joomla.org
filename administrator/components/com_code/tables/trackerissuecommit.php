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
 * Code tracker issue commit table object.
 */
class CodeTableTrackerIssueCommit extends CodeTable
{
	/**
	 * {@inheritdoc}
	 */
	protected $_legacyLookup = 'jc_commit_id';

	/**
	 * Class constructor.
	 *
	 * @param	JDatabaseDriver  $db  A database connector object.
	 */
	public function __construct($db)
	{
		parent::__construct('#__code_tracker_issue_commits', 'commit_id', $db);
	}
}
