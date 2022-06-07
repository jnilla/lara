<?php
namespace Jnilla\Joomla\ComponentFramework\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Object\CMSObject as JObject;
use Joomla\CMS\Toolbar\ToolbarHelper as JToolbarHelper;
use Joomla\CMS\Uri\Uri as JUri;
use Joomla\CMS\Filesystem\Folder as JFolder;
use Box\Spout\Common\Exception\EncodingConversionException;

/**
 * Helper base class
 */
class Base{
    
    /**
     * Auto register component helpers
     *
     * @param string $path
     *      Helpers folder path
     * 
     * return void
     */
    public static function autoRegisterComponentHelpers($path){
        $helpers = JFolder::files($path, '.php');
        foreach($helpers as $helper){
            $helper = basename($helper, '.php');
            \JLoader::register($helper, "$path/$helper.php");
        }
    }
    
	/**
	 * Prepares the framework variables
	 *
	 * @param array $customFrameworkVariables
	 *         Pass customs variables to merge them with
     *         the frameworks variables.
	 *
	 */
	public static function prepareFrameworkVariables($customFrameworkVariables = []){
		$frameworkVariables = [];
		
		// Load framework configurations
		$frameworkVariables['frameworkConfigurations'] = self::loadFrameworkConfigurations();
		
		// Merge $customVariables to $frameworkVariables
		$frameworkVariables = array_merge_recursive($customFrameworkVariables, $frameworkVariables);
        
		// Add component name
		$frameworkVariables['componentNameInPascalCase'] = $frameworkVariables['frameworkConfigurations']['componentNameInPascalCase'];
        
		// Add item name using the class name variable
		if(!isset($frameworkVariables['itemNameInPascalCase']) && isset($frameworkVariables['className'])){
			$frameworkVariables['itemNameInPascalCase'] = self::getItemNameFromClassName($frameworkVariables['componentNameInPascalCase'], $frameworkVariables['className']);
		}

		// Add item list name using the item name
		if(isset($frameworkVariables['itemNameInPascalCase'])){
			$frameworkVariables['itemListNameInPascalCase'] = $frameworkVariables['itemNameInPascalCase'].'List';
		}
		
		// Add current element name using current element sub type
		// Note: Element as in current model, view, controller and sub type as in item, itemList, html, etc.
		if(isset($frameworkVariables['currentElementSubType'])){
			switch ($frameworkVariables['currentElementSubType']){
				case 'item':
					$frameworkVariables['currentElementNameInPascalCase'] = $frameworkVariables['itemNameInPascalCase'];
					break;
				case 'itemList':
					$frameworkVariables['currentElementNameInPascalCase'] = $frameworkVariables['itemListNameInPascalCase'];
					break;
				case 'html':
				    $frameworkVariables['currentElementNameInPascalCase'] = $frameworkVariables['className'];
				    break;
			}
		}

		// Add letter case types variations for the following prefixes
		$prefixes = array(
			'componentName',
			'itemName',
			'itemListName',
			'currentElementName',
		);
		foreach($prefixes as $prefix){
			if(isset($frameworkVariables["{$prefix}InPascalCase"])){
				$variations = self::getLetterCaseTypesVariationsFromPascalCaseText($prefix, $frameworkVariables["{$prefix}InPascalCase"]);
				$frameworkVariables = array_merge_recursive($variations, $frameworkVariables);
			}
		}

		// Add reference to current client helper
		if(JFactory::getApplication()->isClient('site')){
			$frameworkVariables['helper'] = "Jnilla\Joomla\ComponentFramework\Helper\Site";
		}else{
			$frameworkVariables['helper'] = "Jnilla\Joomla\ComponentFramework\Helper\Admin";
		}

		// Add configurations related to current class name for easy access on variable extraction
		if(isset($frameworkVariables['className'])){
			if(isset($frameworkVariables['frameworkConfigurations'][$frameworkVariables['className']])){
				$frameworkVariables = array_merge_recursive($frameworkVariables['frameworkConfigurations'][$frameworkVariables['className']], $frameworkVariables);
			}
		}

		// Sort array for aesthetic reasons
		ksort($frameworkVariables);
		
		return $frameworkVariables;
	}

	/**
	 * Load framework configurations
	 *
	 * @return    array    Framework configuration
	 */
	public static function loadFrameworkConfigurations(){
		// Load current framework configurations file (current as in frontend or backend depending on JPATH_COMPONENT value)
		$filePath = JPATH_COMPONENT.'/framework-configurations.php';

		if(file_exists($filePath)){
			require $filePath;
			return $frameworkConfigurations;
		}
	}

	/**
	 * Get letter case types variations from pascal case text
	 *
	 * @param    array     $variationNamePrefix    Prefix used to create the names of the variations
	 * @param    string    $textInPascalCase
	 *
	 * @return    array    Array with case types variations
	 */
	public static function getLetterCaseTypesVariationsFromPascalCaseText($variationNamePrefix, $textInPascalCase){
		$variations = [];
		$variations[$variationNamePrefix."InCamelCase"] = lcfirst($textInPascalCase);
		$variations[$variationNamePrefix."InLowerCase"] = strtolower($textInPascalCase);
		$variations[$variationNamePrefix."InUpperCase"] = strtoupper($textInPascalCase);
		return $variations;
	}

	/**
	 * Get the item name from a class name
	 *
	 * @param     string    $componentNameInPascalCase    Component name in pascal case
	 * @param     string    $classNameInPascalCase    Class Name in pascal case
	 *
	 * @return    string    Item name in pascal case
	 */
	public static function getItemNameFromClassName($componentNameInPascalCase, $classNameInPascalCase){
		$result1 = preg_replace("/^{$componentNameInPascalCase}(?:Model|View|Controller|Table)/i", '', $classNameInPascalCase);
		$result2 = preg_replace("/List$/i", '', $result1);

		if($result2 === '') $result2 = $result1;

		return $result2;
	}

	/**
	 * Generate context string
	 *
	 * @param     array    $parts    Optional parts to build the context string
	 *
	 * @return    array    Context string
	 */
	public static function generateContextString($parts = array())
	{
		$app = JFactory::getApplication();
		$names = array(
			'app',
			'option',
			'model',
			'view',
			'layout',
			'tmpl',
			'lang',
			'forcedlang',
			'menuitem',
		);

		// app: Joomla application name (site, administrator)
		if(!isset($parts['app']))
		{
			$parts['app'] = $app->getName();
		}

		// option: If not set guess the component name from the request
		if(!isset($parts['option']))
		{
			$parts['option'] = $app->input->get('option', '', 'cmd');
		}

		// model
		if(isset($parts['model']))
		{
			$parts['model'] = $app->input->get('view', '', 'cmd');
		}

		// view
		if(!isset($parts['view']))
		{
			$parts['view'] = $app->input->get('view', '', 'cmd');
		}

		// layout: Usefull for modal windows.
		if(!isset($parts['layout']))
		{
			$value = $app->input->get('layout', null, 'cmd');
			if(isset($value))
			{
				$parts['layout'] = $value;
			}
		}

		// tmpl: Usefull for modal windows
		if(!isset($parts['tmpl']))
		{
			$value = $app->input->get('tmpl', null, 'cmd');
			if(isset($value))
			{
				$parts['tmpl'] = $value;
			}
		}

		// menuitem
		if(!isset($parts['menuitem']))
		{
			if($parts['app'] === 'site')
			{
				$menuItem = $app->getMenu()->getActive();
				if(empty($menuItem)) $menuItem = $app->getMenu()->getDefault();
				$parts['menuitem'] = $menuItem->id;
			}
		}

		// lang: Current language
		if(!isset($parts['lang']))
		{
			$parts['lang'] = $app->getLanguage()->getTag();
		}

		// forcedlang: Usefull when languages are forced
		if(!isset($parts['forcedlang']))
		{
			$value = $app->input->get('forcedLanguage', null, 'cmd');
			if(isset($value))
			{
				$parts['forcedlang'] = $app->getLanguage()->getTag();
			}
		}

		// Generate the context string
		$context = array();
		foreach($names as $name)
		{
			if(isset($parts[$name]))
			{
				$context[] = $name.':'.$parts[$name];
			}
		}

		return implode('.', $context);
	}



	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   JView  $view  Views object.
	 *
	 * @return  void
	 *
	 */
	public static function getActions(&$view){
		extract($view->frameworkVariables);

		$user = JFactory::getUser();
		$result = new JObject();
		$xml = JFactory::getXml(JPATH_ADMINISTRATOR."/components/com_$componentNameInLowerCase/access.xml", true);
		$actions = $xml->section->action;

		foreach($actions as $action){
			$action = (string)$action['name'];
			$result->set($action, $user->authorise($action, $componentNameInLowerCase));
		}

		return $result;
	}

	/**
	 * Merge two arrays and remove duplicates
	 *
	 * @param    array  $array1  An array
	 * @param    array  $array2  An array
	 *
	 * @return   Array    Resulting array
	 *
	 */
	public static function arrayMergeAndRemoveDuplicates($array1, $array2){
		if(!isset($array1) || !is_array($array1)) $array1 = array();
		if(!isset($array2) || !is_array($array2)) $array2 = array();

		return array_unique(array_merge($array1, $array2));
	}

	/**
	 * Creates a list of form field names
	 *
	 * @param   JForm   $form       Form object
	 * @param   string  $groupName  (optional) Form group name. If not set return all fields
	 * @param   array   $exclude    (Optional) Array of names to exclude
	 *
	 * @return    Array    Array of form fields
	 *
	 */
	public static function listFormFieldsNames($form, $groupName = "", $exclude = array()){
		$array = array();
		$fields = $form->getGroup($groupName);

		foreach($fields as $field){
			$name = $field->getAttribute('name');

			if(in_array($name, $exclude)){
				continue;
			}

			$array[] = $field->getAttribute('name');
		}

		return $array;
	}

	/*
	 * Gets the current relative URI path
	 *
	 * return    string    Current relative URI path
	 */
	static function getRelativeUriPath(){
		$baseUri = JUri::base();
		$baseUri = preg_quote($baseUri, '/');
		$currentUri = JUri::current();

		return preg_replace("/^$baseUri/", '', $currentUri, 1);
	}
}


