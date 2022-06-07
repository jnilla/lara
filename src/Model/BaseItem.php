<?php
namespace Jnilla\Joomla\ComponentFramework\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel as JModelAdmin;
use Joomla\CMS\Table\Table as JTable;
use Joomla\CMS\Factory as JFactory;
use Exception;

/**
 * Item model base class
 */
class BaseItem extends JModelAdmin{

	public $frameworkVariables = array();

	protected $item = null;

	// TODO, check if this code can be removed
// 	public function populateState(){
// 		parent::populateState();
// 	}

	/**
	 * Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 */
	public function __construct($config = array()){
		// Add element type
		$this->frameworkVariables['currentElementSubType'] = 'item';

		// Initialize framework variables
		$this->frameworkVariables = \Jnilla\Joomla\ComponentFramework\Helper\Base::prepareFrameworkVariables($this->frameworkVariables);

		parent::__construct($config);
	}

	/**
	 * Returns a reference to the a Table object, always creating it
	 */
	public function getTable($type = '', $prefix = '', $config = array()){
		// Extract framework variables
		extract($this->frameworkVariables);

		if(empty($type)){
			$type = $itemNameInLowerCase;
		}

		if(empty($prefix)){
			$prefix = "{$componentNameInCamelCase}Table";
		}

		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the form.
	 */
	public function getForm($data = array(), $loadData = true){
		// Extract framework variables
		extract($this->frameworkVariables);

		// Check if form file exist
		$formPath = JPATH_COMPONENT . "/models/forms/$itemNameInLowerCase.xml";
		if(!file_exists($formPath)){
			throw new Exception("Form file is required: $formPath");
		}

		// Load the form
		$form = $this->loadForm("com_$componentNameInLowerCase.$itemNameInLowerCase", $itemNameInLowerCase, array(
			'control' => 'jform',
			'load_data' => $loadData
		));

		if(empty($form)){
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 */
	protected function loadFormData(){
		// Extract framework variables
		extract($this->frameworkVariables);

		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState("com_$componentNameInLowerCase.edit.$itemNameInLowerCase.data", array());

		if(empty($data)){
			if($this->item === null) $this->item = $this->getItem();
			$data = $this->item;
		}

		return $data;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 */
	protected function prepareTable($table){
		// Extract framework variables
		extract($this->frameworkVariables);

		jimport('joomla.filter.output');


		// TODO: Add code to detect if ordering field exist or not, because this code throws an error if column does not exist
// 		if(empty($table->id)){
// 			// Set ordering to the last item if not set
// 			if(!isset($table->ordering)){
// 				$db = JFactory::getDbo();
// 				$db->setQuery("SELECT MAX(ordering) FROM #__{$componentNameInLowerCase}_{$itemNameInLowerCase}");
// 				$max = $db->loadResult();
// 				$table->ordering = $max + 1;
// 			}
// 		}
	}
}



