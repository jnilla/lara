<?php
namespace Jnilla\Lara\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController as JControllerAdmin;

/**
 * Item list controller base class
 */
class BaseItemList extends JControllerAdmin{

	public $frameworkVariables = array();

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     \JControllerLegacy
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function __construct($config = array()){
		// Add element type
		$this->frameworkVariables['currentElementSubType'] = 'itemList';

		// Initialize framework variables
		$this->frameworkVariables = \Jnilla\Lara\Helper\Base::prepareFrameworkVariables($this->frameworkVariables);

		$this->text_prefix = "LIB_JNILLACOMPONENTFRAMEWORK";

		parent::__construct($config);
	}

	/**
	 * Proxy for getModel.
	 */
	public function getModel($name = '', $prefix = '', $config = array()){
		// Extract framework variables
		extract($this->frameworkVariables);

		if(empty($name)){
			$name = $itemNameInLowerCase;
		}
		if(empty($prefix)){
			$prefix = "{$componentNameInLowerCase}Model";
		}
		if(empty($config)){
			$config = array('ignore_request' => true);
		}

		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}


