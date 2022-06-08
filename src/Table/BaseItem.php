<?php
namespace Jnilla\Lara\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\Registry\Registry as JRegistry;
use Joomla\CMS\Access\Access as JAccess;
use Joomla\CMS\Table\Table as JTable;

/**
 * Item table base class
 */
class BaseItem extends \Joomla\CMS\Table\Table{

	public $frameworkVariables = array();

	/**
	 * Constructor
	 */
	public function __construct(&$db){
		// Initialize framework variables
		$this->frameworkVariables = \Jnilla\Lara\Helper\Base::prepareFrameworkVariables($this->frameworkVariables);

		// Extract framework variables
		extract($this->frameworkVariables);

		// Define subform fields to set correct json encoding
		$formPath = JPATH_COMPONENT . "/models/forms/$itemNameInLowerCase.xml";
		if(file_exists($formPath)){
			$form = simplexml_load_file($formPath);
			foreach($form->fields->field as $field){
				if($field['type'] == 'subform'){
					$this->_jsonEncode[] = (string)$field['name'];
				}
			}
		}

		parent::__construct("#__{$componentNameInLowerCase}_{$itemNameInLowerCase}", "id", $db);
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 */
	public function bind($src, $ignore = ""){
		// Extract framework variables
		extract($this->frameworkVariables);

		if($src["id"] == 0){
			$src["created_by"] = JFactory::getUser()->id;
		}

		if($src["id"] == 0){
			$src["modified_by"] = JFactory::getUser()->id;
		}

		if(isset($src["params"]) && is_array($src["params"])){
			$registry = new JRegistry();
			$registry->loadArray($src["params"]);
			$src["params"] = (string)$registry;
		}

		if(isset($src["metadata"]) && is_array($src["metadata"])){
			$registry = new JRegistry();
			$registry->loadArray($src["metadata"]);
			$src["metadata"] = (string)$registry;
		}

		if(!JFactory::getUser()->authorise("core.admin", "$componentNameInLowerCase.$itemNameInLowerCase" . $src["id"])){
			$actions = JAccess::getActionsFromFile(JPATH_ADMINISTRATOR . "/components/com_$componentNameInLowerCase/access.xml", "/access/section[@name=\"$itemNameInLowerCase\"]/");
			$default_actions = JAccess::getAssetRules("$componentNameInLowerCase.$itemNameInLowerCase" . $src["id"])->getData();
			$array_jaccess = array();

			foreach($actions as $action){
				$array_jaccess[$action->name] = $default_actions[$action->name];
			}

			$src["rules"] = $this->JAccessRulestoArray($array_jaccess);
		}

		// Bind the rules for ACL where supported.
		if(isset($src["rules"]) && is_array($src["rules"])){
			$this->setRules($src["rules"]);
		}

		return parent::bind($src, $ignore);
	}

	/**
	 * This function convert an array of JAccessRule objects into an rules array.
	 */
	private function JAccessRulestoArray($jaccessrules){
		$rules = array();

		foreach($jaccessrules as $action => $jaccess){
			$actions = array();

			if($jaccess){
				foreach($jaccess->getData() as $group => $allow){
					$actions[$group] = ((bool)$allow);
				}
			}

			$rules[$action] = $actions;
		}

		return $rules;
	}

	/**
	 * Overloaded check function
	 */
	public function check(){
		// If there is an ordering column and this is a new row then get the next ordering value
		if(property_exists($this, "ordering") && $this->id == 0){
			$this->ordering = self::getNextOrder();
		}

		return parent::check();
	}

	/**
	 * Define a namespaced asset name for inclusion in the #__assets table
	 */
	protected function _getAssetName(){
		// Extract framework variables
		extract($this->frameworkVariables);

		$k = $this->_tbl_key;

		return "com_$componentNameInLowerCase." . $itemNameInLowerCase . "." . (int)$this->$k;
	}

	/**
	 * Returns the parent asset"s id. If you have a tree structure, retrieve the parent"s id using the external key field
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null){
		// Extract framework variables
		extract($this->frameworkVariables);

		// We will retrieve the parent-asset from the Asset-table
		$assetParent = JTable::getInstance("Asset");

		// Default: if no asset-parent can be found we take the global asset
		$assetParentId = $assetParent->getRootId();

		// The item has the component as asset-parent
		$assetParent->loadByName("com_$componentNameInLowerCase");

		// Return the found asset-parent-id
		if($assetParent->id){
			$assetParentId = $assetParent->id;
		}

		return $assetParentId;
	}

	/**
	 * Delete a record by id
	 */
	public function delete($pk = null){
		$this->load($pk);
		$result = parent::delete($pk);

		return $result;
	}
}
