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

    File: create_universe/0.tpl
*}

{if !isset($variables['body_class'])}
{$variables['body_class'] = "tki"}
{/if}
  <body class="{$variables['body_class']}">
<div class="wrapper">

{if $variables['goodpass'] === true}
{$langvars['l_cu_step_title']|replace:'[current]':$variables['current_step']|replace:'[total]':$variables['steps']} - {$langvars['l_cu_welcome']}
{else}
{$langvars['l_cu_welcome']} - {$langvars['l_cu_badpass_title']}
{/if}

<form accept-charset='utf-8' name='create_universe' action='create_universe.php' method='post'><div style="text-align:center">
<table style="border-spacing:1px; width:700px; background-color:#000; border:0px; margin-left:auto; margin-right:auto">
    <tr>
      <th colspan="2" style="text-align:left; width:700px; background-color:#9999cc; color:#000; font-size:0.8em; font-weight:normal">{$langvars['l_cu_welcome']}</th>
    </tr>
    <tr>
      <td colspan="2" style="text-align:left; width:700px; background-color:#C0C0C0; color:#000; height:0.8em; font-size: 0.8em">{$langvars['l_cu_allow_create']}</td>
    </tr>
    <tr>
        <td style="text-align:left; background-color:#ccccff; width:600px; font-size: 0.8em; color:#000">{$langvars['l_cu_pw_to_continue']}</td>
        <td style="background-color:#C0C0C0; width:100px; font-size: 0.8em; color:#000"><input autofocus type=password name=swordfish size=20></td>
    </tr>
    <tr>
    {if $variables['goodpass'] === true}
      <td style="background-color:#9999cc; width:100%; text-align:left; font-size: 0.8em" colspan="2"></td>
    {else}
      <td style="background-color:#9999cc; width:100%; text-align:left; font-size:0.8em; color:darkred" colspan="2">{$langvars['l_cu_bad_password']}</td>
    {/if}
    </tr>
    <tr>
      <td colspan="2" style="text-align:center; width:700px; background-color:#C0C0C0; color:#000; font-size:0.8em; font-weight:normal;"><input type=submit value={$langvars['l_submit']}><input type=reset value={$langvars['l_reset']}><input type=hidden name=step value={$variables['next_step']}></td>
    </tr>
    <tr>
      <td colspan="2" style="background-color:#9999cc; width:100%; text-align:left; font-size:0.8em"></td>
    </tr>
</table>
</div><p>
</form>
