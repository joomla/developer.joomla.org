<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Make sure our trait is loaded
require_once __DIR__ . '/gforgetrait.php';

/**
 * Connector class to a GForge Advanced Server SOAP API.
 *
 * @see  http://joomlacode.org/gf/xmlcompatibility/soap5/
 */
class GForge
{
	use GForgeTrait;

	/**
	 * The URI for the API
	 *
	 * @var  string
	 */
	protected $apiUri = '/xmlcompatibility/soap5/?wsdl';

	/**
	 * Object constructor.  Creates the connection to the GForge site instance.
	 *
	 * @param   string  $site     The URL to the gforge instance.
	 * @param   array   $options  The SOAP options for the connection.
	 *
	 * @throws  RuntimeException
	 */
	public function __construct($site, $options = array())
	{
		// Attempt to connect to the SOAP gateway.
		$this->client = new SoapClient($site . $this->apiUri, $options);

		// Check for an error.
		if (!$this->client)
		{
			throw new RuntimeException('Unable to connect to GForge instance at ' . $site);
		}
	}

	/**
	 * Object destructor.  Signs out and closes the connection.
	 */
	public function __destruct()
	{
		// Check to see if the connection is live.
		if ($this->client)
		{
			// Check to see if we are signed in.
			if ($this->sessionhash)
			{
				$this->logout();
			}

			// Kill the connection.
			unset($this->client);
		}
	}

	/**
	 * Method to get user data by username.
	 *
	 * @param   array  $ids  The optional user IDs to get user data for, defaults to the user
	 *                       signed into the current session.
	 *
	 * @return  object  User data object on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getUsersById($ids = array())
	{
		try
		{
			// Attempt to get the user object by the username or "unix name" in GForge speak.
			return $this->client->getUserArray($this->sessionhash, $ids);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Failed to get users (' . implode(',', $ids) . '): ' . $e->faultstring);
		}
	}

	/**
	 * Method to get the projects a user belongs to by username.
	 *
	 * @param   string  $username  The optional username to get the project list for, defaults to
	 *                             the user signed into the current session.
	 *
	 * @return  object  Project data array on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getUserProjects($username = null)
	{
		try
		{
			// Attempt to get the project data array by the username or "unix name" in GForge speak.
			return $this->client->getUserProjects($this->sessionhash, $username ? $username : $this->username);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get projects for user ' . ($username ? $username : $this->username) . ': ' . $e->faultstring);
		}
	}

	/**
	 * Method to get a project object by name.
	 *
	 * @param   string  $name  The name of the project for which to get the data object.
	 *
	 * @return  object  Project data object on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getProject($name)
	{
		try
		{
			// Attempt to get the project data object by the name or "unix name" in GForge speak.
			return $this->client->getProjectByUnixName($this->sessionhash, $name);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get project ' . $name . ': ' . $e->faultstring);
		}
	}

	/**
	 * Method to get a project members by project id.
	 *
	 * @param   integer  $projectId  The project id.
	 *
	 * @return  array  Project members data array on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getProjectMembers($projectId)
	{
		try
		{
			return $this->client->getProjectMembers($this->sessionhash, $projectId);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get members : ' . $e->faultstring);
		}
	}


	/**
	 * Method to get a project object by id.
	 *
	 * @param   integer  $id  The name of the project for which to get the data object.
	 *
	 * @return  object  Project data object on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getProjectById($id)
	{
		try
		{
			// Attempt to get the project data object by the ID.
			return $this->client->getProject($this->sessionhash, $id);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get project ' . $name . ': ' . $e->faultstring);
		}
	}

	/**
	 * Method to get an array of file Systems by section and ref_id
	 * Section and ref_id are parts of download url : download/{section}/{ref_id}
	 *
	 * @param   string   $section  The section name.
	 * @param   integer  $fileId  The file id.
	 *
	 * @return  array  File systems data array on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getFilesystems($section, $refId)
	{
		try
		{
			return $this->client->getFilesystems($this->sessionhash, $section, $refId);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get file systems for section ' . $section . ' and ref id ' . $refId . ' : ' . $e->faultstring);
		}
	}

	/**
	 * Method to get the project trackers by project name or id.
	 *
	 * @param   mixed    $project   Either the project name or numeric id for the project to get a list of tracker data objects.
	 * @param   boolean  $isPublic  Flag to return public or private trackers
	 *
	 * @return  object  Tracker data array on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getProjectTrackers($project, $isPublic = true)
	{
		// If a project name was given go find the project id based on the name.
		if (!is_numeric($project))
		{
			// Attempt to get the project object from the name.
			$project = $this->getProject($project);

			// Assign the project id based on the returned project or return false if not found.
			if (!$project)
			{
				return false;
			}

			$projectId = $project->project_id;
		}
		// Easy peasy...
		else
		{
			$projectId = $project;
		}

		try
		{
			// Attempt to get the project tracker array by the project id.
			return $this->client->getTrackers($this->sessionhash, $projectId, $isPublic, -1);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get trackers for project ' . $project . ': ' . $e->faultstring);
		}
	}

	/**
	 * Method to get a tracker object by id.
	 *
	 * @param   integer  $trackerId  The tracker id for which to get the data object.
	 *
	 * @return  object  Tracker data object on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getTracker($trackerId)
	{
		try
		{
			// Attempt to get the tracker data object by id.
			return $this->client->getTracker($this->sessionhash, $trackerId);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get tracker ' . $trackerId . ': ' . $e->faultstring);
		}
	}

	/**
	 * Method to get a list of tracker fields from a specific tracker by tracker id.
	 *
	 * @param   integer  $trackerId  The numeric id of the tracker for which to get a list of fields.
	 *
	 * @return  array  Tracker field data array on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getTrackerFields($trackerId)
	{
		try
		{
			// Attempt to get a list of tracker field data by tracker id.
			return $this->client->getTrackerExtraFields($this->sessionhash, $trackerId, -1);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get fields for tracker ' . $trackerId . ': ' . $e->faultstring);
		}
	}

	/**
	 * Method to get a list of tracker field values from a specific field by field id.
	 *
	 * @param   integer  $fieldId  The numeric id of the field for which to get a list of values.
	 *
	 * @return  array  Tracker field value data array on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getTrackerFieldValues($fieldId)
	{
		try
		{
			// Attempt to get a list of tracker field values by field id.
			return $this->client->getTrackerExtraFieldElements($this->sessionhash, $fieldId, '', -1, -1);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get values for tracker field ' . $fieldId . ': ' . $e->faultstring);
		}
	}

	/**
	 * Method to get a list of tracker items from a specific tracker by tracker id.
	 *
	 * @param   integer  $trackerId  The numeric id of the tracker for which to get a list of items.
	 *
	 * @return  array  Tracker item data array on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getTrackerItems($trackerId)
	{
		try
		{
			// Attempt to get a list of tracker item data by tracker id.
			// Get in batches to avoid errors
			$increment = 1000;
			$limit     = 1 + (int) (20000 / $increment);
			$itemArray = array();
			$items     = array();

			for ($i = 0; $i < 20; $i++)
			{
				$start         = $i * $increment;
				$itemArray[$i] = $this->client->getTrackerItemsShort($this->sessionhash, $trackerId, -1, -1, $increment, $start);
				$items         = array_merge($items, $itemArray[$i]);
			}

			return $items;
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get tracker items for tracker ' . $trackerId . ': ' . $e->faultstring);
		}
	}

	/**
	 * Method to get a tracker item object by id.
	 *
	 * @param   integer  $itemId  The tracker item id for which to get the data object.
	 *
	 * @return  object  Tracker item data object on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getTrackerItem($itemId)
	{
		try
		{
			// Attempt to get the item data object by item id.
			return $this->client->getTrackerItemFull($this->sessionhash, $itemId);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get tracker item ' . $itemId . ': ' . $e->faultstring);
		}
	}

	/**
	 * Method to get an array of tracker item changes by id.
	 *
	 * @param   integer  $itemId  The tracker item id for which to get the changes array.
	 *
	 * @return  array  Tracker item changes data array on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getTrackerItemChanges($itemId)
	{
		try
		{
			// Attempt to get the changes data array by the tracker item id.
			return $this->client->getAuditTrails($this->sessionhash, $itemId);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get changes for tracker item ' . $itemId . ': ' . $e->faultstring);
		}
	}

	/**
	 * Method to get an array of tracker item messages by id.
	 *
	 * @param   integer  $itemId  The tracker item id for which to get the messages array.
	 *
	 * @return  array  Tracker item messages data array on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getTrackerItemMessages($itemId)
	{
		try
		{
			// Attempt to get the messages data array by the tracker item id.
			return $this->client->getTrackerItemMessages($this->sessionhash, $itemId, -1);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get messages for tracker item ' . $itemId . ': ' . $e->faultstring);
		}
	}

	/**
	 * Method to get an array of Docman folders by project Id
	 *
	 * @param   integer  $projectId  The project id.
	 *
	 * @return  array  Docman folders data array on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getDocmanFolders($projectId)
	{
		try
		{
			return $this->client->getDocmanFolders($this->sessionhash, $projectId);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get folders for project id ' . $projectId . ': ' . $e->faultstring);
		}
	}

	/**
	 * Method to get an array of Docman folder files by folder Id
	 *
	 * @param   integer  $folderId  The folder id.
	 *
	 * @return  array  Folder files data array on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getDocmanFiles($folderId)
	{
		try
		{
			return $this->client->getDocmanFiles($this->sessionhash, $folderId);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get files for folder id ' . $folderId . ': ' . $e->faultstring);
		}
	}

	/**
	 * Method to get an array of file Versions by file Id
	 *
	 * @param   integer  $fileId  The file id.
	 *
	 * @return  array  File versions data array on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getDocmanFileVersions($fileId)
	{
		try
		{
			return $this->client->getDocmanFileVersions($this->sessionhash, $fileId);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get versions for file id ' . $fileId . ': ' . $e->faultstring);
		}
	}

	/**
	 * Method to get an array of forum Threads by forum Id
	 *
	 * @param   integer  $forumId  The forum id for.
	 *
	 * @return  array  Forum threads data array on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getForumThreads($forumId)
	{
		try
		{
			return $this->client->getForumThreads($this->sessionhash, $forumId);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get threads for forum id ' . $forumId . ': ' . $e->faultstring);
		}
	}

	/**
	 * Method to get an array of thread messages by thread Id
	 *
	 * @param   integer  $threadId  The forum thread id.
	 *
	 * @return  array  Thread messages data array on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getForumMessages($threadId)
	{
		try
		{
			return $this->client->getForumMessages($this->sessionhash, $threadId);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get messages for thread id ' . $threadId . ': ' . $e->faultstring);
		}
	}
}
