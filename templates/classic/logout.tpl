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

    File: logout.tpl
*}

{extends file="layout.tpl"}
{block name=title}{$langvars['l_logout']}{/block}

{block name=body}

<h1>{$langvars['l_logout']}</h1>

{if ($variables['session_username'] != '')}
    {$langvars['l_logout_score']} {$variables['current_score']}.<br><br>
    {$variables['l_logout_text_replaced']}
{else}
    {$variables['linkback']['fulltext']|replace:"[here]":"<a href='{$variables['linkback']['link']}'>{$langvars['l_here']}</a>"}
{/if}
{/block}
