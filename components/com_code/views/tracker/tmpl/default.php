<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Version;

// Enable Chosen
HTMLHelper::_('formbehavior.chosen', '.advancedSelect');

// Load the CSS Stylesheet
HTMLHelper::_('stylesheet', 'com_code/default.css', ['version' => 'auto', 'relative' => true, 'detectDebug' => (bool) JDEBUG], []);

$isJ4 = (new Version)->isCompatible('4.0.0');
$toggleClass = $isJ4 ? 'show' : 'overflow';
$toggleElementType = $isJ4 ? 'svg' : 'span';
$toggleElement = $isJ4 ? 'fa-plus fa-minus' : 'icon-plus icon-minus';

// Toggle advanced search elements
$toggleAdvSearch = <<< JS
	jQuery(document).ready(function () {
	    jQuery('#adv-search-button').click(function () {
	        jQuery(this).find('$toggleElementType').toggleClass('$toggleElement');
	        jQuery('#filters-advanced').toggleClass('$toggleClass');
	    });
	});
JS;

$this->document->addScriptDeclaration($toggleAdvSearch, 'text/javascript');

// Required to get the ordering working
$orderingJavascript = <<< JS
	Joomla.orderTable = function() {
		Joomla.tableOrdering(order, dirn);
	};
JS;

$this->document->addScriptDeclaration($orderingJavascript, 'text/javascript');
?>

<div class="issue-tracker<?php echo $this->pageclass_sfx?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	</div>
	<?php endif;?>

	<div class="page-header">
		<h2>
			<?php echo $this->item->title; ?>
		</h2>
	</div>

	<p class="lead">
		<?php echo HTMLHelper::_('content.prepare', $this->item->description, '', 'com_code.tracker'); ?>
	</p>

	<form action="<?php echo $this->formURL ?>" method="post" name="trackerForm" id="adminForm">
		<input type="hidden" name="filter_order" value="<?php echo $this->getModel()->getState('list.ordering', 'issue_id') ?>">
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->getModel()->getState('list.direction', 'DESC') ?>">
		<input type="hidden" name="task" value="tracker">

		<?php echo $this->loadTemplate('filters'); ?>

		<table class="table table-striped table-bordered table-hover" id="sortTable">
			<thead>
				<tr>
					<th scope="col">
						<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'jc_issue_id', $this->order_Dir, $this->order, 'tracker'); ?>
					</th>
					<th width="50%" class="list-title" scope="col">
						<?php echo HTMLHelper::_('grid.sort', 'JGLOBAL_TITLE', 'title', $this->order_Dir, $this->order, 'tracker'); ?>
					</th>
					<th scope="col">
						<?php echo HTMLHelper::_('grid.sort', 'COM_CODE_TRACKER_PRIORITY', 'priority', $this->order_Dir, $this->order, 'tracker'); ?>
					</th>
					<th scope="col">
						<?php echo HTMLHelper::_('grid.sort', 'COM_CODE_TRACKER_CREATED', 'created_date', $this->order_Dir, $this->order, 'tracker'); ?>
					</th>
					<th scope="col">
						<?php echo HTMLHelper::_('grid.sort', 'COM_CODE_TRACKER_MODIFIED', 'modified_date', $this->order_Dir, $this->order, 'tracker'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($this->items as $i => $issue) : ?>
				<tr title="<?php echo $this->escape($issue->title); ?>">
					<th scope="row">
						<a href="<?php echo Route::_('index.php?option=com_code&view=issue&issue_id=' . $issue->jc_issue_id); ?>" title="<?php echo Text::sprintf('COM_CODE_TRACKER_VIEW_ISSUE_REPORT', $issue->jc_issue_id); ?>">
							<?php echo $issue->jc_issue_id; ?>
						</a>
					</th>
					<td width="50%">
						<a href="<?php echo Route::_('index.php?option=com_code&view=issue&issue_id=' . $issue->jc_issue_id); ?>" title="<?php echo Text::sprintf('COM_CODE_TRACKER_VIEW_ISSUE_REPORT', $issue->jc_issue_id); ?>">
							<?php echo $issue->title; ?>
						</a>
					</td>
					<td>
						<span class="priority-<?php echo (int) $issue->priority ?>"><?php echo $this->priorities[$issue->priority]; ?></span>
					</td>
					<td>
						<?php echo Text::sprintf('COM_CODE_TRACKER_EDITED_BY', HTMLHelper::_('date', $issue->created_date, 'j M Y, G:i'), $issue->created_user_name); ?>
					</td>
					<td>
						<?php echo Text::sprintf('COM_CODE_TRACKER_EDITED_BY', HTMLHelper::_('date', $issue->modified_date, 'j M Y, G:i'), $issue->modified_user_name); ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</form>

	<?php if (!empty($this->items)) : ?>
		<?php if (($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->page->pagesTotal > 1)) : ?>
		<div class="<?php echo $isJ4 ? 'com-code-tracker__pagination' : 'pagination' ?>">
			<?php if ($this->params->def('show_pagination_results', 1)) : ?>
				<p class="counter <?php echo $isJ4 ? 'float-end' : 'pull-right' ?>">
					<?php echo $this->page->getPagesCounter(); ?>
				</p>
			<?php endif; ?>

			<?php echo $this->page->getPagesLinks(); ?>
		</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
