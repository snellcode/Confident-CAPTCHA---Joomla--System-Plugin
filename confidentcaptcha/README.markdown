PHP Library and Sample Code for Confident CAPTCHA
=================================================
version 20100621_PHP_1.1 - August 2nd, 2010

Thank you for downloading this library and sample code.

REQUIREMENTS
------------

 * This library requires PHP version 5.0 or later.
 * PHP must have cURL and SimpleXML support built in and enabled.
 * The page that renders the Confident CAPTCHA requires jQuery 1.4.2.

USAGE
-----

 1. Sign up for Confident CAPTCHA at
    <http://confidenttechnologies.com/purchase/CAPTCHA_signup.php>
 2. Create an account at <https://login.confidenttechnologies.com>
 3. Modify config.php, filling in the API credentials from
    <https://login.confidenttechnologies.com/dashboard/sites/>
 4. Try out the sample code, including check.php to check your API credentials.
 5. Copy callback.php and the confidentcaptcha folder into your own project.
    Look at sample_before.php and sample_after.php to see what code is necessary
    to integrate Confident CAPTCHA. Be sure to include jQuery in your page.

Please send your questions and feedback to
<https://login.confidenttechnologies.com/dashboard/contactus/general/>

FILES
-----

 * README.TXT - This document
 * LICENSE - License for ConfidentCAPTCHA samples and libraries
 * BSD_LICENSE - The BSD license
 * GPL2_LICENSE - The GPL version 2.0 license
 * config.php - Configuration items, including API credentials, for the samples
   and library.
 * index.php - Menu for the samples and library documentation.
 * check.php - Checks the local and remote configuration.
 * explore.php - Explore CAPTCHA customizations without modifying the code.
 * sample_before.php - An example form before adding ConfidentCAPTCHA
 * sample_after.php - An example form after adding ConfidentCAPTCHA
 * make_docs.sh - Script to generate the PHPDocumentor-based docs
 * docs - Generated documentation folder
 * callback.php - An AJAX callback page for instant feedback and audio CAPTCHAs. 
 * confidentcaptcha - The ConfidentCAPTCHA library code:
     * ccap_api.php - Provides CCAP_Api, the direct interface to the CAPTCHA API.
     * ccap_policy.php - Provides CCAP_Policy, which handles normal and abnormal
       API responses.
     * ccap_prod_closed_policy.php - Provides CCAP_ProductionFailClosed, which
       stops forms when CAPTCHA creation fails (recommended for account creation
       forms).
     * ccap_prod_open_policy.php - Provides CCAP_ProductionFailOpen, which allows
       forms to continue when CAPTCHA creation failsi.  Recommended for contact
       forms).
     * ccap_dev_policy.php - Provides CCAP_DevelopmentPolicy, a debug
       implementation of CCAP_Policy.
     * ccap_persist.php - Provides CCAP_PersistSession, which remembers CAPTCHA
       state between pages loads.
     * ccap_policy_factory.php - Provides name-based creation of CCAP_Policy
       instances, used by CCAP_PersistSession to reload a policy.

VERSION HISTORY
---------------

 - 20100610_PHP - June 10th, 2010
     * Original (versioned) release

 - 20100621_PHP - June 21st, 2010
     * Updated to work with API version 20100610
     * Support the new namespaced HTML and JavaScript elements
     * CAPTCHA parameters are stored in session, so that similar CAPTCHA can be
       created on callback
     * Sample implements "fail open" strategy - Forms work even when there are
       configuration or server issues.

 - 20100621_PHP_1.1 - August 2nd, 2010
     * Massive refactor of the PHP code, including changing variable names for
       better integration with existing code.
     * Library code has been moved to the confidentcaptcha subdirectory, and has
       been refactored into several classes.  New policy classes help handle
       normal and abnormal API responses.
     * The sample code has been expanded into several files.
     * PHPDocumentor comments have been added to the the library, and generated
       documentation is included in the download package.

