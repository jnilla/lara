<?php
namespace Jnilla\Lara\Controller;

defined('_JEXEC') or die;

/**
 * Master controller base class
 */
class BaseMaster extends \Joomla\CMS\MVC\Controller\BaseController{

	public $frameworkVariables = array();

	/**
	 * Constructor
	 */
	public function __construct($config = array()){
		// Initialize framework variables
		$this->frameworkVariables = \Jnilla\Lara\Helper\Base::prepareFrameworkVariables($this->frameworkVariables);

		parent::__construct($config);
	}

}


