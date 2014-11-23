{*
    The Kabal Invasion - A web-based 4X space game
    Copyright © 2014 The Kabal Invasion development team, Ron Harwood, and the BNT development team.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

    File: admin.tpl
*}

{if !isset($variables['body_class'])}
{$variables['body_class'] = "tki"}
{/if}
  <body class="{$variables['body_class']}">
<div class="wrapper">
<h1>{$langvars['l_admin_title']}</h1>

{if $variables['is_admin'] != true}
    <form accept-charset="utf-8" action="admin.php" method="post">
    {$langvars['l_admin_password']}: <input type="password" name="swordfish" size="20" maxlength="20">&nbsp;&nbsp;
    <input type="submit" value="{$langvars['l_submit']}"><input type="reset" value="{$langvars['l_reset']}">
    </form>
{else}

    {if $variables['menu'] == ''}
        {$langvars['l_admin_welcome']}<br><br>
        {$langvars['l_admin_menulist']}<br>
        <form accept-charset="utf-8" action="admin.php" method="post">
        <select name="menu">
            {foreach $variables['filename'] as $admin_file}
            <option value="{$admin_file['file']}">{$admin_file['option_title']}</option>
            {/foreach}
        </select>
        <input type="hidden" name="swordfish" value="{$variables['swordfish']}">
        <input type="submit" value="{$langvars['l_submit']}">
        </form>
    {else}
{*        {if $variables['menu_location'] === false}
            {$langvars['l_admin_unknown_function']}
        {else}
            {$variables['menu_location']}
        {/if}
            {$variables['menu_location']}*}

        {* Now check and handle the inclusion of the admin module templates *}
        {if isset($variables['module'])}
            {include file="admin_{$variables['module']}.tpl" inline}
        {/if}

        {if $variables['button_main'] == true}
            <p><form accept-charset="utf-8" action="admin.php" method="post">
            <input type="hidden" name="swordfish" value="{$variables['swordfish']}">
            <input type="submit" value="{$langvars['l_admin_return_admin_menu']}">
            </form>
        {/if}
    {/if}
<br>
{/if}
<br>
{$variables['linkback']['fulltext']|replace:"[here]":"<a href='{$variables['linkback']['link']}'>{$langvars['l_here']}</a>"}
