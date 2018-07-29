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

// Load the CSS stylesheets
HTMLHelper::_('stylesheet', 'com_code/default.css', ['version' => 'auto', 'relative' => true, 'detectDebug' => (bool) JDEBUG], []);
?>

<div class="issue<?php echo $this->pageclass_sfx?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	</div>
	<?php endif;?>

	<div class="page-header">
		<h2>
			[#<?php echo $this->item->jc_issue_id; ?>] - <?php echo $this->item->title; ?>
		</h2>
	</div>

	<div class="issue__content">
		<h3><?php echo Text::_('COM_CODE_ISSUE_SUMMARY'); ?></h3>
		<div class="row-fluid">
			<div class="span9">
				<?php echo nl2br($this->item->description); ?>
			</div>
			<aside class="span3 well">
				<dl>
					<dt><?php echo Text::_('COM_CODE_ISSUE_OPENED_ON'); ?></dt>
					<dd><?php echo Text::sprintf('COM_CODE_ISSUE_OPENED_ON_INFO', HTMLHelper::_('date', $this->item->created_date, 'j M Y, G:i'), $this->item->created_by_name); ?></dd>

					<?php if ($this->item->state == '0') : ?>
						<dt><?php echo Text::_('COM_CODE_ISSUE_CLOSED_ON'); ?></dt>
						<dd><?php echo HTMLHelper::_('date', $this->item->close_date, 'j M Y, G:i'); ?></dd>
					<?php endif; ?>

					<dt><?php echo Text::_('COM_CODE_ISSUE_STATUS'); ?></dt>
					<dd><?php echo $this->item->status_name; ?></dd>
				</dl>
			</aside>
		</div>

		<?php if (!empty($this->tags)) : ?>
			<h4><?php echo Text::_('COM_CODE_ISSUE_FILED_UNDER'); ?></h4>
			<ul class="issue-tags">
				<?php foreach ($this->tags as $tag) : ?>
					<li class="issue-tags__tag"><?php echo $tag->tag; ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if (!empty($this->comments)) : ?>
			<h4><?php echo Text::_('COM_CODE_ISSUE_RESPONSES'); ?></h4>
			<?php foreach ($this->comments as $comment) : ?>
				<div class="issue-comment">
					<span class="issue-comment__owner">
						<?php echo Text::sprintf('COM_CODE_ISSUE_POSTED_DETAILS', HTMLHelper::_('date', $comment->created_date, 'j M Y, G:i'), $comment->commenter_name); ?>
					</span>
					<div class="issue-comment__details">
						<?php echo nl2br($comment->body); ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>

		<?php if (!empty($this->commits)) : ?>
			<h4><?php echo Text::_('COM_CODE_ISSUE_COMMITS'); ?></h4>
			<?php foreach ($this->commits as $commit) : ?>
				<div class="issue-commit">
					<span class="issue-commit__owner">
						<?php echo Text::sprintf('COM_CODE_ISSUE_COMMIT_DETAILS', HTMLHelper::_('date', $commit->created_date, 'j M Y, G:i'), $commit->committer_name); ?>
					</span>
					<div class="issue-commit__details">
						<?php echo nl2br($commit->message); ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
