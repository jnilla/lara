<?php
namespace Jnilla\Lara\View;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as JViewLegacy;
use Exception;

/**
 * Item view base class
 */
class BaseItem extends JViewLegacy{

	public $frameworkVariables = array();

	protected $state;

	protected $item;

	protected $form;

	/**
	 * Constructor
	 */
	public function __construct($config = array()){
		// Add element type
		$this->frameworkVariables['currentElementSubType'] = 'item';

		// Initialize framework variables
		$this->frameworkVariables = \Jnilla\Lara\Helper\Base::prepareFrameworkVariables($this->frameworkVariables);

		parent::__construct($config);
	}

	/**
	 * Display the view
	 */
	public function display($tpl = null){
		// Extract framework variables
		extract($this->frameworkVariables);

		$model = $this->getModel();

		$this->state = $model->getState();
		$this->item = $model->getItem();
		$this->form = $model->getForm();

		// Check for errors
		if(count($errors = $this->get('Errors'))){
			throw new Exception(implode("\n", $errors));
		}

		// Store list of form fields from framework variables
		$formFields = $helper::listFormFieldsNames($this->form);
		$this->frameworkVariables["formFields"] = array();
		$this->frameworkVariables["formFields"] = $helper::arrayMergeAndRemoveDuplicates($this->frameworkVariables["formFields"], $formFields);

		parent::display($tpl);
	}
}




