<?php
/**
 * @package     Joomla.DeveloperNetwork
 * @subpackage  com_ghmarkdowndisplay
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var GHMarkdownDisplayViewRepository $this */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('formbehavior.chosen', 'select');

$js = <<< JS
Joomla.submitbutton = function(task) {
	if (task === 'repository.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
		Joomla.submitform(task, document.getElementById('item-form'));
	}
};
JS;

Factory::getDocument()->addScriptDeclaration($js);
?>

<form action="<?php echo Route::_('index.php?option=com_ghmarkdowndisplay&view=repository&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>
	<div class="form-horizontal">
		<div class="row-fluid">
			<div class="span9">
				<fieldset class="adminform">
					<?php echo $this->form->renderField('repository_owner'); ?>
					<?php echo $this->form->renderField('repository_name'); ?>
				</fieldset>
			</div>
			<div class="span3">
				<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>

		<input type="hidden" name="task" value="" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
