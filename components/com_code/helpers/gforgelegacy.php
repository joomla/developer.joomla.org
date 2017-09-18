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
JLoader::register(GForgeTrait::class, __DIR__ . '/gforgetrait.php');

/**
 * Connector class to a GForge Advanced Server Legacy SOAP API.
 *
 * @see  http://joomlacode.org/gf/xmlcompatibility/soap/
 */
class GForgeLegacy
{
	use GForgeTrait;

	/**
	 * The URI for the API
	 *
	 * @var  string
	 */
	protected $apiUri = '/xmlcompatibility/soap/?wsdl';

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
	 * Method to get an array of tracker file changes by id.
	 *
	 * @param   integer  $itemId     The tracker item id for which to get the files array.
	 * @param   integer  $trackerId  The tracker id in which the item resides.
	 * @param   integer  $projectId  The project id in which the tracker resides.
	 *
	 * @return  array  Tracker item files data array on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getTrackerItemFiles($itemId, $trackerId, $projectId)
	{
		try
		{
			// Attempt to get the files data array by the tracker item id.
			return $this->client->getArtifactFiles($this->sessionhash, $projectId, $trackerId, $itemId);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get files for tracker item ' . $itemId . ': ' . $e->faultstring);
		}
	}

	/**
	 * Method to get a file data object by id.
	 *
	 * @param   integer  $fileId     The file id for which to get the files array.
	 * @param   integer  $itemId     The tracker item id to which the file is attached.
	 * @param   integer  $trackerId  The tracker id in which the item resides.
	 * @param   integer  $projectId  The project id in which the tracker resides.
	 *
	 * @return  array  Tracker item files data array on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getTrackerItemFile($fileId, $itemId, $trackerId, $projectId)
	{
		try
		{
			// Attempt to get the file data object by the file id.
			return $this->client->getArtifactFileData($this->sessionhash, $projectId, $trackerId, $itemId, $fileId);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Unable to get  ' . $fileId . ': ' . $e->faultstring);
		}
	}
}
