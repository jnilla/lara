<?php
namespace Jnilla\Lara\View;

defined('_JEXEC') or die;

/**
 * Item view admin class
 */
class AdminItem extends BaseItem{


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




