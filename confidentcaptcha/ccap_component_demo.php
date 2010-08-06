<?php

/*
1. save this file as /components/com_ccap_component_demo/ccap_component_demo.php
2. browse to index.php?option=com_ccap_component_demo
3. use this as a guide to implement in your own component
*/

defined('_JEXEC') or die('Restricted access');

$dispatcher = JDispatcher::getInstance();

// If this a form submission... (use in your controller method)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {  // this would not be needed in the contoller, because you know it's a submit already
	
	// get array of all responses to this event
	$check = $dispatcher->trigger('onCaptchaFormSubmit');
	
	// we'll just check the first one (we only have one captcha listener)
	if ($check[0] === false) {
		// this would actually be in your controller function 
		// that handled the form submit, and determines what to do next
		// example...
		// return false; // captcha failed, so don't allow the form
	}
}
?>

<h1><?php echo JRequest::getVar('test'); ?></h1>

<form action="index.php?option=com_ccap_component_demo" method="post">

	<input type="text" name="test" id="test" />
	<?php 
	// display method, so it look at any listeners, and will output 
	// captcha text blocks here (usually just one)
	$captchas = $dispatcher->trigger('onCaptchaFormDisplay');
	echo $captchas[0]; // just output the first enabled captcha
	?>
	<input type="submit" />
	
</form>
