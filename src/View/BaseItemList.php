<?php
namespace Jnilla\Joomla\ComponentFramework\View;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as JViewLegacy;
use Exception;

/**
 * Item list view base class
 */
class BaseItemList extends JViewLegacy{

	public $frameworkVariables = array();

	/**
	 * Constructor
	 */
	public function __construct($config = array()){
		// Set element sub type
		$this->frameworkVariables['currentElementSubType'] = 'itemList';

		// Initialize framework variables
		$this->frameworkVariables = \Jnilla\Joomla\ComponentFramework\Helper\Base::prepareFrameworkVariables($this->frameworkVariables);

		// Extract framework variables
		extract($this->frameworkVariables);

		// Filter form file is required. Check if exist
		$formPath = JPATH_COMPONENT."/models/forms/filter_{$itemListNameInLowerCase}.xml";
		if(!file_exists($formPath)) throw new Exception("Form file is required: $formPath");

		parent::__construct($config);
	}
}




