<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Trait for common methods in the GForge adapters
 */
trait GForgeTrait
{
	/**
	 * The client object connected to the GForge instance.
	 *
	 * @var  SoapClient
	 */
	protected $client;

	/**
	 * The session hash for the SOAP session.
	 *
	 * @var  string
	 */
	protected $sessionhash;

	/**
	 * The username for the signed in session.
	 *
	 * @var  string
	 */
	protected $username;

	/**
	 * Method to sign into GForge using password authentication.
	 *
	 * @param   string   $username  The username for the account to login.
	 * @param   string   $password  The password for the account to login.
	 *
	 * @return	boolean  True on success.
	 *
	 * @throws  RuntimeException
	 */
	public function login($username, $password)
	{
		try
		{
			// Attempt to sign into the account and get the session hash.
			$sessionhash = $this->client->login($username, $password);

			// Cache the session hash and username for later use.
			$this->sessionhash = $sessionhash;
			$this->username = $username;

			return true;
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Login Failed: ' . $e->faultstring);
		}
	}

	/**
	 * Method to sign out of GForge.
	 *
	 * @return	boolean  True on success.
	 *
	 * @throws  RuntimeException
	 */
	public function logout()
	{
		try
		{
			// Attempt to sign out.
			$this->client->logout($this->sessionhash);
			$this->sessionhash = null;
			$this->username = null;

			return true;
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Logout Failed: ' . $e->faultstring);
		}
	}

	/**
	 * Method to get user data by username.
	 *
	 * @param   string  $username  The optional username to get user data for, defaults to the user
	 *                             signed into the current session.
	 *
	 * @return  object   User data object on success.
	 *
	 * @throws  RuntimeException
	 */
	public function getUser($username = null)
	{
		try
		{
			// Attempt to get the user object by the username or "unix name" in GForge speak.
			return $this->client->getUserByUnixName($this->sessionhash, $username ? $username : $this->username);
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Failed to get user ' . ($username ? $username : $this->username) . ': ' . $e->faultstring);
		}
	}

	/**
	 * Method to get a list of client functions.
	 *
	 * @return  array  Functions array on success.
	 *
	 * @throws  RuntimeException
	 */
	protected function getClientFunctions()
	{
		try
		{
			// Attempt to get the client functions.
			return $this->client->__getFunctions();
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Failed to get functions: ' . $e->faultstring);
		}
	}

	/**
	 * Method to get a list of client types.
	 *
	 * @return  array  Array of types on success.
	 *
	 * @throws  RuntimeException
	 */
	protected function getClientTypes()
	{
		try
		{
			// Attempt to get the client types.
			return $this->client->__getTypes();
		}
		catch (SoapFault $e)
		{
			throw new RuntimeException('Failed to get types: ' . $e->faultstring);
		}
	}
}
