<?php
namespace Jnilla\Lara\Controller;

defined('_JEXEC') or die;

/**
 * Item controller base class
 */
class BaseItem extends \Joomla\CMS\MVC\Controller\FormController{

	public $frameworkVariables = array();

	/**
	 * Constructor
	 */
	public function __construct(){
		// Add element type
		$this->frameworkVariables['currentElementSubType'] = 'item';

		// Initialize framework variables
		$this->frameworkVariables = \Jnilla\Lara\Helper\Base::prepareFrameworkVariables($this->frameworkVariables);

		$this->view_list = $this->frameworkVariables["itemListNameInCamelCase"];

		parent::__construct();
	}
}



