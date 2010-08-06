<?php
/* This is a sample form demonstrating how to integrate Confident CAPTCHA into
 * you own forms.
 *
 * This form has Confident CAPTCHA.  See sample_before.php for what the
 * form looked like before adding Confident CAPTCHA.
 */

/* Requires for Confident CAPTCHA */
require_once('config.php');
require_once('confidentcaptcha/ccap_api.php');
require_once('confidentcaptcha/ccap_persist.php');
require_once('confidentcaptcha/ccap_prod_open_policy.php');

$ccap_api = new CCAP_API(
    $ccap_api_settings['customer_id'],
    $ccap_api_settings['site_id'],
    $ccap_api_settings['api_username'],
    $ccap_api_settings['api_password'],
    $ccap_server_url);
$ccap_persist = new CCAP_PersistSession();
// Using Fail Open policy - It's OK if sales gets a little spam
$ccap_policy = new CCAP_ProductionFailOpen($ccap_api, $ccap_persist);

$ccap_policy->start_captcha_page();

// Default values are last time's values
$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
$phone = isset($_REQUEST['phone']) ? $_REQUEST['phone'] : '';
$email = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';
$comments = isset($_REQUEST['comments']) ? $_REQUEST['comments'] : '';
$alert='';

// If this a form submission...
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    // Check required elements
    $missing = array();
    if (empty($name)) $missing[] = 'Name';
    if (empty($email)) $missing[] = 'Email';
    if (empty($comments)) $missing[] = 'Comments';

    if (!empty($missing)) {
        $message = "Please fill out the following required fields: ";
        $message .= implode(', ', $missing);
        $message .= '.';
    } elseif (!strpos($email, '@', 1)) {
        // TODO: Bob - bob@bob and bob@@bob.com aren't valid, either
        $message = "Please enter a valid email address.";
    } elseif (!$ccap_policy->check_form($_REQUEST)) {
        $message = "CAPTCHA failed - please try again.";
    } else {
        // TODO: Bob - add code to email this message to sales
        $message = "Thank you for your comments, $name.\n";
        $message .= "Our sales team will get back with you shortly.";
        // Clear form
        $name = "";
        $phone = "";
        $email = "";
        $comments = "";
    }
    
    $alert = "";
    if ($message) {
        // TODO: Bob - is there something we should be doing to sanitize?
        $jmessage = preg_replace("/\r?\n/", "\\n", addslashes($message));
        $alert = "<script type=\"text/javascript\">
            alert(\"$jmessage\");
        </script>";
    }
}

// Start a new Confident CAPTCHA on every page load
$ccap_policy->reset();
$ccap_captcha = $ccap_policy->create_visual($ccap_callback_url, $ccap_options);

// Show the form on GET or POST   
echo <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Contact Us</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <style type="text/css">
  p {
      font-family: sans-serif;
      font-size: 13px;
  }
  label {
      font-family: sans-serif;
      font-size: 16px;
  }
  </style>
  <!-- Confident CAPTCHA requires jquery -->
  <script type='text/javascript'
    src='http://code.jquery.com/jquery-1.4.2.min.js'></script>
</head>
<body>
  <h1>Contact Us</h1>
  <p>Please feel free to provide feedback about your experience. Be sure to include your name and contact information so we can respond.</p>

<form method="post">
<div><label>Name: *</label></div>
<div>
 <input type="text" maxlength="128" name="name" size="60" value="$name" />
</div>
<div><label>Phone: </label></div>
<div>
 <input type="text" maxlength="128" name="phone" size="60" value="$phone" />
</div>
<div><label>Email: *</label></div>
<div>
 <input type="text" maxlength="128" name="email" size="60" value="$email" />
</div>
<div><label>Comments: *</label></div>
<div>
 <textarea cols="60" rows="5" name="comments">$comments</textarea>
</div>
<div>
<!-- Confident CAPTCHA start -->
$ccap_captcha
<!-- Confident CAPTCHA end -->
</div>
<div>
  <input type="submit" name="op" value="Submit" />
</div>
</form>
$alert
<p><a href="index.php">Return to the index</a></p>
</body>
</html>

HTML;
