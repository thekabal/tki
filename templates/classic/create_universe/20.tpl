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

    File: create_universe/20.tpl
*}

{extends file="layout.tpl"}
{block name=title}{$langvars['l_cu_step_title']|replace:'[current]':$variables['current_step']|replace:'[total]':$variables['steps']} - {$langvars['l_cu_title']}{/block}
{block name=body}
<form accept-charset='utf-8' name='create_universe' action='create_universe.php' method='post'><div align="center">
<center>
<table border="0" cellpadding="1" width="700" cellspacing="1" bgcolor="#000000">
    <tr>
      <th width="700" colspan="2" bgcolor="#9999cc" align="left"><h1 style="color:#000; height: 0.8em; font-size: 0.8em;font-weight: normal;">{$langvars['l_cu_confirm_settings']|replace:'[sector_max]':$variables['sector_max']}</h1></th>
    </tr>
    {if $variables['fedsecs'] > $variables['sector_max']}
    <tr>
      <td width="700" colspan="2" bgcolor="#C0C0C0" align="left"><font color="#000000" size="1"><font color=red>{$langvars['l_cu_fedsec_smaller']}</font></font></td>
    </tr>
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" align="left"><font color="#000000" size="1"> </font></td>
    </tr>
    {else}
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_special_ports']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000">{$variables['spp']}</font></td>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_ore_ports']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000">{$variables['oep']}</font></td>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_organics_ports']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000">{$variables['ogp']}</font></td>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_goods_ports']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000">{$variables['gop']}</font></td>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_energy_ports']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000">{$variables['enp']}</font></td>
    </tr>
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" height="1"></td>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_init_comm_sell']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000">{$variables['initscommod']} %</font></td>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_init_comm_buy']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000">{$variables['initbcommod']} %</font></td>
    </tr>
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" height="1"></td>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_empty_sectors']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000">{$variables['empty']}</font></td>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_fed_sectors']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000">{$variables['fedsecs']}</font></td>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_loops']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000">{$variables['loops']}</font></td>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_unowned_planets']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000">{$variables['nump']}</font></td>
    </tr>
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" height="1"></td>
    </tr>
    <tr>
      <td width="700" colspan="2" bgcolor="#C0C0C0" align="left"><font color="#000000" size="1"><font color=red>{$langvars['l_cu_table_drop_warn']}</font></font></td>
    </tr>
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" height="1"></td>
    </tr>
    <tr>
      <td width="700" colspan="2" bgcolor="#C0C0C0" align="left"><font color="#000000" size="1"><p align='center'><input autofocus="autofocus" type=submit value='{$langvars['l_confirm']}'></p></font></td>
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
    <input type=hidden name=sector_max value={$variables['sector_max']}>
    <input type=hidden name=swordfish value={$variables['swordfish']}>
    <input type=hidden name=newlang value={$variables['newlang']}>
    <input type="hidden" name="autorun" value="{$variables['autorun']}">
    {/if}
  </table>
  </center>
</div><p>
</form>

{if $variables['autorun']}
<script type="text/javascript" defer="defer" src="templates/classic/javascript/autorun.js.php"></script>
{/if}
{/block}
