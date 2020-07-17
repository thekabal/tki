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

    File: create_universe/70.tpl
*}

{if !isset($variables['body_class'])}
{$variables['body_class'] = "tki"}
{/if}
  <body class="{$variables['body_class']}">
<div class="wrapper">

{$langvars['l_cu_step_title']|replace:'[current]':$variables['current_step']|replace:'[total]':$variables['steps']} - {$langvars['l_cu_welcome']}

<form accept-charset='utf-8' name='create_universe' action='create_universe.php' method='post'><div align="center">
<center>
<table border="0" cellpadding="1" width="700" cellspacing="1" bgcolor="#000000">
    <tr>
      <th width="700" colspan="2" bgcolor="#9999cc" align="left"><h1 style="color:#000; height: 0.8em; font-size: 0.8em;font-weight: normal;">{$langvars['l_cu_setup_step_seven']}</h1></th>
    </tr>
    {if $variables['setup_unowned_results']['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_unowned_planets']|replace:'[elapsed]':$variables['setup_unowned_results'].elapsed|replace:'[nump]':$variables['setup_unowned_results'].nump}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_selected']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['setup_unowned_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_unowned_planets']|replace:'[elapsed]':$variables['setup_unowned_results'].elapsed|replace:'[nump]':$variables['setup_unowned_results'].nump}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" height="1"></td>
    </tr>
    {for $i=1 to $variables['insert_link_loops']}
    {if $variables['insert_loop_sectors_results'][$i]['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_loop_sectors']|replace:'[loop]':$variables['insert_loop_sectors_result'][$i]['loop']|replace:'[loops]':$variables['insert_loop_sectors_result'][$i]['loops']|replace:'[start]':$variables['insert_loop_sectors_result'][$i]['start']|replace:'[finish]':$variables['insert_loop_sectors_result'][$i]['finish']|replace:'[elapsed]':$variables['insert_loop_sectors_result'][$i]['elapsed']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_created']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['insert_loop_sectors_results'][$i]['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_loop_sectors']|replace:'[loop]':$variables['insert_loop_sectors_result'][$i]['loop']|replace:'[loops]':$variables['insert_loop_sectors_result'][$i]['loops']|replace:'[start]':$variables['insert_loop_sectors_result'][$i]['start']|replace:'[finish]':$variables['insert_loop_sectors_result'][$i]['finish']|replace:'[elapsed]':$variables['insert_loop_sectors_result'][$i]['elapsed']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {/for}
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" height="1"></td>
    </tr>
    {for $j=1 to $variables['insert_oneway_loops']}
    {if $variables['insert_random_oneway_results'][$j]['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_loop_random_oneway']|replace:'[loop]':$variables['insert_random_oneway_result'][$j]['loop']|replace:'[loops]':$variables['insert_random_oneway_result'][$j]['loops']|replace:'[start]':$variables['insert_random_oneway_result'][$j]['start']|replace:'[finish]':$variables['insert_random_oneway_result'][$j]['finish']|replace:'[elapsed]':$variables['insert_random_oneway_result'][$j]['elapsed']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_created']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['insert_random_oneway_results'][$j]['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_loop_random_oneway']|replace:'[loop]':$variables['insert_random_oneway_result'][$j]['loop']|replace:'[loops]':$variables['insert_random_oneway_result'][$j]['loops']|replace:'[start]':$variables['insert_random_oneway_result'][$j]['start']|replace:'[finish]':$variables['insert_random_oneway_result'][$j]['finish']|replace:'[elapsed]':$variables['insert_random_oneway_result'][$j]['elapsed']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {/for}
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" height="1"></td>
    </tr>
    {for $k=1 to $variables['insert_twoway_loops']}
    {if $variables['insert_random_twoway_results'][$k]['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_loop_random_twoway']|replace:'[loop]':$variables['insert_random_twoway_result'][$k]['loop']|replace:'[loops]':$variables['insert_random_twoway_result'][$k]['loops']|replace:'[start]':$variables['insert_random_twoway_result'][$k]['start']|replace:'[finish]':$variables['insert_random_twoway_result'][$k]['finish']|replace:'[elapsed]':$variables['insert_random_twoway_result'][$k]['elapsed']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_created']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['insert_random_twoway_results'][$k]['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_loop_random_twoway']|replace:'[loop]':$variables['insert_random_twoway_result'][$k]['loop']|replace:'[loops]':$variables['insert_random_twoway_result'][$k]['loops']|replace:'[start]':$variables['insert_random_twoway_result'][$k]['start']|replace:'[finish]':$variables['insert_random_twoway_result'][$k]['finish']|replace:'[elapsed]':$variables['insert_random_twoway_result'][$k]['elapsed']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {/for}
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" height="1"></td>
    </tr>
    {if $variables['remove_links_results']['result'] === true}
    <tr title="{$langvars['l_cu_no_errors_found']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_remove_links']|replace:'[elapsed]':$variables['remove_links_results'].elapsed}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_deleted']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['remove_links_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_remove_links']|replace:'[elapsed]':$variables['remove_links_results'].elapsed}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" align="left"><font color="#000000" size="1"></font></td>
    </tr>
    <tr>
      <td width="700" colspan="2" bgcolor="#C0C0C0" align="left"><font color="#000000" size="1"><p align='center'><input autofocus="autofocus" type=submit value='{$langvars['l_cu_continue']}'></p></font></td>
    </tr>
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" align="left"><font color="#000000" size="1"> </font></td>
    </tr>
    <input type=hidden name=step value={$variables['next_step']}>
    <input type=hidden name=spp value={$variables['spp']}>
    <input type=hidden name=oep value={$variables['oep']}>
    <input type=hidden name=ogp value={$variables['ogp']}>
    <input type=hidden name=gop value={$variables['gop']}>
    <input type=hidden name=enp value={$variables['enp']}>
    <input type=hidden name=initscommod value={$variables['initscommod']}>
    <input type=hidden name=initbcommod value={$variables['initbcommod']}>
    <input type=hidden name=nump value={$variables['nump']}>
    <input type=hidden name=fedsecs value={$variables['fedsecs']}>
    <input type=hidden name=loops value={$variables['loops']}>
    <input type=hidden name=max_sectors value={$variables['max_sectors']}>
    <input type=hidden name=swordfish value={$variables['swordfish']}>
    <input type="hidden" name="autorun" value="{$variables['autorun']}">
  </table>
  </center>
</div><p>
</form>

{if $variables['autorun']}
<script type="text/javascript" defer="defer" src="templates/classic/javascript/autorun.js.php"></script>
{/if}
