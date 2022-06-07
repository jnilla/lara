<?php
namespace Jnilla\Joomla\ComponentFramework\View;

defined('_JEXEC') or die;

/**
 * Item list view admin class
 */
class AdminItemList extends BaseItemList{

	protected $items;

	protected $pagination;

	protected $state;

	protected $params;

	/**
	 * Display the view
	 */
	public function display($tpl = null){
		// Extract framework variables
		extract($this->frameworkVariables);

		$model = $this->getModel();

		$this->items = $model->getItems();
		$this->pagination = $model->getPagination();
		$this->state = $model->getState();
		$this->filterForm = $model->getFilterForm();
		$this->activeFilters = $model->getActiveFilters();

		// Check for errors
		if(count($errors = $model->getErrors())){
			throw new \Exception(implode("\n", $errors));
		}

		$helper::addToolbar($this);

		$helper::addSidebar($this);

		parent::display($tpl);
	}
}



