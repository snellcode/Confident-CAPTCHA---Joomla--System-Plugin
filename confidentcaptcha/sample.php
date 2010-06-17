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
    <li>Solve the CAPTCHA, then Submit</li>
    <li>Fail the CAPTCHA, then Submit</li>
  </ol>
<?php
	// This is how to put a ConfidentSecure Single CAPTCHA on your page
	$body = create_captcha($api_settings, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
	echo "
	<form method='post'>
		<!-- Your other form inputs (email entry, comment entry, etc.) go here -->
		$body
		<input type='submit' name='submit' value='Submit' />
	</form>";

	if (array_key_exists('submit', $_REQUEST)) {
		// this is how you check the results of the captcha solution
		$valid = check_captcha($_REQUEST['code'], $_REQUEST['captcha_id'], $api_settings);
		echo "<p>CAPTCHA solution was ";
		if ($valid) {
			echo 'correct';
		} else {
			echo 'incorrect';
		}
		echo ". Click above to try another, or go back to the <a href='sample.php'>config check</a>.</p>";
	}
	else {
		echo "<p>Solve the CAPTCHA above, then click Submit.</p>\n";
	}
}

elseif (isset($_REQUEST['captcha_type']) and $_REQUEST['captcha_type'] == 'multiple') {
  // Multiple method requires a PHP session
  session_start();
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
    <li>Solve the CAPTCHA, then Submit</li>
    <li>Fail the CAPTCHA, then Submit</li>
    <li>Fail the CAPTCHA, then solve the second CAPTCHA, then Submit</li>
    <li>Fail the CAPTCHA three times</li>
  </ol>

  <!-- Needed for ConfidentSecure Multiple CAPTCHA -->
  <script type='text/javascript'>
    var CALLBACK_URL = '<?php echo $callback_url; ?>';
    var INCLUDE_AUDIO = true;
  </script> 
<?php
	// This is how to put a ConfidentSecure Multiple CAPTCHA on your page
	// If this is a POST with a block_id, then verify the CAPTCHA
	if (array_key_exists('block_id', $_REQUEST)) {
		// Was the audio CAPTCHA attempted?
		if (isset($_SESSION['onekey_verified'])) {
			$valid = $_SESSION['captcha_verified'];
		}
		// Was the visual CAPTCHA attempted?
		elseif (isset($_SESSION['captcha_verified'])) {
			$valid = $_SESSION['captcha_verified'];
		}
		// Check the visual code with the CAPTCHA server
		else {
			$block_id = $_REQUEST['block_id'];
			$captcha_id = $_REQUEST['captcha_id'];
			$code = $_REQUEST['code'];
			$valid = check_instance($block_id, $captcha_id, $code, $api_settings);
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
	unset($_SESSION['block_id']);
	unset($_SESSION['onekey_verified']);
	unset($_SESSION['captcha_verified']);

	// Create a multi-CAPTCHA block
	$block = create_block($api_settings, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
	$block_id = $block['body'];
	
	// Create a visual instance in that block
	$body = create_instance($block_id, $api_settings, 'lightbox', True, 3, 4, 3);
	echo "
		<form method='POST'>
		<!-- Your other form inputs (email entry, comment entry, etc.) go here -->
		$body
		<input type='submit' name='submit' value='Submit'>
	</form>";

	// The result of the last attempt, if any
	echo $check_text;	
}

else { ?>
 <p>Welcome to the Confident CAPTCHA PHP sample.  The table below 
  details if your configuration is supported by Confident CAPTCHA.  Local settings
  are set in <tt>config.php</tt>, and remote settings come from
  <a href="http://captcha.confidenttechnologies.com/">captcha.confidenttechnologies.com</a>.</p>

  <?php
    $resp = check_credentials($api_settings);
    if ($resp['status'] == 200) {
    	echo $resp['body'];
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
