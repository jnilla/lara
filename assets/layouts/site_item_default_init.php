<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;

// Extract framework variables
extract($this->frameworkVariables);

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');

// Define page heading
$pageHeading = JText::_("LIB_LARA_EDITING_ITEM")." id ".$this->item->id;

?>
<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function () {
		//
	});

	Joomla.submitbutton = function (task) {
		if(task == '<?php echo $itemNameInLowerCase; ?>.cancel'){
			Joomla.submitform(task, document.getElementById('<?php echo $itemNameInLowerCase; ?>-form'));
		}else{
			if (task != '<?php echo $itemNameInLowerCase; ?>.cancel' && document.formvalidator.isValid(document.id('<?php echo $itemNameInLowerCase; ?>-form'))) {
				Joomla.submitform(task, document.getElementById('<?php echo $itemNameInLowerCase; ?>-form'));
			}else{
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<h1 class="page-title"><?php echo $pageHeading; ?></h1>




