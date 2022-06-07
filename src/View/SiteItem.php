<?php
namespace Jnilla\Joomla\ComponentFramework\View;

defined('_JEXEC') or die;

/**
 * Item view site class
 */
class SiteItem extends BaseItem{
	
	/**
	 * Display the view
	 */
	public function display($tpl = null){
		// Extract framework variables
		extract($this->frameworkVariables);
		
		$helper::addToolbar($this);

		parent::display($tpl);
	}
	
}




