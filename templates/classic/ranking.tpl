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

    File: ranking.tpl
*}

{if !isset($variables['body_class'])}
{$variables['body_class'] = "tki"}
{/if}
  <body class="{$variables['body_class']}">
<div class="wrapper">

{if $variables['num_players'] > 0}
    <br>
    {$langvars['l_ranks_pnum']}: {$variables['num_players']|number_format}<br>
    {$langvars['l_ranks_dships']}<br>
    <br>
{/if}

{if isset($players)}
    <!-- Display the Rank Table -->
    <table style="border-collapse:separate; border-spacing:0px; border:none;">
      <tr style="padding:2px; background-color:{$variables['color_header']};">
        <td style="padding:2px;"><strong>{$langvars['l_ranks_rank']}</strong></td>
        <td style="padding:2px;"><strong><a href="{$variables['link']}">{$langvars['l_score']}</a></strong></td>
        <td style="padding:2px;"><strong>{$langvars['l_player']}</strong></td>
        <td style="padding:2px; width:16px;"></td>
        <td style="padding:2px;"><strong><a href="{$variables['link']}?sort=turns">{$langvars['l_turns_used']}</a></strong></td>
        <td style="padding:2px;"><strong><a href="{$variables['link']}?sort=login">{$langvars['l_ranks_lastlog']}</a></strong></td>
        <td style="padding:2px;"><strong><a href="{$variables['link']}?sort=good">{$langvars['l_ranks_good']}</a>/<a href="{$variables['link']}?sort=bad">{$langvars['l_ranks_evil']}</a></strong></td>
        <td style="padding:2px;"><strong><a href="{$variables['link']}?sort=team">{$langvars['l_team_team']}</a></strong></td>
        <td style="padding:2px;"><strong><a href="{$variables['link']}?sort=online">Online</a></strong></td>
        <td style="padding:2px;"><strong><a href="{$variables['link']}?sort=efficiency">Eff. Rating.</a></strong></td>
      </tr>

{* Cycle through the player list *}
{foreach $players as $player}
      <!-- Adding Ranking for player {$player['character_name']} -->
      <tr style="padding:2px; background-color:{cycle values="{$variables['color_line1']},{$variables['color_line2']}"};">
        <td style="padding:2px;">{$player['rank']}</td>
        <td style="padding:2px;">{$player['score']|number_format:0:".":","}</td>

{* Check to see if they are an admin, admins do not have an insignia, and they are diplayed in a blue colour *}
{if isset($player['type']) && $player['type'] == "admin"}
        <td style="padding:2px; color:#0099FF;"><span style="font-weight:bold;">{$player['character_name']}</span></td>
{elseif isset($player['type']) && $player['type'] == "npc"}
        <td style="padding:2px; color:#009900;"><span style="font-weight:bold;">{$player['character_name']}</span></td>
{else}
        <td style="padding:2px;">&nbsp;{$player['insignia']} <span style="font-weight:bold;">{$player['character_name']}</span></td>
{/if}

{* Check to see if they have been either banned or locked *}
{if isset($player['banned']) && $player['banned'] == false}
        <td><img style="cursor:help; padding-top:2px;" src="{$template_dir}/images/ban_status_ok.png" alt="Player Status: OK" title="Player Status: OK" /></td>
{elseif isset($player['banned']) && $player['banned'] == true && isset($player['ban_info']['type']) && $player['ban_info']['type'] > 1}
        <td><img style="cursor:help; padding-top:2px;" src="{$template_dir}/images/ban_status_banned.png" alt="Player Status: Banned" title="{$player['ban_info']['public_info']}" /></td>
{elseif isset($player['banned']) && $player['banned'] == true && isset($player['ban_info']['type']) && $player['ban_info']['type'] == 1}
        <td><img style="cursor:help; padding-top:2px;" src="{$template_dir}/images/ban_status_locked.png" alt="Player Status: Locked" title="{$player['ban_info']['public_info']}" /></td>
{/if}

        <td style="padding:2px;">{$player['turns_used']|number_format:0:".":","}</td>
        <td style="padding:2px;">{$player['last_login']}</td>
        <td style="padding:2px;">&nbsp;&nbsp;{$player['rating']}</td>

{* Check to see if they are an admin, if so diplay in a blue colour *}
{if isset($player['type']) && $player['type'] == "admin"}
        <td style="padding:2px; color:#0099FF;">{$player['team_name']}</td>
{elseif isset($player['type']) && $player['type'] == "npc"}
        <td style="padding:2px; color:#009900;">{$player['team_name']}</td>
{else}
        <td style="padding:2px;">{$player['team_name']}&nbsp;</td>
{/if}

{if isset($player['online']) && $player['online'] == true}
        <td style="padding:2px; color:#00FF00;">{$langvars['l_online']}</td>
{else}
        <td style="padding:2px; color:#7F7F7F;">{$langvars['l_offline']}</td>
{/if}

        <td style="padding:2px;">{$player['efficiency']|number_format:0:".":","}</td>
      </tr>
{/foreach}
    </table>

{else}
    {$langvars['l_ranks_none']}<br>
{/if}
    <br>

<!-- Display link back (index, main) -->
    {$variables['linkback']['caption']|replace:"[here]":"<a href='{$variables['linkback']['link']}'>{$langvars['l_here']}</a>"}
