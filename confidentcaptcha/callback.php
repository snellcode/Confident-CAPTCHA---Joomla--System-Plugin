<?php 

/* Callback resource for Confident CAPTCHA AJAX calls
 */

require_once('config.php');
require_once('confidentcaptcha/ccap_api.php');
require_once('confidentcaptcha/ccap_persist.php');
require_once('confidentcaptcha/ccap_policy_factory.php');

session_start();

$ccap_api = new CCAP_API(
    $ccap_api_settings['customer_id'],
    $ccap_api_settings['site_id'],
    $ccap_api_settings['api_username'],
    $ccap_api_settings['api_password'],
    $ccap_server_url);
$ccap_persist = new CCAP_PersistSession();
$ccap_policy = CCAP_PolicyFactory::restore($ccap_persist, $ccap_api,
    $ccap_default_policy);

/* Generate callback response */
function captcha_callback($ccap_policy)
{
    // Peform any setup needed at the start of a page w/ CAPTCHA
    $start_error = $ccap_policy->start_captcha_page();
    if ($start_error !== NULL) {
        return "Error starting callback page: $start_error";
    }
    
    $endpoint = (isset($_REQUEST['endpoint'])) ? $_REQUEST['endpoint'] : '';
    return $ccap_policy->callback($endpoint, $_REQUEST);
}

if ($ccap_policy) {    
    $ret = captcha_callback($ccap_policy);
    $content = $ret[0];
    $headers = $ret[1];
    foreach ($headers as $h) header($h);
    echo $content;
} else {
    header("HTTP/1.0 500 Server Error");
    echo 'Policy is not set, and $ccap_default_policy is invalid';
}
