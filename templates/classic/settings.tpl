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

    File: settings.tpl
*}

{extends file="layout.tpl"}

{block name=body}
<h1>{$langvars['l_set_game_admins']}</h1>
{* Need an if statement here to detect if anyone is an admin or developer *}
<div style="width:798px; font-size:14px; color:#fff; background-color:#500050; padding-top:2px; padding-bottom:2px; border:#fff 1px solid;">&nbsp;{$langvars['l_set_no_admins']}</div>
<br>
<h1>{$langvars['l_set_settings']}</h1>
<table style="width:800px; font-size:14px; color:#fff; border:#fff 1px solid;" border="0" cellpadding="2" cellspacing="0"><tbody>
    <tr bgcolor="#300030">
        <td>&nbsp;{$langvars['l_set_game_version']}</td>
        <td style="text-align:right;">{$variables['release_version']}&nbsp;</td>
    </tr>
<tr bgcolor="#400040"><td>&nbsp;{$langvars['l_set_game_name']}</td><td style="text-align:right;">{$variables['game_name']}&nbsp;</td></tr>
<tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_mines_level']}</td><td style="text-align:right;">{$variables['mine_hullsize']}&nbsp;</td></tr>
<tr bgcolor="#400040"><td>&nbsp;{$langvars['l_set_ewd_level']}</td><td style="text-align:right;">{$variables['max_ewdhullsize']}&nbsp;</td></tr>
<tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_num_sec']}</td><td style="text-align:right;">{$variables['max_sectors']}&nbsp;</td></tr>
<tr bgcolor="#400040"><td>&nbsp;{$langvars['l_set_max_links']}</td><td style="text-align:right;">{$variables['max_links']}&nbsp;</td></tr>
<tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_max_fed']}</td><td style="text-align:right;">{$variables['max_fed_hull']}&nbsp;</td></tr>

{if $variables['allow_ibank'] === true}
<tr bgcolor="#400040"><td>&nbsp;{$langvars['l_set_igb_enabled']}</td><td style="text-align:right;">{$langvars['l_yes']}&nbsp;</td></tr>
{/if}
<tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_igb_interest_rate']}</td><td style="text-align:right;">{$variables['ibank_interest']}&nbsp;</td></tr>
<tr bgcolor="#400040"><td>&nbsp;{$langvars['l_set_igb_loan_rate']}</td><td style="text-align:right;">{$variables['ibank_loaninterest']}&nbsp;</td></tr>
<tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_base_level']}</td><td style="text-align:right;">{$variables['base_defense']}&nbsp;</td></tr>
<tr bgcolor="#400040"><td>&nbsp;{$langvars['l_set_colonists']}</td><td style="text-align:right;">{$variables['colonist_limit']}&nbsp;</td></tr>
<tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_max_turns']}</td><td style="text-align:right;">{$variables['max_turns']}&nbsp;</td></tr>
<tr bgcolor="#400040"><td>&nbsp;{$langvars['l_set_max_planets']}</td><td style="text-align:right;">{$variables['max_planets_sector']}&nbsp;</td></tr>
<tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_max_traderoutes']}</td><td style="text-align:right;">{$variables['max_traderoutes_player']}&nbsp;</td></tr>
<tr bgcolor="#400040"><td>&nbsp;{$langvars['l_set_colonist_prod']}</td><td style="text-align:right;">{$variables['colonist_production_rate']}&nbsp;</td></tr>
<tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_energy_fits']}</td><td style="text-align:right;">{$variables['energy_per_fighter']}&nbsp;</td></tr>
<tr bgcolor="#400040"><td>&nbsp;{$langvars['l_set_fit_degrade']}</td><td style="text-align:right;">{$variables['defence_degrade_rate']}&nbsp;</td></tr>
<tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_sector_own']}&nbsp;</td><td style="text-align:right;">{$variables['min_bases_to_own']}&nbsp;</td></tr>
<tr bgcolor="#400040"><td>&nbsp;{$langvars['l_set_planet_interest']}</td><td style="text-align:right;">{$variables['interest_rate']}&nbsp;</td></tr>
<tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_prod_fit']}</td><td style="text-align:right;">{$variables['cols_needed_fit']}&nbsp;</td></tr>
<tr bgcolor="#400040"><td>&nbsp;{$langvars['l_set_prod_torp']}</td><td style="text-align:right;">{$variables['cols_needed_torp']}&nbsp;</td></tr>
<tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_prod_ore']}</td><td style="text-align:right;">{$variables['cols_needed_ore']}&nbsp;</td></tr>
<tr bgcolor="#400040"><td>&nbsp;{$langvars['l_set_prod_org']}</td><td style="text-align:right;">{$variables['cols_needed_org']}&nbsp;</td></tr>
<tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_prod_goods']}</td><td style="text-align:right;">{$variables['cols_needed_goods']}&nbsp;</td></tr>
<tr bgcolor="#400040"><td>&nbsp;{$langvars['l_set_prod_energy']}</td><td style="text-align:right;">{$variables['cols_needed_ene']}&nbsp;</td></tr>
<tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_prod_credits']}</td><td style="text-align:right;">{$variables['cols_needed_cred']}&nbsp;</td></tr>
<!--
$variables['sched_igb'] = $tkireg->sched_igb;
$variables['sched_news'] = $tkireg->sched_news;
$variables['sched_planets'] = $tkireg->sched_planets;
$variables['sched_ports'] = $tkireg->sched_ports;

line("News will be generated every", "{$sched_news} minutes", "right");
line("Planets will generate production every", "{$sched_planets} minutes", "right");
$use_new_sched_planet = true; // We merged this change in, so all new versions use this
line(" -> Using new Planet Update Code", ($use_new_sched_planet?"<span style='color:#0f0;'>Yes</span>":"<span style='color:#ff0;'>No</span>"), "right");
line(" -> Limit captured planets Max Credits to ". number_format($max_credits_without_base, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']), ($sched_planet_valid_credit$
line("Ports will regenerate x {$port_regenrate} every", "{$sched_ports} minutes", "right");
line("Ships will be towed from fed sectors every", "{$sched_turns} minutes", "right");
line("Rankings will be generated every", "{$sched_ranking} minutes", "right");
line("Sector Defences will degrade every", "{$sched_degrade} minutes", "right");
line("The planetary apocalypse will occur every&nbsp;", "{$sched_apocalypse} minutes", "right");
-->
</tbody></table>
<br>
<br>
<h1>{$langvars['l_set_game_sched_settings']}</h1>
<table style="width:800px; font-size:14px; color:#fff; border:#fff 1px solid;" border="0" cellpadding="2" cellspacing="0"><tbody><tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_sched_ticks']}</td><td style="text-align:right;">{$variables['sched_ticks']} {$langvars['l_set_minutes']}&nbsp;</td></tr>
<tr bgcolor="#400040"><td>&nbsp;{$langvars['l_set_sched_turns']}</td><td style="text-align:right;">{$variables['sched_turns']}&nbsp;{$langvars['l_set_minutes']}&nbsp;</td></tr>
<tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_sched_defense']}</td><td style="text-align:right;">{$variables['sched_turns']}&nbsp;{$langvars['l_set_minutes']}&nbsp;</td></tr>
<tr bgcolor="#400040"><td>&nbsp;{$langvars['l_set_sched_xenobe']}</td><td style="text-align:right;">{$variables['sched_turns']}&nbsp;{$langvars['l_set_minutes']}&nbsp;</td></tr>
<tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_sched_igb_interest']}&nbsp;</td><td style="text-align:right;">{$variables['sched_igb']}&nbsp;{$langvars['l_set_minutes']}&nbsp;</td></tr>
<tr bgcolor="#400040"><td>&nbsp;{$langvars['l_set_sched_news']}</td><td style="text-align:right;">{$variables['sched_news']}&nbsp;{$langvars['l_set_minutes']}&nbsp;</td></tr>
<tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_sched_planet_prod']}</td><td style="text-align:right;">{$variables['sched_planets']}&nbsp;{$langvars['l_set_minutes']}&nbsp;</td></tr>
<tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_sched_limit_cap']} {$variables['max_credits_without_base']}</td><td style="text-align:right;"><span style="color:#0f0;">Yes</span>&nbsp;</td></tr>
<tr bgcolor="#400040"><td>&nbsp;{$langvars['l_set_sched_port_regen']|replace:"[port_regenrate]":$variables['port_regenrate']}</td><td style="text-align:right;">{$variables['sched_ports']}&nbsp;{$langvars['l_set_minutes']}&nbsp;</td></tr>
<tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_sched_fed_tow']}</td><td style="text-align:right;">{$variables['sched_turns']}&nbsp;{$langvars['l_set_minutes']}&nbsp;</td></tr>
<tr bgcolor="#400040"><td>&nbsp;{$langvars['l_set_sched_ranking']}</td><td style="text-align:right;">{$variables['sched_ranking']}&nbsp;{$langvars['l_set_minutes']}&nbsp;</td></tr>
<tr bgcolor="#300030"><td>&nbsp;{$langvars['l_set_sched_def_degrade']}</td><td style="text-align:right;">{$variables['sched_degrade']}&nbsp;{$langvars['l_set_minutes']}&nbsp;</td></tr>
<tr bgcolor="#400040"><td>&nbsp;{$langvars['l_set_sched_apoc']}&nbsp;</td><td style="text-align:right;">{$variables['sched_apocalypse']}&nbsp;{$langvars['l_set_minutes']}&nbsp;</td></tr>
</tbody></table><br>
<br>
Click <a href="index.php">here</a> to return to the login screen.<p></p>
{/block}
