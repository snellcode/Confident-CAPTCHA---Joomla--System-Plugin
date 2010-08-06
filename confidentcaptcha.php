<?php
/**
 * @version		1.0.4
 * @package		Confident CAPTCHA
 * @author 		Confident Technologies
 * @author mail	info@confidenttechnologies.com
 * @link		http://www.confidenttechnologies.com
 * @copyright	Copyright (C) 2010 Confident Technologies - All rights reserved.
 * @license		GNU/GPL
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.plugin.plugin' );


/**
 * Example system plugin
 */
class plgSystemConfidentCAPTCHA extends JPlugin
{
    /**
    * Constructor
    *
	* For php4 compatability we must not use the __constructor as a constructor for plugins
	* because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	* This causes problems with cross-referencing necessary for the observer design pattern.
	*
	* @access      protected
	* @paramobject  $subject The object to observe
	* @paramarray   $config  An array that holds the plugin configuration
	* @since1.0
	*/
	function plgSystemConfidentCAPTCHA( &$subject, $config )
	{
		parent::__construct( $subject, $config );
		require_once(dirname(__FILE__).'/confidentcaptcha/config.php');
		require_once(dirname(__FILE__).'/confidentcaptcha/confidentcaptcha/ccap_api.php');
		require_once(dirname(__FILE__).'/confidentcaptcha/confidentcaptcha/ccap_persist.php');
		require_once(dirname(__FILE__).'/confidentcaptcha/confidentcaptcha/ccap_prod_open_policy.php');
		
		$this->ccap_callback_url = $ccap_callback_url;
		$this->ccap_options = $ccap_options;
		
		$ccap_api = new CCAP_API(
			$this->params->get('customer_id'),
			$this->params->get('site_id'),
			$this->params->get('api_username'),
			$this->params->get('api_password'),
			$this->params->get('captcha_server_url','http://captcha.confidenttechnologies.com/')
		);
		
		$this->ccap_persist = new CCAP_PersistSession();
		$this->ccap_policy = new CCAP_ProductionFailOpen($ccap_api, $this->ccap_persist);
	}

	// the captch check logic
	function onAfterRoute()
	{

		// init vars
		$app =& JFactory::getApplication();
		$uri =& JFactory::getUri();
		$option = JRequest::getCmd('option');
		$task = JRequest::getCmd('task');		
		$check = false;
		$redirect = $uri->toString();
		
		// assign check to true for matching conditions
		if ($this->params->get('enabled_site_contact',1) && $app->isSite() && $option == 'com_contact' && $task == 'submit') {
			$check = true;
		}

		if ($this->params->get('enabled_site_registration',1) && $app->isSite() && $option == 'com_user' && $task == 'register_save') {
			$redirect = JRoute::_('index.php?option=com_user&view=register');
			$check = true;
		}
		
		if ($this->params->get('enabled_administrator_login',0) && $app->isAdmin() && $option == 'com_login' && $task == 'login') {
			$check = true;	
		}
	
		if ($check) {
			if (!$this->onCaptchaFormSubmit()) {
				$app->redirect($redirect);
				die();
			}
		}	
			
	}

	// the captch display logic
	function onAfterDispatch()
	{
		// init vars
		$app =& JFactory::getApplication();
		$document =& JFactory::getDocument();
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');	
		
		// defaults
		$display = false;
		$pattern = '<button class="button validate" type="submit">';
		$renderer = 'component';

		// assign display to true for matching conditions (override defaults)
		if ($this->params->get('enabled_site_contact',1) && $app->isSite() && $option == 'com_contact' && $view == 'contact') {
			$display = true;
		}

		if ($this->params->get('enabled_site_registration',1) && $app->isSite() && $option == 'com_user' && $view == 'register') {
			$display = true;
		}

		if ($this->params->get('enabled_administrator_login',1) && $app->isAdmin() && $option == 'com_login') {
			$display = true;
			$pattern = '<div class="button_holder">';
		}
				
		if ($display) {
			if ($captcha = $this->onCaptchaFormDisplay()) {
				// could use DOM parser, regex, or teplate overrides as alternative methods to inject captcha
				$buffer = $document->getBuffer($renderer);
				$output = str_replace($pattern, $captcha.$pattern, $buffer);
				$document->setBuffer($output, $renderer);
			} 
		}
				
	}

	// wrapper for CC check captcha core  ( onAfterRoute() )
	function onCaptchaFormSubmit()
	{	
		$app =& JFactory::getApplication();
		$this->ccap_policy->start_captcha_page();
		$check = $this->ccap_policy->check_form($_REQUEST);
		
		if (!$check) {
			$app->enqueueMessage('CAPTCHA failed - please try again.', 'error');
			return false; // block form submit
		}

		return true; // allow form submit
		
	}
		
	// wrapper for CC create_captcha() method ( onAfterDispatch() )
	function onCaptchaFormDisplay() 
	{
		if ($this->params->get('load_jquery',1)) {
			$document =& JFactory::getDocument();
			$headData = $document->getHeadData();
			$headData['scripts']['http://code.jquery.com/jquery-1.4.2.min.js'] = 'text/javascript';
			$headData['script']['text/javascript'] = "jQuery.noConflict();\n" . $headData['script']['text/javascript'];
			$document->setHeadData($headData);
		}
		
		$this->ccap_policy->reset();
		$ccap_captcha = $this->ccap_policy->create_visual($this->ccap_callback_url, $this->ccap_options);
		return $ccap_captcha;
	}

}
