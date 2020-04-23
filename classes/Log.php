<?php declare(strict_types = 1);
/**
 * classes/Log.php from The Kabal Invasion.
 * The Kabal Invasion is a Free & Opensource (FOSS), web-based 4X space/strategy game.
 *
 * @copyright 2020 The Kabal Invasion development team, Ron Harwood, and the BNT development team
 *
 * @license GNU AGPL version 3.0 or (at your option) any later version.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Tki;

class Log
{
    public static function logParse(array $langvars, array $entry): array
    {
        $log_list = array();
        $retvalue = array();
        $langvars['l_log_nopod'] = "<font color=yellow><strong>" . $langvars['l_log_nopod'] . "</strong></font>"; // This should be done better, but I needed it moved out of the language file.

        self::getLogInfo($log_list, $entry['type'], $titletemp, $texttemp);

        switch ($entry['type'])
        {
            case LogEnums::LOGIN: //data args are : [ip]
            case LogEnums::LOGOUT:
            case LogEnums::BADLOGIN:
            case LogEnums::HARAKIRI:
                $retvalue['text'] = str_replace("[ip]", "<font color=white><strong>$entry[data]</strong></font>", $texttemp);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
                break;

            case LogEnums::ATTACK_OUTMAN: //data args are : [player]
            case LogEnums::ATTACK_OUTSCAN:
            case LogEnums::ATTACK_EWD:
            case LogEnums::ATTACK_EWDFAIL:
            case LogEnums::SHIP_SCAN:
            case LogEnums::SHIP_SCAN_FAIL:
            case LogEnums::KABAL_ATTACK:
            case LogEnums::TEAM_NOT_LEAVE:
                $retvalue['text'] = str_replace("[player]", "<font color=white><strong>$entry[data]</strong></font>", $texttemp);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
                break;

            case LogEnums::ATTACK_LOSE: //data args are : [player] [pod]
                list($name, $pod) = explode("|", $entry['data']);

                $retvalue['text'] = str_replace("[player]", "<font color=white><strong>$name</strong></font>", $texttemp);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
                $retvalue['text'] = $retvalue['text'] . $langvars['l_log_nopod'];
                if ($pod == 'Y')
                {
                    $retvalue['text'] = $retvalue['text'] . $langvars['l_log_pod'];
                }
                break;

            case LogEnums::ATTACKED_WIN: //data args for text are : [player] [armor] [fighters]
                list($name, $armor, $fighters) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[player]", "<font color=white><strong>$name</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[armor]", "<font color=white><strong>$armor</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[fighters]", "<font color=white><strong>$fighters</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=yellow>" . $retvalue['title'] . "</font>";
                break;

            case LogEnums::TOLL_PAID: //data args are : [toll] [sector]
            case LogEnums::TOLL_RECV:
                list ($toll, $sector) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[toll]", "<font color=white><strong>$toll</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::HIT_MINES: //data args are : [mines] [sector]
                list ($mines, $sector) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[mines]", "<font color=white><strong>$mines</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=yellow>" . $retvalue['title'] . "</font>";
                break;

            case LogEnums::SHIP_DESTROYED_MINES: //data args are : [sector] [pod]
                list ($sector, $pod) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
                $retvalue['text'] = $retvalue['text'] . $langvars['l_log_nopod'];
                if ($pod == 'Y')
                {
                    $retvalue['text'] = $retvalue['text'] . $langvars['l_log_pod'];
                }
                break;

            case LogEnums::DEFS_KABOOM: //data args are : [sector] [pod]
                list ($sector, $pod) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
                $retvalue['text'] = $retvalue['text'] . $langvars['l_log_nopod'];
                if ($pod == 'Y')
                {
                    $retvalue['text'] = $retvalue['text'] . $langvars['l_log_pod'];
                }
                break;

            case LogEnums::PLANET_DEFEATED_D: //data args are :[planet_name] [sector] [name]
                list ($planet_name, $sector, $name) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[planet_name]", "<font color=white><strong>$planet_name</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=yellow>" . $retvalue['title'] . "</font>";
                break;

            case LogEnums::PLANET_DEFEATED:
                list ($planet_name, $sector, $name) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[planet_name]", "<font color=white><strong>$planet_name</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
                break;

            case LogEnums::PLANET_SCAN:
            case LogEnums::PLANET_SCAN_FAIL:
                list ($planet_name, $sector, $name) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[planet_name]", "<font color=white><strong>$planet_name</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::PLANET_NOT_DEFEATED: //data args are : [planet_name] [sector] [name] [ore] [organics] [goods] [salvage] [credits]
                list ($planet_name, $sector, $name, $ore, $organics, $goods, $salvage, $credits) = explode("|", $entry['data']);
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

            case LogEnums::RAW: //data is stored as a message
                $retvalue['title'] = $titletemp;
                $retvalue['text'] = $entry['data'];
                break;

            case LogEnums::DEFS_DESTROYED: //data args are : [quantity] [type] [sector]
                list ($quantity, $type, $sector) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[quantity]", "<font color=white><strong>$quantity</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[type]", "<font color=white><strong>$type</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::PLANET_EJECT: //data args are : [sector] [player]
                list ($sector, $name) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::STARVATION: //data args are : [sector] [starvation]
                list ($sector, $starvation) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[starvation]", "<font color=white><strong>$starvation</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=yellow>" . $retvalue['title'] . "</font>";
                break;

            case LogEnums::TOW: //data args are : [sector] [newsector] [hull]
                list ($sector, $newsector, $hull) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[newsector]", "<font color=white><strong>$newsector</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[hull]", "<font color=white><strong>$hull</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::DEFS_DESTROYED_F: //data args are : [fighters] [sector]
                list ($fighters, $sector) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[fighters]", "<font color=white><strong>$fighters</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::TEAM_REJECT: //data args are : [player] [teamname]
                list ($player, $teamname) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[player]", "<font color=white><strong>$player</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[teamname]", "<font color=white><strong>$teamname</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::TEAM_RENAME: //data args are : [team]
            case LogEnums::TEAM_M_RENAME:
            case LogEnums::TEAM_KICK:
            case LogEnums::TEAM_CREATE:
            case LogEnums::TEAM_LEAVE:
            case LogEnums::TEAM_LEAD:
            case LogEnums::TEAM_JOIN:
            case LogEnums::TEAM_INVITE:
                $retvalue['text'] = str_replace("[team]", "<font color=white><strong>$entry[data]</strong></font>", $texttemp);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::TEAM_NEWLEAD: //data args are : [team] [name]
            case LogEnums::TEAM_NEWMEMBER:
                list ($team, $name) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[team]", "<font color=white><strong>$team</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::ADMIN_HARAKIRI: //data args are : [player] [ip]
                list ($player, $ip_address) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[player]", "<font color=white><strong>$player</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[ip]", "<font color=white><strong>$ip_address</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::ADMIN_ILLEGVALUE: //data args are : [player] [quantity] [type] [holds]
                list ($player, $quantity, $type, $holds) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[player]", "<font color=white><strong>$player</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[quantity]", "<font color=white><strong>$quantity</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[type]", "<font color=white><strong>$type</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[holds]", "<font color=white><strong>$holds</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::ADMIN_PLANETDEL: //data args are : [attacker] [defender] [sector]
                list ($attacker, $defender, $sector) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[attacker]", "<font color=white><strong>$attacker</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[defender]", "<font color=white><strong>$defender</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::DEFENSE_DEGRADE: //data args are : [sector] [degrade]
                list ($sector, $degrade) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[degrade]", "<font color=white><strong>$degrade</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::PLANET_CAPTURED: //data args are : [cols] [credits] [owner]
                list ($cols, $credits, $owner) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[cols]", "<font color=white><strong>$cols</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[credits]", "<font color=white><strong>$credits</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[owner]", "<font color=white><strong>$owner</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::BOUNTY_CLAIMED:
                list ($amount,$bounty_on,$placed_by) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[amount]", "<font color=white><strong>$amount</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[bounty_on]", "<font color=white><strong>$bounty_on</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[placed_by]", "<font color=white><strong>$placed_by</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::BOUNTY_PAID:
                list ($amount,$bounty_on) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[amount]", "<font color=white><strong>$amount</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[bounty_on]", "<font color=white><strong>$bounty_on</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::BOUNTY_CANCELLED:
                list ($amount,$bounty_on) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[amount]", "<font color=white><strong>$amount</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[bounty_on]", "<font color=white><strong>$bounty_on</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::BOUNTY_FEDBOUNTY:
                $retvalue['text'] = str_replace("[amount]", "<font color=white><strong>$entry[data]</strong></font>", $texttemp);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::SPACE_PLAGUE:
                list ($name, $sector, $percentage) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[percentage]", "<font color=white><strong>$percentage</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::PLASMA_STORM:
                list ($name,$sector) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                break;

            case LogEnums::PLANET_BOMBED:
                list ($planet_name, $sector, $name, $beams, $torps, $figs) = explode("|", $entry['data']);
                $retvalue['text'] = str_replace("[planet_name]", "<font color=white><strong>$planet_name</strong></font>", $texttemp);
                $retvalue['text'] = str_replace("[sector]", "<font color=white><strong>$sector</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[name]", "<font color=white><strong>$name</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[beams]", "<font color=white><strong>$beams</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[torps]", "<font color=white><strong>$torps</strong></font>", $retvalue['text']);
                $retvalue['text'] = str_replace("[figs]", "<font color=white><strong>$figs</strong></font>", $retvalue['text']);
                $retvalue['title'] = $titletemp;
                $retvalue['title'] = "<font color=red>" . $retvalue['title'] . "</font>";
                break;

            case LogEnums::ATTACK_DEBUG:
                // Attack debug logs
                if (count(explode("|", $entry['data'])) == 7)
                {
                    list ($step, $attacker_armor, $target_armor, $attacker_fighters, $target_fighters, $attacker_id, $target_id) = explode("|", $entry['data']);
                    $retvalue['text']  = "Attacker Ship: {$attacker_id}, Armor: {$attacker_armor}, Fighters: {$attacker_fighters}<br>\n";
                    $retvalue['text'] .= "Target Ship: {$target_id}, Armor: {$target_armor}, Fighters: {$target_fighters}\n";
                }
                else
                {
                    list ($step, $attacker_id, $target_id, $info) = explode("|", $entry['data']);
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

    public static function getLogInfo(array $log_list, ?int $log_id = null, ?string &$title = null, ?string &$text = null): void
    {
        $title = null;
        $text = null;

        if ($log_id < count($log_list))
        {
            if (array_key_exists("l_log_title_" . $log_list[$log_id], $GLOBALS))
            {
                $title = $GLOBALS["l_log_title_" . $log_list[$log_id]];
            }

            if (array_key_exists("l_log_text_" . $log_list[$log_id], $GLOBALS))
            {
                $text = $GLOBALS["l_log_text_" . $log_list[$log_id]];
            }
        }
    }
}
