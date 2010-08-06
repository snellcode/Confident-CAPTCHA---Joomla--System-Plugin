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
 * Confident CAPTCHA Library for PHP - API interface
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
 * @author    Chad Blomquist <cblomquist@confidenttech.com>
 * @author    John Whitlock <jwhitlock@confidenttech.com>
 * @copyright Copyright (c) 2010, Confident Technologies, Inc.
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL v2.0 or later
 * @license   http://www.opensource.org/licenses/bsd-license.php Simplified BSD License
 * @version   20100621_PHP_1.1
 */

/**
 * Response from a Confident CAPTCHA API call
 * @package confidentcaptcha-php
 */
class CCAP_ApiResponse
{
    /**
     * HTTP status code returned by API
     *
     * Standard HTTP codes are used, as well as 0 to signify that the server
     * did not respond.
     * @var integer
     */
    var $status;

    /**
     * HTTP body returned by API
     *
     * If the {@link $status} is 200, then the body is the response from the
     * API. Otherwise, the response is the cURL error.
     * @var string
     */
    var $body;

    /**
     * Request method
     *
     * @var string
     */
    var $method;

    /**
     * Request URL
     *
     * @var string
     */
    var $url;

    /**
     * Request form parameters, or NULL if not a POST
     *
     * @var string
     */
    var $form;

    /**
     * True if response from CAPTCHA API server, false if shortcut used
     *
     * @var boolean
     */
    var $from_remote;

    /**
     * Construct a CCAP_ApiResponse
     *
     * @param integer $status HTTP status code
     * @param string  $body   HTTP response body
     * @param string  $method HTTP request method
     * @param string  $url    Request URL
     * @param string  $form   Form parameters (or NULL if not a POST)
     * @param boolean $from_remote TRUE if CAPTCHA API server response, FALSE
     *  if response is due to a shortcut check
     */
    public function __construct($status, $body, $method, $url, $form,
        $from_remote)
    {
        $this->status = $status;
        $this->body   = $body;
        $this->method = $method;
        $this->url    = $url;
        $this->form   = $form;
        $this->from_remote = $from_remote;
    }
}


/**
 * Interface for the Confident CAPTCHA API
 *
 * The Confident CAPTCHA API, with documentation, is found at:
 * {@link http://captcha.confidenttechnologies.com}
 * @package confidentcaptcha-php
 */
class CCAP_Api
{
    /**
     * API Customer ID (same for all sites created by your account)
     * @var string
     */
    public $customer_id;

    /**
     * API Site ID (unique to the a website or even webpage)
     * @var string
     */
    public $site_id;

    /**
     * API Username (secret associated with site)
     * @var string
     */
    public $api_username;

    /**
     * API Password (secret associated with site)
     * @var string
     */
    public $api_password;

    /**
     * Confident CAPTCHA API Server URL
     *
     * This should always be http://captcha.confidenttechnologies.com, unless
     * told differently by Confident Technologies technical support.
     * @var string
     */
    public $captcha_server_url;

    /**
     * If True, don't call the server if the response is known
     *
     * Many users fail to set their API credentials correctly.  When True,
     * the library won't bother calling the CAPTCHA API server in some cases,
     * but instead return the expected failure.  When False, the CAPTCHA API
     * server is always called.
     * @var boolean
     */
    public $use_shortcuts;

    /**
     * Version of this library
     *
     * A version string such as 20100621_PHP_1, passed as library_version in
     * each API call.
     *
     * The first part, 20100621_, tells the Confident CAPTCHA server that
     * you expect the server to use the June 21st, 2010 version of the API.
     * There are only a few recognized values, and using an unrecognized
     * value is undefined.
     *
     * The combined 20100621_PHP is used to determine if the library is
     * supported.  For a list of supported libraries, see
     * {@link http://captcha.confidenttechnologies.com/version
     *  the version XML returned from the server}.
     * Contact Confident Technologies to add your own library to the list.
     *
     * Everything after this is ignored by the API, and can be used for
     * versioning your library.  This may be useful for diagnosing issues -
     * if a known bad library_version appears in request, we can contact the
     * customer to request them to update their library.
     * 
     * @var string
     */
    protected $library_version;


    /**
     * Construct a CCAP_Api with API credentials
     *
     * All API credentials come from your account on
     * {@link https://login.confidenttechnologies.com}
     * , created from the
     * {@link http://confidenttechnologies.com/purchase/CAPTCHA_signup.php
     *  Confident Technologies signup page}
     *
     * @param string $customer_id        API Customer ID
     * @param string $site_id            API Site ID
     * @param string $api_username       API Username
     * @param string $api_password       API Password
     * @param string $captcha_server_url Confident CAPTCHA API Server URL
     * @param string $library_version    This library's version string
     * @param boolean $use_shortcuts     True if shortcut mode should be used
     */
    public function __construct($customer_id, $site_id, $api_username,
        $api_password,
        $captcha_server_url = 'http://captcha.confidenttechnologies.com',
        $library_version = '20100621_PHP_1.1', $use_shortcuts = FALSE)
    {
        $this->customer_id = $customer_id;
        $this->site_id = $site_id;
        $this->api_username = $api_username;
        $this->api_password = $api_password;
        $this->library_version = $library_version;
        $this->captcha_server_url = $captcha_server_url;
        $this->use_shortcuts = $use_shortcuts;
    }

    /**
     * Prepare the URL and form parameters for a request
     *
     * @param string  $resource        The resource to call
     * @param string  $method          The HTTP method to use
     * @param array   $params          The parameters to send
     * @param boolean $use_credentials Include API credentials in call
     * @return array with keys 'url' and 'form'
     */
    protected function prep_req($resource, $method, $params,
        $use_credentials)
    {
        $url = $this->captcha_server_url . $resource;

        if ($params == NULL) {
            $params = array();
        }
        $params["library_version"] = $this->library_version;
        if ($use_credentials) {
            $params['customer_id']  = $this->customer_id;
            $params['site_id']      = $this->site_id;
            $params['api_password'] = $this->api_password;
            $params['api_username'] = $this->api_username;
        }

        $form = NULL;
        if (strtoupper($method) == 'GET') {
            $url .= '?' . http_build_query($params);
        } elseif (strtoupper($method) == 'POST' and $params) {
            $form = http_build_query($params);
        }
        return Array('url'=>$url, 'form'=>$form);
    }

    /**
     * Call the Confident CAPTCHA API
     *
     * @param string  $resource        The resource to call
     * @param string  $method          The HTTP method to use (POST or GET)
     * @param array   $params          The parameters to send
     * @param boolean $use_credentials Include API credentials in call
     * @return CCAP_ApiResponse  The response
     */
    protected function call($resource, $method, $params, $use_credentials)
    {
        $req = $this->prep_req($resource, $method, $params, $use_credentials);
        $url = $req['url'];
        $form = $req['form'];

        $ch = curl_init();

        if (strtoupper($method) == 'POST') {
            curl_setopt($ch, CURLOPT_POST, TRUE);

            if ($form) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $form);
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $body = curl_exec($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($body === FALSE) {
            $response = new CCAP_ApiResponse($http_code, curl_error($ch),
                strtoupper($method), $url, $form, TRUE);
        } else {
            $response = new CCAP_ApiResponse($http_code, $body, 
                strtoupper($method), $url, $form, TRUE);
        }

        curl_close($ch);
        return $response;
    }
    
    /**
     * Create a shortcut response, faking a call the Confident CAPTCHA API
     *
     * This is used when a parameter is unset or known, and a failed
     * response can be generated without bothering the CAPTCHA server.
     * It is enabled by setting {@link $use_shortcut}.
     *
     * @param string  $resource        The resource to call
     * @param string  $method          The HTTP method to use (POST or GET)
     * @param array   $params          The parameters to send
     * @param boolean $use_credentials Include API credentials in call
     * @param integer $status          The status to return
     * @param string  $body            The body to return
     * @return CCAP_ApiResponse  The response
     */
    protected function shortcut($resource, $method, $params, 
        $use_credentials, $status, $body)
    {
        $req = $this->prep_req($resource, $method, $params, $use_credentials);
        $url = $req['url'];
        $form = $req['form'];
        return new CCAP_ApiResponse($status, $body, 
            strtoupper($method), $url, $form, FALSE);
    }

    /**
     * Get the user's IP address and User Agent
     *
     * These are used by Confident Technologies to build a risk profile for
     * the CAPTCHA user.
     * @return array The end user's ip_addr and user_agent
     */
    protected function get_user_info()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            // IP from share internet
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // IP passed from proxy
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            // IP directly from caller
            $ip=$_SERVER['REMOTE_ADDR'];
        } else {
            $ip="";
        }

        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            // User-Agent header
            $agent = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $agent = "";
        }

        return Array('ip_addr' => $ip, 'user_agent' => $agent);
    }

    /**
     * Check that API credentials are set
     *
     * @return boolean False if credentials are unset, True if set
     */
    private function api_credentials_set()
    {
        return (!empty($this->customer_id) and
                !empty($this->site_id) and
                !empty($this->api_username) and
                !empty($this->api_password)
        );
    }

    /**
     * Check that visual CAPTCHA settings are good
     *
     * @return NULL if good, string if issue
     */
    private function check_visual_settings($params)
    {
        // TODO: check security level
        // TODO: check colors
        // TODO: check display style
        return NULL;
    }

    /**
     * Check that API credentials and other settings are valid.
     *
     * @return CCAP_ApiResponse  body is an HTML table describing
     *  if the API credentials are valid.  If the string "api_failed='True'"
     *  appears in the response, then other calls will fail.
     */
    public function check_credentials()
    {
        return $this->call('/check_credentials', 'GET', NULL, TRUE);
    }

    /**
     * Get API version XML
     *
     * @return CCAP_ApiResponse body is API version XML.
     */
    public function version()
    {
        return $this->call('/version', 'GET', NULL, FALSE);
    }

    /**
     * Create a multiple-CAPTCHA block.
     *
     * This can be used to give the end user quick feedback if the CAPTCHA
     * attempt succeeded or failed, and generate a new visual CAPTCHA without
     * refreshing the page (JavaScript and a callback required).
     *
     * @return CCAP_ApiResponse  If success, body is block_id
     */
    public function create_block()
    {
        $resource = '/block';
        $method = 'POST';
        $params = $this->get_user_info();
        $cred = TRUE;

        if ($this->use_shortcuts) {
            if (!$this->api_credentials_set()) {
                return $this->shortcut($resource, $method, $params, $cred,
                    401, "<p><b>401 Not Authorized</b>(API credentials".
                    " unset, shortcut used).</p>");
            }
        }

        return $this->call($resource, $method, $params, $cred);
    }

    /**
     * Create a visual CAPTCHA in a multiple-CAPTCHA block
     *
     * For security, the width, height, and length must define a visual
     * CAPTCHA that has a low probability of randomly guessing.  Some
     * acceptable values are 3x3 w/ length 4, or 4x4 w/ length 3.
     *
     * @param string  $block_id      Block ID returned from 
     *                               {@link create_block()}
     * @param string  $display_style 'flyout' or 'lightbox'
     * @param boolean $include_audio Include audio CAPTCHA (if enabled)
     * @param integer $height        Height of visual CAPTCHA in pictures
     * @param integer $width         Width of visual CAPTCHA in pictures
     * @param integer $length        Number of pictures the user must pick
     * @param integer $code_color    Color of letter code on pictures
     *
     * @return CCAP_ApiResponse  If success, status is 200 and body
     *  is partial HTML to inject into page.  If status is 410, then the end
     *  user has used up all their attempts.
     */
    public function create_visual($block_id, $display_style='flyout', 
        $include_audio=false, $height=3, $width=3, $length=4, 
        $code_color='White')
    {
        $resource = "/block/$block_id/visual";
        $method = 'POST';

        $params = Array(
            'display_style' => $display_style,
            'include_audio_form' => $include_audio,
        );

        if ($height != NULL && $height != '') {
            $params['height'] = $height;
        }
        if ($width != NULL && $width != '') {
            $params['width'] = $width;
        }
        if ($length != NULL && $length != '') {
            $params['captcha_length'] = $length;
        }
        if ($code_color != NULL && $code_color != '') {
            $params['image_code_color'] = $code_color;
        }

        $cred = FALSE;

        if ($this->use_shortcuts) {
            if (empty($block_id)) {
                // If block_id is unset, the call will fail with 404
                return $this->shortcut($resource, $method, $params, $cred,
                    404, "<p><b>404 Not Found</b>(block_id is empty,".
                    " shortcut used).</p>");
            }

            $check = $this->check_visual_settings($params);
            if (!is_null($check)) {
                // Visual Settings are bad, will result in 400
                return $this->shortcut($resource, $method, $params, $cred,
                    400, $check);
            }
        }

        return $this->call($resource, $method, $params, $cred);
    }

    /**
     * Check a visual CAPTCHA in a multiple-CAPTCHA block
     *
     * For security, the width, height, and length must define a visual
     * CAPTCHA that has a low probability of randomly guessing.  Some
     * acceptable values are 3x3 w/ length 4, or 4x4 w/ length 3.
     *
     * @param string $block_id   Block ID returned from {@link create_block()}
     * @param string $visual_id  Visual ID in return from {@link create_visual()}
     * @param string $code       User's CAPTCHA solution
     *
     * @return CCAP_ApiResponse The body is 'True' if the solution was
     *  correct, 'False' if incorrect or unknown captcha_id
     */
    public function check_visual($block_id, $visual_id, $code)
    {
        $resource = "/block/$block_id/visual/$visual_id";
        $method = 'POST';
        $params = Array('code' => $code);
        $cred = FALSE;

        if ($this->use_shortcuts) {
            if (empty($block_id)) {
                // If block_id is unset, the call will fail with 404
                return $this->shortcut($resource, $method, $params, $cred,
                    404, "<p><b>404 Not Found</b>(block_id is empty,".
                    " shortcut used).</p>");
            } elseif (empty($visual_id)) {
                // If visual_id is unset, the call will fail with 404
                return $this->shortcut($resource, $method, $params, $cred,
                    404, "<p><b>404 Not Found</b>(visual_id is empty,".
                    " shortcut used).</p>");
            } elseif (empty($code)) {
                // If the code is unset, the call will fail with 200, False
                return $this->shortcut($resource, $method, $params, $cred,
                    200, "False");
            }
        }

        return $this->call($resource, $method, $params, $cred);
    }

    /**
     * Start an audio CAPTCHA by calling the user
     *
     * Audio CAPTCHA must be enabled for your account.  Only US numbers
     * are currently supported.
     *
     * @param string $block_id     Block ID returned from 
     *                             {@link create_block()}
     * @param string $phone_number User's 10-digit US phone number
     *
     * @return CCAP_ApiResponse If successful, status is 200 and
     *  body is an audio_id used to check if the call completed successfully
     */
    public function start_audio($block_id, $phone_number)
    {
        $resource = "/block/$block_id/audio";
        $method = 'POST';
        $params = Array('phone_number' => $phone_number);
        $cred = FALSE;

        if ($this->use_shortcuts) {
            if (empty($block_id)) {
                // If block_id is unset, the call will fail with 404
                return $this->shortcut($resource, $method, $params, $cred,
                    404, "<p><b>404 Not Found</b>(block_id is empty,".
                    " shortcut used).</p>");
            } elseif (empty($phone_number)) {
                // If the phone number is unset, the call will fail with 400
                return $this->shortcut($resource, $method, $params, $cred,
                    400, "<p><b>404 Not Found</b>(phone_number is empty,".
                    " shortcut used).</p>");
            }
        }

        return $this->call($resource, $method, $params, $cred);
    }

    /**
     * Check the progress of an audio CAPTCHA
     *
     * @param string $block_id Block ID returned from {@link create_block()}
     * @param string $audio_id Audio ID in return from {@link start_audio()}
     *
     * @return CCAP_ApiResponse
     */
    public function check_audio($block_id, $audio_id)
    {
        $resource = "/block/$block_id/audio/$audio_id";
        $method = 'GET';
        $params = NULL;
        $cred = FALSE;

        if ($this->use_shortcuts) {
            if (empty($block_id)) {
                // If block_id is unset, the call will fail with 404
                return $this->shortcut($resource, $method, $params, $cred, 
                    404, "<p><b>404 Not Found</b>(block_id is empty,".
                    " shortcut used).</p>");
            } elseif (empty($audio_id)) {
                // If audio_id is unset, the call will fail with 404
                return $this->shortcut($resource, $method, $params, $cred, 
                    404, "<p><b>404 Not Found</b>(audio_id is empty,".
                    " shortcut used).</p>");
            }
        }

        return $this->call($resource, $method, $params, $cred);
    }

    /**
     * Create a single CAPTCHA
     *
     * This CAPTCHA is designed to be checked at form submission
     *
     * For security, the width, height, and length must define a visual
     * CAPTCHA that has a low probability of randomly guessing.  Some
     * acceptable values are 3x3 w/ length 4, or 4x4 w/ length 3.
     *
     * @deprecated Prefer {@link create_block()} / {@link create_visual()}
     *
     * @param string  $display_style 'flyout' or 'lightbox'
     * @param boolean $include_audio Include audio CAPTCHA (if enabled for
     *                               site)
     * @param integer $height        Height of visual CAPTCHA in pictures
     * @param integer $width         Width of visual CAPTCHA in pictures
     * @param integer $length        Number of pictures the user must pick
     * @param integer $code_color    Color of letter code on pictures
     *
     * @return CCAP_ApiResponse  If success, status is 200 and body
     *  is partial HTML to inject into page.  If status is 410, then the end
     *  user has used up all their attempts.
     */
    public function create_captcha($display_style='flyout',
        $include_audio=false, $height=3, $width=3, $length=4,
        $code_color='White')
    {
        $resource = "/captcha";
        $method = "POST";
        $user_info = $this->get_user_info();
        $params = Array(
            'ip_addr' => $user_info['ip_addr'],
            'user_agent' => $user_info['user_agent'],
            'display_style' => $display_style,
            'height' => $height,
            'width' => $width,
            'captcha_length' => $length,
            'image_code_color' => $code_color,
        );
        $cred = TRUE;

        if ($this->use_shortcuts) {
            $check = $this->check_visual_settings($params);
            if (!is_null($check)) {
                // Visual Settings are bad, will result in 400
                return $this->shortcut($resource, $method, $params, $cred,
                    400, $check);
            }
        }

        return $this->call($resource, $method, $params, $cred);
    }

    /**
     * Check a single CAPTCHA
     *
     * For security, the width, height, and length must define a visual
     * CAPTCHA that has a low probability of randomly guessing.  Some
     * acceptable values are 3x3 w/ length 4, or 4x4 w/ length 3.
     *
     * NOTE - The parameter order is different than the check_captcha
     * call from earlier versions of this library.
     *
     * @deprecated Prefer {@link check_visual()}
     *
     * @param string $captcha_id CAPTCHA ID in return from {@link create_captcha()}
     * @param string $code       User's CAPTCHA solution
     *
     * @return CCAP_ApiResponse The body is 'True' if the solution was
     *  correct, 'False' if incorrect or unknown captcha_id
     */
    public function check_captcha($captcha_id, $code)
    {
        $resource = "/captcha/$captcha_id";
        $method = 'POST';
        $params = Array('code' => $code);
        $cred = TRUE;

        if ($this->use_shortcuts) {
            if (empty($captcha_id)) {
                // If captcha_id is unset, the call will fail with 404
                return $this->shortcut($resource, $method, $params, $cred,
                    404, "<p><b>404 Not Found</b>(captcha_id is empty,".
                    " shortcut used).</p>");
            } elseif (empty($code)) {
                // If the code is unset, the call will fail with 200, False
                return $this->shortcut($resource, $method, $params, $cred,
                    200, "False");
            }
        }

        return $this->call($resource, $method, $params, $cred);
    }

    /**
     * Start a voice CAPTCHA by calling the user
     *
     * Audio CAPTCHA must be enabled for your account.  Only US numbers
     * are currently supported.
     *
     * @deprecated Prefer {@link create_block()} / {@link start_audio()}
     *
     * @param string $phone_number User's 10-digit US phone number
     *
     * @return CCAP_ApiResponse If successful, the status is 200
     *  and the body is a onekey_id used to check if the call completed
     *  successfully
     */
    public function start_onekey($phone_number)
    {
        $resource = '/onekey';
        $method = 'POST';
        $params = Array('phone_number' => $phone_number);
        $cred = TRUE;

        if ($this->use_shortcuts) {
            if (empty($phone_number)) {
                // If the phone number is unset, the call will fail with 400
                return $this->shortcut($resource, $method, $params, $cred,
                    400, "<p><b>404 Not Found</b>(phone_number is empty,".
                    " shortcut used).</p>");
            }
        }

        return $this->call($resource, $method, $params, $cred);
    }

    /**
     * Check the progress of a voice CAPTCHA
     *
     * @deprecated Prefer {@link check_audio()}
     *
     * @param string $onekey_id One Key ID in return from start_audio()
     *
     * @return CCAP_ApiResponse
     */
    public function check_onekey($onekey_id)
    {
        $resource = "/onekey/$onekey_id";
        $method = 'POST';
        $params = NULL;
        $cred = TRUE;

        if ($this->use_shortcuts) {
            if (empty($onekey_id)) {
                // If onekey_id is unset, the call will fail with 404
                return $this->shortcut($resource, $method, $params, $cred,
                    404, "<p><b>404 Not Found</b>(onekey_id is empty,".
                    " shortcut used).</p>");
            }
        }

        return $this->call($resource, $method, $params, $cred);
    }
}

// vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: 
