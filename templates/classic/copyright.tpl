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

    File: copyright.tpl
*}

{if !isset($variables['body_class'])}
{$variables['body_class'] = "tki"}
{/if}
  <body class="{$variables['body_class']}">
<div class="wrapper">

<div class="index-header"><img height="150" width="994" style="width:100%" class="index" src="templates/{$variables['template']}/images/header1.png" alt="{$langvars['l_tki']}"></div>

<div class="index-header-text">{$langvars['l_tki']}</div>
<br style="clear:both">
<div class="index-welcome">
<h1 class="index-h1">{$langvars['l_welcome_tki']}</h1>
<p>The Kabal Invasion forked from "Blacknova Traders". <br><br>
The lead programmer for TKI (thekabal) was for over a decade actively involved in the development of BNT.<br><br>
TKI contains code from Ron Harwood, the original developer of BNT, along with code from dozens of contributors to BNT, and many contributors to NGS/TKI (The first iteration of TKI).<br><br>
For brevity, we have noted these in the source code files as "Portions also Copyright The Blacknova Development team".<br><br>
The Kabal Invasion development team is deeply appreciative for all of the contributions made to these projects.<br><br>
</p>

<br style="clear:both">
</div>
<br>
