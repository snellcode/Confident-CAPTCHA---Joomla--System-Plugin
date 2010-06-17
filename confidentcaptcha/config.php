<?php

// enter your login.confidenttechnologies.com account information here
$api_settings = array(
    'customer_id' => '',
    'site_id' => '',
    'api_username' => '',
    'api_password' => '',

    // you shouldn't need to modify this, unless you are running your own captcha service.
    'captcha_server_url' => 'http://captcha.confidenttechnologies.com',
);

// The path to the callback function, used in the multiple CAPTCHA method.
// If you place callback.php in the same folder as the form that contains
//  Confident CAPTCHA, then the relative URL of 'callback.php' will work.
// If you place the Confident CAPTCHA library files in a different folder,
//  you'll need a full URL path like '/confident/callback.php'
$callback_url = 'callback.php';

?>
