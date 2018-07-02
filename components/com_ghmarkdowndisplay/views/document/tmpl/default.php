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

/** @var GHMarkdownDisplayViewDocument $this */

HTMLHelper::_('stylesheet', 'com_ghmarkdowndisplay/github-light.css', ['version' => '0.5.0', 'relative' => true, 'detectDebug' => (bool) JDEBUG]);

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
		<h2 itemprop="headline">
			<?php echo $this->escape($this->item->name); ?>
		</h2>
		<?php if ($this->item->published == 0) : ?>
			<span class="label label-warning"><?php echo Text::_('JUNPUBLISHED'); ?></span>
		<?php endif; ?>
	</div>

	<div class="row-fluid">
		<div class="span3">
			<?php foreach ($this->navigation['sections'] as $section) : ?>
				<h4><?php echo $section['name'];?></h4>
				<ul class="nav menu nav-stacked nav-tabs">
					<?php $sectionDocs = array_filter(
						$this->navigation['documents'],
						function ($document) use ($section)
						{
							return $document['section_id'] == $section['id'];
						}
					); ?>

					<?php $linkBase = 'index.php?option=com_ghmarkdowndisplay&view=document&repository=' . $section['repository_id'] . '&id='; ?>

					<?php foreach ($sectionDocs as $document): ?>
						<li class="<?php echo $this->item->id == $document['id'] ? 'active' : ''; ?>">
							<a href="<?php echo Route::_($linkBase . $document['id']); ?>"><?php echo $document['name']; ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endforeach;?>
		</div>

		<div class="span9">
			<?php echo $this->renderedDocument; ?>
		</div>
	</div>
</div>
