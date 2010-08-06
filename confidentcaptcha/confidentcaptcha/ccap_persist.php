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
 * Confident CAPTCHA library for PHP - Persistence Interface
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
 * @version   20100621_PHP_1.1
 */

/**
 * Persistence interface for Confident CAPTCHA Policies
 *
 * Confident CAPTCHA stores most of its state in the HTML included on the
 * page, passed from the Confident CAPTCHA server.  However, it can be
 * augmented with additional state, stored server-side or in additional
 * forms inputs.  This class provides the interface for policies to store or
 * or load information from the chosen persistence strategy.
 *
 * @package confidentcaptcha-php
 */
abstract class CCAP_Persistence
{
    /**
     * Initialize the policy state from persistance store
     *
     * @var CCAP_Policy $policy The policy instance to initialize
     */
    abstract public function load(&$policy);

    /**
     * Get stored policy name
     * @return string The policy class name
     */
    abstract public function policy_name();

    /**
     * Save the policy state to persistance store
     *
     * @var CCAP_Policy $policy The policy instance to save
     */
    abstract public function save(&$policy);

    /**
     * Clear the policy state in persistance store
     *
     * @var CCAP_Policy $policy The policy instance to clear
     */
    abstract public function reset(&$policy);
}

/**
 * "Do Nothing" as persistance strategy.
 *
 * Used for stateless Confident CAPTCHA.  This restricts you to delayed
 * CAPTCHA validation, and means forms will fail when the CAPTCHA server
 * fails.  This class is not recommended for general use, but it is a good
 * for a quick API credentials check, or as a starting place for your own
 * persistance implementation, or if you want to handle persistance yourself
 * (for example, storing the CCAP_Policy instance in the session).
 *
 * @package confidentcaptcha-php
 */
class CCAP_PersistNull extends CCAP_Persistence
{
    /**
     * Initialize the policy state from persistance store
     *
     * The null policy doesn't load anything.
     *
     * @var CCAP_Policy $policy The policy instance to initialize
     */
    public function load(&$policy)
    {
    }
    
    /**
     * Get stored policy name
     *
     * The null policy doesn't store the policy name, so return is NULL.
     *
     * @return string The policy class name
     */
    public function policy_name()
    {
        return NULL;
    }

    /**
     * Save the policy state to persistance store
     *
     * The null policy doesn't save anything.
     *
     * @var CCAP_Policy $policy The policy instance to save
     */
    public function save(&$policy)
    {
    }

    /**
     * Clear the policy state in persistance store
     *
     * The null policy is already clear.
     *
     * @var CCAP_Policy $policy The policy instance to clear
     */
    public function reset(&$policy)
    {
    }
}

/**
 * Session-based persistence for Confident CAPTCHA Policies
 *
 * Uses PHP's $_SESSION to store Confident CAPTCHA state.  All functions
 * require a session.  Either call session_start() at the top of a PHP
 * page that will use CAPTCHA, or, better yet, call
 * CCAP_Policy->start_captcha_page() at the top of the PHP page,
 * before any output or headers are sent.
 *
 * @package confidentcaptcha-php
 */
class CCAP_PersistSession extends CCAP_Persistence
{   
    /**
     * Load state from session
     * @var CCAP_Policy $policy The policy instance to initialize
     */
    public function load(&$policy)
    {
        // Start the session if it hasn't been started yet
        if (!session_id()) session_start();
        
        // Load state from session
        $state_key = "CONFIDENTCAPTCHA_POLICY_STATE";
        if (isset($_SESSION) and isset($_SESSION[$state_key])) {
            foreach($_SESSION[$state_key] as $key => $value) {
                if (is_scalar($value) or is_array($value)) {
                    $policy->$key = $value;
                }
            }
        }
    }

    /**
     * Get the stored policy name from the SESSION
     * @return string The policy class name, or NULL if none stored
     */
    public function policy_name()
    {
        // Start the session if it hasn't been started yet
        if (!session_id()) session_start();
        
        $name_key = "CONFIDENTCAPTCHA_POLICY_NAME";
        if (isset($_SESSION) and isset($_SESSION[$name_key])) {
            return $_SESSION[$name_key];
        } else {
            return NULL;
        }
    }

    /**
     * Save state to session
     *
     * @var CCAP_Policy $policy The policy instance to save
     */
    public function save(&$policy)
    {
        // Start the session if it hasn't been started yet
        if (!session_id()) session_start();

        // Save state to session
        $state_key = "CONFIDENTCAPTCHA_POLICY_STATE";
        $name_key = "CONFIDENTCAPTCHA_POLICY_NAME";
        if (isset($_SESSION)) {
            // Store policy name
            $_SESSION[$name_key] = get_class($policy);
            
            // Store policy
            $vars = get_object_vars($policy);
            $out_vars = Array();
            foreach($vars as $key => $value) {
                if (is_scalar($value) or is_array($value)) {
                    $out_vars[$key] = $value;
                }
            }
            $_SESSION[$state_key] = $out_vars;
        }
    }

    /**
     * Reset state in session
     */
     public function reset(&$policy)
     {
         // Start the session if it hasn't been started yet
         if (!session_id()) session_start();

         // Unset state
         $state_key = "CONFIDENTCAPTCHA_POLICY_STATE";
         $name_key = "CONFIDENTCAPTCHA_POLICY_NAME";
         if (isset($_SESSION)) {
             unset($_SESSION[$state_key]);
             unset($_SESSION[$name_key]);
         }
     }
}