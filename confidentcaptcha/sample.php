<?php session_start(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head runat="server">
    <title>Confident CAPTCHA</title>
    <script type='text/javascript' src='http://code.jquery.com/jquery-1.4.2.min.js'></script>    
</head>
<body>
<?php
require_once ("config.php");
require_once ("captchalib.php");

$debug = true; // Good value for initial development
//$debug = false; // Good value for production

if (isset($_REQUEST['captcha_type']) and $_REQUEST['captcha_type'] == 'single') { 
?>
  <p>This is a sample page for the single method of Confident CAPTCHA.
  If this were a real page, then this would be part of a form, such as a sign-up
  form, a blog comment form, or some other page where you want to prove that the
  user is human before allowing them access.</p>
  <p>When you solve the CAPTCHA below, nothing will happen until you submit the
  form.  At that point, the CAPTCHA will be checked.</p>
  <p>Things to try:</p>
  <ol>
    <li>Solve the CAPTCHA, then Submit.</li>
    <li>Fail the CAPTCHA, then Submit.</li>
    <li>Submit without attempting the CAPTCHA.</li>
  </ol>
<?php

    // This is how to put a ConfidentSecure Single CAPTCHA on your page
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_SESSION['confidentcaptcha_started'])) {
            // this is how you check the results of the captcha solution
            $valid = check_captcha($_REQUEST['confidentcaptcha_code'],
                $_REQUEST['confidentcaptcha_captcha_id'], $api_settings);
            if ($valid['status'] == 200) {
                $captcha_solved = ($valid['body'] == 'True');
            } else if ($valid['status'] == 404) {
                $captcha_solved=false;
            } else {
                if ($debug == true) {
                    echo "<p><i>Failed to verify CAPTCHA,, because ";
                    echo "check_captcha call failed with status code: " . $response['status'];
                    echo "<br />response body: </i><br />" . $response['body'];
                    echo "<br /><i>The form will act like the CAPTCHA succeeded.</i></p>";
                }
                $captcha_solved = true;
            }
        } else {
            if ($debug == true) {
                echo "<p><i>Failed to verify CAPTCHA, because there was no CAPTCHA.";
                echo "<br />The form will act like the CAPTCHA succeeded.</i></p>";
            }
            $captcha_solved = true;
        }
        if ($captcha_solved) {
            $check_text='<p>Success!  Try another';
        } else {
            $check_text='<p>Incorrect.  Try again';
        }
        $check_text.=", or go back to the <a href='sample.php'>config check</a>.</p>";
    }
    else {
        $check_text = "<p>Solve the CAPTCHA above, then click Submit.</p>\n";
    }
    unset($_SESSION['confidentcaptcha_started']);

    $response = create_captcha($api_settings, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
    if ($response['status'] == 200) {
        $captcha_html = $response['body'];
        $_SESSION['confidentcaptcha_started'] = true;
    } else {
        if ($debug == true) {
            echo "<p><i>No CAPTCHA available, because ";
            echo "create_captcha call failed with status code: " . $response['status'];
            echo ", response body: </i><br />" . $response['body'];
            echo "<br /><i>The form will act like there is no CAPTCHA.</i></p>";
        }
        $captcha_html = "";
    }
    echo "
    <form method='post'>
        <!-- Your other form inputs (email entry, comment entry, etc.) go here -->
        $captcha_html
        <input type='submit' name='submit' value='Submit' />
    </form>
    $check_text";
}
elseif (isset($_REQUEST['captcha_type']) and $_REQUEST['captcha_type'] == 'multiple') {
  ?>
  <p>This is a sample page for the single method of Confident CAPTCHA.
  If this were a real page, then this would be part of a form, such as a sign-up
  form, a blog comment form, or some other page where you want to prove that the
  user is human before allowing them access.</p>
  <p>When you solve the CAPTCHA below, it will immediately confirm if the CAPTCHA
  is correct.  The result will be stored in the server-side session data store.
  When you then submit the form, this data store will be checked to see what the
  result was.</p>
  <p>Things to try:</p>
  <ol>
    <li>Solve the CAPTCHA, then Submit.</li>
    <li>Fail the CAPTCHA, then Submit.</li>
    <li>Fail the CAPTCHA, then solve the second CAPTCHA, then Submit.</li>
    <li>Fail the CAPTCHA three times, then Submit.</li>
    <li>Submit without attempting the CAPTCHA.</li>
  </ol>
  <!-- Needed for ConfidentSecure Multiple CAPTCHA -->
  <script type='text/javascript'>
    var CONFIDENTCAPTCHA_CALLBACK_URL = '<?php echo $callback_url; ?>';
    var CONFIDENTCAPTCHA_INCLUDE_AUDIO = true;
  </script> 
<?php
    // This is how to put a ConfidentSecure Multiple CAPTCHA on your page
    // If this is a POST with a block_id, then verify the CAPTCHA
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_SESSION['confidentcaptcha_block_id'])) {
            // Was the audio CAPTCHA attempted?
            if (isset($_SESSION['confidentcaptcha_audio_verified'])) {
                $valid = $_SESSION['confidentcaptcha_audio_verified'];
            }
            // Was the visual CAPTCHA attempted?
            elseif (isset($_SESSION['confidentcaptcha_visual_verified'])) {
                $valid = $_SESSION['confidentcaptcha_visual_verified'];
            }
            // Check the visual code with the CAPTCHA server
            else {
                $block_id = $_REQUEST['confidentcaptcha_block_id'];
                $captcha_id = $_REQUEST['confidentcaptcha_captcha_id'];
                $code = $_REQUEST['confidentcaptcha_code'];
                if ($block_id != $_SESSION['confidentcaptcha_block_id']) {
                    // Request forgery
                    $valid=false;
                } else {
                    $response = check_instance($block_id, $captcha_id, $code, $api_settings);
                    if ($response['status'] == 200) {
                        if ($response['body'] == 'True') {
                            $valid=true;
                        }
                    } else if ($response['status'] == 404) {
                        $valid=false;
                    } else {
                        if ($debug == true) {
                            echo "<p>Failed to verify CAPTCHA, because ";
                            echo "check_instance call failed with status code: " . $response['status'];
                            echo "<br />response body: <br />" . $response['body'];
                            echo "<br />The form will act like the CAPTCHA succeeded.</p>";
                        }
                        $valid=true;
                    }
                }
            }
        } else {
            if ($debug == true) {
                echo "<p><i>Failed to verify CAPTCHA, because there was no CAPTCHA.";
                echo "<br />The form will act like the CAPTCHA succeeded.</i></p>";
            }
            $valid = true;
        }

        if ($valid) {
            $check_text='<p>Success!  Try another';
        } else {
            $check_text='<p>Incorrect.  Try again';
        }
        $check_text.=", or go back to the <a href='sample.php'>config check</a>.</p>";
    }
    else {
        $check_text='<p>Solve the CAPTCHA above, then click Submit.</p>';
    }
    unset($_SESSION['confidentcaptcha_block_id']);
    unset($_SESSION['confidentcaptcha_audio_verified']);
    unset($_SESSION['confidentcaptcha_visual_verified']);

    // Create a multi-CAPTCHA block
    $block = create_block($api_settings, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
    if ($block['status'] == 200) {
        $block_id = $block['body'];
        $_SESSION['confidentcaptcha_block_id'] = $block_id;
    } else {
        if ($debug == true) {
            echo "<p><i>No CAPTCHA available, because ";
            echo "create_block call failed with status code: " . $block['status'];
            echo ", response body: </i><br />" . $block['body'];
            echo "<br /><i>The form will act like there is no CAPTCHA.</i></p>";
        }
        $block_id = null;
    }

    if ($block_id != null) {
        // Create a visual instance in that block
        $captcha_resp = create_instance($block_id, $api_settings, 'lightbox', True, 3, 4, 3);
        if ($captcha_resp['status'] == 200) {
            $captcha_html = $captcha_resp['body'];
        } else {
            if ($debug == true) {
                echo "<p><i>No CAPTCHA available, because ";
                echo "create_instance call failed with status code: " . $response['status'];
                echo ", response body: </i><br />" . $response['body'];
                echo "<br /><i>The form will act like there is no CAPTCHA.</i></p>";
            }
            $captcha_html = "";
        }
    } else {
        // Already reported block failure
    }
    echo "
    <form method='POST'>
        <!-- Your other form inputs (email entry, comment entry, etc.) go here -->
        $captcha_html
        <input type='submit' name='submit' value='Submit'>
    </form>
    $check_text";
}

else { ?>
 <p>Welcome to the Confident CAPTCHA PHP sample.  The table below 
  details if your configuration is supported by Confident CAPTCHA.  Local settings
  are set in <tt>config.php</tt>, and remote settings come from
  <a href="http://captcha.confidenttechnologies.com/">captcha.confidenttechnologies.com</a>.</p>

  <?php
    $response = check_credentials($api_settings);
    if ($response['status'] == 200) {
        echo $response['body'];
        $credentials_good = (false === strstr($response['body'], "api_failed='True'"));
    }
    else {
        echo "check_credentials call failed with status code: " . $response['status'];
        echo "<br />response body: <br />" . $response['body'];
        $credentials_good = false;
    }

    if ($credentials_good) { echo "
        <p>
            Your configuration is supported by the Confident CAPTCHA PHP sample
            code. Use this <tt>config.php</tt> in your own project.
        </p>";
    } else { echo "
        <p>
            <b>Your configuration is <i>not</i> supported by the Confident
            CAPTCHA PHP sample code</b>.  Please fix the errors before trying the
            samples and integrating into your own project.
        </p>";
    }
  ?>
  
  <p>There are two CAPTCHA configurations available:</p>
  <ul>
    <li><a href="?captcha_type=single">Single CAPTCHA Method</a> - One CAPTCHA attempt, checked at form submit</li>
    <li><a href="?captcha_type=multiple">Multiple CAPTCHA Method</a> - Multiple CAPTCHA attempts, checked at CAPTCHA completion</li>
  </ul>
<?php } ?>
</body>
</html>
