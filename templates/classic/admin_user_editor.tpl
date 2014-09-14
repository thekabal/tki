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

    File: admin_user_editor.tpl
*}
<strong>{$langvars['l_admin_user_editor']}</strong>
<br>
<form accept-charset="utf-8" action="admin.php" method="post">
{if $variables['user'] == ''}
    <select size="20" name="user">
        {foreach $variables['players'] as $player}
            <option value="{$player['ship_id']}">{$player['character_name']}</option>
        {/foreach}
    </select>
    &nbsp;<input type="submit" value="{$langvars['l_edit']}">
{else}
    {if $variables['operation'] == ''}
        <table border="0" cellspacing="0" cellpadding="5">
        <tr><td>{$langvars['l_admin_player_name']}</td><td><input type="text" name="character_name" value="{$variables['character_name']}"></td></tr>
        <tr><td>{$langvars['l_admin_password']}</td><td><input type="text" name="password2" placeholder="*Encrypted*"></td></tr>
        <tr><td>{$langvars['l_admin_email']}</td><td><input type="email" name="email" placeholder="admin@example.com" value="{$variables["email"]}"></td></tr>
        <tr><td>{$langvars['l_admin_user_id']}</td><td>{*{$variables['user']}*}</td></tr>
        <tr><td>{$langvars['l_ship']}</td><td><input type="text" name="ship_name" value="{$variables['ship_name']}"></td></tr>
        <tr><td>{$langvars['l_admin_destroyed']}</td><td><input type="checkbox" name="ship_destroyed" value="on" {$variables['ship_destroyed']}></td></tr>
        <tr><td>{$langvars['l_admin_levels']}</td>
        <td><table border="0" cellspacing="0" cellpadding="5">
        <tr><td>{$langvars['l_hull']}</td><td><input type="text" size="5" name="hull" value="{$variables['hull']}"></td>
        <td>{$langvars['l_engines']}</td><td><input type="text" size="5" name="engines" value="{$variables['engines']}"></td>
        <td>{$langvars['l_power']}</td><td><input type="text" size="5" name="power" value="{$variables['power']}"></td>
        <td>{$langvars['l_computer']}</td><td><input type="text" size="5" name="computer" value="{$variables['computer']}"></td></tr>
        <tr><td>{$langvars['l_sensors']}</td><td><input type="text" size="5" name="sensors" value="{$variables['sensors']}"></td>
        <td>{$langvars['l_armor']}</td><td><input type="text" size="5" name="armor" value="{$variables['armor']}"></td>
        <td>{$langvars['l_shields']}</td><td><input type="text" size="5" name="shields" value="{$variables['shields']}"></td>
        <td>{$langvars['l_beams']}</td><td><input type="text" size="5" name="beams" value="{$variables['beams']}"></td></tr>
        <tr><td>{$langvars['l_torps']}</td><td><input type="text" size="5" name="torp_launchers" value="{$variables['torp_launchers']}"></td>
        <td>{$langvars['l_cloak']}</td><td><input type="text" size="5" name="cloak" value="{$variables['cloak']}"></td></tr>
        </table></td></tr>
        <tr><td>{$langvars['l_holds']}</td>
        <td><table border="0" cellspacing="0" cellpadding="5">
        <tr><td>{$langvars['l_ore']}</td><td><input type="text" size="8" name="ship_ore" value="{$variables['ship_ore']}"></td>
        <td>{$langvars['l_organics']}</td><td><input type="text" size="8" name="ship_organics" value="{$variables['ship_organics']}"></td>
        <td>{$langvars['l_goods']}</td><td><input type="text" size="8" name="ship_goods" value="{$variables['ship_goods']}"></td></tr>
        <tr><td>{$langvars['l_energy']}</td><td><input type="text" size="8" name="ship_energy" value="{$variables['ship_energy']}"></td>
        <td>{$langvars['l_colonists']}</td><td><input type="text" size="8" name="ship_colonists" value="{$variables['ship_colonists']}"></td></tr>
        </table></td></tr>
        <tr><td>{$langvars['l_admin_combat']}</td>
        <td><table border="0" cellspacing="0" cellpadding="5">
        <tr><td>{$langvars['l_fighters']}</td><td><input type="text" size="8" name="ship_fighters" value="{$variables['ship_fighters']}"></td>
        <td>{$langvars['l_torps']}</td><td><input type="text" size="8" name="torps" value="{$variables['torps']}"></td></tr>
        <tr><td>{$langvars['l_armorpts']}</td><td><input type="text" size="8" name="armor_pts" value="{$variables['armor_pts']}"></td></tr>
        </table></td></tr>
        <tr><td>{$langvars['l_devices']}</td>
        <td><table border="0" cellspacing="0" cellpadding="5">
        <tr><td>{$langvars['l_admin_beacons']}</td><td><input type="text" size="5" name="dev_beacon" value="{$variables['dev_beacon']}"></td>
        <td>{$langvars['l_warpedit']}</td><td><input type="text" size="5" name="dev_warpedit" value="{$variables['dev_warpedit']}"></td>
        <td>{$langvars['l_genesis']}</td><td><input type="text" size="5" name="dev_genesis" value="{$variables['dev_genesis']}"></td></tr>
        <tr><td>{$langvars['l_deflect']}</td><td><input type="text" size="5" name="dev_minedeflector" value="{$variables['dev_minedeflector']}"></td>
        <td>{$langvars['l_ewd']}</td><td><input type="text" size="5" name="dev_emerwarp" value="{$variables['dev_emerwarp']}"></td></tr>
        <tr><td>{$langvars['l_escape_pod']}</td><td><input type="checkbox" name="dev_escapepod" value="on"{$variables['dev_escapepod']}></td>
        <td>{$langvars['l_fuel_scoop']}</td><td><input type="checkbox" name="dev_fuelscoop" value="on"{$variables['dev_fuelscoop']}></td></tr>
        </table></td></tr>
        <tr><td>{$langvars['l_credits']}</td><td><input type="text" name="credits" value="{$variables['credits']}"></td></tr>
        <tr><td>{$langvars['l_turns']}</td><td><input type="text" name="turns" value="{$variables['turns']}"></td></tr>
        <tr><td>{$langvars['l_admin_current_sector']}</td><td><input type="text" name="sector" value="{$variables['sector']}"></td></tr>
        </table>
        <br>
        <input type="hidden" name="user" value="{$variables['user']}">
        <input type="hidden" name="operation" value="save">
        <input type="submit" value="{$langvars['l_save']}">
    {elseif $variables['operation'] == "save"}
        {$langvars['l_admin_changes_saved']}<br><br>
        <input type="submit" value="{$langvars['l_admin_return_user_editor']}">
    {else}
       {$langvars['l_admin_invalid_operation']}
    {/if}
{/if}
<input type="hidden" name="menu" value="user_editor.php">
<input type="hidden" name="swordfish" value="{$variables['swordfish']}">
</form>
