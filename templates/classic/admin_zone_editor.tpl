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

    File: admin_zone_editor.tpl
*}
<strong>{$langvars['l_admin_zone_editor']}</strong>
<br>
<form accept-charset="utf-8" action="admin.php" method="post">
{if $variables['zone'] == ''}
    <select size="20" name="zone">
    {foreach $variables['zones'] as $zone}
        <option value="{$zone['zone_id']}">{$zone['zone_name']}</option>
    {/foreach}
    </select>
    <input type="hidden" name="operation" value="edit">
    &nbsp;<input type="submit" value="{$langvars['l_edit']}">
{else}
    {if $variables['operation'] == "edit"}
        <table border="0" cellspacing="0" cellpadding="5">
        <tr><td>{$langvars['l_admin_zone_id']}</td><td>{$variables['zone_id']}</td></tr>
        <tr><td>{$langvars['l_ze_name']}</td><td><input type="text" name="zone_name" value="{$variables['zone_name']}"></td></tr>
        <tr><td>{$langvars['l_admin_allow_beacon']}</td><td><input type="checkbox" name="zone_beacon" value="on" {$variables['allow_beacon']}></td>
        <tr><td>{$langvars['l_admin_allow_attack']}</td><td><input type="checkbox" name="zone_attack" value="on" {$variables['allow_attack']}></td>
        <tr><td>{$langvars['l_admin_allow_warpedit']}</td><td><input type="checkbox" name="zone_warpedit" value="on" {$variables['allow_warpedit']}></td>
        <tr><td>{$langvars['l_admin_allow_planet']}</td><td><input type="checkbox" name="zone_planet" value="on" {$variables['allow_planet']}></td>
        <tr><td>{$langvars['l_admin_max_hull']}</td><td><input type="text" name="zone_hull" value="{$variables['max_hull']}"></td></tr>
        </table>
        <br>
        <input type="hidden" name="zone" value="{$variables['zone']}">
        <input type="hidden" name="operation" value="save">
        <input type="submit" value="{$langvars['l_save']}">
    {elseif $variables['operation'] == "save"}
        {$langvars['l_admin_changes_saved']}<br><br>
        <input type="submit" value="{$langvars['l_admin_return_zone_editor']}">
    {else}
        {$langvars['l_admin_invalid_operation']}
    {/if}
{/if}
<input type="hidden" name="menu" value="zone_editor.php">
<input type="hidden" name="swordfish" value="{$variables['swordfish']}">
</form>
