<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$this->document->getWebAssetManager()
    ->useStyle('com_finder.finder')
    ->useScript('com_finder.finder');

// JUI Template Change
Joomla\CMS\HTML\HTMLHelper::_('jquery.framework');
$this->document->addScriptDeclaration("
  jQuery('#advancedSearch').on('show.bs.collapse', function () {
    jQuery('#advancedSearch').addClass('clearfix').css('overflow', 'inherit');
  }).on('hide.bs.collapse', function () {
    jQuery('#advancedSearch').removeClass('clearfix').css('overflow', 'hidden');
});
");
// END JUI Template Change
?>
<div class="com-finder finder">
    <?php if ($this->params->get('show_page_heading')) : ?>
        <h1>
            <?php if ($this->escape($this->params->get('page_heading'))) : ?>
                <?php echo $this->escape($this->params->get('page_heading')); ?>
            <?php else : ?>
                <?php echo $this->escape($this->params->get('page_title')); ?>
            <?php endif; ?>
        </h1>
    <?php endif; ?>
    <div id="search-form" class="com-finder__form">
        <?php echo $this->loadTemplate('form'); ?>
    </div>
    <?php // Load the search results layout if we are performing a search. ?>
    <?php if ($this->query->search === true) : ?>
        <div id="search-results" class="com-finder__results">
            <?php echo $this->loadTemplate('results'); ?>
        </div>
    <?php endif; ?>
</div>
