<?php
// The Kabal Invasion - A web-based 4X space game
// Copyright © 2014 The Kabal Invasion development team, Ron Harwood, and the BNT development team
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU Affero General Public License as
//  published by the Free Software Foundation, either version 3 of the
//  License, or (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Affero General Public License for more details.
//
//  You should have received a copy of the GNU Affero General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// File: classes/Template.php

namespace Tki;

define('TEMPLATE_USE_SMARTY', 0x00000000, true);

class Template
{
    private $api = null;

    public function __construct()
    {
        $this->initialized = (boolean) false;
        $this->api = array();
        $this->initialize(TEMPLATE_USE_SMARTY);
    }

    public function __destruct()
    {
        unset($this->modules);
    }

    // Needs to be updated to suit Smarty and XML Template Systems.
    public function initialize($type = null, $themeName = 'classic')
    {
        if ($this->initialized != true)
        {
            if ($type === TEMPLATE_USE_SMARTY)
            {
                $smarty_errors = null;
                if (!is_dir('templates'))
                {
                    $smarty_errors.='The Kabal Invasion smarty error: The templates/ subdirectory under the main TKI directory does not exist. Please create it.<br>';
                }

                $cache_perms = is_writable('templates/_cache');
                $compile_perms = is_writable('templates/_compile');

                if (!$cache_perms)
                {
                    $smarty_errors.= 'The Kabal Invasion smarty error: The templates/_cache directory needs to have its permissions set to be writable by the web server user, OR 777, or ugo+rwx.<br>';
                }

                if (!$compile_perms)
                {
                    $smarty_errors.= 'The Kabal invasion smarty error: The templates/_compile directory needs to have its permissions set to be writable by the web server user, OR 777, or ugo+rwx.<br>';
                }

                if ($smarty_errors !== null)
                {
                    die ($smarty_errors);
                }
                else
                {
                    // Create the module.
                    $api = new Smarty($this);
                }
            }
            else
            {
                return (boolean) false;
            }

            // Set Wrapper Class Name.
            $this->api_class = get_class($api);

            // Add Wrapper to list.
            $this->api[$this->api_class] = $api;

            // Initialize Wrapper.
            $this->api[$this->api_class]->initialize('CRAP');

            // Flag as initialised.
            $this->initialized = (boolean) true;

            // Clear unwanted info.
            unset($api);

            // return true;
            return (boolean) true;
        }
    }

    public function addVariables($node, $variables)
    {
        $this->api[$this->api_class]->addVariables($node, $variables);
    }

    public function getVariables($node)
    {
        if (method_exists($this->api[$this->api_class], 'GetVariables') === true)
        {
            return $this->api[$this->api_class]->getVariables($node);
        }

        return (boolean) false;
    }

    public function display($template_file = null)
    {
        $this->api[$this->api_class]->Display($template_file);
    }

    public function test()
    {
        $this->api[$this->api_class]->Test();
    }

    public function getAPI()
    {
        return $this->api;
    }

    public function setTheme($theme = null)
    {
        if (method_exists($this->api[$this->api_class], 'SetTheme') === true)
        {
            $this->api[$this->api_class]->SetTheme($theme);
        }
    }

    public function handleCompression($output = null)
    {
        // Check to see if we have data, if not error out.
        if (is_null($output))
        {
            return $output;
        }

        // Handle the supported compressions.
        $supported_enc = array();
        if (array_key_exists('HTTP_ACCEPT_ENCODING', $_SERVER))
        {
            $supported_enc = explode(',', $_SERVER['HTTP_ACCEPT_ENCODING']);
        }

        if (in_array('gzip', $supported_enc) === true)
        {
            header('Vary: Accept-Encoding');
            header('Content-Encoding: gzip');
            header('DEBUG: gzip found');

            return gzencode($output, 9);
        }
        elseif (in_array('deflate', $supported_enc) === true)
        {
            header('Vary: Accept-Encoding');
            header('Content-Encoding: deflate');
            header('DEBUG: deflate found');

            return gzdeflate($output, 9);
        }
        else
        {
            header('DEBUG: None found');

            return $output;
        }

//        return $output; // Leaving this here because during debugging removing compression can be very helpful
    }
}

