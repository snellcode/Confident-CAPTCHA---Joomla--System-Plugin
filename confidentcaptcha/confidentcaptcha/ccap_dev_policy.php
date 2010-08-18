<?php
/*
You must elect to use this software under one of:
 * The GNU GPL v2.0 License or later
 * The Simplified BSD License

If licensed under GNU GPL v2.0 or later:
 Copyright 2010 Confident Technologies, Inc.  All rights reserved.

 This file is part of the Confident CAPTCHA Library for PHP.

 The Confident CAPTCHA Library for PHP is free software: you can
 redistribute it and/or modify it under the terms of the GNU General Public
 License as published by the Free Software Foundation, either version 2 of
 the License, or (at your option) any later version.

 The Confident CAPTCHA Library for PHP is distributed in the hope that it will
 be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General
 Public License for more details.

 You should have received a copy of the GNU General Public License along with
 the Confident CAPTCHA Library for PHP.  If not, see
 <http://www.gnu.org/licenses/>.

If licensed under the Simplified BSD License:
 Copyright 2010 Confident Technologies, Inc. All rights reserved.

 Redistribution and use in source and binary forms, with or without
 modification, are permitted provided that the following conditions are met:

   1. Redistributions of source code must retain the above copyright notice, 
      this list of conditions and the following disclaimer.

   2. Redistributions in binary form must reproduce the above copyright 
      notice, this list of conditions and the following disclaimer in the 
      documentation and/or other materials provided with the distribution.

   THIS SOFTWARE IS PROVIDED BY CONFIDENT TECHNOLOGIES, INC. ``AS IS'' AND ANY
   EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
   WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
   DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> OR CONTRIBUTORS BE LIABLE
   FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
   DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
   SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER 
   CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
   LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
   OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
   DAMAGE.

   The views and conclusions contained in the software and documentation are 
   those of the authors and should not be interpreted as representing official
   policies, either expressed or implied, of Confident Technologies, Inc.
*/

/**
 * Confident CAPTCHA library for PHP - Policy for Page Development
 *
 * Confident CAPTCHA is free image-based challenge that relies on image
 * categorization being easy for humans (easier than text-based CAPTCHAs)
 * but hard for computers.  It is provided as a web service.  To use this
 * library, you'll need to sign up for a free account on the
 * {@link http://confidenttechnologies.com/purchase/CAPTCHA_signup.php Confident Technologies signup page}
 *
 * For the latest version of this library, or to file issues, request
 * features, etc., see
 * {@link http://github.com/ConfidentTech/confidentcaptcha-php the project page on GitHub}.
 *
 * Confident Technologies dual-licenses this code under GNU GPL v2.0 or later
 * and the Simplified BSD License.  Contact Confident Technologies if you need
 * different licensing terms.
 *
 * @package   confidentcaptcha-php
 * @author    John Whitlock <jwhitlock@confidenttech.com>
 * @copyright Copyright (c) 2010, Confident Technologies, Inc.
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2.0 or later
 * @license   http://www.opensource.org/licenses/bsd-license.php Simplified BSD License
 * @version   20100813_PHP_1.2
 */

/**
 * Include definition of {@link CCAP_Policy}
 */
require_once("ccap_policy.php");

/**
 * Confident CAPTCHA policy for page development
 *
 * Most web developers start by adding Confident CAPTCHA to an existing page
 * in their development environment.  Configuration and usage errors are more
 * common during this phase.  This policy causes these errors to be noisy, so
 * that a developer can quickly fix issues without resorting to logs.
 *
 * @package confidentcaptcha-php
 */
class CCAP_DevelopmentPolicy extends CCAP_Policy
{

    /**
     * Storage for API debug messages
     * @var Array
     */
    var $api_debug_messages = Array();

    /**
     * Construct a CCAP_DevelopmentPolicy
     *
     * Sets the 'use_shortcuts' boolean, to avoid bugging the CAPTCHA API
     * server during the awkward development stage.
     *
     * @param CCAP_Api $api The API interface to use for calls
     */
    public function __construct($api, $persist)
    {
        parent::__construct($api, $persist);
        $this->api->use_shortcuts = TRUE;
        $this->api_debug_level = 2;
    }

    /**
     * Handle debug information by dumping to output
     */
    protected function handle_debug($debug)
    {
        $debug_html = str_replace("\n", "<br>\n", $debug);
        $this->api_debug_messages[] = $debug_html;
    }
    
    /**
     * Callback for developer policy - add get_debug function
     */
    protected function callback_extensions($endpoint, $request)
    {
        $content = NULL;
        $headers = Array();
        if ($endpoint == 'get_api_debug') {
            $content = array_shift($this->api_debug_messages);
            if (is_null($content)) $content = "";
        } elseif ($endpoint == 'get_policy_dump') {
            $content = htmlentities(var_export($this, TRUE));
        } else {
            $this->api_debug_messages[] = "Unknown callback '$endpoint'";
        }
        if (is_null($content)) {
            $result = NULL;
        } else {
            $result = Array($content, $headers);
        }
        return $result;
    }
    
    /**
     * Get HTML for debug section
     *
     * Inject this into your page to add the API debug messages.
     * @param string $callback_url The callback URL
     * @return string HTML to inject into page
     */
    public function get_debug_html($callback_url)
    {
        // Insertion to get debug messages
        // Don't use on your page
        return <<< DEBUG
<div id="confidentcaptcha_debug" style="display: none">
<h2>Confident CAPTCHA Debug Messages</h2>
<p>
Debug messages will appear here if you are using CCAP_DevelopmentPolicy.
Don't use this debug code in production - it will leak your API credentials.
</p><p>
Use the <a href="#confidentcaptcha_actions">links at the bottom</a> to get
more debug information.
</p>
<ul></ul>
<a name="confidentcaptcha_actions">Actions:</a>
<a href="#" class='confidentcaptcha_debug_refresh'>Fetch new debug
 messages</a>
<a href="#" class='confidentcaptcha_debug_dump'>Dump policy state</a>
</div>
<script type="text/javascript">
    function confidentcaptcha_get_debug(depth, first_call, method)
    {
        if (depth > 5) { return; }
        if (!"$callback_url") {
            $("#confidentcaptcha_debug").css("display","block");
            $("#confidentcaptcha_debug ul").append(
                "<li>callback_url is not set</li>");
            return;
        }
        $.ajax({
            type: 'POST',
            url: "$callback_url",
            data: {endpoint: method},
            dataType: 'text',
            success: function(html) {
                $("#confidentcaptcha_debug").css("display","block");
                if (html) {
                    $("#confidentcaptcha_debug ul").append(
                        "<li>"+html+"</li>");
                    // Recursively call until empty string is returned
                    if (method == 'get_api_debug') {
                        confidentcaptcha_get_debug(depth + 1, false, method);
                    }
                } else if (depth == 1) {
                    $("#confidentcaptcha_debug ul").append(
                        "<li>No new debug messages</li>");
                }
            },
            error: function() {
                if (!first_call) {
                    // Will return 400 if CCAP_DevelopmentPolicy is not used
                    $("#confidentcaptcha_debug ul").append(
                        "<li><b>Error: callback failed.  Are you using" +
                        "CCAP_DevelopmentPolicy?</b></li>"
                    );
                }
            }
        });
    };
    $(document).ready(function() {
        confidentcaptcha_get_debug(1, true, 'get_api_debug');

        $("a.confidentcaptcha_debug_refresh").click(function() {
            confidentcaptcha_get_debug(1, false, 'get_api_debug');
            return false;
        });
        $("a.confidentcaptcha_debug_dump").click(function() {
            confidentcaptcha_get_debug(1, false, 'get_policy_dump');
            return false;
        });
    });
</script>
DEBUG;
    }
}
