<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_latest
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$i = 0;
?>
<div class="accordion latestnews<?php echo $moduleclass_sfx; ?>" id="latestnews">
<?php foreach ($list as $item) :  ?>
	<div class="accordion-group">
	    <div class="accordion-heading">
	      <a class="accordion-toggle" data-toggle="collapse" data-parent="#latestnews" href="#latestnews<?php echo $i; ?>">
	        <?php echo $item->title; ?></a>
	      </a>
	    </div>
	    <div id="latestnews<?php echo $i; ?>" class="accordion-body collapse <?php echo $i == 0 ? 'in' : ''; ?>">
	      <div class="accordion-inner">
	        <?php echo $item->introtext; ?>
	        <br />
	        <a href="<?php echo $item->link; ?>" class="btn"><?php echo $item->title; ?></a>
	      </div>
	    </div>
  </div>
<?php $i++; endforeach; ?>
</div>