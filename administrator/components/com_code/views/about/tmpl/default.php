<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_code
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>

<div class="row">
    <div id="j-sidebar-container" class="col-md-2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="col-md-10">
        <?php echo Text::_('COM_CODE_XML_DESCRIPTION'); ?>
    </div>
</div>
