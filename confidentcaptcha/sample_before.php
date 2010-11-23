<?php
/* This is a sample form demonstrating how to integrate Confident CAPTCHA into
 * you own forms.
 *
 * This form does not have Confident CAPTCHA.  See sample_after.php for the
 * changes needed to add Confident CAPTCHA.
 */

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
</head>
<body>
  <h1>Contact Us</h1>
  <p>Please feel free to provide feedback about your experience. Be sure to include your name and contact information so we can respond.</p>

<form method="post" action="">
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
  <input type="submit" name="op" value="Submit" />
</div>
</form>
$alert
<p><a href="index.php">Return to the index</a></p>
</body>
</html>

HTML;
