<?php

$library_version = "20100621_PHP";

function call($url, $method = "POST", $params = null) {
	global $library_version;
	if ($params == null) {
		$params = array();
	}
	$params["library_version"] = $library_version;
	
	$ch = curl_init();

	if (strtolower($method) == 'post') {
		curl_setopt($ch, CURLOPT_POST, true);

		if ($params) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}
	} elseif (strtolower($method) == 'get') {
		if ($params) {
			$url .= '?' . http_build_query($params);
		}
	}

	curl_setopt($ch, CURLOPT_URL, $url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$body = curl_exec($ch);

	if ($body === false) {
		$response = array(
			'status' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
			'body' => curl_error($ch),
		);
		// TODO: Should we instead use trigger_error?  Will pollute page w/o handler
		//trigger_error($response['body'], E_USER_WARNING);
	} else {
		$response = array(
			'status' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
			'body' => $body,
		);
	}
	
	curl_close($ch);
	return $response;
}

function check_credentials($api_settings) {
	return call($api_settings['captcha_server_url'].'/check_credentials', 'GET', $api_settings);
}

function create_block($api_settings, $ipaddr, $user_agent) {
	$params = Array(
		'api_username' => $api_settings['api_username'],
		'api_password' => $api_settings['api_password'],
		'customer_id' => $api_settings['customer_id'],
		'site_id' => $api_settings['site_id'],
		'ip_addr' => $ipaddr,
		'user_agent' => $user_agent,
	);
	return call($api_settings['captcha_server_url'].'/block', 'POST', $params);
}

function create_instance($block_id, $api_settings, $display_style='flyout', 
	$include_audio=false, $height=3, $width=3, $length=4, $code_color='White') {
	$params = Array(
		'display_style' => $display_style,
		'include_audio_form' => $include_audio,
	);

	if ($height != null && $height != '') {
		$params['height'] = $height;
	}
	if ($width != null && $width != '') {
		$params['width'] = $width;
	}
	if ($length != null && $length != '') {
		$params['captcha_length'] = $length;
	}
	if ($code_color != null && $code_color != '') {
		$params['image_code_color'] = $code_color;
	}

	// Store settings for callback	
	if (isset($_SESSION)) {
		$_SESSION['confidentcaptcha_display_style'] = $display_style;
		$_SESSION['confidentcaptcha_include_audio'] = $include_audio;
		$_SESSION['confidentcaptcha_height'] = $height;
		$_SESSION['confidentcaptcha_width'] = $width;
		$_SESSION['confidentcaptcha_captcha_length'] = $length;
		$_SESSION['confidentcaptcha_code_color'] = $code_color;
	}

	return call($api_settings['captcha_server_url']."/block/$block_id/visual", 'POST', $params);
}

function check_instance($block_id, $captcha_id, $code, $api_settings) {
	$params = Array(
		'code' => $code,
	);
	return call($api_settings['captcha_server_url']."/block/$block_id/visual/$captcha_id", 'POST', $params);
}

function start_block_onekey($block_id, $phone_number, $api_settings) {
	$params = Array(
		'phone_number' => $phone_number,
	);
	return call($api_settings['captcha_server_url']."/block/$block_id/audio", 'POST', $params);
}

function check_block_onekey($block_id, $captcha_id, $api_settings) {
	return call($api_settings['captcha_server_url']."/block/$block_id/audio/$captcha_id", 'GET');
}

function create_captcha($api_settings, $ipaddr, $user_agent, $display_style='flyout',
	$include_audio=false, $height=3, $width=3, $length=4, $code_color='White') {
	$params = Array(
		'api_username' => $api_settings['api_username'],
		'api_password' => $api_settings['api_password'],
		'customer_id' => $api_settings['customer_id'],
		'site_id' => $api_settings['site_id'],
		'display_style' => $display_style,
		'ip_addr' => $ipaddr,
		'user_agent' => $user_agent,
		'height' => $height,
		'width' => $width,
		'captcha_length' => $length,
		'image_code_color' => $code_color,
	);
	return call($api_settings['captcha_server_url']."/captcha", 'POST', $params);
}

function check_captcha($code, $captcha_id, $api_settings) {
	$params = Array(
		'api_username' => $api_settings['api_username'],
		'api_password' => $api_settings['api_password'],
		'customer_id' => $api_settings['customer_id'],
		'site_id' => $api_settings['site_id'],
		'code' => $code,
	);
	return call($api_settings['captcha_server_url']."/captcha/$captcha_id", 'POST', $params);
}

function start_onekey($phone_number, $api_settings) {
	$params = Array(
		'api_username' => $api_settings['api_username'],
		'api_password' => $api_settings['api_password'],
		'customer_id' => $api_settings['customer_id'],
		'site_id' => $api_settings['site_id'],
		'phone_number' => $phone_number,
	);
	return call($api_settings['captcha_server_url']."/onekey", 'POST', $params);
}

function check_onekey($onekey_id, $api_settings) {
	$params = Array(
		'api_username' => $api_settings['api_username'],
		'api_password' => $api_settings['api_password'],
		'customer_id' => $api_settings['customer_id'],
		'site_id' => $api_settings['site_id'],
	);
	return call($api_settings['captcha_server_url']."/onekey/$onekey_id", 'POST', $params);
}
