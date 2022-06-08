<?php
namespace Jnilla\Lara\View;

defined('_JEXEC') or die;

use Jnilla\Lara\Helper\Admin as JnAdminHelper;
use Joomla\CMS\MVC\View\HtmlView as JViewLegacy;
use Exception;

/**
 * Html view base class
 */
class BaseHtml extends JViewLegacy{

	public $frameworkVariables = array();

	/**
	 * Constructor
	 */
	public function __construct($config = array()){
		// Add element type
		$this->frameworkVariables['currentElementSubType'] = 'html';
		
		// Initialize framework variables
		$this->frameworkVariables = JnAdminHelper::prepareFrameworkVariables($this->frameworkVariables);

		parent::__construct($config);
	}

}




