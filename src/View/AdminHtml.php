<?php
namespace Jnilla\Joomla\ComponentFramework\View;

defined('_JEXEC') or die;

use Jnilla\Joomla\ComponentFramework\Helper\Admin as JnAdminHelper;

/**
 * Html view admin class
 */
class AdminHtml extends BaseHtml{

	/**
	 * Display the view
	 */
	public function display($tpl = null){
		JnAdminHelper::addToolbar($this);
		
		JnAdminHelper::addSidebar($this);

		parent::display($tpl);
	}


}




