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

    File: admin_universe_editor.tpl
*}

<strong>{$langvars['l_admin_universe_editor']}</strong>
<br>{$langvars['l_admin_expand_or_contract']}<br>

{if $variables['action'] == ''}
    <form accept-charset="utf-8" action="admin.php" method="post">
    {$langvars['l_admin_universe_size']}: <input type="text" name="radius" value="{$variables['universe_size']}">
    <input type="hidden" name="swordfish" value="{$variables['swordfish']}">
    <input type="hidden" name="menu" value="universe_editor.php">
    <input type="hidden" name="action" value="doexpand">
{*    <input type="submit" value="{$langvars['l_admin_change_universe_size']}">*}
    <br>
    <input type="submit" value="Submit">
    <br><br>
    {$langvars['l_admin_universe_resize_slow']}

    </form>
{elseif $variables['action'] == "doexpand"}
    <br><font size='+2'>{$langvars['l_admin_universe_update']}</font><br><br>
    {foreach $variables['changed_sectors'] as $changed_sector}
    {$changed_sector}<br>
    {/foreach}
{/if}
