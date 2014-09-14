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
// File: classes/Smarty.php

namespace Bnt;

class Smarty
{
    private $smarty                            = null;
    private $parent                            = null;

    public function __construct($parent)
    {
        $this->parent = $parent;
        $this->smarty = new \Smarty();
    }

    public function __destruct()
    {
    }

    public function initialize()
    {
        // Setup all Smarty's Path locations.
        $this->smarty->setCompileDir('templates/_compile/');
        $this->smarty->setCacheDir('templates/_cache/');
        $this->smarty->setConfigDir('templates/_configs/');

        // Add a Modifier Wrapper for PHP Function: number_format.
        // Usage in the tpl file {$number|number_format:decimals:"dec_point":"thousands_sep"}
        $this->smarty->registerPlugin('modifier', 'number_format', 'number_format');

        // Add a Modifier Wrapper for PHP Function: strlen.
        // Usage in the tpl file {$string|strlen}
        $this->smarty->registerPlugin('modifier', 'strlen', 'strlen');

        // Add a Modifier Wrapper for PHP Function: gettype.
        // Usage in the tpl file {$variable|gettype}
        $this->smarty->registerPlugin('modifier', 'gettype', 'gettype');

        $this->smarty->enableSecurity();

        // Smarty Caching.
        $this->smarty->caching = false;
    }

    public function setTheme($themeName = null)
    {
        $this->smarty->setTemplateDir("templates/{$themeName}");
        $this->addVariables('template_dir', "templates/{$themeName}");
    }

    public function addVariables($nodeName, $variables)
    {
        // We don't require the container so remove it.
        if (is_array($variables) && isset($variables['container']) == true)
        {
            unset ($variables['container']);
        }

        $tmpNode = $this->smarty->getTemplateVars($nodeName);

        if (!is_null($tmpNode))
        {
            // Now we make sure we don't want dupes which causes them to become an array.
            foreach ($variables as $key => $value)
            {
                if (array_key_exists($key, $tmpNode) && $tmpNode[$key] == $value)
                {
                    unset($variables[$key]);
                }
            }

            $variables = array_merge_recursive($tmpNode, $variables);
        }
        $this->smarty->assign($nodeName, $variables);
    }

    public function getVariables($nodeName)
    {
        return $this->smarty->getTemplateVars($nodeName);
    }

    public function test()
    {
        $this->smarty->testInstall();
    }

    public function display($template_file)
    {
        // Process template and return the output in a
        // varable so that we can compress it or not.
        try
        {
            $output = $this->smarty->fetch($template_file);
        }
        catch (exception $e)
        {
            // $output = $this->smarty->fetch ($template_file);
            $output  = 'The smarty template system is not working. We suggest checking the specific template you are using for an error in the page that you want to access.';
        }

        echo $output;
    }
}
