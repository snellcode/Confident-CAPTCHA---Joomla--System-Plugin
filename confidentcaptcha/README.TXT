PHP Library and Sample Code for Confident CAPTCHA
version 20100608-PHP

Thank you for downloading this library and sample code.  The included files
are:
 * README.TXT - This document
 * captchalib.php - The PHP library
 * config.php - Configuration template
 * callback.php - The 
 * sample.php - The PHP sample

REQUIREMENTS

 *  This library requires PHP version 5.0 or later.
 * PHP must have cURL support built in and enabled.
 * The page that renders the ConfidentCaptcha requires jQuery.

USAGE

 1. Sign up for Confident CAPTCHA at:

http://confidenttechnologies.com/purchase/CAPTCHA_signup.php

 2. Create an account at:

https://login.confidenttechnologies.com

 3. Modify config.php, filling in the API credentials from:

https://login.confidenttechnologies.com/dashboard/sites/

 4. Still in config.php, set the value of $callback_url to the relative URL to
callback.php.
 
 5. Integrate the library into your code.  You need captchalib.php, callback.php,
 and a (customized) config.php in your project.  Look at sample.php for 
 integration ideas.

Please send your questions and feedback to:

https://login.confidenttechnologies.com/dashboard/contactus/general/
