<?php
/**
 * 
 * @author Krios Mane
 * @version 0.1
 * @license https://opensource.org/licenses/GPL-3.0
 * 
 */
 
?>
<script type="text/javascript" src="/assets/js/<?php echo ENVIRONMENT ?>/app.config.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/<?php echo ENVIRONMENT ?>/fab.app.config.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/plugin/jquery-touch/jquery.ui.touch-punch.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/bootstrap/bootstrap.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/notification/SmartNotification.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/notification/FabtotumNotification.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/smartwidgets/jarvis.widget.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/plugin/msie-fix/jquery.mb.browser.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/plugin/fastclick/fastclick.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/plugin/jquery-validate/jquery.validate.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/app.min.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/fab.app.js?v=<?php echo FABUI_VERSION ?>"></script>
<script type="text/javascript" src="/assets/js/fabtotum.js?v=<?php echo FABUI_VERSION ?>"></script>
<?php if(ENVIRONMENT == 'development'): //only for development purpose ?>
<?php endif; ?>
<script type="text/javascript">
	$(document).ready(function() {
		$(".power-off").on('click', function() {
			$.SmartMessageBox({
                title: "<i class='fa fa-power-off'></i> <span class='txt-color-orangeDark'><strong>Shutd down now?</strong></span> ",
                content: "",
                buttons: "[" + _("No") + "][" + _("Yes") + "]"
            }, function(ButtonPressed) {
               if(ButtonPressed == _("Yes")) fabApp.poweroff();
           });
		});		
	});
</script>
<?php echo $jsScripts; ?>
<?php echo $jsInLine; ?>
	
