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
 * A helper class for selection lists in com_code's front-end
 */
class CodeHelperSelect
{
	/**
	 * Get a numeric priority to string map
	 *
	 * @return  array
	 */
	public static function getPrioritiesRaw()
	{
		return [
			'1' => JText::_('COM_CODE_TRACKER_HIGH_PRIORITY'),
			'2' => JText::_('COM_CODE_TRACKER_MEDIUM_HIGH_PRIORITY'),
			'3' => JText::_('COM_CODE_TRACKER_MEDIUM_PRIORITY'),
			'4' => JText::_('COM_CODE_TRACKER_LOW_PRIORITY'),
			'5' => JText::_('COM_CODE_TRACKER_VERY_LOW_PRIORITY'),
		];
	}

	/**
	 * Return the priorities as JHtml select options
	 *
	 * @param   string  $defaultOptionKey  The translation key for the default selection option
	 *
	 * @return  array
	 */
	public static function getPrioritiesOptions($defaultOptionKey = null)
	{
		return static::arrayToOptions(static::getPrioritiesRaw(), $defaultOptionKey);
	}

	/**
	 * Returns an array mapping status IDs to status strings
	 *
	 * @param   integer  $trackerId  Optional tracker ID to filter the status array by
	 *
	 * @return  array
	 */
	public static function getStatusRaw($trackerId = null)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select('*')
			->from('#__code_tracker_status');

		if ($trackerId)
		{
			$query->where('tracker_id = ' . (int) $trackerId);
		}

		return $db->setQuery($query)->loadAssocList('jc_status_id', 'title');
	}

	/**
	 * Returns the statuses as JHtml select options
	 *
	 * @param   integer  $trackerId         Optional tracker ID to filter the status array by
	 * @param   string   $defaultOptionKey  The translation key for the default selection option
	 *
	 * @return  array
	 */
	public static function getStatusOptions($trackerId = null, $defaultOptionKey = null)
	{
		return static::arrayToOptions(static::getStatusRaw($trackerId), $defaultOptionKey);
	}

	/**
	 * Returns the issue tags
	 *
	 * @return  array
	 */
	public static function getTagRaw()
	{
		$db = JFactory::getDbo();

		return $db->setQuery(
			$db->getQuery(true)
				->select('*')
				->from('#__code_tags')
		)->loadAssocList('tag_id', 'tag');
	}

	/**
	 * Returns the tag filter options
	 *
	 * @return  stdClass[]
	 */
	public static function getTagOptions()
	{
		$array = static::getTagRaw();

		$options = array();

		$options[] = JHtml::_('select.option', -1, JText::_('None'));

		foreach ($array as $k => $v)
		{
			$options[] = JHtml::_('select.option', $k, $v);
		}

		return $options;
	}

	/**
	 * Returns the comparison operator options
	 *
	 * @return  stdClass[]
	 */
	public static function getComparatorOptions()
	{
		return [
			JHtml::_('select.option', '1', JText::_('COM_CODE_TRACKER_IS')),
			JHtml::_('select.option', '0', JText::_('COM_CODE_TRACKER_IS_NOT')),
		];
	}

	/**
	 * Returns the date filter options
	 *
	 * @return  stdClass[]
	 */
	public static function getDateOptions()
	{
		return [
			JHtml::_('select.option', 'none', JText::_('COM_CODE_TRACKER_NONE')),
			JHtml::_('select.option', 'created', JText::_('COM_CODE_TRACKER_CREATED')),
			JHtml::_('select.option', 'modified', JText::_('COM_CODE_TRACKER_LAST_MODIFIED')),
			JHtml::_('select.option', 'closed', JText::_('COM_CODE_TRACKER_CLOSED')),
		];
	}

	/**
	 * Convert an array of options to a JHtmlSelect-compatible options array
	 *
	 * @param   string  $defaultOptionKey  The translation key for the default selection option
	 *
	 * @return  stdClass[]
	 */
	protected static function arrayToOptions($array, $defaultOptionKey = null)
	{
		$options = [];

		if (empty($defaultOptionKey))
		{
			$defaultOptionKey = 'JGLOBAL_SELECT_AN_OPTION';
		}

		$options[] = JHtml::_('select.option', 0, JText::_($defaultOptionKey));

		foreach ($array as $k => $v)
		{
			$options[] = JHtml::_('select.option', $k, $v);
		}

		return $options;
	}
}
