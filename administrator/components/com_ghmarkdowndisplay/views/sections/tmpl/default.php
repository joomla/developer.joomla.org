<?php
/**
 * @package     Joomla.DeveloperNetwork
 * @subpackage  com_ghmarkdowndisplay
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var GHMarkdownDisplayViewSections $this */

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder === 'a.ordering';

if ($saveOrder)
{
	HTMLHelper::_('sortablelist.sortable', 'sectionList', 'adminForm', strtolower($listDirn), 'index.php?option=com_ghmarkdowndisplay&task=sections.saveOrderAjax&tmpl=component');
}
?>
<form action="<?php echo Route::_('index.php?option=com_ghmarkdowndisplay&view=sections'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="col-md-2">
		<?php echo JHtmlSidebar::render(); ?>
	</div>
	<div id="j-main-container" class="col-md-10">
		<?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
		<div class="clearfix"> </div>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo Text::_('COM_GHMARKDOWNDISPLAY_MSG_NO_SECTIONS_MATCHING_QUERY'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="sectionList">
				<thead>
					<tr>
						<th width="1%" class="nowrap text-center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th width="1%" class="text-center">
							<?php echo HTMLHelper::_('grid.checkall'); ?>
						</th>
						<th width="5%" class="nowrap text-center">
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_GHMARKDOWNDISPLAY_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
						</th>
						<th width="20%" class="nowrap">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_GHMARKDOWNDISPLAY_HEADING_REPOSITORY', 'r.name', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($this->items as $i => $item) : ?>
						<?php
						$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->id || $item->checked_out == 0;
						?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="order nowrap text-center d-none d-md-table-cell">
								<?php
								$iconClass = '';

								if (!$saveOrder)
								{
									$iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::_('tooltipText', 'JORDERINGDISABLED');
								}
								?>
								<span class="sortable-handler<?php echo $iconClass; ?>">
									<span class="icon-menu" aria-hidden="true"></span>
								</span>
								<?php if ($saveOrder) : ?>
									<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order">
								<?php endif; ?>
							</td>
							<td class="text-center">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="text-center">
								<div class="btn-group">
									<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'sections.'); ?>

									<?php HTMLHelper::_('actionsdropdown.' . ((int) $item->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'sections'); ?>
									<?php echo HTMLHelper::_('actionsdropdown.render', $this->escape($item->name)); ?>
								</div>
							</td>
							<td class="has-context">
								<div class="float-start break-word">
									<?php if ($item->checked_out) : ?>
										<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'sections.', $canCheckin); ?>
									<?php endif; ?>
									<a href="<?php echo Route::_('index.php?option=com_ghmarkdowndisplay&task=section.edit&id=' . (int) $item->id); ?>">
										<?php echo $this->escape($item->name); ?>
									</a>
								</div>
							</td>
							<td>
								<?php echo $this->escape($item->repository); ?>
							</td>
							<td class="d-none d-md-table-cell">
								<?php echo (int) $item->id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<?php echo $this->pagination->getListFooter(); ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
