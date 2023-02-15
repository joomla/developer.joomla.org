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
use Joomla\CMS\Version;

$version = new Version;
$isJ4 = (new Version)->isCompatible('4.0.0');

if ($isJ4) {
    HTMLHelper::_('bootstrap.tooltip', '.hasTooltip');
} else {
    HTMLHelper::_('behavior.calendar');
}

/** @var CodeModelTracker $model */
$model = $this->getModel();
?>

<div class="clearfix">
    <div class="<?php echo $isJ4 ? 'float-start' : 'pull-left'?> form-search">

    </div>
    <div class="<?php echo $isJ4 ? 'd-sm-none d-md-none float-end' : 'hidden-phone hidden-tablet pull-right'?>">
        <label for="limit" class="<?php echo $isJ4 ? 'visually-hidden' : 'element-invisible'?>">
            <?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC') ?>
        </label>
        <?php echo $this->page->getLimitBox(); ?>
    </div>
</div>
<div class="clearfix">
    <div id="tracker-filters" class="form-inline <?php echo $isJ4 ? 'card' : 'well'?>">
        <?php if ($isJ4) : ?>
        <div class="card-body">
        <?php endif ?>
        <div class="<?php echo $isJ4 ? 'row' : 'row-fluid'?>">
            <div class="<?php echo $isJ4 ? 'col-md-6' : 'span6'?>">
                <div class="control-group">
                    <label for="filter_search" class="<?php echo $isJ4 ? 'form-label' : 'control-label'?>">
                        <?php echo Text::_('JSEARCH_FILTER'); ?>
                    </label>
                    <div class="controls">
                        <?php if ($isJ4) : ?>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" name="search" id="filter_search" value="<?php echo $this->escape($model->getState('filter.search')) ?>"/>
                            <span class="input-group-text"><span aria-hidden="true" class="fa fa-search"></span></span>
                        </div>
                        <?php else: ?>
                        <div class="input-append">
                            <input type="text" name="search" id="filter_search" value="<?php echo $this->escape($model->getState('filter.search')) ?>"/>
                            <button type="submit" class="btn hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', 'JSEARCH_FILTER_SUBMIT'); ?>">
                                <span aria-hidden="true" class="icon-search"></span>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="<?php echo $isJ4 ? 'col-md-6' : 'span6'?>">
                <div class="control-group">
                    <label class="<?php echo $isJ4 ? 'form-label' : 'control-label'?>" for="filter_date_field">
                        <?php echo Text::_('COM_CODE_TRACKER_FILTER_DATE'); ?>
                    </label>
                    <div class="controls">
                        <?php echo HTMLHelper::_(
                            'select.genericlist',
                            CodeHelperSelect::getDateOptions(),
                            'filter_date_field',
                            [
                                'onchange' => 'document.forms.trackerForm.submit();',
                                'class'    => $isJ4 ? 'form-select form-control-sm' : 'input-small',
                            ],
                            'value',
                            'text',
                            $model->getState('issue.date_field')
                        ); ?>
                        <input type="hidden" name="filter_date_filtering" value="range" />

                        <?php echo HTMLHelper::_('calendar', $model->getState('issue.start_date_range'), 'filter_start_date_range', 'filter_start_date_range', '%Y-%m-%d', ['class' => $isJ4 ? 'form-control-sm' : 'input-small']); ?>
                        <?php echo HTMLHelper::_('calendar', $model->getState('issue.end_date_range'), 'filter_end_date_range', 'filter_end_date_range', '%Y-%m-%d', ['class' => $isJ4 ? 'form-control-sm' : 'input-small']); ?>

                        <button type="submit" class="btn <?php echo $isJ4 ? 'btn-secondary' : ''; ?> hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', 'JSEARCH_FILTER_SUBMIT'); ?>">
                            <span aria-hidden="true" class="<?php echo $isJ4 ? 'fa fa-search' : 'icon-search'?>"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <button id="adv-search-button" type="button" class="btn btn-primary" data-toggle="collapse" data-target="#filters-advanced">
            <span aria-hidden="true" class="<?php echo $isJ4 ? 'fa fa-plus' : 'icon-plus'?>"></span> <?php echo Text::_('COM_CODE_TRACKER_FILTER_ADVANCED_SEARCH'); ?></button>

        <div id="filters-advanced" class="<?php echo $isJ4 ? 'row' : 'row-fluid'?> collapse">
            <div class="<?php echo $isJ4 ? 'col-md-6' : 'span6'?>">
                <div class="control-group">
                    <label class="control-label" for="filter_status_id">
                        <?php echo Text::_('COM_CODE_TRACKER_FILTER_STATUS') ?>
                    </label>
                    <div class="controls">
                        <?php echo HTMLHelper::_(
                            'select.genericlist',
                            CodeHelperSelect::getComparatorOptions(),
                            'filter_status_id_include',
                            [
                                'onchange' => 'document.forms.trackerForm.submit();',
                                'class'    => $isJ4 ? 'form-select form-control-sm' : 'input-small'
                            ],
                            'value',
                            'text',
                            $model->getState('issue.status_id_include')
                        ); ?>
                        <?php echo HTMLHelper::_(
                            'select.genericlist',
                            CodeHelperSelect::getStatusOptions($this->item->jc_tracker_id),
                            'filter_status_id',
                            [
                                    'onchange' => 'document.forms.trackerForm.submit();',
                                    'class'    => $isJ4 ? 'form-select' : ''
                            ],
                            'value',
                            'text',
                            $model->getState('issue.status_id')
                        ); ?>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="filter_tag_id">
                        <?php echo Text::_('COM_CODE_TRACKER_FILTER_TAG') ?>
                    </label>
                    <div class="controls">
                        <?php echo HTMLHelper::_(
                            'select.genericlist',
                            CodeHelperSelect::getComparatorOptions(),
                            'filter_tag_id_include',
                            [
                                'onchange' => 'document.forms.trackerForm.submit();',
                                'class'    => $isJ4 ? 'form-select form-control-sm' : 'input-small'
                            ],
                            'value',
                            'text',
                            $model->getState('issue.tag_id_include')
                        ); ?>
                        <?php echo HTMLHelper::_(
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
                        <button type="submit" class="btn <?php echo $isJ4 ? 'btn-secondary' : ''; ?> hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', 'JSEARCH_FILTER_SUBMIT'); ?>">
                            <span aria-hidden="true" class="<?php echo $isJ4 ? 'fa fa-search' : 'icon-search'?>"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="<?php echo $isJ4 ? 'col-md-6' : 'span6'?>">
                <div class="control-group">
                    <label class="control-label" for="filter_submitter_name">
                        <?php echo Text::_('COM_CODE_TRACKER_FILTER_SUBMITTER') ?>
                    </label>
                    <div class="controls">
                        <?php echo HTMLHelper::_(
                            'select.genericlist',
                            CodeHelperSelect::getComparatorOptions(),
                            'filter_submitter_id_include',
                            [
                                'onchange' => 'document.forms.trackerForm.submit();',
                                'class'    => $isJ4 ? 'form-select form-control-sm' : 'input-small'
                            ],
                            'value',
                            'text',
                            $model->getState('issue.submitter_id_include')
                        ); ?>
                        <input type="text" <?php echo $isJ4 ? 'class="form-control"' : '' ?> name="filter_submitter_name" id="filter_submitter_name" value="<?php echo $this->escape($model->getState('issue.submitter_name')) ?>"/>
                        <button type="submit" class="btn <?php echo $isJ4 ? 'btn-secondary' : ''; ?> hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', 'JSEARCH_FILTER_SUBMIT'); ?>">
                            <span aria-hidden="true" class="<?php echo $isJ4 ? 'fa fa-search' : 'icon-search'?>"></span>
                        </button>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label" for="filter_closer_name">
                        <?php echo Text::_('COM_CODE_TRACKER_FILTER_CLOSER') ?>
                    </label>
                    <div class="controls">
                        <?php echo HTMLHelper::_(
                            'select.genericlist',
                            CodeHelperSelect::getComparatorOptions(),
                            'filter_closer_id_include',
                            [
                                'onchange' => 'document.forms.trackerForm.submit();',
                                'class'    => $isJ4 ? 'form-select form-control-sm' : 'input-small'
                            ],
                            'value',
                            'text',
                            $model->getState('issue.closer_id_include')
                        ); ?>
                        <input type="text" <?php echo $isJ4 ? 'class="form-control"' : '' ?> name="filter_closer_name" id="filter_closer_name" value="<?php echo $this->escape($model->getState('issue.closer_name')) ?>"/>
                        <button type="submit" class="btn <?php echo $isJ4 ? 'btn-secondary' : ''; ?> hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', 'JSEARCH_FILTER_SUBMIT'); ?>">
                            <span aria-hidden="true" class="<?php echo $isJ4 ? 'fa fa-search' : 'icon-search'?>"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php if ($isJ4) : ?>
    </div>
    <?php endif ?>
    </div>
</div>
