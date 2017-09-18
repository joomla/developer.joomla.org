<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Utilities\ArrayHelper;

// Include the GForge connector classes.
JLoader::register(GForge::class, JPATH_COMPONENT . '/helpers/gforge.php');
JLoader::register(GForgeLegacy::class, JPATH_COMPONENT . '/helpers/gforgelegacy.php');

/**
 * Tracker Synchronization Model for Joomla Code
 */
class CodeModelTrackerSync extends BaseDatabaseModel
{
	/**
	 * The GForge SOAP connector object.
	 *
	 * @var  GForge
	 */
	protected $gforge;

	/**
	 * The GForge legacy SOAP connector object.
	 *
	 * @var  GForgeLegacy
	 */
	protected $gforgeLegacy;

	/**
	 * Associative array of tracker fields.
	 *
	 * @var  array
	 */
	protected $fields = array();

	/**
	 * Associative array of tracker field data values.
	 *
	 * @var  array
	 */
	protected $fieldValues = array();

	/**
	 * Associative array of processing statistics
	 *
	 * @var  array
	 */
	protected $processingTotals = array();

	/**
	 * Date object with the time the script started
	 *
	 * @var  JDate
	 */
	protected $startTime;

	/**
	 * Synchronize the data from Joomlacode
	 *
	 * @return  bool  True on success
	 */
	public function sync()
	{
		// Initialize the logger
		$options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
		$options['text_file'] = 'gforge_sync.php';
		Log::addLogger($options, Log::INFO);
		Log::add('Starting the GForge Sync', Log::INFO);

		// Log the start time
		$this->startTime = Factory::getDate();

		// Initialize variables.
		$username = Factory::getConfig()->get('gforgeLogin');
		$password = Factory::getConfig()->get('gforgePassword');
		$project  = 5; // Joomla project id.

		// Wrap the processing in try/catch to log errors
		try
		{
			// Connect to the main SOAP interface.
			$this->gforge = new GForge('http://joomlacode.org/gf');
			$this->gforge->login($username, $password);

			// Connect to the legacy SOAP interface.
			$this->gforgeLegacy = new GForgeLegacy('http://joomlacode.org/gf');
			$this->gforgeLegacy->login($username, $password);

			// Get the tracker data from the SOAP interface.
			$trackers = $this->gforge->getProjectTrackers($project, true);

			if (empty($trackers))
			{
				throw new RuntimeException('Unable to get trackers from the server.');
			}

			// Sync each tracker.
			$trackers = array_reverse($trackers);

			foreach ($trackers as $tracker)
			{
				$currentTrackers = array(8103);

				if (in_array($tracker->tracker_id, $currentTrackers))
				{
					$this->populateTrackerFields($tracker->tracker_id);
					$this->syncTracker($tracker);
				}
			}
		}
		catch (RuntimeException $e)
		{
			Log::add('An error occurred during the sync: ' . $e->getMessage(), Log::INFO);

			$this->sendEmail();

			return false;
		}

		return true;
	}

	/**
	 * Send e-mail notification to users specified in the component config
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	private function sendEmail()
	{
		// Get data
		$config    = Factory::getConfig();
		$mailer    = Factory::getMailer();
		$params    = ComponentHelper::getParams('com_code');
		$addresses = $params->get('email_error', '');

		// Build the message
		$message = sprintf(
			'The sync cron job on developer.joomla.org started at %s failed to complete properly.  Please check the logs for further details.',
			(string) $this->startTime
		);

		// Make sure we have e-mail addresses in the config
		if (strlen($addresses) < 2)
		{
			return;
		}

		$addresses = explode(',', $addresses);

		// Send a message to each user
		foreach ($addresses as $address)
		{
			if (!$mailer->sendMail($config->get('mailfrom'), $config->get('fromname'), $address, 'JoomlaCode Sync Error', $message))
			{
				Log::add(sprintf('An error occurred sending the notification e-mail to %s.  Error: %s', $address, $e->getMessage()), Log::INFO);

				continue;
			}
		}
	}

	/**
	 * Synchronize the given tracker
	 *
	 * @param   object  $tracker  Tracker data object
	 *
	 * @return  boolean
	 */
	private function syncTracker($tracker)
	{
		// Prepare the processing totals for this tracker
		$this->processingTotals = array('issues' => 0, 'changes' => 0, 'messages' => 0, 'users' => 0);

		// Get a tracker table object.
		/** @var CodeTableTracker $table */
		$table = $this->getTable('Tracker', 'CodeTable');

		// Load any existing data by legacy id.
		$table->loadByLegacyId($tracker->tracker_id);

		$data = array();

		// If the tracker ID is null, assume we're inserting a new record
		if ($table->tracker_id === null)
		{
			$data = array(
				'jc_tracker_id' => $tracker->tracker_id,
				'title'         => $tracker->tracker_name,
				'description'   => $tracker->description
			);
		}

		// Populate the appropriate fields from the server data object.
		$data['item_count'] = $tracker->item_total;

		// Bind the data to the tracker object.
		$table->bind($data);

		// Attempt to store the tracker data.
		if (!$table->store())
		{
			throw new RuntimeException($table->getError());
		}

		// Get the tracker item data from the SOAP interface.
		$items = $this->gforge->getTrackerItems($tracker->tracker_id);

		if (empty($items))
		{
			throw new RuntimeException('Unable to get tracker items from the server for tracker: ' . $tracker->summary);
		}

		// Date for testing whether to sync or not
		$cutoffDate = new DateTime;
		$cutoffDate->sub(new DateInterval('P1Y'));

		$totalCount     = count($items);
		$skippedCount   = 0;
		$processedCount = 0;
		$erroredItems   = array();

		// Sync each tracker item.
		foreach ($items as $item)
		{
			// Don't fail the entire operation over one bad item
			try
			{
				$this->syncTrackerItem($item, $tracker->tracker_id, $table->tracker_id);

				$processedCount++;
			}
			catch (RuntimeException $e)
			{
				$erroredItems[] = $item->tracker_item_id;
				Log::add('An error occurred processing item ' . $item->tracker_item_id . ': ' . $e->getMessage());
			}
		}

		Log::add('Tracker: ' . $tracker->tracker_id . '; Skipped: ' . $skippedCount . ';  Processed issues: ' . $processedCount . ';  Total: ' . $totalCount);
		Log::add('Issues: ' . $this->processingTotals['issues'] . ';  Changes: ' . $this->processingTotals['changes'] . ';  Users: ' . $this->processingTotals['users'] . ' ;');

		if (count($erroredItems))
		{
			Log::add('Errored Items: ' . implode(', ', $erroredItems));
		}

		return true;
	}

	/**
	 * Synchronize the requested issue from a tracker
	 *
	 * @param   integer  $issueId    Issue ID to synchronize
	 * @param   integer  $trackerId  Tracker ID the issue is assigned to
	 *
	 * @return  boolean
	 */
	public function syncIssue($issueId, $trackerId)
	{
		// Initialize the logger
		$options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
		$options['text_file'] = 'gforge_sync.php';
		Log::addLogger($options, Log::INFO);
		Log::add('Starting the GForge Sync for item ' . $issueId . ' from tracker ' . $trackerId, Log::INFO);

		// Log the start time
		$this->startTime = Factory::getDate();

		// Initialize variables.
		$username = Factory::getConfig()->get('gforgeLogin');
		$password = Factory::getConfig()->get('gforgePassword');

		// Connect to the main SOAP interface.
		$this->gforge = new GForge('http://joomlacode.org/gf');
		$this->gforge->login($username, $password);

		// Connect to the legacy SOAP interface.
		$this->gforgeLegacy = new GForgeLegacy('http://joomlacode.org/gf');
		$this->gforgeLegacy->login($username, $password);

		// Get the tracker from the GForge server.
		$tracker = $this->gforge->getTracker($trackerId);

		// If a tracker wasn't found return false.
		if (!is_object($tracker))
		{
			Log::add('Unable to get tracker from the server.');

			return false;
		}

		// Synchronize the tracker fields.
		$this->populateTrackerFields($tracker->tracker_id);

		// Get a tracker table object.
		/** @var CodeTableTracker $table */
		$table = $this->getTable('Tracker', 'CodeTable');

		// Load any existing data by legacy id.
		$table->loadByLegacyId($tracker->tracker_id);

		$data = array();

		// If the tracker ID is null, assume we're inserting a new record
		if ($table->tracker_id === null)
		{
			$data = array(
				'jc_tracker_id' => $tracker->tracker_id,
				'title'         => $tracker->tracker_name,
				'description'   => $tracker->description
			);
		}

		// Populate the appropriate fields from the server data object.
		$data['item_count'] = $tracker->item_total;

		// Bind the data to the tracker object.
		$table->bind($data);

		// Attempt to store the tracker data.
		if (!$table->store())
		{
			Log::add($table->getError());

			return false;
		}

		// Create a stub item until the script queries the full tracker item
		$item = (object) array('tracker_item_id' => $issueId);

		// Don't fail the entire operation over one bad item
		try
		{
			return $this->syncTrackerItem($item, $trackerId, $table->tracker_id);
		}
		catch (RuntimeException $e)
		{
			Log::add('An error occurred processing item ' . $issueId . ': ' . $e->getMessage());

			return false;
		}
	}

	/**
	 * @param   object   $item             The tracker item to update
	 * @param   integer  $legacyTrackerId  The legacy tracker ID
	 * @param   integer  $trackerId        The system's tracker ID
	 *
	 * @return  bool
	 */
	private function syncTrackerItem($item, $legacyTrackerId, $trackerId)
	{
		// Get the database object
		$db = $this->getDbo();

		// Build the query to see if the item already exists.
		$db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName(array('issue_id', 'modified_date', 'status')))
				->from($db->quoteName('#__code_tracker_issues'))
				->where($db->quoteName('jc_issue_id') . ' = ' . (int) $item->tracker_item_id)
		);

		// Execute the query to find out if the item exists.
		$exists = $db->loadObject();

		// Get full data on the tracker item from the GForge server.
		$item = $this->gforge->getTrackerItem($item->tracker_item_id);

		// If a tracker item wasn't found return false.
		if (!is_object($item))
		{
			throw new RuntimeException('Failed to retrieve tracker item ' . $item->tracker_item_id . ' from the remote server.');
		}

		// No need to process an issue that hasn't changed.
		if (!empty($exists->status) && !empty($exists->issue_id) && ($exists->modified_date == $item->last_modified_date))
		{
			return true;
		}

		// Get accessory data on the tracker item from the GForge server.
		$changes = $this->gforge->getTrackerItemChanges($item->tracker_item_id);

		/*
		 * Synchronize all users relevant to the tracker item.
		 */

		// Get a list of all of the user ids to look up.
		$usersToLookUp = array($item->submitted_by, $item->last_modified_by);

		// Add each user ID that submitted a response to the list.
		foreach ($item->messages as $message)
		{
			$usersToLookUp[] = $message->submitted_by;
		}

		// Add each user ID that committed a code change to the list.
		foreach ($item->scm_commits as $commit)
		{
			$usersToLookUp[] = $commit->user_id;
		}

		// Add each user ID that is assigned to the list.
		foreach ($item->assignees as $assignee)
		{
			$usersToLookUp[] = $assignee->assignee;
		}

		// Add each user ID that made a change to the list.
		foreach ($changes as $change)
		{
			$usersToLookUp[] = $change->user_id;
		}

		// Remove any duplicates.
		$usersToLookUp = array_values(array_unique($usersToLookUp));

		// Get rid of user id 0
		sort($usersToLookUp);

		if ($usersToLookUp[0] == 0)
		{
			array_shift($usersToLookUp);
		}

		// Get the syncronized user ids.
		$users = $this->syncUsers($usersToLookUp);

		/*
		 * Synchronize the tracker issue table.
		 */

		// Get a tracker issue table object.
		/** @var CodeTableTrackerIssue $table */
		$table = $this->getTable('TrackerIssue', 'CodeTable');

		// Load any existing data by legacy id.
		$table->loadByLegacyId($item->tracker_item_id);

		// Populate the appropriate fields from the server data object.
		$data = array(
			'tracker_id'     => $legacyTrackerId,
			'priority'       => $item->priority,
			'created_date'   => $item->open_date,
			'created_by'     => $users[$item->submitted_by],
			'modified_date'  => $item->last_modified_date,
			'modified_by'    => @$users[$item->last_modified_by],
			'close_date'     => $item->close_date,
			'title'          => $item->summary,
			'description'    => $item->details,
			'jc_issue_id'    => $item->tracker_item_id,
			'jc_created_by'  => $item->submitted_by,
			'jc_modified_by' => $item->last_modified_by
		);

		// Only populate the close by data if necessary.
		if ($item->close_date && @$users[$item->last_modified_by])
		{
			$data['close_by']    = $users[$item->last_modified_by];
			$data['jc_close_by'] = $item->last_modified_by;
		}

		if (!isset($item->close_date))
		{
			$data['close_date'] = $db->getNullDate();
		}

		// Bind the data to the issue object.
		$table->bind($data);

		// Attempt to store the issue data.
		if (!$table->store(true))
		{
			throw new RuntimeException($table->getError());
		}

		$this->processingTotals['issues']++;

		// Synchronize the messages associated with the tracker item.
		if (is_array($item->messages))
		{
			if (!$this->syncTrackerItemMessages($item->messages, $users, $table->issue_id, $table->tracker_id, $table->jc_issue_id))
			{
				return false;
			}
		}

		// Synchronize the changes associated with the tracker item.
		if (is_array($changes))
		{
			if (!$this->syncTrackerItemChanges($changes, $users, $table->issue_id, $table->tracker_id, $table->jc_issue_id))
			{
				return false;
			}
		}

		// Synchronize the commits associated with the tracker item.
		if (is_array($item->scm_commits))
		{
			if (!$this->syncTrackerItemCommits($item->scm_commits, $users, $table->issue_id, $table->tracker_id, $table->jc_issue_id))
			{
				return false;
			}
		}

		// Synchronize the extra fields for the tracker item.
		if (is_array($item->extra_field_data))
		{
			if (!$this->syncTrackerItemExtraFields($item->extra_field_data, $table->issue_id, $table->jc_issue_id))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Synchronize a tracker item's extra fields
	 *
	 * @param   array    $fieldValues  Array of field data
	 * @param   integer  $issueId      Issue ID
	 * @param   integer  $jcIssueId    JoomlaCode Issue ID
	 *
	 * @return  boolean  True on success
	 */
	private function syncTrackerItemExtraFields($fieldValues, $issueId, $jcIssueId)
	{
		// Some GForge tracker fields we don't care about as far as tags are concerned.
		$ignore = array(
			'duration',
			'percentcomplete',
			'estimatedeffort',
			'build'
		);

		// Get the list of relevant tags.
		$db   = $this->getDbo();
		$tags = array();

		foreach ($fieldValues as $value)
		{
			// Ignore some fields we don't care about.
			if (in_array($this->fields[$value->tracker_extra_field_id]['alias'], $ignore))
			{
				continue;
			}
			// Special case for status.
			elseif ($this->fields[$value->tracker_extra_field_id]['alias'] == 'status')
			{
				// Make sure we have a status for it.
				if (isset($this->fieldValues[$value->field_data]) && isset($this->fieldValues[$value->field_data]['value_id']))
				{
					// Set the status value/name for the issue.
					$db->setQuery(
						$db->getQuery(true)
							->update($db->quoteName('#__code_tracker_issues'))
							->set($db->quoteName('status') . ' = ' . (int) $this->fieldValues[$value->field_data]['value_id'])
							->where($db->quoteName('issue_id') . ' = ' . (int) $issueId)
					)->execute();
				}

				continue;
			}

			if (!empty($this->fieldValues[$value->field_data]))
			{
				$tags[] = $this->fieldValues[$value->field_data]['name'];
			}
		}

		// If there are no tags, move on.
		if (empty($tags))
		{
			return true;
		}

		// Make sure the tags we need are synced.
		if (!$tags = $this->syncTags($tags))
		{
			return false;
		}

		// Get the current tag maps for the issue.
		$db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName('tag_id'))
				->from($db->quoteName('#__code_tracker_issue_tag_map'))
				->where($db->quoteName('issue_id') . ' = ' . (int) $jcIssueId)
		);

		$existing = (array) $db->loadColumn();
		$existing = ArrayHelper::toInteger($existing);

		// Get the list of tag maps to add and delete.
		$add = array_diff(array_keys($tags), $existing);
		$del = array_diff($existing, array_keys($tags));

		// Delete the necessary tag maps.
		if (!empty($del))
		{
			$db->setQuery(
				$db->getQuery(true)
					->delete($db->quoteName('#__code_tracker_issue_tag_map'))
					->where($db->quoteName('issue_id') . ' = ' . (int) $jcIssueId)
					->where($db->quoteName('tag_id') . ' IN (' . implode(', ', $del) . ')')
			)->execute();
		}

		// Add the necessary tag maps.
		$query = $db->getQuery(true)
			->insert($db->quoteName('#__code_tracker_issue_tag_map'))
			->columns(array($db->quoteName('issue_id'), $db->quoteName('tag_id')));

		foreach ($add as $tag)
		{
			$query->values((int) $jcIssueId . ', ' . (int) $tag);
		}

		$db->setQuery($query)->execute();

		return true;
	}

	/**
	 * Synchronize a tracker item's changes
	 *
	 * @param   array    $commits          Array of commit data
	 * @param   array    $users            Array of user IDs
	 * @param   string   $issueId          Issue ID
	 * @param   integer  $trackerId        Tracker ID
	 * @param   integer  $legacyIssueId    Legacy issue ID
	 *
	 * @return  boolean  True on success
	 */
	private function syncTrackerItemCommits($commits, $users, $issueId, $trackerId, $legacyIssueId)
	{
		// Synchronize each commit.
		foreach ($commits as $commit)
		{
			// Get a tracker issue commit table object.
			/** @var CodeTableTrackerIssueCommit $table */
			$table = $this->getTable('TrackerIssueCommit', 'CodeTable');

			// Load any existing data by legacy id.
			$table->loadByLegacyId($commit->scm_commit_id);

			// Skip over rows that exist and haven't changed.
			if ($table->commit_id && $table->created_date == $commit->commit_date)
			{
				continue;
			}

			// Populate the appropriate fields from the server data object.
			$data = array(
				'issue_id'      => $issueId,
				'tracker_id'    => $trackerId,
				'created_date'  => $commit->commit_date,
				'created_by'    => $users[$commit->user_id],
				'message'       => $commit->message_log,
				'jc_commit_id'  => $commit->scm_commit_id,
				'jc_issue_id'   => $legacyIssueId,
				'jc_created_by' => $commit->user_id
			);

			// Bind the data to the object.
			$table->bind($data);

			// Attempt to store the data.
			if (!$table->store())
			{
				throw new RuntimeException($table->getError());
			}
		}

		return true;
	}

	/**
	 * Synchronize a tracker item's changes
	 *
	 * @param   array    $changes          Array of change data
	 * @param   array    $users            Array of user IDs
	 * @param   string   $issueId          Issue ID
	 * @param   integer  $trackerId        Tracker ID
	 * @param   integer  $legacyIssueId    Legacy issue ID
	 *
	 * @return  boolean  True on success
	 */
	private function syncTrackerItemChanges($changes, $users, $issueId, $trackerId, $legacyIssueId)
	{
		// Synchronize each change.
		foreach ($changes as $change)
		{
			// Ignore non-status changes for now.
			if ($change->field_name != 'status')
			{
				continue;
			}

			// Get a tracker issue change table object.
			/** @var CodeTableTrackerIssueChange $table */
			$table = $this->getTable('TrackerIssueChange', 'CodeTable');

			// Load any existing data by legacy id.
			$table->loadByLegacyId($change->audit_trail_id);

			// Skip over rows that exist and haven't changed.
			if ($table->change_id && $table->change_date == $change->change_date)
			{
				continue;
			}

			// Populate the appropriate fields from the server data object.
			$data = array(
				'issue_id'      => $issueId,
				'tracker_id'    => $trackerId,
				'change_date'   => $change->change_date,
				'change_by'     => $users[$change->user_id],
				'data'          => serialize($change),
				'jc_change_id'  => $change->audit_trail_id,
				'jc_issue_id'   => $legacyIssueId,
				'jc_change_by'  => $change->user_id
			);

			// Bind the data to the object.
			$table->bind($data);

			// Attempt to store the data.
			if (!$table->store())
			{
				throw new RuntimeException($table->getError());
			}

			$this->processingTotals['changes']++;
		}

		return true;
	}

	/**
	 * Synchronize a tracker item's messages
	 *
	 * @param   array    $messages         Array of message data
	 * @param   array    $users            Array of user IDs
	 * @param   string   $issueId          Issue ID
	 * @param   integer  $trackerId        Tracker ID
	 * @param   integer  $legacyIssueId    Legacy issue ID
	 *
	 * @return  boolean  True on success
	 */
	private function syncTrackerItemMessages($messages, $users, $issueId, $trackerId, $legacyIssueId)
	{
		// Synchronize each message.
		foreach ($messages as $message)
		{
			// Get a tracker issue response table object.
			/** @var CodeTableTrackerIssueResponse $table */
			$table = $this->getTable('TrackerIssueResponse', 'CodeTable');

			// Load any existing data by legacy id.
			$table->loadByLegacyId($message->tracker_item_message_id);

			// Skip over rows that exist and haven't changed.
			if ($table->response_id && $table->created_date == $message->adddate)
			{
				continue;
			}

			// Populate the appropriate fields from the server data object.
			$data = array(
				'issue_id'       => $issueId,
				'tracker_id'     => $trackerId,
				'created_date'   => $message->adddate,
				'created_by'     => $users[$message->submitted_by],
				'body'           => $message->body,
				'jc_response_id' => $message->tracker_item_message_id,
				'jc_issue_id'    => $legacyIssueId,
				'jc_created_by'  => $message->submitted_by
			);

			// Bind the data to the object.
			$table->bind($data);

			// Attempt to store the data.
			if (!$table->store())
			{
				throw new RuntimeException($table->getError());
			}

			$this->processingTotals['messages']++;
		}

		return true;
	}

	/**
	 * Method to make sure a set of tag values are syncronized with the local system.  This
	 * method will return an associative array of tag_id => tag values.
	 *
	 * @param   array  $values  An array of tag values to make sure exist in the local system.
	 *
	 * @return  array  An array of tag_id => tag values.
	 *
	 * @since   1.0
	 */
	private function syncTags($values)
	{
		// Initialize variables.
		$tags = array();
		$db   = $this->getDbo();

		// Build the query to see if the items already exist.
		$query = $db->getQuery(true)
			->select($db->quoteName(array('tag_id', 'tag')))
			->from($db->quoteName('#__code_tags'));

		foreach ($values as $k => $value)
		{
			$query->where($db->quoteName('tag') . ' = ' . $db->quote($value), 'OR');
		}

		$db->setQuery($query);

		// Execute the query to find out if the items exist.
		$exists = (array) $db->loadObjectList();

		// Build out the array of tags based on those that already exist.
		foreach ($exists as $exist)
		{
			$tags[(int) $exist->tag_id] = $exist->tag;
		}

		// Get the list of tags to store.
		$store = array_diff(array_values($values), array_values($tags));

		if (empty($store))
		{
			return $tags;
		}

		// Store the values.
		foreach ($store as $value)
		{
			// Insert the new tag.
			$db->setQuery(
				$db->getQuery(true)
					->insert($db->quoteName('#__code_tags'))
					->columns(array($db->quoteName('tag')))
					->values($db->quote($value))
			)->execute();

			$tags[(int) $db->insertid()] = $value;
		}

		return $tags;
	}

	/**
	 * Method to make sure a set of legacy user ids are syncronized with the GForge server.  This
	 * method will return an associative array of legacy => local user id values.
	 *
	 * @param   array  $ids  An array of legacy GForge user ids.
	 *
	 * @return  array  An array of legacy => local user ids.
	 *
	 * @since   1.0
	 */
	private function syncUsers($ids)
	{
		// Initialize variables.
		$db    = $this->getDbo();
		$users = array();

		// Ensure the ids are integers.
		$ids = ArrayHelper::toInteger($ids);

		// Build the query to see if the items already exist.
		$db->setQuery(
			$db->getQuery(true)
				->select($db->quoteName(array('user_id', 'jc_user_id')))
				->from($db->quoteName('#__code_users'))
				->where($db->quoteName('jc_user_id') . ' IN (' . implode(',', $ids) . ')')
		);

		// Execute the query to find out if the items exist.
		$exists = (array) $db->loadObjectList();

		// Build out the array of users based on those that already exist.
		foreach ($exists as $exist)
		{
			$users[$exist->jc_user_id] = (int) $exist->user_id;
		}

		// Get the list of user ids for user objects to extract data from the server.
		$get = array_diff($ids, array_keys($users));

		if (empty($get))
		{
			return $users;
		}

		// Get the list of user objects from the server.
		$got = $this->gforge->getUsersById($get);

		if (empty($got))
		{
			throw new RuntimeException('Unable to get users from the server.');
		}

		// Sync each user.
		foreach ($got as $user)
		{
			// Get a user table object.
			/** @var CodeTableUser $table */
			$table = $this->getTable('User', 'CodeTable');

			// Load any existing data by JC user ID
			$table->loadByLegacyId($user->user_id);

			// Populate the appropriate fields from the server data object.
			$data = array(
				'jc_user_id' => $user->user_id,
				'first_name' => $user->firstname,
				'last_name'  => $user->lastname,
				'username'   => $user->unix_name,
				'email'      => $user->email
			);

			// Bind the data to the user object.
			$table->bind($data);

			// Attempt to store the user data.
			if (!$table->store())
			{
				Log::add('Failed to store user ID ' . $user->user_id . ': ' . $table->getError());
			}

			$this->processingTotals['users']++;

			$users[$table->jc_user_id] = (int) $table->user_id;
		}

		return $users;
	}

	/**
	 * Method to populate the tracker field array
	 *
	 * @param   integer  $trackerId  The tracker ID to populate
	 *
	 * @return  void
	 */
	private function populateTrackerFields($trackerId)
	{
		$fields = $this->gforge->getTrackerFields($trackerId);

		foreach ($fields as $field)
		{
			if (empty($this->fields[$field->tracker_extra_field_id]))
			{
				$this->fields[$field->tracker_extra_field_id] = array(
					'field_id'   => $field->tracker_extra_field_id,
					'name'       => $field->field_name,
					'alias'      => $field->alias,
					'tracker_id' => $field->tracker_id
				);

				if ($field->alias == 'status')
				{
					$this->populateTrackerStatus($this->fields[$field->tracker_extra_field_id], $trackerId);
				}
			}

			$this->populateTrackerFieldValues($this->fields[$field->tracker_extra_field_id], $trackerId);
		}
	}

	/**
	 * Populates the status table with data for the specified tracker
	 *
	 * @param   array    $field            The status field data
	 * @param   integer  $legacyTrackerId  The tracker ID being updated
	 *
	 * @return  boolean  True on success
	 */
	private function populateTrackerStatus($field, $legacyTrackerId)
	{
		// Get a tracker table object.
		/** @var CodeTableTracker $table */
		$tracker = $this->getTable('Tracker', 'CodeTable');
		$tracker->loadByLegacyId($legacyTrackerId);

		$values = $this->gforge->getTrackerFieldValues($field['field_id']);

		foreach ($values as $value)
		{
			// Get a tracker status table object.
			/** @var CodeTableTrackerStatus $table */
			$table = $this->getTable('TrackerStatus', 'CodeTable');

			// Load any existing data by legacy id.
			$table->loadByLegacyId($value->element_id);

			// Skip over rows that exist and haven't changed.
			if ($table->status_id && ($table->title == $value->element_name) && ($table->state_id == $value->status_id))
			{
				continue;
			}

			// Populate the appropriate fields from the server data object.
			$data = array(
				'tracker_id'    => $legacyTrackerId,
				'state_id'      => $value->status_id,
				'title'         => $value->element_name,
				'jc_status_id'  => $value->element_id
			);

			// Bind the data to the object.
			$table->bind($data);

			// Attempt to store the data.
			if (!$table->store())
			{
				throw new RuntimeException($table->getError());
			}
		}

		return true;
	}

	/**
	 * Method to populate the field data array
	 *
	 * @param   array  $field  The field data to populate
	 *
	 * @return  void
	 */
	private function populateTrackerFieldValues($field)
	{
		$values = $this->gforge->getTrackerFieldValues($field['field_id']);

		foreach ($values as $value)
		{
			if (empty($this->fieldValues[$value->element_id]))
			{
				$this->fieldValues[$value->element_id] = array(
					'value_id' => $value->element_id,
					'field_id' => $value->tracker_extra_field_id,
					'name'     => $value->element_name
				);
			}
		}
	}
}
