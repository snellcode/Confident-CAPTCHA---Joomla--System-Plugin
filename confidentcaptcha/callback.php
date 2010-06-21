<?php 

require_once("captchalib.php");
require_once("config.php");

session_start();

function get_val($key, $default=null) {
	if (isset($_REQUEST) and isset($_REQUEST[$key])) {
		return $_REQUEST[$key];
	} else if (isset($_SESSION) and isset($_SESSION['confidentcaptcha_'.$key])) {
		return $_SESSION['confidentcaptcha_'.$key];
	} else {
		return $default;
	}
} 

if ($_REQUEST['endpoint'] == 'block_onekey_start') {
	$resp = start_block_onekey($_REQUEST['block_id'], $_REQUEST['phone_number'], $api_settings);
	$_SESSION['confidentcaptcha_onekey_id'] = $resp['body'];
	$xml = "<?xml version=\"1.0\"?>\n<response><status>".$resp['status']."</status><onekey_id>".$resp['body']."</onekey_id></response>";
	header("Content-type: text/xml"); 
	echo $xml;
}

elseif ($_REQUEST['endpoint'] == 'block_onekey_verify') {
	$resp = check_block_onekey($_REQUEST['block_id'], $_REQUEST['captcha_id'], $api_settings);
	$xml = simplexml_load_string($resp['body']);
	if ($xml->authenticated == "True") {
		$_SESSION['confidentcaptcha_onekey_verified'] = true;
	}
	header("Content-type: text/xml");
	echo $resp['body'];
}

else if ($_REQUEST['endpoint'] == 'create_block') {
	$block = create_block($api_settings, $_REQUEST['ip_addr'], $_REQUEST['user_agent']);
	echo $block['body'];
}

else if ($_REQUEST['endpoint'] == 'create_captcha_instance') {
	$display_style=get_val('display_style');
	$include_audio=get_val('include_audio');
	$height=get_val('height');
	$width=get_val('width');
	$captcha_length=get_val('captcha_length');
	$resp = create_instance($_REQUEST['block_id'], $api_settings, $display_style,
		$include_audio, $height, $width, $captcha_length);
	if ($resp['status'] == 410) {
		header($_SERVER["SERVER_PROTOCOL"]." 410 Gone");
		exit;
	}
	echo $resp['body'];
}

else if ($_REQUEST['endpoint'] == 'verify_block_captcha') {
	$resp = check_instance($_REQUEST['block_id'], $_REQUEST['captcha_id'],
		$_REQUEST['code'], $api_settings);
	if ($resp['status'] == 200) {
		if ($resp['body'] == 'True') {
			$_SESSION['confidentcaptcha_visual_verified'] = true;
			echo 'true'; exit;
		}
	}
	echo 'false'; exit;
}
