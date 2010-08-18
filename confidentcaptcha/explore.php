<?php

/**
 * This is a demonstration of the Confident CAPTCHA library.  To use this
 * demo, you must sign up for a free account at:
 *  http://confidenttechnologies.com/purchase/CAPTCHA_signup.php
 *
 * The code is a bit complex, indended to be viewed from a browser rather than
 * read as code.  See 'sample_before.php' and 'sample_after.php' for a
 * practical example of how to add Confident CAPTCHA to your own forms.  Be
 * sure to also look for a plugin for your platform, which is even easier.
 */

require_once('config.php');
require_once('confidentcaptcha/ccap_api.php');
require_once('confidentcaptcha/ccap_persist.php');
require_once('confidentcaptcha/ccap_policy_factory.php');

// Get a value from an array, or NULL
function array_get($a, $key) {
    return (isset($a[$key])) ? $a[$key] : NULL;
}

// Use configured state as the working API
$ccap_api_good = new CCAP_API(
    $ccap_api_settings['customer_id'],
    $ccap_api_settings['site_id'],
    $ccap_api_settings['api_username'],
    $ccap_api_settings['api_password'],
    $ccap_server_url);

// Has the configuration been tested?
$settings_good = array_get($_REQUEST, 'ccap_settings_good');
if (!$settings_good) {
    $cred_check = $ccap_api_good->check_credentials();
    if ($cred_check->status != 200) {
        $settings_good = FALSE;
    } else {
        $settings_good = (FALSE === stripos($cred_check->body,
            "api_failed='True'"));
    }
}

// Use a working system or a bad one?
$fail_sim = array_get($_REQUEST, 'ccap_fail_sim');
$valid_fail_sims = Array(
    '' => 'No simulated API failure.',
    'bad_credentials' => 'All API calls using API credentials will fail with
        HTTP code 401 NOT AUTHORIZED, as if using bad credentials.',
    'server_not_responding' => 'All API calls will fail with status 0,
        as if the CAPTCHA API server is unavailable.'
);
if (!in_array($fail_sim, array_keys($valid_fail_sims))) {
    $fail_sim = NULL;
}

if (empty($fail_sim)) {
    // Use good API
    $ccap_api = $ccap_api_good;
} elseif ($fail_sim == 'bad_credentials') {
    class CCAP_Api401 extends CCAP_Api {
        protected function call($resource, $method, $params, 
            $use_credentials)
        {
            if ($use_credentials) {
                $response = $this->shortcut($resource, $method, $params,
                    $use_credentials, 401, 'Not Authorized (Fake)');
            } else {
                $response = parent::call($resource, $method, $params, 
                    $use_credentials);
            }
            return $response;
        }
    }
    $ccap_api = new CCAP_Api401(
        $ccap_api_settings['customer_id'],
        $ccap_api_settings['site_id'],
        $ccap_api_settings['api_username'],
        $ccap_api_settings['api_password'],
        $ccap_server_url);
} elseif ($fail_sim == 'server_not_responding') {
    class CCAP_ApiDead extends CCAP_Api {
        protected function call($resource, $method, $params, 
            $use_credentials)
        {
            return $this->shortcut($resource, $method, $params,
                $use_credentials, 0, 'Server not responding (fake)');
        }
    }
    $ccap_api = new CCAP_ApiDead(
        $ccap_api_settings['customer_id'],
        $ccap_api_settings['site_id'],
        $ccap_api_settings['api_username'],
        $ccap_api_settings['api_password'],
        $ccap_server_url);
}

// TODO: Different persistence methods?
$ccap_persist = new CCAP_PersistSession();

// Pick the policy
$policy = array_get($_REQUEST, 'ccap_policy');
$valid_policies = Array(
    '' => "Use default of $ccap_default_policy", 
    'CCAP_ProductionFailOpen' => 'When CAPTCHA creation fails,
        the form will succeed.  Useful for contact forms, where you want
        the form to work even when the CAPTCHA doesn\'t.',
    'CCAP_ProductionFailClosed' => 'When CAPTCHA creation fails,
        the form will fail.  Useful for account creation forms, where you
        don\'t want the form to proceed without a CAPTCHA check.',
    'CCAP_DevelopmentPolicy' => 'Records all calls made to the
        CAPTCHA API server.  Useful for initial form development and
        troubleshooting, but will leak secrets if used in production.'
);
if (empty($policy)) {
    $used_policy = $ccap_default_policy;
} elseif (in_array($policy, array_keys($valid_policies))) {
    $used_policy = $policy;
} else {
    $used_policy = $ccap_default_policy;
    if (!in_array($used_policy, array_keys($valid_policies))) {
        die("Bad default policy '$ccap_default_policy'");
    }
}
$ccap_policy = CCAP_PolicyFactory::create($used_policy, $ccap_api,
    $ccap_persist);


function option_get($key) {
    if (isset($_REQUEST["ccap_$key"])) {
        return $_REQUEST["ccap_$key"];
    } elseif (isset($ccap_options[$key])) {
        return $ccap_options[$key];
    } else {
        return NULL;
    }
}
// Load CAPTCHA parameters
$display_style = option_get('display_style');
$include_audio = option_get('include_audio');
$height = option_get('height');
$width = option_get('width');
$length = option_get('length');
$code_color = option_get('code_color');

// Calculate CAPTCHA strength
function factorial ($x) 
{
    if ($x <= 1)
        return 1;
    else
        return ($x * factorial ($x-1));
}

function captcha_strength($height, $width, $length)
{
    $images = $height * $width;
    if ($length >= $images)
        return 0;
    else
        return factorial($images) / factorial($images - $length);
}

$used_height = intval($height);
$used_width = intval($width);
$used_length = intval($length);
if (empty($height)) $used_height = 3;
if (empty($width)) $used_width = 3;
if (empty($length)) $used_length = 4;
$strength = captcha_strength($used_height, $used_width, $used_length);
$strength_text = "1 in $strength chance of a spam bot randomly guessing
  correctly, based on height, width, length";
if ($strength < 1000) $strength_text .= ' (must be at least 1 in 1000)';

// display_style
$valid_display_styles = Array(
    '' => "Use default of 'flyout'", 
    'flyout' => 'When clicked, the CAPTCHA flies out from the button',
    'lightbox' => 'When clicked, the CAPTCHA appears in a lightbox',
    'modal' => 'When clicked, the CAPTCHA appears in a modal lightbox',
);
if (!in_array($display_style, array_keys($valid_display_styles))) {
    $display_style = NULL;
}

// include_audio
if ($include_audio == 'TRUE' or $include_audio === '1') {
    $include_audio = TRUE;
} elseif ($include_audio == 'FALSE' or $include_audio === '0') {
    $include_audio = FALSE;
} elseif (!is_bool($include_audio)) {
    $include_audio = NULL;
}

if (!is_numeric($height)) $height = NULL;
if (!is_numeric($width)) $width = NULL;
if (!is_numeric($length)) $length = NULL;

$valid_colors = array('White', 'Red', 'Orange', 'Yellow', 'Green', 'Teal',
    'Blue', 'Indigo', 'Violet', 'Gray');
if (!in_array($code_color, $valid_colors)) $code_color= NULL; 

/* URL query for these settings */
function url($extra = NULL)
{
    global $display_style, $include_audio, $height, $width, $length,
        $code_color, $policy, $settings_good, $fail_sim;

    $p = array();
    if ($settings_good) $p['ccap_settings_good'] = $settings_good;
    if (!empty($display_style)) $p['ccap_display_style'] = $display_style;
    if (!is_null($include_audio)) $p['ccap_include_audio'] = $include_audio;
    if (!empty($height)) $p['ccap_height'] = $height;
    if (!empty($width)) $p['ccap_width'] = $width;
    if (!empty($length)) $p['ccap_length'] = $length;
    if (!empty($code_color)) $p['ccap_code_color'] = $code_color;
    if (!empty($policy)) $p['ccap_policy'] = $policy;
    if (!empty($fail_sim)) $p['ccap_fail_sim'] = $fail_sim;
    
    if (is_array($extra)) $p = array_merge($p, $extra);

    $url = $_SERVER['SCRIPT_NAME'];
    if ($p) $url .= '?' . http_build_query($p);
    return $url;
}

/* Page Templates */

/* Generate a page from a template and an array of key=>value */
function generate_page($template, $tags)
{
    $page = $template;
    $keys = array();
    $values = array();
    foreach ($tags as $key => $value) {
        $page = str_replace('{'.$key.'}', $value, $page);
    }
    return $page;
}

// Debug HTML and JavaScript (Development policy only)
if ($used_policy == 'CCAP_DevelopmentPolicy') {
    $debug_area = $ccap_policy->get_debug_html($ccap_callback_url);
} else {
    $debug_area = '';
}

// Shared page header
$header_template = <<<TEMPLATE
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head runat="server">
    <title>{TITLE}</title>
    <script type='text/javascript'
      src='http://code.jquery.com/jquery-1.4.2.min.js'></script>
</head>
TEMPLATE;

// Error page template
$error_template = $header_template . <<<TEMPLATE
  <p>We're sorry, something went wrong.  Please try again later.</p>
  {ERROR}
  $debug_area
</body>
</html>
TEMPLATE;

// CAPTCHA page template
$captcha_template = $header_template . <<<TEMPLATE
<body>
  <h1>Confident CAPTCHA Demonstration</h1>
  <p>
    This is a demo of Confident CAPTCHA. If this were a real page, then this
    would be part of a form, such as a sign-up form, a blog comment form, or
    some other page where you want to prove that the user is human before
    allowing them access.
  </p>
  <p>{WHEN_CHECKED}</p>
  <p>Things to try:</p>
  <ol>
    {THINGS_TO_TRY}
  </ol>
  <form method='POST'>
      <!-- Your other form inputs (email, comments, etc.) go here -->
      {CAPTCHA_HTML}
      <input type='submit' name='submit' value='Submit'>
  </form>
  <p>{CHECK_CAPTCHA_TEXT}</p>
  $debug_area
</body>
</html>
TEMPLATE;

/* Generate the captcha page */
function captcha_page($with_callback, $ccap_policy)
{
    global $captcha_template, $error_template, $ccap_callback_url,
        $display_style, $include_audio, $height, $width, $length, 
        $code_color;

    $title = 'Confident CAPTCHA';

    // Peform any setup needed at the start of a page w/ CAPTCHA
    $start_error = $ccap_policy->start_captcha_page();
    if ($start_error !== NULL) {
        $tags = array(
            'ERROR'       => $start_error,
            'TITLE'       => $title.' - Configuration Error',
            'HEAD_SCRIPT' => ''
        );
        $error_page = generate_page($error_template, $tags);
        return $error_page;
    }
    
    // If POST, then check last CAPTCHA
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $captcha_solved = $ccap_policy->check_form($_REQUEST);
        // For this sample, just print if successful or not.
        if ($captcha_solved) {
            $check_captcha_text = 'Success!  Try another, or ';
        } else {
            $check_captcha_text = 'Incorrect.  Try again, or ';
        }
    } else {
        $check_captcha_text = "Solve the CAPTCHA above, then click Submit.
            The result will appear here.  Or, you can ";
    }
    $url = url();
    $check_captcha_text .= "<a href=\"$url\">go back to the previous page</a>
        to change the CAPTCHA type or settings.";
    

    // On both POST and GET, Generate new CAPTCHA HTML
    $ccap_policy->reset();
    $callback_url = ($with_callback) ? $ccap_callback_url : NULL;
    $captcha_html = $ccap_policy->create_visual($callback_url, array(
        'display_style' => $display_style,
        'include_audio' => $include_audio,
        'height' => $height,
        'width' => $width,
        'length' => $length,
        'code_color' => $code_color));

    // Insert the Confident CAPTCHA into page template
    if ($with_callback) {
        // CAPTCHA with a callback for instant feedback
        $things_to_try = "
            <li>Solve the CAPTCHA, then Submit.</li>
            <li>Fail the CAPTCHA, then Submit.</li>
            <li>Fail the CAPTCHA, then solve the second CAPTCHA, then Submit.</li>
            <li>Fail the CAPTCHA three times, then Submit.</li>
            <li>Submit without attempting the CAPTCHA.</li>";
        $when_checked = "
            When you solve the CAPTCHA below, it will immediately confirm if
            the CAPTCHA is correct.  The result will be stored in the 
            server-side session data store.  When you then submit the form, 
            this data store will be checked to see what the result was.";
    } else {
        // CAPTCHA without a callback
        $things_to_try = "
            <li>Solve the CAPTCHA, then Submit.</li>
            <li>Fail the CAPTCHA, then Submit.</li>
            <li>Submit without attempting the CAPTCHA.</li>";
        $when_checked = "
            When you solve the CAPTCHA below, nothing will happen until you
            submit the form.  At that point, the CAPTCHA will be checked.";
    }
    $tags = array(
        'TITLE' => $title,
        'HEAD_SCRIPT' => "",
        'WHEN_CHECKED' => $when_checked,
        'THINGS_TO_TRY' => $things_to_try,
        'CAPTCHA_HTML' => $captcha_html,
        'CHECK_CAPTCHA_TEXT' => $check_captcha_text,
        'CONFIG_URL' => url()
    );
    $captcha_page = generate_page($captcha_template, $tags);

    return $captcha_page;
}

/* Construct the settings as a list */
function settings_sublist($valid_array, $my_value, $url_key)
{
    $m = $my_value;
    $u = $url_key;
    $o = '';
    foreach($valid_array as $k => $d) {
        $o .= "\n    <li>";
        if ($k != $m) $o .= '<a href="' . url(array($u => $k)) . '">';
        else $o .= '<b>';
        $o .= ($k) ? $k : '&lt;Unset&gt;';
        if ($k == $m) $o .= '</b> (selected)';
        else $o.='</a>';
        $o .= " - $d";
        $o .= '</li>';
    }
    return $o;
}

// New settings form
function new_settings_form()
{
    global $display_style, $include_audio, $height, $width, $length, 
        $code_color, $policy, $settings_good, $fail_sim,
        $valid_policies, $valid_display_styles, $valid_colors, 
        $valid_fail_sims, $ccap_default_policy, $strength_text;
    
    $policy_sublist = settings_sublist($valid_policies, $policy,
        'ccap_policy');
    $fail_sublist = settings_sublist($valid_fail_sims, $fail_sim,
        'ccap_fail_sim');
    $display_sublist = settings_sublist($valid_display_styles,
        $display_style, 'ccap_display_style');
    
    $audio_options = Array(
        '' => 'Use default of no audio',
        'TRUE' => 'Include audio option (if enabled for account)',
        'FALSE' => 'Don\'t include audio option');
    if ($include_audio === TRUE or $include_audio === 1) {
        $iaudio = 'TRUE';
    } elseif ($include_audio === FALSE or $include_audio === 0) {
        $iaudio = 'FALSE';
    } else {
        $iaudio = '';
    }
    $audio_sublist = settings_sublist($audio_options, $iaudio,
        'ccap_include_audio');
    
    if (empty($code_color)) {
        $colors = '<b>&lt;Unset&gt;</b>(default White)';
    } else {
        $colors = '<a href="' . url(array('ccap_code_color' => '')) .
            '">&lt;Unset&gt;</a>(default White)';
    }
    foreach($valid_colors as $c) {
        $colors .= ', ';
        if ($code_color == $c) {
            $colors .= "<b>$c</b> (selected)";
        } else {
            $colors .= '<a href="' . url(array('ccap_code_color' => $c)) .      
                "\">$c</a>";
        }
    }
    $colors .= '.';
    
    $dh = (empty($height)) ? '(default 3)' : '';
    $dw = (empty($width))  ? '(default 3)' : '';
    $dl = (empty($length)) ? '(default 4)' : '';
    
    $f = <<<FORM
<h2>CAPTCHA Settings</h2>
<form>
<ul>
 <li>CAPTCHA Policy:<ul>$policy_sublist</ul></li>
 <li>Failure Simulation:<ul>$fail_sublist</ul></li>
 <li>Display Style:<ul>$display_sublist</ul></li>
 <li>Include Audio?:<ul>$audio_sublist</ul></li>
 <li>Image Code Color:$colors</li>
</ul>
<h3>CAPTCHA Strength settings</h3>
<ul>
 <li>Height: <input type="text" name="ccap_height" value="$height" />$dh</li>
 <li>Width: <input type="text" name="ccap_width" value="$width" />$dw</li>
 <li>Length: <input type="text" name="ccap_length" value="$length" />$dl</li>
 <li>Current Strength: $strength_text</li>
</ul>
<input type="submit" value="Submit" />
</form>
FORM;
    return $f;
}

// Sample index page template - good config
$good_index_template = $header_template . <<<TEMPLATE
<body>
  <h1>Confident CAPTCHA Explorer</h1>
  <p>(<a href="index.php">Return to the index</a>)</p>
  <p>There are two main ways to add Confident CAPTCHA to your form:</p>
  <ul>
  <li><a href="{IN_PAGE_URL}">Instant Verification CAPTCHA Method</a> -
    CAPTCHA is checked on completion, and the user gets multiple chances.
    This requires a callback resource for the instant verification.
  </li>
  <li><a href="{AT_POST_URL}">Delayed Verification CAPTCHA Method</a> -
    CAPTCHA is checked after form submission, and the user gets one chance.
    Audio CAPTCHA not supported.
  </li>
 </ul>
 {NEW_SETTINGS_FORM}
 {FAIL_MESSAGE}
 $debug_area
</body>
</html>
TEMPLATE;

// Sample index page template - bad config
$bad_index_template = $header_template . <<<TEMPLATE
<body>
 <h1>Confident CAPTCHA Demonstration - Errors Detected</h1>
 <p>
 Your configuration is NOT supported by Confident CAPTCHA.  Please visit the
 <a href="check.php">configuration check page</a> and fix any problems.
 </p>
 $debug_area
</body>
</html>
TEMPLATE;

/* Generate the index page */
function index_page($ccap_policy)
{
    global $good_index_template, $bad_index_template, $ccap_callback_url,
        $fail_sim, $ccap_api_good;
    
    $ccap_policy->reset();
    $check_config_response = $ccap_policy->check_config($ccap_callback_url);
    $config_good = $check_config_response['passed'];
    
    $fail_message = '';
    if (!$config_good and $fail_sim) {
        // If the config only bad because of failure simulation?
        
        // Save API
        $check_config_html = $check_config_response['html'];
        $bad_api = $ccap_policy->api;
        
        // Try with good credentials
        $ccap_policy->api = $ccap_api_good;
        $check_config_response = 
            $ccap_policy->check_config($ccap_callback_url);
        $config_good = $check_config_response['passed'];
        
        // Switch back
        $ccap_policy->api = $bad_api;
        
        if ($config_good) {
            $fail_message = "
                <h2>Failed Configuration</h2>
                <p>The simulated failure is causing the configuraton check to
                fail.  The failed configuration is:</p>"
                 . str_replace("h1>", "h3>", $check_config_html);
        }
    }
    
    if ($config_good) {
        $template = $good_index_template;
        $new_settings_form = new_settings_form();
    } else {
        $template = $bad_index_template;
        $new_settings_form = '';
    }

    $tags = array(
        'TITLE'              => 'Welcome to the Confident CAPTCHA Explorer',
        'HEAD_SCRIPT'        => '',
        'NEW_SETTINGS_FORM'  => $new_settings_form,
        'IN_PAGE_URL'        => url(array('with_callback'=>'1')),
        'AT_POST_URL'        => url(array('with_callback'=>'0')),
        'FAIL_MESSAGE'       => $fail_message
    );
    $index_page = generate_page($template, $tags);
    return $index_page;
}


// Handle the request
if (isset($_REQUEST['with_callback'])) {
    $page = captcha_page(('1' == $_REQUEST['with_callback']), $ccap_policy);
} elseif (isset($_REQUEST['ccap_config_page'])) {
    $page = config_page($ccap_policy);
} else {
    $page = index_page($ccap_policy);
}
echo $page;