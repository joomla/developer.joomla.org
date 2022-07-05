<?php
/**
 * @package     Joomla.BugSquad
 * @subpackage  com_trackerstats
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * JSON controller for Trackerstats -- Returns data array for rendering wiki activity bar charts
 */
class TrackerstatsControllerWiki extends BaseController
{
	/**
	 * Method to display a view.
	 *
	 * @param	boolean  $cachable   If true, the view output will be cached
	 * @param	array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	$this
	 */
	public function display($cachable = false, $urlparams = [])
	{
		Factory::getApplication()->mimeType = 'application/json';

		$label = (object) [
			'label' => Text::_('COM_TRACKERSTATS_WIKI_LABEL_EDITS')
		];

		try
		{
			$response = HttpFactory::getHttp()->get(
				'https://docs.joomla.org/api.php?action=query&list=allusers&format=json&auexcludegroup=bot&aulimit=100&auprop=editcount&auactiveusers=',
				['Content-type: application/json']
			);
		}
		catch (Exception $e)
		{
			// Error handling?
			echo json_encode([[[]], [], [$label], Text::_('COM_TRACKERSTATS_WIKI_LABEL_EDITS_BY_CONTRIBUTOR_IN_PAST_30_DAYS')]);

			return $this;
		}

		// Getting results
		$users = json_decode($response->body);

		// Convert to array for processing
		$workArray       = [];
		$totalEditsArray = [];

		foreach ($users->query->allusers as $user)
		{
			if ($user->name == 'MediaWiki default')
			{
				continue;
			}

			$workArray[$user->name]       = $user->recentactions;
			$totalEditsArray[$user->name] = $user->editcount;
		}

		asort($workArray, SORT_NUMERIC);

		// Slice the last 25 entries
		$maxCount   = 25;
		$arrayCount = count($workArray);

		if ($arrayCount > $maxCount)
		{
			$sliceStart = $arrayCount - $maxCount;
			$workArray  = array_slice($workArray, $sliceStart, $maxCount);
		}

		$people = [];
		$edits  = [];
		$i      = 0;

		foreach ($workArray as $k => $v)
		{
			if ($v > 0 && $i++ < $maxCount)
			{
				$edits[]  = $v;
				$people[] = Text::sprintf('COM_TRACKERSTATS_WIKI_CHART_PERSON_LABEL', $k, $totalEditsArray[$k]);
			}
		}

		// Send the response.
		echo json_encode([[$edits], $people, [$label], Text::_('COM_TRACKERSTATS_WIKI_LABEL_EDITS_BY_CONTRIBUTOR_IN_PAST_30_DAYS')]);

		return $this;
	}
}
