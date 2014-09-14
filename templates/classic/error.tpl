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

    File: error.tpl
*}

{extends file="layout.tpl"}
{block name=title}{$langvars['l_error_title']}{/block}

{block name=body}
<div class="error_text">
{block name=body_title}<h1>{$langvars['l_error_heading']}</h1>{/block}
<p class="error_text">{$langvars['l_error_ohdear']}<br><br>
{$langvars['l_error_explain1']|replace:"[forums]":"<a href='//{$variables['linkforums']['link']}'>{$langvars['l_error_forums']}</a>"}<br><br>
{$langvars['l_error_explain2']}<br>
{$langvars['l_error_explain3']}<br><br>

<div class="error_location">
{if isset($variables['error_type']) && $variables['error_type'] == 'direct'}
{$langvars['l_error_explain4']}<br>
{$langvars['l_error_type']}<br>
{elseif isset($variables['error_type']) && $variables['error_type'] == 'standard'}
{$langvars['l_error_explain4']}<br>
{$langvars['l_error_explain5']}<br>
{$langvars['l_error_explain6']}<br>
{$langvars['l_error_explain7']}<br>
{$langvars['l_error_explain8']}<br>
{else}
{$langvars['l_error_type']}<br>
{/if}
{$langvars['l_error_explain9']}<br>
</div>

<div class="error_content">
{if isset($variables['error_type']) && $variables['error_type'] == 'direct'}
{$variables['error_page']}<br>
{$langvars['l_error_direct']}<br>
{elseif isset($variables['error_type']) && $variables['error_type'] == 'standard'}
{$variables['error_page']}<br>
{$variables['error_line']}<br>
{$variables['post']}<br>
{$variables['get']}<br>
{$variables['session']}<br>
{else}
{$langvars['l_error_server']}<br>
{/if}
{$variables['request_uri']}<br>
</div>
</p>

<p class="error_footer"><br>
{$langvars['l_error_escape']|replace:"[escape]":"<a href='{$variables['linkback']['link']}'>{$langvars['l_error_escape_text']}</a>"}
</p><br>
</div>
{/block}
