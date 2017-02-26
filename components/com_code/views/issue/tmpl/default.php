<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load the CSS stylesheets
JHtml::_('stylesheet', 'com_code/default.css', [], true);
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

	<div id="issue-content">
		<h4><?php echo JText::_('COM_CODE_ISSUE_SUMMARY'); ?></h4>
		<div class="row-fluid">
			<div class="span9">
				<div class="issue-description">
					<?php echo nl2br($this->item->description); ?>
				</div>
			</div>
			<div class="span3 well">
				<h5><?php echo JText::_('COM_CODE_ISSUE_OPENED_ON'); ?></h5>
				<div><?php echo JText::sprintf('COM_CODE_ISSUE_OPENED_ON_INFO', JHtml::_('date', $this->item->created_date, 'j M Y, G:i'), $this->item->created_by_name); ?></div>
				<?php if ($this->item->state == '0') : ?>
					<h5><?php echo JText::_('COM_CODE_ISSUE_CLOSED_ON'); ?></h5>
					<div><?php echo JHtml::_('date', $this->item->close_date, 'j M Y, G:i'); ?></div>
				<?php endif; ?>
				<h5><?php echo JText::_('COM_CODE_ISSUE_STATUS'); ?></h5>
				<div><?php echo $this->item->status_name; ?></div>
			</div>
		</div>

		<?php if (!empty($this->tags)) : ?>
			<div class="issue-tags">
				<h4><?php echo JText::_('COM_CODE_ISSUE_FILED_UNDER'); ?></h4>
					<ul>
						<?php foreach ($this->tags as $tag) : ?>
							<li><?php echo $tag->tag; ?></li>
						<?php endforeach; ?>
					</ul>
			</div>
		<?php endif; ?>

		<?php if (!empty($this->comments)) : ?>
			<div class="issue-comments">
				<h4><?php echo JText::_('COM_CODE_ISSUE_RESPONSES'); ?></h4>
					<?php foreach ($this->comments as $comment) : ?>
						<div class="issue-comment well">
							<span class="comment-owner">
								<?php echo JText::sprintf('COM_CODE_ISSUE_POSTED_DETAILS', JHtml::_('date', $comment->created_date, 'j M Y, G:i'), $comment->commenter_name); ?>
							</span>
							<div class="issue-comment-details">
								<?php echo nl2br($comment->body); ?>
							</div>
						</div>
					<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if (!empty($this->commits)) : ?>
			<div class="issue-commits">
				<h4><?php echo JText::_('COM_CODE_ISSUE_COMMITS'); ?></h4>
				<?php foreach ($this->commits as $commit) : ?>
					<div class="issue-commit well">
						<span class="commit-owner">
							<?php echo JText::sprintf('COM_CODE_ISSUE_COMMIT_DETAILS', JHtml::_('date', $commit->created_date, 'j M Y, G:i'), $commit->committer_name); ?>
						</span>
						<div class="issue-commit-details">
							<?php echo nl2br($commit->message); ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
