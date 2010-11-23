<?php
require_once('config.php');
require_once('confidentcaptcha/ccap_api.php');

$ccap_api= new CCAP_API(
    $ccap_api_settings['customer_id'],
    $ccap_api_settings['site_id'],
    $ccap_api_settings['api_username'],
    $ccap_api_settings['api_password'],
    $ccap_server_url);

// Has the configuration been tested?
$cred_check = $ccap_api->check_credentials();
if ($cred_check->status != 200) {
    $credentials_good = FALSE;
} else {
    $credentials_good = (FALSE === stripos($cred_check->body,
        "api_failed='True'"));
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <title>Confident CAPTCHA - PHP Library and Sample Code</title>
</head>
<body>
  <h1>PHP Library and Sample Code for Confident CAPTCHA</h1>

<?php if ($credentials_good) { ?>
<p>
The samples are:
</p>
<ul>
  <li><a href="check.php">check.php</a> - Check if your
    configuration is supported by Confident CAPTCHA</li>
  <li><a href="explore.php">explore.php</a> - Try out Confident CAPTCHA options</li>
  <li><a href="sample_before.php">sample_before.php</a> - A sample form before
    adding Confident CAPTCHA</li>
  <li><a href="sample_after.php">sample_after.php</a> - A sample form after adding Confident CAPTCHA</li>
  <?php if (file_exists('sample_diff.html')) { ?>
    <li><a href="sample_diff.html">sample_diff.html</a> - The differences between sample_before.php and sample_after.php</li>
  <?php } else { ?>
    <!-- sample_diff.html not created.  Run make_docs.sh -->
  <?php } ?>
</ul>
<p>You can also read the <a href="docs/index.html">library reference</a>.</p>

<?php } else /* credentials not good */ { ?>

<p>
Your credentials in <tt>config.php</tt> are <b>not</b> valid.  See 
<a href="check.php">check.php</a> for details.  Please edit
<tt>config.php</tt> and add or fix your API credentials.  If you do not have an
account yet, please 
<a href="http://www.confidenttechnologies.com/purchase">
sign up for a free account
</a>.
</p>

<?php } ?>

<p>
If you have any questions, feel free to
<a href="http://www.confidenttechnologies.com/contact">contact us</a>.
</p>

</body>
</html>
