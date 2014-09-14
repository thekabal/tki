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

    File: admin_sector_editor.tpl
*}

<h2>{$langvars['l_admin_sector_editor']}</h2>
<form accept-charset='utf-8' action='admin.php' method='post'>
{if $variables['sector'] == ''}
    <select size='20' name='sector'>
    {foreach $variables['sectors'] as $edit_sector}
        <option value="{$edit_sector['sector_id']}">{$edit_sector['sector_id']}</option>
    {/foreach}
    </select>
    &nbsp;<input type='submit' value='{$langvars['l_edit']}'>
{else}
    {if $variables['operation'] == ''}
        <table border='0' cellspacing='2' cellpadding='2'>
        <tr><td><tt>{$langvars['l_admin_sector_id']}</tt></td><td><font color='#6f0'>{$variables['sector']}</font></td>
        <td align='right'><tt>{$langvars['l_admin_sector_name']}</tt></td><td><input type='text' size='15' name='sector_name' value="{$variables['sector_name']}"></td>
        <td align='right'><tt>{$langvars['l_admin_zone_id']}</tt></td><td>
        <select size='1' name='zone_id'>

        {foreach $variables['zones'] as $zone}
            {if $zone['zone_id'] == $variables['selected_zone']}
                <option selected='selected' value="{$zone['zone_id']}">{$zone['zone_name']}</option>
            {else}
                <option value="{$zone['zone_id']}">{$zone['zone_name']}</option>
            {/if}
        {/foreach}

        </select></td></tr>
        <tr><td><tt>{$langvars['l_admin_beacon']}</tt></td><td colspan='5'><input type='text' size='70' name='beacon' value="{$variables['beacon']}"></td></tr>
        <tr><td><tt>{$langvars['l_admin_distance']}</tt></td><td><input type='text' size='9' name='distance' value="{$variables['distance']}"></td>
        <td align='right'><tt>{$langvars['l_admin_angle1']}</tt></td><td><input type='text' size='9' name='angle1' value="{$variables['angle1']}"></td>
        <td align='right'><tt>{$langvars['l_admin_angle2']}</tt></td><td><input type='text' size='9' name='angle2' value="{$variables['angle2']}"></td></tr>
        <tr><td colspan='6'><hr></td></tr>
        </table>

        <table border='0' cellspacing='2' cellpadding='2'>
        <tr><td><tt>{$langvars['l_admin_port_type']}</tt></td><td>
        <select size='1' name='port_type'>
        <option $oportnon='none'>{$langvars['l_none']}</option>
        <option $oportspe='special'>{$langvars['l_special']}</option>
        <option $oportorg='organics'>{$langvars['l_organics']}</option>
        <option $oportore='ore'>{$langvars['l_ore']}</option>
        <option $oportgoo='goods'>{$langvars['l_goods']}</option>
        <option $oportene='energy'>{$langvars['l_energy']}</option>
        </select></td>
        <td align='right'><tt>{$langvars['l_organics']}</tt></td><td><input type='text' size='9' name='port_organics' value="{$variables['port_organics']}"></td>
        <td align='right'><tt>{$langvars['l_ore']}</tt></td><td><input type='text' size='9' name='port_ore' value="{$variables['port_ore']}"></td>
        <td align='right'><tt>{$langvars['l_goods']}</tt></td><td><input type='text' size='9' name='port_goods' value="{$variables['port_goods']}"></td>
        <td align='right'><tt>{$langvars['l_energy']}</tt></td><td><input type='text' size='9' name='port_energy' value="{$variables['port_energy']}"></td></tr>
        <tr><td colspan='10'><hr></td></tr>
        </table>

        <br>
        <input type='hidden' name='sector' value="{$variables['sector']}">
        <input type='hidden' name='operation' value='save'>
        <input type='submit' size='1' value="{$langvars['l_save']}">
    {elseif $variables['operation'] == "save"}
        {if $variables['secupdate'] == false}
            {$langvars['l_admin_sector_editor_failed']}<br><br>
            {$variables['db_error_msg']}<br>
        {else}
            {$langvars['l_admin_sector_editor_saved']}<br><br>
        {/if}
        <input type='submit' value="{$langvars['l_admin_return_sector_editor']}">
    {else}
        {$langvars['l_admin_invalid_operation']}
    {/if}
{/if}
<input type='hidden' name='menu' value='sector_editor.php'>
<input type='hidden' name='swordfish' value="{$variables['swordfish']}">
</form>
