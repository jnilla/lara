<?php
defined('_JEXEC') or die;

require __DIR__."/../../../autoload.php";

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\MVC\Controller\BaseController as JControllerLegacy;
use Jnilla\Lara\Helper\Base as LaraBaseHelper;

extract(LaraBaseHelper::prepareFrameworkVariables());

JFactory::getLanguage()->load('lib_lara', JPATH_SITE);

LaraBaseHelper::autoRegisterComponentHelpers(JPATH_COMPONENT_ADMINISTRATOR."/helpers");
LaraBaseHelper::autoRegisterComponentHelpers(JPATH_COMPONENT_SITE."/helpers");

JLoader::register("{$componentNameInPascalCase}Controller", JPATH_COMPONENT.'/controller.php');

$controller = JControllerLegacy::getInstance($componentNameInPascalCase);
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

