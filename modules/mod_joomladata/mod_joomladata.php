<?php
/**
 * Joomla! Stat Charts
 *
 * @copyright  Copyright (C) 2015 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

defined('_JEXEC') or die;

/**
 * Module variables
 * -----------------
 * @var   object                     $module    A module object
 * @var   array                      $attribs   An array of attributes for the module (probably from the XML)
 * @var   array                      $chrome    The loaded module chrome files
 * @var   JApplicationCms            $app       The active application singleton
 * @var   string                     $scope     The application scope before the module was included
 * @var   \Joomla\Registry\Registry  $params    Module parameters
 * @var   string                     $template  The active template
 * @var   string                     $path      The path to this module file
 * @var   JLanguage                  $lang      The active JLanguage singleton
 * @var   string                     $content   Module output content
 */

// Include the helper
JLoader::register('JoomlaStatChartsHelper', __DIR__ . '/helper.php');

// Strip any trailing slashes in case someone saves their URL with it
$serverUrl  = rtrim($params->get('serverUrl', 'https://developer.joomla.org/stats'), '/');
$dataSource = $params->get('dataSource', 'php_version');

$sourceUrl = $serverUrl . '/' . $dataSource;

// Request our data
try
{
	$response = JHttpFactory::getHttp()->get($sourceUrl);
}
catch (Exception $e)
{
	// Connection error, oops
	return;
}

// If there wasn't a successful response, there's nothing to render
if ($response->code !== 200)
{
	return;
}

// Extract our data
$rawData = json_decode($response->body, true);

// For a more readable output, we're going to merge some data together
switch ($dataSource)
{
	case 'server_os':
		// Sort the data
		arsort($rawData['data']['server_os']);

		// Convert the raw data into the structure used by the chart
		$chartData = [];

		foreach ($rawData['data']['server_os'] as $os => $count)
		{
			$label       = ($os == 'unknown') ? JText::_('MOD_JOOMLADATA_LABEL_UNKNOWN_SERVER') : $os;
			$chartData[] = ['name' => $label, 'count' => $count];
		}

		break;

	case 'php_version':
		// Sort the data
		ksort($rawData['data']['php_version']);

		// Convert the raw data into the structure used by the chart
		$chartData = [];

		foreach ($rawData['data']['php_version'] as $version => $count)
		{
			$chartData[] = ['name' => $version, 'count' => $count];
		}

		break;

	default:
		// Sort the data
		if ($dataSource == 'db_version' || $dataSource == 'cms_version')
		{
			ksort($rawData['data'][$dataSource]);
		}
		else
		{
			arsort($rawData['data'][$dataSource]);
		}

		// Convert the raw data into the structure used by the chart
		$chartData = [];

		foreach ($rawData['data'][$dataSource] as $group => $value)
		{
			$key         = 'MOD_JOOMLADATA_LABEL_' . strtoupper($group);
			$label       = $lang->hasKey($key) ? JText::_($key) : $group;
			$chartData[] = ['name' => $label, 'count' => $value];
		}

		break;
}

// Build the output
require JModuleHelper::getLayoutPath('mod_joomladata', $params->get('layout', 'default'));
