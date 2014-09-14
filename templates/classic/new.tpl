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

    File: index.tpl
*}

{extends file="layout.tpl"}
{block name=title}{$langvars['l_welcome_bnt']}{/block}

{block name=body}
<div class="index-header"><img height="150" width="994" style="width:100%" class="index" src="templates/{$variables['template']}/images/header1.png" alt="{$langvars['l_bnt']}"></div>

<div class="index-header-text">{$langvars['l_bnt']}</div>
<br>
<div class="index-welcome">
<h1 style='text-align:center'>{$langvars['l_new_title']}</h1>
<form accept-charset="utf-8" action="new2.php{$variables['link']}" method="post">
    <dl class='twocolumn-form'>
        <dt style='padding:3px'><label for='username'>{$langvars['l_login_email']}:</label></dt>
        <dd style='padding:3px'><input type='email' id='username' name='username' size='20' maxlength='40' value='' placeholder='someone@example.com' style='width:200px'></dd>
        <dt style='padding:3px'><label for='shipname'>{$langvars['l_new_shipname']}:</label></dt>
        <dd style='padding:3px'><input type='text' id='shipname' name='shipname' size='20' maxlength='20' value='' style='width:200px'></dd>
        <dt style='padding:3px'><label for='character'>{$langvars['l_new_pname']}:</label></dt>
        <dd style='padding:3px'><input type='text' id='character' name='character' size='20' maxlength='20' value='' style='width:200px'></dd>
        <dt style='padding:3px'><label for='password'>{$langvars['l_login_pw']}:</label></dt>
        <dd style='padding:3px'><input type='password' id='password' name='password' size='20' maxlength='20' value='' style='width:200px'></dd>
    </dl>
<br style="clear:both">
<div style="text-align:center">
<span class="button green"><a class="nocolor" href="#" onclick="document.forms[0].submit();return false;"><span class="shine"></span>{$langvars['l_submit']}</a></span>
<span class="button red"><a class="nocolor" href="#" onclick="document.forms[0].reset();return false;"><span class="shine"></span>{$langvars['l_reset']}</a></span>
<div style="width: 0; height: 0; overflow: hidden;"><input type="submit" value="{$langvars['l_submit']}"></div> 
</div>
</form>
<br>
        {$langvars['l_new_info']}<br></div>
<br>
{/block}
