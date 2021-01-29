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

{if !isset($body_class)}
{$body_class = "tki"}
{/if}
  <body class="{$body_class}">
<div class="wrapper">

<header><div class="index-header"><img height="150" width="994" style="width:100%" class="index" src="templates/{$template}/images/header1.png" alt="{$langvars['l_tki']}"></div></header>

<div class="index-header-text">{$langvars['l_tki']}</div>
<br>
<h2 style="display:none">{$langvars['l_navigation']}</h2>
<div class="navigation" role="navigation">
<ul class="navigation">
<li class="navigation"><a href="new.php{$link}"><span class="button blue"><span class="shine"></span>{$langvars['l_new_player']}</span></a></li>
<li class="navigation"><a href="mailto:{$admin_mail}"><span class="button gray"><span class="shine"></span>{$langvars['l_login_emailus']}</span></a></li>
<li class="navigation"><a href="ranking.php{$link}"><span class="button purple"><span class="shine"></span>{$langvars['l_rankings']}</span></a></li>
<li class="navigation"><a href="faq.php{$link}"><span class="button brown"><span class="shine"></span>{$langvars['l_faq']}</span></a></li>
<li class="navigation"><a href="settings.php{$link}"><span class="button red"><span class="shine"></span>{$langvars['l_settings']}</span></a></li>
<li class="navigation"><a href="//{$link_forums}"><span class="button orange"><span class="shine"></span>{$langvars['l_forums']}</span></a></li>
</ul></div><br style="clear:both">
<div><p></p></div>
<div class="index-welcome">
<h1 class="index-h1">{$langvars['l_welcome_tki']}</h1>
<p>{$langvars['l_tki_description']}<br></p>
<form accept-charset="utf-8" action="login2.php{$link}" method="post">
<dl class="twocolumn-form">
<dt><label for="email">{$langvars['l_login_email']}</label></dt>
<dd><input type="email" id="email" name="email" size="20" maxlength="40" placeholder="someone@example.com"></dd>
<dt><label for="pass">{$langvars['l_login_pw']}:</label></dt>
<dd><input type="password" id="pass" name="pass" size="20" maxlength="20"></dd>
</dl>
<br style="clear:both">
<div style="text-align:center">{$langvars['l_login_forgotpw']}</div><br>
<div style="text-align:center">
<span class="button green"><a class="nocolor" href="#" onclick="document.forms[0].submit();return false;"><span class="shine"></span>{$langvars['l_login_title']}</a></span>
<div style="width: 0; height: 0; overflow: hidden;"><input type="submit" value="{$langvars['l_login_title']}"></div>
</div>
</form>
<br>
<p class="cookie-warning">{$langvars['l_cookie_warning']}</p></div>
<br>
