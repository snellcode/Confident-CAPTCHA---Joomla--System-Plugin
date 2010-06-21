PHP Library and Sample Code for Confident CAPTCHA
version 20100621_PHP

Thank you for downloading this library and sample code.  The included files
are:
 * README.TXT - This document
 * captchalib.php - The PHP library
 * config.php - Configuration template
 * callback.php - The 
 * sample.php - The PHP sample

REQUIREMENTS

 * This library requires PHP version 5.0 or later.
 * PHP must have cURL and SimpleXML support built in and enabled.
 * The page that renders the Confident CAPTCHA requires jQuery 1.4.2.

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
 integration ideas.  Be sure to include jQuery in your page.

Please send your questions and feedback to:

https://login.confidenttechnologies.com/dashboard/contactus/general/

VERSION HISTORY

20100610_PHP - June 10th, 2010
 * Original (versioned) release

20100621_PHP - June 21st, 2010
 * Updated to work with API version 20100610
 * Support the new namespaced HTML and JavaScript elements
 * CAPTCHA parameters are stored in session, so that similar CAPTCHA can be created on callback
 * Sample implements "fail open" strategy - Forms work even when there is configuration or server issues.
