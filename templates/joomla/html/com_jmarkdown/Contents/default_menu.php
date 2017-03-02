<?php
/**
 * @package     J2Markdown
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2015 J2Store . All rights reserved.
 * @license GNU GPL v3 or later
 * @link        http://j2store.org
 * */
defined('_JEXEC') or die;
$route = "index.php?option=com_jmarkdown&view=Contents&slug=";

?>
<?php if(isset($this->sections_data)):?>
	<?php foreach ($this->sections_data as $section):?>
	<h4><?php echo $section['section_name'];?></h4>
	<ul class="nav menu nav-stacked nav-tabs">
		<?php if(isset($section['topics']) && !empty($section['topics'])):?>
			<?php foreach ($section['topics'] as $topic):?>
				<?php if(isset($topic['topic_name']) && !empty($topic['topic_name'])):?>
				<?php
				$class_is_active = '';
				if($this->slug == $topic['slug']) $class_is_active = 'active';?>
				<li class="<?php echo $class_is_active; ?>">
					<a href="<?php echo $route.$topic['slug'];?>"><?php echo $topic['topic_name'];?></a>
				</li>
				<?php endif;?>
			<?php endforeach;?>
		<?php endif;?>
		</ul>
	<?php endforeach;?>
<?php endif;?>
