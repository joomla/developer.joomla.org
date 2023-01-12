<?php
/**
 * @package     Joomla.DeveloperNetwork
 * @subpackage  com_ghmarkdowndisplay
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Version;

/** @var GHMarkdownDisplayViewDocument $this */

HTMLHelper::_('stylesheet', 'com_ghmarkdowndisplay/github-light.css', ['version' => '0.5.0', 'relative' => true, 'detectDebug' => (bool) JDEBUG]);

$isJ4 = (new Version)->isCompatible('4.0.0');
?>
<div class="github-documentation<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<div class="page-header">
			<h1>
				<?php echo $this->escape($this->params->get('page_heading')); ?>
			</h1>
		</div>
	<?php endif; ?>

	<div class="page-header">
		<div class="<?php echo $isJ4 ? 'float-end' : 'pull-right' ?>">
			<a href="https://github.com/<?php echo $this->item->repository_owner; ?>/<?php echo $this->item->repository_name; ?>/edit/master/<?php echo ltrim($this->item->file, '/'); ?>" target="_blank" class="btn btn-primary">
				<?php echo Text::_('COM_GHMARKDOWNDISPLAY_EDIT_ON_GITHUB'); ?>
			</a>
		</div>
		<h2 itemprop="headline">
			<?php echo $this->escape($this->item->name); ?>
		</h2>
		<?php if ($this->item->published == 0) : ?>
			<span class="label label-warning"><?php echo Text::_('JUNPUBLISHED'); ?></span>
		<?php endif; ?>
	</div>

	<div class="<?php echo $isJ4 ? 'row' : 'row-fluid' ?>">
		<div class="<?php echo $isJ4 ? 'col-md-3' : 'span3' ?>">
			<?php foreach ($this->navigation['sections'] as $section) : ?>
				<h4><?php echo $section['name'];?></h4>
				<ul class="nav menu nav-tabs <?php echo $isJ4 ? 'flex-column' : 'nav-stacked'; ?>">
					<?php $sectionDocs = array_filter(
						$this->navigation['documents'],
						function ($document) use ($section)
						{
							return $document['section_id'] == $section['id'];
						}
					); ?>

					<?php $linkBase = 'index.php?option=com_ghmarkdowndisplay&view=document&repository=' . $section['repository_id'] . '&id='; ?>

					<?php foreach ($sectionDocs as $document): ?>
						<li class="<?php echo $this->item->id == $document['id'] ? 'active' : ''; ?> <?php echo $isJ4 ? 'nav-item' : '' ?>">
							<a class="<?php echo $isJ4 ? 'nav-link' : '' ?>" href="<?php echo Route::_($linkBase . $document['id']); ?>"><?php echo $document['name']; ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endforeach;?>
		</div>

		<div class="<?php echo $isJ4 ? 'col-md-9' : 'span9' ?>">
			<?php echo $this->renderedDocument; ?>
		</div>
	</div>
</div>
