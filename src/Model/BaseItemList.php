<?php
namespace Jnilla\Lara\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper as JComponentHelper;

/**
 * Item list model base class
 */
class BaseItemList extends \Joomla\CMS\MVC\Model\ListModel{

	public $frameworkVariables = array();

	/**
	 * Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 */
	public function __construct($config = array()){
		// Add element type
		$this->frameworkVariables['currentElementSubType'] = 'itemList';

		// Initialize framework variables
		$this->frameworkVariables = \Jnilla\Lara\Helper\Base::prepareFrameworkVariables($this->frameworkVariables);

		// Extract framework variables
		extract($this->frameworkVariables);

		// Get item list view name
		$itemListViewNameInPascalCase = $componentNameInPascalCase.'View'.$itemListNameInPascalCase;

		// Use configurations from the item list view if needed
		if(isset($this->frameworkVariables['frameworkConfigurations'][$itemListViewNameInPascalCase])){
			$itemListViewConfigurations = $this->frameworkVariables['frameworkConfigurations'][$itemListViewNameInPascalCase];

			// if $orderingFields is not set try to use data from the view's configurations
			if(!isset($orderingFields) && isset($itemListViewConfigurations['columns'])){
				$orderingFields = [];
				foreach($itemListViewConfigurations['columns'] as $colum){
					$orderingFields[] = $colum['field'];
				}
				$orderingFields[] = 'id';

				$this->frameworkVariables['orderingFields'] = $orderingFields;
			}

			// if $defaultOrdering is not set try to use data from the view's configurations
			if(!isset($defaultOrdering) && isset($itemListViewConfigurations['defaultOrdering'])){
				$defaultOrdering = $itemListViewConfigurations['defaultOrdering'];

				$this->frameworkVariables['defaultOrdering'] = $defaultOrdering;
			}

			// if $textSearchFields is not set try to use data from the view's configurations
			if(!isset($textSearchFields) && isset($itemListViewConfigurations['columns'])){
				$textSearchFields = $itemListViewConfigurations['textSearchFields'];

				$this->frameworkVariables['textSearchFields'] = $textSearchFields;
			}
		}

		// Generate context string if not set
		if (!isset($this->context))
		{
			$this->context = $helper::generateContextString(array(
				'option' => "com_$componentNameInLowerCase",
				'model' => $currentElementNameInLowerCase,
			));
		}

		// Filter form file is required. Check if exist
		$formPath = JPATH_COMPONENT."/models/forms/filter_{$itemListNameInLowerCase}.xml";
		if(!file_exists($formPath)){
			throw new \Exception("Form file is required: $formPath");
		}

		// Create list of filter fields. This will be used to create database queries
		$form = $this->getFilterForm(null, false);
		$fields = $helper::listFormFieldsNames($form, "filter", array("search"));
		$this->frameworkVariables['filterFields'] = $helper::arrayMergeAndRemoveDuplicates(
			isset($this->frameworkVariables['filterFields']) ? $this->frameworkVariables['filterFields'] : '',
			$fields
		);

		// Create list of state fields. This will be used to store the fields values
		// in the user state vars
		$this->frameworkVariables['stateFields'] = $helper::arrayMergeAndRemoveDuplicates(
			isset($this->frameworkVariables['stateFields']) ? $this->frameworkVariables['stateFields'] : '',
			isset($this->frameworkVariables['filterFields']) ? $this->frameworkVariables['filterFields'] : ''
		);

		// Create list of whitelist input fields. Input fields that are not listed here will be excluded
		// for security reasons
		$this->frameworkVariables['whitelistFields'] = $helper::arrayMergeAndRemoveDuplicates(
			isset($this->frameworkVariables['whitelistFields']) ? $this->frameworkVariables['whitelistFields'] : '',
			isset($this->frameworkVariables['filterFields']) ? $this->frameworkVariables['filterFields'] : ''
		);
		$this->frameworkVariables['whitelistFields'] = $helper::arrayMergeAndRemoveDuplicates(
			$this->frameworkVariables['whitelistFields'],
			$this->frameworkVariables['stateFields']
		);
		$this->frameworkVariables['whitelistFields'] = $helper::arrayMergeAndRemoveDuplicates(
			$this->frameworkVariables['whitelistFields'],
			$this->frameworkVariables['orderingFields']
		);

		// Pass whitelist input fields to parent constructor
		$fields = $this->frameworkVariables['whitelistFields'];
		foreach($fields as $field){
			$config["filter_fields"][] = $field;
			$config["filter_fields"][] = "a.$field";
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null){
		// Extract framework variables
		extract($this->frameworkVariables);

		// Set fields states
		foreach($stateFields as $stateField){
			$state = $this->getUserStateFromRequest($this->context.".filter.$stateField", "filter_$stateField");
			$this->setState("filter.$stateField", $state);
		}

		// Set component params state
		$state = JComponentHelper::getParams("com_$componentNameInLowerCase");
		$this->setState('params', $state);

		// Set ordering state
		parent::populateState($defaultOrdering['field'], $defaultOrdering['direction']);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return   string A store id.
	 *
	 * @since    1.6
	 */
	protected function getStoreId($id = ''){
		// Extract framework variables
		extract($this->frameworkVariables);

		// Compile id
		foreach($whitelistFields as $whitelistField){
			$id .= ":".$this->getState("filter.$whitelistField");
		}

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery(){
		// Extract framework variables
		extract($this->frameworkVariables);

		// Get dbo
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select
		$query->select($this->getState('list.select', 'DISTINCT a.*'));

		// From
		$query->from("#__{$componentNameInLowerCase}_{$itemNameInLowerCase} AS a");

// 		// Join users table for row checkout support (id)
// 		$query->select("uc.name AS uEditor");
// 		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

// 		// Join users table for row checkout support (created_by)
// 		$query->select('created_by.name AS created_by');
// 		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

// 		// Join users table for row checkout support (modified_by)
// 		$query->select('modified_by.name AS modified_by');
// 		$query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');

		// Filter fields
		foreach($filterFields as $filterField){
			$state = $this->getState("filter.$filterField");
			if($state == '') continue;

			$state = $db->Quote('%'.$db->escape($state, true).'%');
			$query->where("a.$filterField LIKE $state");
		}

		// Text search. Basically applies the search term to all fields in $textSearchFields
		$state = $this->getState('filter.search');
		if(!empty($state)){
			if(stripos($state, 'id:') === 0){
				// Text search by id support
				$query->where('a.id = '.(int)substr($state, 3));
			}else{
				// TODO i think isset needs to be changed by count instead, do some testing first
				if(isset($textSearchFields)){
					$state = $db->Quote('%'.$db->escape($state, true).'%');
					$where = '';
					foreach($textSearchFields as $textSearchField){
						if($where == ""){
							$where = "a.$textSearchField LIKE $state";
						}else{
							$where .= " OR a.$textSearchField LIKE $state";
						}
					}
					$query->where("($where)");
				}
			}
		}

		// Ordering
		$orderingField = $this->getState('list.ordering');
		$orderDirection = $this->getState('list.direction');
		if($orderingField && $orderDirection){
			$query->order($db->escape($orderingField.' '.$orderDirection));
		}

		return $query;
	}
}





