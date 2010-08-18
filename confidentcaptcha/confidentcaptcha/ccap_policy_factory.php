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
 * Confident CAPTCHA library for PHP - Policy Factory
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
 * Policy factory for Confident CAPTCHA policies
 *
 * When recreating a policy with the help of the persistence engine, this
 * class can be used to create or restore a policy by class name.
 *
 * @package confidentcaptcha-php
 */
class CCAP_PolicyFactory
{
    /**
     * Construction parameters for a policy
     * @var array
     */
    static protected $policies = array();
    
    /**
     * Static class has been initialized
     * @var boolean
     */
    static protected $initialized = FALSE;

    /**
     * Initialize the Policy Factory
     */
    static protected function init()
    {
        if (!CCap_PolicyFactory::$initialized) {
            CCap_PolicyFactory::$initialized = NULL;
            CCap_PolicyFactory::add('CCAP_ProductionFailOpen',
                'ccap_prod_open_policy.php');
            CCap_PolicyFactory::add('CCAP_ProductionFailClosed',
                'ccap_prod_closed_policy.php');
            CCap_PolicyFactory::add('CCAP_DevelopmentPolicy',
                'ccap_dev_policy.php');
            CCap_PolicyFactory::$initialized = TRUE;
        }
    }

    /**
     * Does the factory know about a policy?
     *
     * @param string $policy_name The name of the policy
     * @return boolean TRUE if known, FALSE if unknown
     */
    static public function known($policy_name)
    {
        CCap_PolicyFactory::init();
        return (isset(CCap_PolicyFactory::$policies[$policy_name]));
    }
    
    /**
     * Teach the factory about a new policy
     *
     * @param string $policy_name The name of the policy class
     * @param string $policy_include The name of the policy include file
     */
    static public function add($policy_name, $policy_include=NULL)
    {
        // Don't call init if we're in init
        if (!is_null(CCap_PolicyFactory::$initialized)) {
            CCap_PolicyFactory::init();
        }
        CCap_PolicyFactory::$policies[$policy_name] = $policy_include;
    }

    /**
     * Create a new policy by name
     *
     * @param string $policy_name The name of the policy to create
     * @param CCAP_Api $api The API class to pass to the policy
     * @param CCAP_Persistance $persist The persistance class for the policy
     * @return CCAP_Policy A newly created policy
     */
    static public function create($policy_name, $api, $persist)
    {
        CCap_PolicyFactory::init();
        if (CCap_PolicyFactory::known($policy_name)) {
            $policy_include = CCap_PolicyFactory::$policies[$policy_name];
            if ($policy_include) include_once($policy_include);
            return new $policy_name($api, $persist);
        } else {
            die("CCAP_PolicyFactory: Unknown policy '$policy_name'");
            return NULL;
        }
    }

    /**
     * Recreate a policy with the help of the persistence framework.
     *
     * If the persistance framework doesn't have a stored policy name, then
     * the $fallback_policy_name is used to create the policy.  If this is
     * unset, die() is called.
     *
     * @param CCAP_Persist $persist The persistence engine to query
     * @param CCAP_API $api The API class to pass to the policy
     * @param string $fallback_policy_name The fallback new policy to create
     * @return CCAP_Policy The restored policy or a new fallback policy
     */
    static public function restore($persist, $api, $fallback_policy_name=NULL)
    {
        CCap_PolicyFactory::init();
        $policy_name = $persist->policy_name();
        if ($policy_name) {
            $policy = CCap_PolicyFactory::create($policy_name, $api,
                $persist);
            $persist->load($policy);
            return $policy;
        } else {
            $policy = CCap_PolicyFactory::create($fallback_policy_name, $api,
                $persist);
            $policy->reset();
            return $policy;
        }
    }
}

