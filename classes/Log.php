<?php
// The Kabal Invasion - A web-based 4X space game
// Copyright © 2014 The Kabal Invasion development team, Ron Harwood, and the BNT development team
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU Affero General Public License as
//  published by the Free Software Foundation, either version 3 of the
//  License, or (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU Affero General Public License for more details.
//
//  You should have received a copy of the GNU Affero General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// File: classes/Log.php
//
// FUTURE: These are horribly bad. They should be broken out of classes, and turned mostly into template
// behaviors. But in the interest of saying goodbye to the includes directory, and raw functions, this
// will at least allow us to auto-load and use classes instead. Plenty to do in the future, though!

namespace Tki;

class Log
{
    public static function logParse($langvars, $entry)
    {
        $log_list = array();
        $retvalue = array();
        $langvars['l_log_nopod'] = "<font color=yellow><strong>" . $langvars['l_log_nopod'] . "</strong></font>"; // This should be done better, but I needed it moved out of the language file.

        self::getLogInfo($log_list, $entry['type'], $titletemp, $texttemp);

        switch($entry['type'])
        {
            case LOG_LOGIN: //data args are : [ip]
            case LOG_LOGOUT:
            case LOG_BADLOGIN:
            case LOG_HARAKIRI:
                $retvalue['text'] = str_replace("[ip]", "<font color=white><strong>$entry[data]</strong></font>", $texttemp);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
                break;

            case LOG_ATTACK_OUTMAN: //data args are : [player]
            case LOG_ATTACK_OUTSCAN:
            case LOG_ATTACK_EWD:
            case LOG_ATTACK_EWDFAIL:
            case LOG_SHIP_SCAN:
            case LOG_SHIP_SCAN_FAIL:
            case LOG_XENOBE_ATTACK:
            case LOG_TEAM_NOT_LEAVE:
                $retvalue['text'] = str_replace("[player]", "<font color=white><strong>$entry[data]</strong></font>", $texttemp);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
                break;

            case LOG_ATTACK_LOSE: //data args are : [player] [pod]
                list($name,$pod) = explode("|", $entry['data']);

                $retvalue['text'] = str_replace("[player]", "<font color=white><strong>$name</strong></font>", $texttemp);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
                if ($pod == 'Y')
                {
                    $retvalue['text'] = $retvalue['text'] . $langvars['l_log_pod'];
                }
                else
                {
                    $retvalue['text'] = $retvalue['text'] . $langvars['l_log_nopod'];
                }
                break;

            case LOG_ATTACKED_WIN: //data args for text are : [player] [armor] [fighters]
                list($name, $armor, $fighters)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[player]", "<font color=white><strong>$name</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[armor]", "<font color=white><strong>$armor</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[fighters]", "<font color=white><strong>$fighters</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=yellow>" . $retvalue['title'] . "</font>";
                break;

            case LOG_TOLL_PAID: //data args are : [toll] [sector]
            case LOG_TOLL_RECV:
                list ($toll, $sector)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[toll]", "<font color=white><strong>$toll</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_HIT_MINES: //data args are : [mines] [sector]
                list ($mines, $sector)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[mines]", "<font color=white><strong>$mines</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=yellow>" . $retvalue['title'] . "</font>";
                break;

            case LOG_SHIP_DESTROYED_MINES: //data args are : [sector] [pod]
                list ($sector, $pod) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
                if ($pod == 'Y')
                {
                    $retvalue['text'] = $retvalue['text'] . $langvars['l_log_pod'];
                }
                else
                {
                    $retvalue['text'] = $retvalue['text'] . $langvars['l_log_nopod'];
                }
                break;

            case LOG_DEFS_KABOOM: //data args are : [sector] [pod]
                list ($sector, $pod)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
                if ($pod == 'Y')
                {
                    $retvalue['text'] = $retvalue['text'] . $langvars['l_log_pod'];
                }
                else
                {
                    $retvalue['text'] = $retvalue['text'] . $langvars['l_log_nopod'];
                }
                break;

            case LOG_PLANET_DEFEATED_D: //data args are :[planet_name] [sector] [name]
                list ($planet_name, $sector, $name)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[planet_name]", "<font color=white><strong>$planet_name</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=yellow>" . $retvalue['title'] . "</font>";
                break;

            case LOG_PLANET_DEFEATED:
                list ($planet_name, $sector, $name)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[planet_name]", "<font color=white><strong>$planet_name</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
                break;

            case LOG_PLANET_SCAN:
            case LOG_PLANET_SCAN_FAIL:
                list ($planet_name, $sector, $name)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[planet_name]", "<font color=white><strong>$planet_name</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_PLANET_NOT_DEFEATED: //data args are : [planet_name] [sector] [name] [ore] [organics] [goods] [salvage] [credits]
                list ($planet_name, $sector, $name, $ore, $organics, $goods, $salvage, $credits)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[planet_name]", "<font color=white><strong>$planet_name</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[ore]", "<font color=white><strong>$ore</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[goods]", "<font color=white><strong>$goods</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[organics]", "<font color=white><strong>$organics</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[salvage]", "<font color=white><strong>$salvage</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[credits]", "<font color=white><strong>$credits</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_RAW: //data is stored as a message
                $retvalue['title'] = $titletemp;
                $retvalue['text'] = $entry['data'];
                break;

            case LOG_DEFS_DESTROYED: //data args are : [quantity] [type] [sector]
                list ($quantity, $type, $sector)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[quantity]", "<font color=white><strong>$quantity</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[type]", "<font color=white><strong>$type</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_PLANET_EJECT: //data args are : [sector] [player]
                list ($sector, $name)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_STARVATION: //data args are : [sector] [starvation]
                list ($sector, $starvation)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[starvation]", "<font color=white><strong>$starvation</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=yellow>" . $retvalue['title'] . "</font>";
                break;

            case LOG_TOW: //data args are : [sector] [newsector] [hull]
                list ($sector, $newsector, $hull)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[newsector]", "<font color=white><strong>$newsector</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[hull]", "<font color=white><strong>$hull</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_DEFS_DESTROYED_F: //data args are : [fighters] [sector]
                list ($fighters, $sector)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[fighters]", "<font color=white><strong>$fighters</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_TEAM_REJECT: //data args are : [player] [teamname]
                list ($player, $teamname)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[player]", "<font color=white><strong>$player</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[teamname]", "<font color=white><strong>$teamname</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_TEAM_RENAME: //data args are : [team]
            case LOG_TEAM_M_RENAME:
            case LOG_TEAM_KICK:
            case LOG_TEAM_CREATE:
            case LOG_TEAM_LEAVE:
            case LOG_TEAM_LEAD:
            case LOG_TEAM_JOIN:
            case LOG_TEAM_INVITE:
                $retvalue['text'] = str_replace("[team]", "<font color=white><strong>$entry[data]</strong></font>", $texttemp);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_TEAM_NEWLEAD: //data args are : [team] [name]
            case LOG_TEAM_NEWMEMBER:
                list ($team, $name)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[team]", "<font color=white><strong>$team</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_ADMIN_HARAKIRI: //data args are : [player] [ip]
                list ($player, $ip)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[player]", "<font color=white><strong>$player</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[ip]", "<font color=white><strong>$ip</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_ADMIN_ILLEGVALUE: //data args are : [player] [quantity] [type] [holds]
                list ($player, $quantity, $type, $holds)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[player]", "<font color=white><strong>$player</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[quantity]", "<font color=white><strong>$quantity</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[type]", "<font color=white><strong>$type</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[holds]", "<font color=white><strong>$holds</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_ADMIN_PLANETDEL: //data args are : [attacker] [defender] [sector]
                list ($attacker, $defender, $sector)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[attacker]", "<font color=white><strong>$attacker</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[defender]", "<font color=white><strong>$defender</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_DEFENCE_DEGRADE: //data args are : [sector] [degrade]
                list ($sector, $degrade)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[degrade]", "<font color=white><strong>$degrade</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_PLANET_CAPTURED: //data args are : [cols] [credits] [owner]
                list ($cols, $credits, $owner)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[cols]", "<font color=white><strong>$cols</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[credits]", "<font color=white><strong>$credits</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[owner]", "<font color=white><strong>$owner</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_BOUNTY_CLAIMED:
                list ($amount,$bounty_on,$placed_by) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[amount]", "<font color=white><strong>$amount</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[bounty_on]", "<font color=white><strong>$bounty_on</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[placed_by]", "<font color=white><strong>$placed_by</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_BOUNTY_PAID:
                list ($amount,$bounty_on) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[amount]", "<font color=white><strong>$amount</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[bounty_on]", "<font color=white><strong>$bounty_on</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_BOUNTY_CANCELLED:
                list ($amount,$bounty_on) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[amount]", "<font color=white><strong>$amount</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[bounty_on]", "<font color=white><strong>$bounty_on</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_BOUNTY_FEDBOUNTY:
                $retvalue['text'] = str_replace("[amount]", "<font color=white><strong>$entry[data]</strong></font>", $texttemp);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_SPACE_PLAGUE:
                list ($name, $sector, $percentage) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[percentage]", "<font color=white><strong>$percentage</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_PLASMA_STORM:
                list ($name,$sector) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LOG_PLANET_BOMBED:
                list ($planet_name, $sector, $name, $beams, $torps, $figs)= explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[planet_name]", "<font color=white><strong>$planet_name</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[beams]", "<font color=white><strong>$beams</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[torps]", "<font color=white><strong>$torps</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[figs]", "<font color=white><strong>$figs</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
                break;

            case LOG_MULTI_BROWSER:
                // Multi Browser Logs.
                list ($ship_ip, $ship_id, $info)= explode("|", $entry['data']);
                $retvalue['text'] = "Account: <span style='color:#ff0;'>{$ship_id}</span> with IP: '<span style='color:#ff0;'>{$ship_ip}</span>' <span style='color:#fff;'>{$info}</span>";
                $retvalue['title'] = "Possible Multi Browser Attempt.";
                break;

            case LOG_ATTACK_DEBUG:
                // Attack debug logs
                if (count(explode("|", $entry['data'])) == 7)
                {
                    list ($step, $attacker_armor, $target_armor, $attacker_fighters, $target_fighters, $attacker_id, $target_id)= explode("|", $entry['data']);
                    $retvalue['text']  = "Attacker Ship: {$attacker_id}, Armor: {$attacker_armor}, Fighters: {$attacker_fighters}<br>\n";
                    $retvalue['text'] .= "Target Ship: {$target_id}, Armor: {$target_armor}, Fighters: {$target_fighters}\n";
                }
                else
                {
                    list ($step, $attacker_id, $target_id, $info)= explode("|", $entry['data']);
                    $retvalue['text']  = "Attacker Ship: {$attacker_id}, Target Ship: {$target_id}, Target Ship: {$info}\n";
                }
                $retvalue['title'] = "Attack Logs Stage: {$step} [Debug].";
                break;

            default:
                $retvalue['text'] = $entry['data'];
                $retvalue['title'] = $entry['type'];
                break;
        }

        return $retvalue;
    }

    public static function getLogInfo($log_list, $id = null, &$title = null, &$text = null)
    {
        $title = null;
        $text = null;

        if ($id < count($log_list))
        {
            if (array_key_exists("l_log_title_". $log_list[$id], $GLOBALS))
            {
                $title = $GLOBALS["l_log_title_". $log_list[$id]];
            }

            if (array_key_exists("l_log_text_". $log_list[$id], $GLOBALS))
            {
                $text = $GLOBALS["l_log_text_". $log_list[$id]];
            }
        }
    }
}
