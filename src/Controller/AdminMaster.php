<?php
namespace Jnilla\Joomla\ComponentFramework\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Uri\Uri as JUri;

/**
 * Master controller admin class
 */
class AdminMaster extends BaseMaster
{

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   mixed    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return   JController This object to support chaining.
	 *
	 */
	public function display($cachable = false, $urlparams = false){
		// Extract framework variables
		extract($this->frameworkVariables);

		// If view is not set redirect user to default view and display a warning
		if($this->input->get('view', '', 'CMD') === '')
		{
			$relUrl = preg_replace('/^'.preg_quote(JUri::base(), '/').'/', '', JUri::getInstance()->toString());
			$url = "index.php?option=com_$componentNameInLowerCase&view=$defaultViewName";

			// Redirect with no warning because this is the default menu item url
			if($relUrl === "index.php?option=com_$componentNameInLowerCase")
			{
				$this->setRedirect($url);
			}
			// Redirect with warning
			else
			{
				$this->setRedirect($url, JText::_('LIB_JNILLACOMPONENTFRAMEWORK_WARNING_EMPTY_VIEW'), 'warning');
			}
			$this->redirect();
		}

		parent::display($cachable, $urlparams);

		return $this;
	}

}




