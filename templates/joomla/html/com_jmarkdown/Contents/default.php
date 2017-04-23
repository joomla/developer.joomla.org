<?php
/**
 * @package     J2Markdown
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2015 J2Store . All rights reserved.
 * @license GNU GPL v3 or later
 * @link        http://j2store.org
 * */
defined('_JEXEC') or die;
require_once JPATH_ADMINISTRATOR.'/components/com_jmarkdown/Helper/JMarkdown.php';

JHtml::_('stylesheet', 'github-light.css', ['version' => '0.3.0', 'relative' => true, 'detectDebug' => (bool) JDEBUG], []);
?>
<div class="jmarkdown">
	<div class="row-fluid">
		<div class="span3">
		<?php echo $this->loadTemplate('menu');?>
		</div>
		<div class="span9">
			<?php if(isset($this->html)):?>
			<?php echo $this->html;?>
			<?php endif;?>
		</div>
	</div>
</div>

<script type="text/javascript">
jQuery( document ).ready(function() {
jQuery("a[href*=#]").click(function(e) {
	console.log(this.hash);
	console.log(location.hash);
	console.log(e);

	 var t, e, n;
        if (this.hash && !document.querySelector(":target")) {
            try {
                t = decodeURIComponent(this.hash.slice(1))
            } catch (i) {
                return
            }
            e = "user-content-" + t, n = document.getElementById(e) || document.getElementsByName(e)[0], null != n && n.scrollIntoView();
        }

});
});
</script>
