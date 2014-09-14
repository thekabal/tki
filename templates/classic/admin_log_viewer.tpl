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

    File: admin_log_viewer.tpl
*}

<form accept-charset="utf-8" action="log.php" method="post">
<input type="hidden" name="swordfish" value="{$variables['swordfish']}">
<input type="hidden" name="player" value="0">
<input type="submit" value="{$langvars['l_admin_view_admin_log']}">
</form>
<form accept-charset="utf-8" action="log.php" method="post">
<input type="hidden" name="swordfish" value="{$variables['swordfish']}">
<select name="player">

{foreach $variables['players'] as $player}
<option value="{$player['ship_id']}">{$player['character_name']}</option>
{/foreach}

</select>&nbsp;&nbsp;
<input type="submit" value="{$langvars['l_admin_view_player_log']}">
</form><hr size="1" width="80%">
