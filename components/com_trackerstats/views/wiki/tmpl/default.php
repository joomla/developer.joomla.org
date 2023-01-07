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
use Joomla\CMS\Router\Route;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('jquery.framework');
HTMLHelper::_('barchart.barchart', 'barchart', 'barchart', true);
?>
<div class="trackerstats-wiki<?php echo $this->pageclass_sfx;?>">
	<?php if ($this->params->def('show_page_heading', 1)) : ?>
		<div class="page-header">
			<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
		</div>
	<?php endif; ?>

	<div id="barchart" style="width:700px; height:600px;" data-href="<?php echo Route::_('index.php?option=com_trackerstats&task=wiki.display&format=json'); ?>"></div>
</div>
