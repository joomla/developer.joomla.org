<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.calendar');

/** @var CodeModelTracker $model */
$model = $this->getModel();
?>

<div class="clearfix">
	<div class="pull-left form-search">

	</div>
	<div class="hidden-phone hidden-tablet pull-right">
		<label for="limit" class="element-invisible">
			<?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC') ?>
		</label>
		<?php echo $this->page->getLimitBox(); ?>
	</div>
</div>
<div class="clearfix">
	<div id="tracker-filters" class="form-inline well">
		<div class="row-fluid">
			<div class="span6">
				<div class="control-group">
					<label for="filter_search" class="control-label">
						<?php echo JText::_('JSEARCH_FILTER'); ?>
					</label>
					<div class="controls">
						<div class="input-append">
							<input type="text" name="search" id="filter_search" value="<?php echo $this->escape($model->getState('filter.search')) ?>"/>
							<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::_('tooltipText', 'JSEARCH_FILTER_SUBMIT'); ?>">
								<i class="icon-search"></i>
							</button>
						</div>
					</div>
				</div>
			</div>

			<div class="span6">
				<div class="control-group">
					<label class="control-label" for="filter_date_field">
						<?php echo JText::_('COM_CODE_TRACKER_FILTER_DATE'); ?>
					</label>
					<div class="controls">
						<?php echo JHtml::_(
							'select.genericlist',
							CodeHelperSelect::getDateOptions(),
							'filter_date_field',
							[
								'onchange' => 'document.forms.trackerForm.submit();',
								'class'    => 'input-small'
							],
							'value',
							'text',
							$model->getState('issue.date_field')
						); ?>
						<input type="hidden" name="filter_date_filtering" value="range" />

						<?php echo JHtml::_('calendar', $model->getState('issue.start_date_range'), 'filter_start_date_range', 'filter_start_date_range', '%Y-%m-%d', ['class' => 'input-small']); ?>
						<?php echo JHtml::_('calendar', $model->getState('issue.end_date_range'), 'filter_end_date_range', 'filter_end_date_range', '%Y-%m-%d', ['class' => 'input-small']); ?>

						<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::_('tooltipText', 'JSEARCH_FILTER_SUBMIT'); ?>">
							<i class="icon-search"></i>
						</button>
					</div>
				</div>
			</div>
		</div>

		<button id="adv-search-button" type="button" class="btn btn-primary" data-toggle="collapse" data-target="#filters-advanced">
        <i class="icon-plus"></i> <?php echo JText::_('COM_CODE_TRACKER_FILTER_ADVANCED_SEARCH'); ?></button>

		<div id="filters-advanced" class="row-fluid collapse">
			<div class="span6">
				<div class="control-group">
					<label class="control-label" for="filter_status_id">
						<?php echo JText::_('COM_CODE_TRACKER_FILTER_STATUS') ?>
					</label>
					<div class="controls">
						<?php echo JHtml::_(
							'select.genericlist',
							CodeHelperSelect::getComparatorOptions(),
							'filter_status_id_include',
							[
								'onchange' => 'document.forms.trackerForm.submit();',
								'class'    => 'input-small'
							],
							'value',
							'text',
							$model->getState('issue.status_id_include')
						); ?>
						<?php echo JHtml::_(
							'select.genericlist',
							CodeHelperSelect::getStatusOptions($this->item->jc_tracker_id),
							'filter_status_id',
							['onchange' => 'document.forms.trackerForm.submit();'],
							'value',
							'text',
							$model->getState('issue.status_id')
						); ?>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="filter_tag_id">
						<?php echo JText::_('COM_CODE_TRACKER_FILTER_TAG') ?>
					</label>
					<div class="controls">
						<?php echo JHtml::_(
							'select.genericlist',
							CodeHelperSelect::getComparatorOptions(),
							'filter_tag_id_include',
							[
								'onchange' => 'document.forms.trackerForm.submit();',
								'class'    => 'input-small'
							],
							'value',
							'text',
							$model->getState('issue.tag_id_include')
						); ?>
						<?php echo JHtml::_(
							'select.genericlist',
							CodeHelperSelect::getTagOptions(),
							'filter_tag_id[]',
							[
								'multiple' => 'multiple',
								'class'    => 'advancedSelect'
							],
							'value',
							'text',
							$model->getState('issue.tag_id')
						); ?>
						<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::_('tooltipText', 'JSEARCH_FILTER_SUBMIT'); ?>">
							<i class="icon-search"></i>
						</button>
					</div>
				</div>
			</div>

			<div class="span6">
				<div class="control-group">
					<label class="control-label" for="filter_submitter_name">
						<?php echo JText::_('COM_CODE_TRACKER_FILTER_SUBMITTER') ?>
					</label>
					<div class="controls">
						<?php echo JHtml::_(
							'select.genericlist',
							CodeHelperSelect::getComparatorOptions(),
							'filter_submitter_id_include',
							[
								'onchange' => 'document.forms.trackerForm.submit();',
								'class'    => 'input-small'
							],
							'value',
							'text',
							$model->getState('issue.submitter_id_include')
						); ?>
						<input type="text" name="filter_submitter_name" id="filter_submitter_name" value="<?php echo $this->escape($model->getState('issue.submitter_name')) ?>"/>
						<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::_('tooltipText', 'JSEARCH_FILTER_SUBMIT'); ?>">
							<i class="icon-search"></i>
						</button>
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="filter_closer_name">
						<?php echo JText::_('COM_CODE_TRACKER_FILTER_CLOSER') ?>
					</label>
					<div class="controls">
						<?php echo JHtml::_(
							'select.genericlist',
							CodeHelperSelect::getComparatorOptions(),
							'filter_closer_id_include',
							[
								'onchange' => 'document.forms.trackerForm.submit();',
								'class'    => 'input-small'
							],
							'value',
							'text',
							$model->getState('issue.closer_id_include')
						); ?>
						<input type="text" name="filter_closer_name" id="filter_closer_name" value="<?php echo $this->escape($model->getState('issue.closer_name')) ?>"/>
						<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::_('tooltipText', 'JSEARCH_FILTER_SUBMIT'); ?>">
							<i class="icon-search"></i>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
