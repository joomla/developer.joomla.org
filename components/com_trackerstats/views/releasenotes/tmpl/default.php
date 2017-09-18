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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
?>
<div class="trackerstats-releasenotes<?php echo $this->pageclass_sfx;?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<div class="page-header">
			<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
		</div>
	<?php endif; ?>

	<div class="cat-items">
		<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post" name="adminForm" >
			<fieldset class="filters btn-toolbar clearfix">
				<div class="btn-group">
					<label class="filter-search-lbl element-invisible" for="filter-search">
						<?php echo Text::_('COM_CONTENT_' . $this->params->get('filter_field') . '_FILTER_LABEL') . '&#160;'; ?>
					</label>
					<div class="input-append">
						<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')) ?>" placeholder="Filter by title..." />
						<button type="submit" class="btn hasTooltip" title="<?php echo HTMLHelper::_('tooltiptext', 'JSEARCH_FILTER_SUBMIT'); ?>">
							<i class="icon-search"></i>
						</button>
					</div>
				</div>
				<div class="btn-group pull-right">
					<label for="limit" class="element-invisible">
						<?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>
					</label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>

				<input type="hidden" name="limitstart" value="" />
			</fieldset>

			<table class="table table-condensed table-striped">
				<thead>
					<tr>
						<th>
							<?php  echo Text::_('COM_TRACKERSTATS_RELEASENOTES_CATEGORY'); ?>
						</th>
						<th>
							<?php  echo Text::_('COM_TRACKERSTATS_RELEASENOTES_ISSUE'); ?>
						</th>
						<th>
							<?php  echo Text::_('COM_TRACKERSTATS_RELEASENOTES_TITLE'); ?>
						</th>
					</tr>
				</thead>

				<tbody>

				<?php foreach ($this->items as $i => $note) : ?>
					<tr>
						<td>
							<?php echo $note->category;?>
						</td>
						<td>
							<a href="<?php echo Route::_(CodeHelperRoute::getIssueRoute($note->jc_issue_id)); ?>">
								<?php echo $this->escape($note->jc_issue_id); ?>
							</a>
						</td>
						<td>
							<?php echo $note->title;?>
						</td>

					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</form>

		<?php if (!empty($this->items)) : ?>
			<?php if (($this->params->def('show_pagination', 2) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
			<div class="pagination">
				<?php if ($this->params->def('show_pagination_results', 1)) : ?>
					<p class="counter pull-right">
						<?php echo $this->pagination->getPagesCounter(); ?>
					</p>
				<?php endif; ?>

				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>
