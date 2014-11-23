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

    File: create_universe/80.tpl
*}

{if !isset($variables['body_class'])}
{$variables['body_class'] = "tki"}
{/if}
  <body class="{$variables['body_class']}">
<div class="wrapper">

<center>
<table border="0" cellpadding="1" width="700" cellspacing="1" bgcolor="#000000">
    <tr>
      <th width="700" colspan="2" bgcolor="#9999cc" align="left"><h1 style="color:#000; height: 0.8em; font-size: 0.8em;font-weight: normal;">{$langvars['l_cu_config_scheduler_title']}</h1></th>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_update_ticks']|replace:'[sched]':$variables['update_ticks_results'].sched}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000"><p align='center'><font size="1" color="Blue">{$langvars['l_cu_already_set']}</font></p></font></td>
    </tr>
    {if $variables['update_turns_results']['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_turns_occur']|replace:'[sched]':$variables['update_turns_results'].sched} - {$langvars['l_cu_completed_in']|replace:'[time]':$variables['update_turns_results'].elapsed}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_inserted']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['update_turns_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_turns_occur']|replace:'[sched]':$variables['update_turns_results'].sched}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {if $variables['update_xenobe_results']['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_xenobes_minutes']|replace:'[sched]':$variables['update_xenobe_results'].sched} - {$langvars['l_cu_completed_in']|replace:'[time]':$variables['update_xenobe_results'].elapsed}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_inserted']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['update_xenobe_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_xenobes_minutes']|replace:'[sched]':$variables['update_xenobe_results'].sched}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {if $variables['update_ibank_results']['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_ibank_interest']|replace:'[sched]':$variables['update_ibank_results'].sched} - {$langvars['l_cu_completed_in']|replace:'[time]':$variables['update_ibank_results'].elapsed}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_inserted']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['update_ibank_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_ibank_interest']|replace:'[sched]':$variables['update_ibank_results'].sched}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {if $variables['update_news_results']['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_news_gen']|replace:'[sched]':$variables['update_news_results'].sched} - {$langvars['l_cu_completed_in']|replace:'[time]':$variables['update_news_results'].elapsed}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_inserted']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['update_news_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_news_gen']|replace:'[sched]':$variables['update_news_results'].sched}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {if $variables['update_planets_results']['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_planets_minutes']|replace:'[sched]':$variables['update_planets_results'].sched} - {$langvars['l_cu_completed_in']|replace:'[time]':$variables['update_planets_results'].elapsed}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_inserted']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['update_planets_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_planets_minutes']|replace:'[sched]':$variables['update_planets_results'].sched}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {if $variables['update_ports_results']['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_port_regen']|replace:'[sched]':$variables['update_ports_results'].sched} - {$langvars['l_cu_completed_in']|replace:'[time]':$variables['update_ports_results'].elapsed}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_inserted']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['update_ports_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_port_regen']|replace:'[sched]':$variables['update_ports_results'].sched}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {if $variables['update_tow_results']['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_tow_sched']|replace:'[sched]':$variables['update_tow_results'].sched} - {$langvars['l_cu_completed_in']|replace:'[time]':$variables['update_tow_results'].elapsed}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_inserted']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['update_tow_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_tow_sched']|replace:'[sched]':$variables['update_tow_results'].sched}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {if $variables['update_ranking_results']['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_ranking_sched']|replace:'[sched]':$variables['update_ranking_results'].sched} - {$langvars['l_cu_completed_in']|replace:'[time]':$variables['update_ranking_results'].elapsed}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_inserted']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['update_ranking_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_ranking_sched']|replace:'[sched]':$variables['update_ranking_results'].sched}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {if $variables['update_degrade_results']['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_sector_degrade']|replace:'[sched]':$variables['update_degrade_results'].sched}  - {$langvars['l_cu_completed_in']|replace:'[time]':$variables['update_degrade_results'].elapsed}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_inserted']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['update_degrade_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_ranking_sched']|replace:'[sched]':$variables['update_degrade_results'].sched}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {if $variables['update_apoc_results']['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_apoc_sched']|replace:'[sched]':$variables['update_apoc_results'].sched} - {$langvars['l_cu_completed_in']|replace:'[time]':$variables['update_apoc_results'].elapsed}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_inserted']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['update_apoc_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_apoc_sched']|replace:'[sched]':$variables['update_apoc_results'].sched}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {if $variables['update_gov_results']['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_governor_sched']|replace:'[sched]':$variables['update_gov_results'].sched} - {$langvars['l_cu_completed_in']|replace:'[time]':$variables['update_gov_results'].elapsed}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_inserted']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['update_gov_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_governor_sched']|replace:'[sched]':$variables['update_gov_results'].sched}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {if $variables['first_news_results']['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_insert_news']} - {$langvars['l_cu_completed_in']|replace:'[time]':$variables['first_news_results'].elapsed}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_inserted']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['first_news_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_insert_news']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" align="left"><font color="#000000" size="1"></font></td>
    </tr>
    <tr>
      <th width="700" colspan="2" bgcolor="#9999cc" align="left"><h1 style="color:#000; height: 0.8em; font-size: 0.8em;font-weight: normal;">{$langvars['l_cu_account_info']} {$variables['admin_name']}</h1></th>
    </tr>
    {if $variables['ibank_results']['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_ibank_info']} {$variables['admin_name']}  - {$langvars['l_cu_completed_in']|replace:'[time]':$variables['ibank_results'].elapsed}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_inserted']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['ibank_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_ibank_info']} {$variables['admin_name']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    <tr>
      <td width="700" colspan="2" bgcolor="#C0C0C0" align="left"><font color="#000000" size="1">{$langvars['l_cu_admin_login']}<br>{$langvars['l_cu_admin_username']} {$variables['admin_mail']}<br>{$langvars['l_cu_admin_password']} {$variables['admin_pass']}</font></td>
    </tr>
    {if $variables['admin_account_results']['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_insert_shipinfo_admin']} {$variables['admin_name']} - {$langvars['l_cu_completed_in']|replace:'[time]':$variables['admin_account_results'].elapsed}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_inserted']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['admin_account_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_insert_shipinfo_admin']} {$variables['admin_name']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}

    {foreach from=$variables['admin_preset_results'] key=count item=result}
        {if $result['result'] === true}
            <tr title="{$langvars['l_cu_no_errors_found']}">
              <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_insert_presetinfo_admin']|replace:'[num]':$count} {$variables['admin_name']} - {$langvars['l_cu_completed_in']|replace:'[time]':$result.elapsed}</font></td>
              <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_inserted']}</font></td>
            </tr>
        {else}
            <tr title="{$result['result']}">
              <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_insert_shipinfo_admin']} {$variables['admin_name']}</font></td>
              <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
            </tr>
        {/if}
    {/foreach}

    {if $variables['admin_zone_results']['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_insert_zoneinfo_admin']} {$variables['admin_name']} - {$langvars['l_cu_completed_in']|replace:'[time]':$variables['admin_zone_results'].elapsed}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_inserted']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['admin_zone_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_insert_zoneinfo_admin']} {$variables['admin_name']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}

    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" align="left"><font color="#000000" size="1"></font></td>
    </tr>
    <tr>
      <td width="700" colspan="2" bgcolor="#C0C0C0" align="left"><font color="#000000" size="1"><p align='center'>{$langvars['l_cu_congrats_success']}<br>{$langvars['l_cu_continue_to_login']}<br><input type=button value='{$langvars['l_cu_continue']}' onClick="javascript: document.location.href='index.php'"></p></font></td>
    </tr>
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" align="left"><font color="#000000" size="1"> </font></td>
    </tr>
  </table>
  </center>
</div><p><br>
