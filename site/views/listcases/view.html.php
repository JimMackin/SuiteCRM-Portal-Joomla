<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

class advancedopenportalViewlistcases extends HtmlView
{
	// Overwriting JViewLegacy display method
	function display($tpl = null) 
	{
        include_once 'components/com_advancedopenportal/models/SugarCasesConnection.php';
        $user = $this->getCurrentUser();
        $this->errors = array();

        $contact_id = $user->getParam('sugarid');
        $caseConnection = SugarCasesConnection::getInstance();
        $this->cases = $caseConnection->getCases($contact_id);
        $this->states = $caseConnection->getStates();
        $this->validPortalUser = SugarCasesConnection::isValidPortalUser($user);
        $this->userBlocked = SugarCasesConnection::isUserBlocked($user);
        $this->contact = $caseConnection->getContact($contact_id);
		// Display the view
		parent::display($tpl);
	}
}
