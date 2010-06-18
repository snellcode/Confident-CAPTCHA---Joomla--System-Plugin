<?php
/**
 * @version		1.0.3
 * @package		Confident CAPTCHA
 * @author 		Phil Snell
 * @author mail	phil@snellcode.com
 * @link		http://snellcode.com
 * @copyright	Copyright (C) 2010 Phil Snell - All rights reserved.
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
		require_once(dirname(__FILE__).'/confidentcaptcha/captchalib.class.php');
		$this->api_settings = array(
			'customer_id' => $this->params->get('customer_id'),
			'site_id' => $this->params->get('site_id'),
			'api_username' => $this->params->get('api_username'),
			'api_password' => $this->params->get('api_password'),
			'captcha_server_url' => $this->params->get('captcha_server_url','http://captcha.confidenttechnologies.com/'),
		);
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
		$code = JRequest::getVar('code');
		$captcha_id = JRequest::getVar('captcha_id');
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
			if (!$this->check($code, $captcha_id)) {
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
			$this->initScripts();
			if ($captcha = $this->create()) {
				// could use DOM parser, regex, or teplate overrides as alternative methods to inject captcha
				$buffer = $document->getBuffer($renderer);
				$output = str_replace($pattern, $captcha.$pattern, $buffer);
				$document->setBuffer($output, $renderer);
			} 
		}
				
	}

	// wrapper for CC check captcha core  ( onAfterRoute() )
	function check($code, $captcha_id)
	{		
		$app =& JFactory::getApplication();
		$response = ConfidentCaptcha::check_captcha($code, $captcha_id, $this->api_settings);

		// an actual failure of the captcha test
		if ($response['status'] === 200 && $response['body'] === 'False') {
			$app->enqueueMessage('Captcha failed','error');
			return false; // deny form only when true captcha failure
		} 
			
		// all non 200 responses are not captcha errors, they are failure of credentials or server
		if ($response['status'] !== 200) {
			if ( $this->params->get('debug',1)) {
				JError::raiseWarning($response['status'], $this->toString()."\n".$response['body']);
			}
		}
		
		return true; // allow form by default
	}
		
	// wrapper for CC create_captch() method ( onAfterDispatch() )
	function create() 
	{
		$response = ConfidentCaptcha::create_captcha($this->api_settings, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] );
		if ($response['status'] !== 200 ) {
			if ( $this->params->get('debug',1)) {
				JError::raiseWarning($response['status'], $this->toString()."\n".$response['body']);
			}
			return false;
		}
		return $response['body'];
	}


	// common scripts needed for captcha display ( onAfterDispatch() )
	function initScripts()
	{
		$document =& JFactory::getDocument();
		$headData = $document->getHeadData();
		$headData['scripts']['http://code.jquery.com/jquery-1.4.2.min.js'] = 'text/javascript';
		$headData['script']['text/javascript'] = "jQuery.noConflict();\n" . $headData['script']['text/javascript'];
		$document->setHeadData($headData);
	}

}
