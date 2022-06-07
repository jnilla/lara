<?php
defined('_JEXEC') or die;

require JPATH_LIBRARIES.'/jnillacomponentframework/vendor/autoload.php';

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\MVC\Controller\BaseController as JControllerLegacy;
use Jnilla\Joomla\ComponentFramework\Helper\Base as JnillaComponentFrameworkBaseHelper;

extract(JnillaComponentFrameworkBaseHelper::prepareFrameworkVariables());

JFactory::getLanguage()->load('lib_jnillacomponentframework', JPATH_SITE);

JnillaComponentFrameworkBaseHelper::autoRegisterComponentHelpers(JPATH_COMPONENT_ADMINISTRATOR."/helpers");
JnillaComponentFrameworkBaseHelper::autoRegisterComponentHelpers(JPATH_COMPONENT_SITE."/helpers");

JLoader::register("{$componentNameInPascalCase}Controller", JPATH_COMPONENT.'/controller.php');

$controller = JControllerLegacy::getInstance($componentNameInPascalCase);
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

