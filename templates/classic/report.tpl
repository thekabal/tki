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

    File: report.tpl
*}

{if !isset($variables['body_class'])}
{$variables['body_class'] = "tki"}
{/if}
  <body class="{$variables['body_class']}">
<div class="wrapper">

<div style="width:90%; margin:auto; font-size:14px;">
    <table border=0 cellspacing=0 cellpadding=0 width="100%">
        <tr bgcolor="{$variables['color_header']}">
            <td><strong>{$langvars['l_player']}: {$variables['playerinfo_character_name']}</strong></td>
            <td align=center><strong>{$langvars['l_ship']}: {$variables['playerinfo_ship_name']}</strong></td>
            <td align=right><strong>{$langvars['l_credits']}: {$variables['playerinfo_credits']|number_format:0:$langvars['local_number_dec_point']:$langvars['local_number_thousands_sep']}</strong></td>
        </tr>
    </table>
    <br>
    <table border=0 cellspacing=5 cellpadding=0  width="100%">
        <tr>
            <td>
                <table border=0 cellspacing=0 cellpadding=0 width="100%">
                    <tr bgcolor="{$variables['color_header']}">
                        <td><strong>{$langvars['l_ship_levels']}</strong></td>
                        <td></td>
                    </tr>
                    <tr bgcolor="{$variables['color_line1']}" style="font-style:italic;">
                        <td>{$langvars['l_hull']}</td>
                        <td style="text-align:right">{$langvars['l_level']} {$variables['playerinfo_hull']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line2']}" style="font-style:italic">
                        <td>{$langvars['l_engines']}</td>
                        <td style="text-align:right">{$langvars['l_level']} {$variables['playerinfo_engines']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line1']}">
                        <td>{$langvars['l_power']}</td>
                        <td style="text-align:right">{$langvars['l_level']} {$variables['playerinfo_power']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line2']}" style="font-style:italic">
                        <td>{$langvars['l_computer']}</td>
                        <td style="text-align:right">{$langvars['l_level']} {$variables['playerinfo_computer']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line1']}">
                        <td>{$langvars['l_sensors']}</td>
                        <td style="text-align:right">{$langvars['l_level']} {$variables['playerinfo_sensors']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line2']}" style="font-style:italic">
                        <td>{$langvars['l_armor']}</td>
                        <td style="text-align:right">{$langvars['l_level']} {$variables['playerinfo_armor']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line1']}" style="font-style:italic">
                        <td> {$langvars['l_shields']}</td>
                        <td style="text-align:right">{$langvars['l_level']} {$variables['playerinfo_shields']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line2']}" style="font-style:italic">
                        <td> {$langvars['l_beams']}</td>
                        <td style="text-align:right">{$langvars['l_level']} {$variables['playerinfo_beams']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line1']}" style="font-style:italic">
                        <td>{$langvars['l_torp_launch']}</td>
                        <td style="text-align:right">{$langvars['l_level']} {$variables['playerinfo_torp_launchers']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line2']}">
                        <td>{$langvars['l_cloak']}</td>
                        <td style="text-align:right">{$langvars['l_level']} {$variables['playerinfo_cloak']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line1']}">
                        <td><i>{$langvars['l_shipavg']}</i></td>
                        <td style="text-align:right">{$langvars['l_level']} {$variables['shipavg']|number_format:0:$langvars['local_number_dec_point']:$langvars['local_number_thousands_sep']}</td>
                    </tr>
                </table>
            </td>
            <td valign=top>
                <table border=0 cellspacing=0 cellpadding=0 width="100%">
                    <tr bgcolor="{$variables['color_header']}">
                        <td><strong>{$langvars['l_holds']}</strong></td>
                        <td align=right><strong>{$variables['holds_used']|number_format:0:$langvars['local_number_dec_point']:$langvars['local_number_thousands_sep']} / {$variables['holds_max']|number_format:0:$langvars['local_number_dec_point']:$langvars['local_number_thousands_sep']}</strong></td>
                    </tr>
                    <tr bgcolor="{$variables['color_line1']}">
                        <td>{$langvars['l_ore']}</td>
                        <td align=right>{$variables['playerinfo_ship_ore']|number_format:0:$langvars['local_number_dec_point']:$langvars['local_number_thousands_sep']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line2']}">
                        <td>{$langvars['l_organics']}</td>
                        <td align=right>{$variables['playerinfo_ship_organics']|number_format:0:$langvars['local_number_dec_point']:$langvars['local_number_thousands_sep']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line1']}">
                        <td>{$langvars['l_goods']}</td>
                        <td align=right>{$variables['playerinfo_ship_goods']|number_format:0:$langvars['local_number_dec_point']:$langvars['local_number_thousands_sep']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line2']}">
                        <td>{$langvars['l_colonists']}</td>
                        <td align=right>{$variables['playerinfo_ship_colonists']|number_format:0:$langvars['local_number_dec_point']:$langvars['local_number_thousands_sep']}</td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr bgcolor="{$variables['color_header']}">
                        <td><strong>{$langvars['l_arm_weap']}</strong></td>
                        <td></td>
                    </tr>
                    <tr bgcolor="{$variables['color_line1']}">
                        <td>{$langvars['l_armorpts']}</td>
                        <td align=right>{$variables['playerinfo_armor_pts']|number_format:0:$langvars['local_number_dec_point']:$langvars['local_number_thousands_sep']} / {$variables['armor_pts_max']|number_format:0:$langvars['local_number_dec_point']:$langvars['local_number_thousands_sep']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line2']}">
                        <td>{$langvars['l_fighters']}</td>
                        <td align=right>{$variables['playerinfo_ship_fighters']|number_format:0:$langvars['local_number_dec_point']:$langvars['local_number_thousands_sep']} / {$variables['ship_fighters_max']|number_format:0:$langvars['local_number_dec_point']:$langvars['local_number_thousands_sep']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line1']}">
                        <td>{$langvars['l_torps']}</td>
                        <td align=right>{$variables['playerinfo_torps']|number_format:0:$langvars['local_number_dec_point']:$langvars['local_number_thousands_sep']} / {$variables['torps_max']|number_format:0:$langvars['local_number_dec_point']:$langvars['local_number_thousands_sep']}</td>
                    </tr>
                </table>
            </td>
            <td valign=top>
                <table border=0 cellspacing=0 cellpadding=0 width="100%">
                    <tr bgcolor="{$variables['color_header']}">
                        <td><strong>{$langvars['l_energy']}</strong></td>
                        <td align=right><strong>{$variables['playerinfo_ship_energy']|number_format:0:$langvars['local_number_dec_point']:$langvars['local_number_thousands_sep']} / {$variables['energy_max']|number_format:0:$langvars['local_number_dec_point']:$langvars['local_number_thousands_sep']}</strong></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr bgcolor="{$variables['color_header']}">
                        <td><strong>{$langvars['l_devices']}</strong></td>
                        <td></strong></td>
                    </tr>
                    <tr bgcolor="{$variables['color_line1']}">
                        <td>{$langvars['l_beacons']}</td>
                        <td align=right>{$variables['playerinfo_dev_beacon']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line2']}">
                        <td>{$langvars['l_warpedit']}</td>
                        <td align=right>{$variables['playerinfo_dev_warpedit']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line1']}">
                        <td>{$langvars['l_genesis']}</td>
                        <td align=right>{$variables['playerinfo_dev_genesis']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line2']}">
                        <td>{$langvars['l_deflect']}</td>
                        <td align=right>{$variables['playerinfo_dev_minedeflector']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line1']}">
                        <td>{$langvars['l_ewd']}</td>
                        <td align=right>{$variables['playerinfo_dev_emerwarp']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line2']}">
                        <td>{$langvars['l_escape_pod']}</td>
                        <td align=right>{$variables['escape_pod']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line1']}">
                        <td>{$langvars['l_fuel_scoop']}</td>
                        <td align=right>{$variables['fuel_scoop']}</td>
                    </tr>
                    <tr bgcolor="{$variables['color_line2']}">
                        <td>{$langvars['l_lssd']}</td>
                        <td align=right>{$variables['lssd']}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
<p align=center><img src="{$variables['ship_img']}" style="border:0px; width:80px; height:60px"></p>
{$variables['linkback']['fulltext']|replace:"[here]":"<a href='{$variables['linkback']['link']}'>{$langvars['l_here']}</a>"}
