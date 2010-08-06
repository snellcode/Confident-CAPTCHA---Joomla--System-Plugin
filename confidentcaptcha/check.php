<?php

/**
 * This script checks if your configuration is supported by Confident CAPTCHA.
 * It checks the PHP version, installed extensions, config.php, and the
 * connection to the remote site.
 */

require_once('config.php');
require_once('confidentcaptcha/ccap_api.php');
require_once('confidentcaptcha/ccap_persist.php');
require_once('confidentcaptcha/ccap_policy_factory.php');

// Check the configuration
$ccap_api = new CCAP_API(
    $ccap_api_settings['customer_id'],
    $ccap_api_settings['site_id'],
    $ccap_api_settings['api_username'],
    $ccap_api_settings['api_password'],
    $ccap_server_url);
$ccap_persist = new CCAP_PersistNull();
$policy = 'CCAP_ProductionFailOpen';
$ccap_policy = CCAP_PolicyFactory::create($policy, $ccap_api, $ccap_persist);
$ccap_policy->reset();
$check_config_response = $ccap_policy->check_config($ccap_callback_url,
    $ccap_options);
$check_config_html = $check_config_response['html'];
$config_good = $check_config_response['passed'];
if ($config_good) {
    $check_instructions = "Your configuration is supported by the 
        Confident CAPTCHA PHP sample code. Use this <tt>config.php</tt> in
        your own project.";
} else {
    $check_instructions = "<b>Your configuration is <i>not</i> supported
        by the Confident CAPTCHA PHP sample code</b>.  Please fix the 
        errors before trying the samples and integrating into your own 
        project.";
}

// Print it
echo <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head runat="server">
    <title>Confident CAPTCHA Configuration Check</title>
    <script type='text/javascript'
      src='http://code.jquery.com/jquery-1.4.2.min.js'></script>
</head>
<body>
 <h1>Confident CAPTCHA Configuration Check</h1>
 <p>
   The tables below describe your configuration and if it is supported by
   Confident CAPTCHA.  Local configuration is set in <tt>config.php</tt>, and
   remote configuration comes from
   <a href="http://captcha.confidenttechnologies.com/">captcha.confidenttechnologies.com</a>.
 </p>
 $check_config_html
 <p>$check_instructions</p>
 <p><a href="index.php">Return to the index</a>.</p>
</body>
</html>
HTML;

