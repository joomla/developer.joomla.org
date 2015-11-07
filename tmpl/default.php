<?php
/**
 * Joomla! Stat Charts
 *
 * @copyright  Copyright (C) 2015 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

defined('_JEXEC') or die;

/* @type  \Joomla\Registry\Registry  $params */
/* @type  array                      $chartData */
/* @type  object                     $module */

// Require our Chart.js source
JHtml::_('script', 'mod_joomladata/Chart.js', false, true);

// Figure out what type of chart we're building
$chartType = $params->get('chartType', 'Doughnut');

// Set the container ID using the module's ID for unique instances
$containerId = 'joomlaChart-' . $module->id;

$dataRows = '';
$iteration = 1;

// Get our helper so we can generate colors
$helper = new JoomlaStatChartsHelper;

foreach ($chartData as $row)
{
	$dataRows .= '{value: ' . $row['count'] . ', label: "' . $row['name'] . '", color: "' . $helper->getColor() . '"}';

	if ($iteration != count($chartData))
	{
		$dataRows .= ',';
	}

	$iteration++;
}
?>

<canvas id="<?php echo $containerId; ?>" width="<?php echo $params->get('containerWidth', 400); ?>" height="<?php echo $params->get('containerHeight', 400); ?>"></canvas>

<script type="text/javascript">
	var data = [<?php echo $dataRows; ?>];
	var <?php echo "ctx{$module->id}"; ?> = document.getElementById('<?php echo $containerId; ?>').getContext('2d');
	var myNewChart = new Chart(<?php echo "ctx{$module->id}"; ?>).<?php echo $chartType; ?>(data);
</script>
