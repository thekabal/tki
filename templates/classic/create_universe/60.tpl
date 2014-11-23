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

    File: create_universe/60.tpl
*}

{if !isset($variables['body_class'])}
{$variables['body_class'] = "tki"}
{/if}
  <body class="{$variables['body_class']}">
<div class="wrapper">

<form accept-charset='utf-8' name='create_universe' action='create_universe.php' method='post'><div align="center">
<center>
<table border="0" cellpadding="1" width="700" cellspacing="1" bgcolor="#000000">
    <tr>
      <th width="700" colspan="2" bgcolor="#9999cc" align="left"><h1 style="color:#000; height: 0.8em; font-size: 0.8em;font-weight: normal;">{$langvars['l_cu_setup_sectors_step']}</h1></th>
    </tr>
    {if $variables['create_sol_results']['result'] === true}
    <tr title='{$langvars['l_cu_no_errors_found']}'>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_create_sol']|replace:'[elapsed]':$variables['create_sol_results'].time} </font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_created']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['create_sol_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_create_sol']|replace:'[elapsed]':$variables['create_sol_results'].time} </font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {if $variables['create_ac_results']['result'] === true}
    <tr title='{$langvars['l_cu_no_errors_found']}'>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_create_ac']|replace:'[elapsed]':$variables['create_ac_results'].time}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_created']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['create_ac_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_create_ac']|replace:'[elapsed]':$variables['create_ac_results'].time}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" height="1"></td>
    </tr>
    {for $i=1 to $variables['insert_sector_loops']}
        {if $variables['insert_sector_results'][$i]['result'] === true}
        <tr title='{$langvars['l_cu_no_errors_found']}'>
            <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_insert_loop_sector_block']|replace:'[loop]':$variables['insert_sector_results'][$i]['loop']|replace:'[loops]':$variables['insert_sector_results'][$i]['loops']|replace:'[start]':$variables['insert_sector_results'][$i]['start']|replace:'[finish]':$variables['insert_sector_results'][$i]['finish']|replace:'[elapsed]':$variables['insert_sector_results'][$i]['elapsed']}</font></td>
            <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_inserted']}</font></td>
        </tr>
        {else}
        <tr title="{$variables['insert_sector_results'][$i]['result']}">
            <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_insert_loop_sector_block']|replace:'[loop]':$variables['insert_sector_results'][$i]['loop']|replace:'[loops]':$variables['insert_sector_results'][$i]['loops']|replace:'[start]':$variables['insert_sector_results'][$i]['start']|replace:'[finish]':$variables['insert_sector_results'][$i]['finish']|replace:'[elapsed]':$variables['insert_sector_results'][$i]['elapsed']}</font></td>
            <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
        </tr>
        {/if}
    {/for}
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" height="1"></td>
    </tr>
    {if $variables['create_unchartered_results']['result'] === true}
    <tr title='{$langvars['l_cu_no_errors_found']}'>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_unchartered']|replace:'[elapsed]':$variables['create_unchartered_results'].time}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_set']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['create_unchartered_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_unchartered']|replace:'[elapsed]':$variables['create_unchartered_results'].time}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {if $variables['create_fedspace_results']['result'] === true}
    <tr title='{$langvars['l_cu_no_errors_found']}'>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_fedspace']|replace:'[elapsed]':$variables['create_fedspace_results'].time}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_set']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['create_fedspace_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_fedspace']|replace:'[elapsed]':$variables['create_fedspace_results'].time}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {if $variables['create_free_results']['result'] === true}
    <tr title='{$langvars['l_cu_no_errors_found']}'>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_free']|replace:'[elapsed]':$variables['create_free_results'].time}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_set']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['create_free_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_free']|replace:'[elapsed]':$variables['create_free_results'].time}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {if $variables['create_warzone_results']['result'] === true}
    <tr title='{$langvars['l_cu_no_errors_found']}'>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_warzone']|replace:'[elapsed]':$variables['create_warzone_results'].time}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_set']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['create_warzone_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_warzone']|replace:'[elapsed]':$variables['create_warzone_results'].time}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {if $variables['create_fed_sectors_results']['result'] === true}
    <tr title='{$langvars['l_cu_no_errors_found']}'>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_fed_sectors']|replace:'[elapsed]':$variables['create_fed_sectors_results'].time|replace:'[fedsecs]':$variables['fedsecs']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_set']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['create_fed_sectors_results']['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_fed_sectors']|replace:'[elapsed]':$variables['create_fed_sectors_results'].time|replace:'[fedsecs]':$variables['fedsecs']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" height="1"></td>
    </tr>
    {for $j=1 to $variables['insert_special_loops']}
    {if $variables['insert_special_ports'][$j]['result'] === true}
    <tr title='{$langvars['l_cu_no_errors_found']}'>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_special_ports']|replace:'[loop]':$variables['insert_special_ports'][$j]['loop']|replace:'[loops]':$variables['insert_special_ports'][$j]['loops']|replace:'[start]':$variables['insert_special_ports'][$j]['start']|replace:'[finish]':$variables['insert_special_ports'][$j]['finish']|replace:'[elapsed]':$variables['insert_special_ports'][$j]['elapsed']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_selected']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['insert_special_ports'][$j]['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_special_ports']|replace:'[loop]':$variables['insert_special_ports'][$j]['loop']|replace:'[loops]':$variables['insert_special_ports'][$j]['loops']|replace:'[start]':$variables['insert_special_ports'][$j]['start']|replace:'[finish]':$variables['insert_special_ports'][$j]['finish']|replace:'[elapsed]':$variables['insert_special_ports'][$j]['elapsed']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {/for}
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" height="1"></td>
    </tr>
    {for $k=1 to $variables['insert_ore_loops']}
    {if $variables['insert_ore_ports'][$k]['result'] === true}
    <tr title='{$langvars['l_cu_no_errors_found']}'>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_ore_ports']|replace:'[loop]':$variables['insert_ore_ports'][$k]['loop']|replace:'[loops]':$variables['insert_ore_ports'][$k]['loops']|replace:'[start]':$variables['insert_ore_ports'][$k]['start']|replace:'[finish]':$variables['insert_ore_ports'][$k]['finish']|replace:'[elapsed]':$variables['insert_ore_ports'][$k]['elapsed']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_selected']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['insert_ore_ports'][$k]['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_ore_ports']|replace:'[loop]':$variables['insert_ore_ports'][$k]['loop']|replace:'[loops]':$variables['insert_ore_ports'][$k]['loops']|replace:'[start]':$variables['insert_ore_ports'][$k]['start']|replace:'[finish]':$variables['insert_ore_ports'][$k]['finish']|replace:'[elapsed]':$variables['insert_ore_ports'][$k]['elapsed']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {/for}
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" height="1"></td>
    </tr>
    {for $l=1 to $variables['insert_organics_loops']}
    {if $variables['insert_organics_ports'][$l]['result'] === true}
    <tr title='{$langvars['l_cu_no_errors_found']}'>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_organics_ports']|replace:'[loop]':$variables['insert_organics_ports'][$l]['loop']|replace:'[loops]':$variables['insert_organics_ports'][$l]['loops']|replace:'[start]':$variables['insert_organics_ports'][$l]['start']|replace:'[finish]':$variables['insert_organics_ports'][$l]['finish']|replace:'[elapsed]':$variables['insert_organics_ports'][$l]['elapsed']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_selected']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['insert_organics_ports'][$l]['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_organics_ports']|replace:'[loop]':$variables['insert_organics_ports'][$l]['loop']|replace:'[loops]':$variables['insert_organics_ports'][$l]['loops']|replace:'[start]':$variables['insert_organics_ports'][$l]['start']|replace:'[finish]':$variables['insert_organics_ports'][$l]['finish']|replace:'[elapsed]':$variables['insert_organics_ports'][$l]['elapsed']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {/for}
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" height="1"></td>
    </tr>
    {for $m=1 to $variables['insert_goods_loops']}
    {if $variables['insert_goods_ports'][$m]['result'] === true}
    <tr title='{$langvars['l_cu_no_errors_found']}'>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_goods_ports']|replace:'[loop]':$variables['insert_goods_ports'][$m]['loop']|replace:'[loops]':$variables['insert_goods_ports'][$m]['loops']|replace:'[start]':$variables['insert_goods_ports'][$m]['start']|replace:'[finish]':$variables['insert_goods_ports'][$m]['finish']|replace:'[elapsed]':$variables['insert_goods_ports'][$m]['elapsed']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_selected']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['insert_goods_ports'][$m]['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_goods_ports']|replace:'[loop]':$variables['insert_goods_ports'][$m]['loop']|replace:'[loops]':$variables['insert_goods_ports'][$m]['loops']|replace:'[start]':$variables['insert_goods_ports'][$m]['start']|replace:'[finish]':$variables['insert_goods_ports'][$m]['finish']|replace:'[elapsed]':$variables['insert_goods_ports'][$m]['elapsed']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {/for}
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" height="1"></td>
    </tr>
    {for $n=1 to $variables['insert_energy_loops']}
    {if $variables['insert_energy_ports'][$n]['result'] === true}
    <tr title='{$langvars['l_cu_no_errors_found']}'>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_energy_ports']|replace:'[loop]':$variables['insert_energy_ports'][$n]['loop']|replace:'[loops]':$variables['insert_energy_ports'][$n]['loops']|replace:'[start]':$variables['insert_energy_ports'][$n]['start']|replace:'[finish]':$variables['insert_energy_ports'][$n]['finish']|replace:'[elapsed]':$variables['insert_energy_ports'][$n]['elapsed']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="Blue">{$langvars['l_cu_selected']}</font></td>
    </tr>
    {else}
    <tr title="{$variables['insert_energy_ports'][$n]['result']}">
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_setup_energy_ports']|replace:'[loop]':$variables['insert_energy_ports'][$n]['loop']|replace:'[loops]':$variables['insert_energy_ports'][$n]['loops']|replace:'[start]':$variables['insert_energy_ports'][$n]['start']|replace:'[finish]':$variables['insert_energy_ports'][$n]['finish']|replace:'[elapsed]':$variables['insert_energy_ports'][$n]['elapsed']}</font></td>
      <td width="100" align="center" bgcolor="#C0C0C0"><font size="1" color="red">{$langvars['l_error']}</font></td>
    </tr>
    {/if}
    {/for}
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" align="left"><font color="#000000" size="1"> </font></td>
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
    <input type=hidden name=swordfish value={$variables['swordfish']}>
    <input type="hidden" name="autorun" value="{$variables['autorun']}">
  </table>
  </center>
</div><p>
</form>

{if $variables['autorun']}
<script type="text/javascript" defer="defer" src="templates/classic/javascript/autorun.js.php"></script>
{/if}
