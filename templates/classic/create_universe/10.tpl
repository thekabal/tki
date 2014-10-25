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

    File: create_universe/10.tpl
*}

{extends file="layout.tpl"}
{block name=title}{$langvars['l_cu_step_title']|replace:'[current]':$variables['current_step']|replace:'[total]':$variables['steps']} - {$langvars['l_cu_title']}{/block}
{block name=body}
<form accept-charset='utf-8' name='create_universe' action='create_universe.php' method='post'><div align="center">
<center>
<table border="0" cellpadding="1" width="700" cellspacing="1" bgcolor="#000000">
    <tr>
      <th width="700" colspan="2" bgcolor="#9999cc" align="left"><h1 style="color:#000; height: 0.8em; font-size: 0.8em;font-weight: normal;">{$langvars['l_cu_base_n_planets']}</h1></th>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_percent_special']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000"><input type=text name=special size=10 maxlength=10 value=1></font></td>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_percent_ore']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000"><input type=text name=ore size=10 maxlength=10 value=15></font></td>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_percent_organics']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000"><input type=text name=organics size=10 maxlength=10 value=10></font></td>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_percent_goods']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000"><input type=text name=goods size=10 maxlength=10 value=15></font></td>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_percent_energy']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000"><input type=text name=energy size=10 maxlength=10 value=10></font></td>
    </tr>
    <tr>
      <td width="700" colspan="2" bgcolor="#C0C0C0" align="left"><font color="#000000" size="1">{$langvars['l_cu_percent_empty']}</font></td>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_init_comm_sell']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000"><input type=text name=initscommod size=10 maxlength=10 value=100.00></font></td>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_init_comm_buy']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000"><input type=text name=initbcommod size=10 maxlength=10 value=100.00></font></td>
    </tr>
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" align="left"><font color="#000000" size="1"> </font></td>
    </tr>
    <tr>
      <th width="700" colspan="2" bgcolor="#9999cc" align="left"><h2 style="color:#000; height: 0.8em; font-size: 0.8em;font-weight: normal;">{$langvars['l_cu_sector_n_link']}</h2></th>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_sector_total']} (<strong>[{$langvars['l_cu_override_config']}]</strong>)</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000"><input type=text name=sektors size=10 maxlength=10 value={$variables['max_sectors']}></font></td>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_fed_sectors']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000"><input type=text name=fedsecs size=10 maxlength=10 value=5></font></td>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_num_loops']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000"><input type=text name=loops size=10 maxlength=10 value=2></font></td>
    </tr>
    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_percent_unowned']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000"><input type=text name=planets size=10 maxlength=10 value=10></font></td>
    </tr>

    <tr>
      <td width="600" bgcolor="#ccccff"><font size="1" color="#000000">{$langvars['l_cu_autorun']}</font></td>
      <td width="100" bgcolor="#C0C0C0"><font size="1" color="#000000"><input type="checkbox" name="autorun" value="on"></font></td>
    </tr>

    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" align="left"><font color="#000000" size="1"> </font></td>
    </tr>
    <tr>
      <td width="700" colspan="2" bgcolor="#C0C0C0" align="left"><font color="#000000" size="1"><p align='center'><input autofocus=autofocus type=submit value={$langvars['l_submit']}><input type=reset value={$langvars['l_reset']}></p></font></td>
    </tr>
    <tr>
      <td width="100%" colspan="2" bgcolor="#9999cc" align="left"><font color="#000000" size="1"> </font></td>
    </tr>
    <input type=hidden name=step value={$variables['next_step']}>
    <input type=hidden name=swordfish value={$variables['swordfish']}>
  </table>
  </center>
</div><p>
</form>
{/block}
