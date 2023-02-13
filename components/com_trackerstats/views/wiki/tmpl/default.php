<?php
/**
 * @package     Joomla.BugSquad
 * @subpackage  com_trackerstats
 *
 * @copyright   Copyright (C) 2011 Mark Dexter. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

try
{
    // TODO: On subdomains this duplicates the subdomain and fails
    $url = Uri::root() . Route::_('index.php?option=com_trackerstats&task=wiki.display&format=json');
    $response = HttpFactory::getHttp()->get($url);
}
catch (Exception $e)
{
    // Connection error, oops
    return;
}

// TODO: Check status code first
$chartData = json_decode($response->getBody());

// Require our Chart.js source. TODO: Currently we share from mod_joomladata but it probably should be the other way around!
HTMLHelper::_('script', 'mod_joomladata/Chart.js', ['version' => '2.9.4', 'relative' => true, 'detectDebug' => (bool) JDEBUG]);

$dataObject = (object) [
    'data'            => [],
];

foreach ($chartData[0][0] as $item) {
    $dataObject->data[] = $item;
}

// Flips the labels from the version we return in the API: TODO: modify the api!
$dataObject->data = array_reverse($dataObject->data);

$data = [
    'labels'   => array_reverse($chartData[1]),
    'datasets' => [$dataObject],
];
?>

<div class="trackerstats-wiki<?php echo $this->pageclass_sfx;?>">
    <?php if ($this->params->def('show_page_heading', 1)) : ?>
        <div class="page-header">
            <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
        </div>
    <?php endif; ?>

    <canvas id="wikiContributors" width="150" height="100"></canvas>

    <script type="text/javascript">
        var style = getComputedStyle(document.body);
        var backgroundColor = style.getPropertyValue('--bs-body-color');
        var wikiData = JSON.parse('<?php echo json_encode($data); ?>');
        wikiData['datasets'][0]['backgroundColor'] = '#006dcc';
        wikiData['datasets'][0]['label'] = '<?php echo $chartData[2][0]->label; ?>';

        var <?php echo "wikiCtx"; ?> = document.getElementById('wikiContributors').getContext('2d');
        var <?php echo "myChart{$module->id}"; ?> = new Chart(<?php echo "wikiCtx"; ?>, {
            type: 'horizontalBar',
            data: wikiData,
            options: {
                title: {
                    display: true,
                    fontSize: '20',
                    text: '<?php echo $chartData[3]; ?>',
                },
                legend: {
                    position: 'right',
                },
                tooltips: {
                    displayColors: false,
                    callbacks: {
                        title: function (tooltipItem, data) {
                            return '';
                        },
                        label: function (tooltipItem, data) {
                            return data.datasets[0].data[tooltipItem.index];
                        }
                    }
                }
            }
        });
    </script>
</div>
