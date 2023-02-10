<?php
/**
 * Joomla! Stat Charts
 *
 * @copyright  Copyright (C) 2015 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * Module variables
 * -----------------
 * @var   object                                  $module    A module object
 * @var   array                                   $attribs   An array of attributes for the module (probably from the XML)
 * @var   array                                   $chrome    The loaded module chrome files
 * @var   \Joomla\CMS\Application\CMSApplication  $app       The active application singleton
 * @var   string                                  $scope     The application scope before the module was included
 * @var   \Joomla\Registry\Registry               $params    Module parameters
 * @var   string                                  $template  The active template
 * @var   string                                  $path      The path to this module file
 * @var   \Joomla\CMS\Language\Language           $lang      The active JLanguage singleton
 * @var   string                                  $content   Module output content
 *
 * Additional variables
 * ---------------------
 * @var   array  $chartData  The data to be rendered in the chart
 */

// Require our Chart.js source
HTMLHelper::_('script', 'mod_joomladata/Chart.js', ['version' => '2.9.4', 'relative' => true, 'detectDebug' => (bool) JDEBUG]);

// Figure out what type of chart we're building
$chartType = $params->get('chartType', 'Doughnut');

// Set the container ID using the module's ID for unique instances
$containerId = 'joomlaChart-' . $module->id;

$labels     = [];
$dataObject = (object) [
	'data'            => [],
	'backgroundColor' => [],
];

// Get our helper so we can generate colors
$helper = new JoomlaStatChartsHelper;

foreach ($chartData as $row)
{
	$labels[] = $row['name'];

	$dataObject->data[]            = $row['count'];
	$dataObject->backgroundColor[] = $helper->getColor();
}

$data = [
	'labels'   => $labels,
	'datasets' => [$dataObject]
];
?>

<canvas id="<?php echo $containerId; ?>" width="100" height="100"></canvas>

<script type="text/javascript">
	var <?php echo "ctx{$module->id}"; ?> = document.getElementById('<?php echo $containerId; ?>').getContext('2d');
	var <?php echo "myChart{$module->id}"; ?> = new Chart(<?php echo "ctx{$module->id}"; ?>, {
		type: '<?php echo strtolower($chartType); ?>',
		data: <?php echo json_encode($data); ?>,
		options: {
			legend: false,
			tooltips: {
				callbacks: {
					label: function (tooltipItem, data) {
						return data.labels[tooltipItem.index] + ': ' + data.datasets[0].data[tooltipItem.index] + '%';
					}
				}
			}
		}
	});
</script>
