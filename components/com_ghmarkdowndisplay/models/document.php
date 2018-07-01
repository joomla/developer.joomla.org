<?php
/**
 * @package     Joomla.DeveloperNetwork
 * @subpackage  com_ghmarkdowndisplay
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;

/**
 * Document model class.
 */
class GHMarkdownDisplayModelDocument extends ItemModel
{
	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  object|boolean  Object on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select(
				[
					'a.*',
					's.name AS section_name',
				]
			)
			->from('#__ghmarkdowndisplay_documents AS a')
			->join('LEFT', '#__ghmarkdowndisplay_sections AS s ON s.id = a.section_id')
			->where('a.id = ' . (int) $pk);

		if (Factory::getUser()->guest)
		{
			$query->where('a.published = 1');
		}

		$document = $db->setQuery($query)->loadObject();

		if ($document === null)
		{
			$this->setError(Text::_('COM_GHMARKDOWNDISPLAY_ERROR_DOCUMENT_NOT_FOUND'));

			return false;
		}

		return $document;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 */
	protected function populateState()
	{
		// Load state from the request.
		$this->setState($this->getName() . '.id', Factory::getApplication()->input->getUint('id'));

		$this->setState('params', Factory::getApplication()->getParams('com_ghmarkdowndisplay'));
	}
}
