<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load the CSS Stylesheet
JHtml::_('stylesheet', 'com_code/default.css', [], true);
?>

<div class="trackers<?php echo $this->pageclass_sfx?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	</div>
	<?php endif;?>

	<div class="page-header">
		<h2>
			<?php echo JText::_('COM_CODE_TRACKERS_ISSUE_TRACKERS'); ?>
		</h2>
	</div>

	<?php foreach ($this->items as $tracker) : ?>
		<div class="trackers branch-<?php echo $tracker->tracker_id; ?> well">
			<h3>
				<a href="<?php echo JRoute::_('index.php?option=com_code&view=tracker&tracker_id=' . $tracker->jc_tracker_id); ?>" title="<?php echo JText::sprintf('COM_CODE_TRACKERS_VIEW_TRACKER', $tracker->title); ?>">
					<?php echo $tracker->title; ?></a>
			</h3>
		</div>
	<?php endforeach; ?>
</div>
