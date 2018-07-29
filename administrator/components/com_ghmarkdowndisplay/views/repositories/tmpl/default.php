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

/** @var GHMarkdownDisplayViewRepositories $this */

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo Route::_('index.php?option=com_ghmarkdowndisplay&view=repositories'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo JHtmlSidebar::render(); ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
		<div class="clearfix"> </div>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo Text::_('COM_GHMARKDOWNDISPLAY_MSG_NO_REPOSITORIES_MATCHING_QUERY'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="repositoryList">
				<thead>
					<tr>
						<th width="1%" class="center">
							<?php echo HTMLHelper::_('grid.checkall'); ?>
						</th>
						<th width="5%" class="nowrap center">
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_GHMARKDOWNDISPLAY_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
						</th>
						<th width="15%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_GHMARKDOWNDISPLAY_HEADING_REPOSITORY_OWNER', 'a.repository_owner', $listDirn, $listOrder); ?>
						</th>
						<th width="15%" class="nowrap hidden-phone">
							<?php echo HTMLHelper::_('searchtools.sort', 'COM_GHMARKDOWNDISPLAY_HEADING_REPOSITORY_NAME', 'a.repository_name', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
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
							<td class="center">
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>
							<td class="center">
								<div class="btn-group">
									<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'repositories.'); ?>

									<?php HTMLHelper::_('actionsdropdown.' . ((int) $item->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'repositories'); ?>
									<?php echo HTMLHelper::_('actionsdropdown.render', $this->escape($item->name)); ?>
								</div>
							</td>
							<td class="has-context">
								<div class="pull-left break-word">
									<?php if ($item->checked_out) : ?>
										<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'repositories.', $canCheckin); ?>
									<?php endif; ?>
									<a href="<?php echo Route::_('index.php?option=com_ghmarkdowndisplay&task=repository.edit&id=' . (int) $item->id); ?>">
										<?php echo $this->escape($item->name); ?>
									</a>
								</div>
							</td>
							<td class="small hidden-phone">
								<?php echo $this->escape($item->repository_owner); ?>
							</td>
							<td class="small hidden-phone">
								<?php echo $this->escape($item->repository_name); ?>
							</td>
							<td class="hidden-phone">
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
