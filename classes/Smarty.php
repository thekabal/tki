<?php declare(strict_types = 1);
/**
 * classes/Smarty.php from The Kabal Invasion.
 * The Kabal Invasion is a Free & Opensource (FOSS), web-based 4X space/strategy game.
 *
 * @copyright 2020 The Kabal Invasion development team, Ron Harwood, and the BNT development team
 *
 * @license GNU AGPL version 3.0 or (at your option) any later version.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Tki;

class Smarty
{
    protected \Smarty $smarty;

    public function __construct()
    {
        $this->smarty = new \Smarty();
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
        $this->smarty->caching = 0;

        $smarty_errors = null;
        if (!is_dir('templates'))
        {
            $smarty_errors .= 'The Kabal Invasion smarty error: ' .
                              'The templates/ subdirectory under the main TKI ' .
                              'directory does not exist. Please create it.<br>';
        }

        $cache_perms = is_writable('templates/_cache');
        $compile_perms = is_writable('templates/_compile');

        if (!$cache_perms)
        {
            $smarty_errors .= '<br><br>The Kabal Invasion smarty error: ' .
                              'The templates/_cache directory needs to have its ' .
                              'permissions set to be writable by the web server ' .
                              'user, OR 777, or ugo+rwx.<br>';
        }

        if (!$compile_perms)
        {
            $smarty_errors .= '<br><br>The Kabal invasion smarty error: ' .
                              'The templates/_compile directory needs to have its ' .
                              'permissions set to be writable by the web server ' .
                              'user, OR 777, or ugo+rwx.<br>';
        }

        if ($smarty_errors !== null)
        {
            throw new \Exception($smarty_errors);
        }
    }

    public function __destruct()
    {
    }

    public function setTheme(string $themeName): void
    {
        $this->smarty->setTemplateDir("templates/{$themeName}");
        $this->addVariables('template_dir', "templates/{$themeName}");
    }

    /**
     * @param ((bool|int|mixed|null|string)[]|mixed|null|scalar)[]|string $variables
     */
    public function addVariables(string $nodeName, $variables): void
    {
        $tmpNode = $this->smarty->getTemplateVars($nodeName);
        if ($tmpNode !== null)
        {
            // Now we make sure we don't want dupes which causes them to become an array.
            if (is_array($variables))
            {
                foreach ($variables as $key => $value)
                {
                    if (array_key_exists($key, $tmpNode) && $tmpNode[$key] == $value)
                    {
                        unset($variables[$key]);
                    }
                }
            }

            if (is_array($variables))
            {
                $variables = array_merge_recursive($tmpNode, $variables);
            }
        }

        $this->smarty->assign($nodeName, $variables);
    }

    public function getVariables(string $nodeName): string
    {
        $temp_variable = $this->smarty->getTemplateVars($nodeName);
        return $temp_variable;
    }

    /**
     * @param ((int|mixed|null|string)[]|bool|int|mixed)[]|scalar $variables
     */
    public function assign(string $nodeName, $variables): void
    {
        $this->smarty->assign($nodeName, $variables);
    }

    public function display(string $template_file): void
    {
        // Process template and return the output in a
        // varable so that we can compress it or not.
        try
        {
            $output = $this->smarty->fetch($template_file);
        }
        catch (\exception $e)
        {
            $output = 'The smarty template system is not working. ' .
                      'We suggest checking the specific template you ' .
                      'are using for an error in the page that you want to access.';
        }

        echo $output;
    }
}
