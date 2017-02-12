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

{if !isset($variables['body_class'])}
{$variables['body_class'] = "tki"}
{/if}
  <body class="{$variables['body_class']}">
<div class="wrapper">

<h1>{$langvars['l_set_game_admins']}</h1>
{* FUTURE: Need an if statement here to detect if anyone is an admin or developer *}
<div class="special">{$langvars['l_set_no_admins']}</div>
<br>
<h1>{$langvars['l_set_settings']}</h1>
<table>
  <tbody>
    <tr>
        <td>{$langvars['l_set_game_version']}</td>
        <td style="text-align:right;">{$variables['release_version']}</td>
    </tr>
    <tr><td>{$langvars['l_set_game_name']}</td><td style="text-align:right;">{$variables['game_name']}</td></tr>
    <tr><td>{$langvars['l_set_mines_level']}</td><td style="text-align:right;">{$variables['mine_hullsize']}</td></tr>
    <tr><td>{$langvars['l_set_ewd_level']}</td><td style="text-align:right;">{$variables['max_ewdhullsize']}</td></tr>
    <tr><td>{$langvars['l_set_num_sec']}</td><td style="text-align:right;">{$variables['max_sectors']}</td></tr>
    <tr><td>{$langvars['l_set_max_links']}</td><td style="text-align:right;">{$variables['max_links']}</td></tr>
    <tr><td>{$langvars['l_set_max_fed']}</td><td style="text-align:right;">{$variables['max_fed_hull']}</td></tr>

{if $variables['allow_ibank'] === true}
    <tr><td>{$langvars['l_set_ibank_enabled']}</td><td style="text-align:right;">{$langvars['l_yes']}</td></tr>
{/if}
    <tr><td>{$langvars['l_set_ibank_interest_rate']}</td><td style="text-align:right;">{$variables['ibank_interest']}</td></tr>
    <tr><td>{$langvars['l_set_ibank_loan_rate']}</td><td style="text-align:right;">{$variables['ibank_loaninterest']}</td></tr>
    <tr><td>{$langvars['l_set_base_level']}</td><td style="text-align:right;">{$variables['base_defense']}</td></tr>
    <tr><td>{$langvars['l_set_colonists']}</td><td style="text-align:right;">{$variables['colonist_limit']}</td></tr>
    <tr><td>{$langvars['l_set_max_turns']}</td><td style="text-align:right;">{$variables['max_turns']}</td></tr>
    <tr><td>{$langvars['l_set_max_planets']}</td><td style="text-align:right;">{$variables['max_planets_sector']}</td></tr>
    <tr><td>{$langvars['l_set_max_traderoutes']}</td><td style="text-align:right;">{$variables['max_traderoutes_player']}</td></tr>
    <tr><td>{$langvars['l_set_colonist_prod']}</td><td style="text-align:right;">{$variables['colonist_production_rate']}</td></tr>
    <tr><td>{$langvars['l_set_energy_fits']}</td><td style="text-align:right;">{$variables['energy_per_fighter']}</td></tr>
    <tr><td>{$langvars['l_set_fit_degrade']}</td><td style="text-align:right;">{$variables['defense_degrade_rate']}</td></tr>
    <tr><td>{$langvars['l_set_sector_own']}</td><td style="text-align:right;">{$variables['min_bases_to_own']}</td></tr>
    <tr><td>{$langvars['l_set_planet_interest']}</td><td style="text-align:right;">{$variables['interest_rate']}</td></tr>
    <tr><td>{$langvars['l_set_prod_fit']}</td><td style="text-align:right;">{$variables['cols_needed_fit']}</td></tr>
    <tr><td>{$langvars['l_set_prod_torp']}</td><td style="text-align:right;">{$variables['cols_needed_torp']}</td></tr>
    <tr><td>{$langvars['l_set_prod_ore']}</td><td style="text-align:right;">{$variables['cols_needed_ore']}</td></tr>
    <tr><td>{$langvars['l_set_prod_org']}</td><td style="text-align:right;">{$variables['cols_needed_org']}</td></tr>
    <tr><td>{$langvars['l_set_prod_goods']}</td><td style="text-align:right;">{$variables['cols_needed_goods']}</td></tr>
    <tr><td>{$langvars['l_set_prod_energy']}</td><td style="text-align:right;">{$variables['cols_needed_ene']}</td></tr>
    <tr><td>{$langvars['l_set_prod_credits']}</td><td style="text-align:right;">{$variables['cols_needed_cred']}</td></tr>
  </tbody>
</table>
<br>
<br>
<h1>{$langvars['l_set_game_sched_settings']}</h1>
<table>
  <tbody>
    <tr><td>{$langvars['l_set_sched_ticks']}</td><td style="text-align:right;">{$variables['sched_ticks']} {$langvars['l_set_minutes']}</td></tr>
    <tr><td>{$langvars['l_set_sched_turns']}</td><td style="text-align:right;">{$variables['sched_turns']}&nbsp;{$langvars['l_set_minutes']}</td></tr>
    <tr><td>{$langvars['l_set_sched_defense']}</td><td style="text-align:right;">{$variables['sched_turns']}&nbsp;{$langvars['l_set_minutes']}</td></tr>
    <tr><td>{$langvars['l_set_sched_kabal']}</td><td style="text-align:right;">{$variables['sched_turns']}&nbsp;{$langvars['l_set_minutes']}</td></tr>
    <tr><td>{$langvars['l_set_sched_ibank_interest']}</td><td style="text-align:right;">{$variables['sched_ibank']}&nbsp;{$langvars['l_set_minutes']}</td></tr>
    <tr><td>{$langvars['l_set_sched_news']}</td><td style="text-align:right;">{$variables['sched_news']}&nbsp;{$langvars['l_set_minutes']}</td></tr>
    <tr><td>{$langvars['l_set_sched_planet_prod']}</td><td style="text-align:right;">{$variables['sched_planets']}&nbsp;{$langvars['l_set_minutes']}</td></tr>
    <tr><td>{$langvars['l_set_sched_limit_cap']} {$variables['max_credits_without_base']}</td><td style="text-align:right;"><span style="color:#0f0;">{$langvars['l_yes']}</span></td></tr>
    <tr><td>{$langvars['l_set_sched_port_regen']|replace:"[port_regenrate]":$variables['port_regenrate']}</td><td style="text-align:right;">{$variables['sched_ports']}&nbsp;{$langvars['l_set_minutes']}</td></tr>
    <tr><td>{$langvars['l_set_sched_fed_tow']}</td><td style="text-align:right;">{$variables['sched_turns']}&nbsp;{$langvars['l_set_minutes']}</td></tr>
    <tr><td>{$langvars['l_set_sched_ranking']}</td><td style="text-align:right;">{$variables['sched_ranking']}&nbsp;{$langvars['l_set_minutes']}</td></tr>
    <tr><td>{$langvars['l_set_sched_def_degrade']}</td><td style="text-align:right;">{$variables['sched_degrade']}&nbsp;{$langvars['l_set_minutes']}</td></tr>
    <tr><td>{$langvars['l_set_sched_apoc']}</td><td style="text-align:right;">{$variables['sched_apocalypse']}&nbsp;{$langvars['l_set_minutes']}</td></tr>
  </tbody>
</table>
<br><br>{$variables['linkback']['caption']|replace:"[here]":"<a href='{$variables['linkback']['link']}'>{$langvars['l_here']}</a>"}
