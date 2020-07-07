<?php declare(strict_types = 1);
/**
 * log.php from The Kabal Invasion.
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

require_once './common.php';

$login = new Tki\Login();
$login->checkLogin($pdo_db, $lang, $tkireg, $template);

// Hack for log bug issue - this really needs to be fixed
$log_list = array(null,
        'LOG_LOGIN', 'LOG_LOGOUT', 'LOG_ATTACK_OUTMAN', 'LOG_ATTACK_OUTSCAN', 'LOG_ATTACK_EWD','LOG_ATTACK_EWDFAIL', 'LOG_ATTACK_LOSE', 'LOG_ATTACKED_WIN', 'LOG_TOLL_PAID', 'LOG_HIT_MINES',
        'LOG_SHIP_DESTROYED_MINES', 'LOG_PLANET_DEFEATED_D', 'LOG_PLANET_DEFEATED', 'LOG_PLANET_NOT_DEFEATED', 'LOG_RAW', 'LOG_TOLL_RECV', 'LOG_DEFS_DESTROYED', 'LOG_PLANET_EJECT', 'LOG_BADLOGIN', 'LOG_PLANET_SCAN',
        'LOG_PLANET_SCAN_FAIL', 'LOG_PLANET_CAPTURE', 'LOG_SHIP_SCAN', 'LOG_SHIP_SCAN_FAIL', 'LOG_KABAL_ATTACK', 'LOG_STARVATION', 'LOG_TOW', 'LOG_DEFS_DESTROYED_F', 'LOG_DEFS_KABOOM', 'LOG_HARAKIRI',
        'LOG_TEAM_REJECT', 'LOG_TEAM_RENAME', 'LOG_TEAM_M_RENAME', 'LOG_TEAM_KICK', 'LOG_TEAM_CREATE', 'LOG_TEAM_LEAVE', 'LOG_TEAM_NEWLEAD', 'LOG_TEAM_LEAD', 'LOG_TEAM_JOIN', 'LOG_TEAM_NEWMEMBER',
        'LOG_TEAM_INVITE', 'LOG_TEAM_NOT_LEAVE', 'LOG_ADMIN_HARAKIRI', 'LOG_ADMIN_PLANETDEL', 'LOG_DEFENSE_DEGRADE', 'LOG_PLANET_CAPTURED', 'LOG_BOUNTY_CLAIMED', 'LOG_BOUNTY_PAID', 'LOG_BOUNTY_CANCELLED', 'LOG_SPACE_PLAGUE',
        'LOG_PLASMA_STORM', 'LOG_BOUNTY_FEDBOUNTY', 'LOG_PLANET_BOMBED', 'LOG_ADMIN_ILLEGVALUE'
                );

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('log', 'common', 'global_includes', 'global_funcs', 'footer', 'planet_report'));

$title = $langvars['l_log_titlet'];
$body_class = 'log';

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title, $body_class);

// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db); // Build a player gateway object to handle the SQL calls
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

$startdate = null;

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$swordfish = null;
$swordfish = filter_input(INPUT_POST, 'swordfish', FILTER_SANITIZE_URL);
if (strlen(trim($swordfish)) === 0)
{
    $swordfish = false;
}

if ($swordfish == \Tki\SecureConfig::ADMIN_PASS) // Check if called by admin script
{
    $playerinfo['ship_id'] = $player;
    if ($player == 0)
    {
        $playerinfo['character_name'] = 'Administrator';
    }
    else
    {
        // Get playerinfo from database
        $players_gateway = new \Tki\Players\PlayersGateway($pdo_db); // Build a player gateway object to handle the SQL calls
        $tmp_playerinfo = $players_gateway->selectPlayerInfoById($player);
        $playerinfo['character_name'] = $tmp_playerinfo['character_name'];
    }
}

$mode = 'compat';
$yres = 558;

if ($mode == 'full')
{
    echo "#divScroller1 {position:relative; overflow:hidden; overflow-y:scroll; z-index:9; left:0px; top:0px; width:100%; height:{$yres}px; visbility:visible; border-width:1px 1px 1px 1px; border-color:#C6D6E7; border-style:solid; scrollbar-track-color: #DEDEEF; scrollbar-face-color:#040658; scrollbar-arrow-color:#DEDEEF}";
}
elseif ($mode == 'moz')
{
    echo "#divScroller1 {position:relative; overflow:visible; overflow-y:scroll; z-index:9; left:0px; top:0px; width:100%; height:{$yres}px; visbility:visible; scrollbar-track-color: #DEDEEF; scrollbar-face-color:#040658; scrollbar-arrow-color:#DEDEEF}";
}

echo '<center>';
echo "<table width=80% border=0 cellspacing=0 cellpadding=0>";

$logline = str_replace("[player]", "$playerinfo[character_name]", $langvars['l_log_log']);

echo "<tr><td><td width=100%><td></tr>";
echo "<tr><td><td align='left' height=20 style='background-image: url(" . $template->getVariables('template_dir') . "/images/top_panel.png); background-repeat:no-repeat'>";
echo "<font size=2 color=#040658><strong>&nbsp;&nbsp;&nbsp;" . $logline . "</strong></font>";
echo "</td><td><td></tr>";
echo "<tr><td valign=bottom>";

if ($mode == 'moz')
{
    echo '<td colspan=2 style="border-width:1px 1px 1px 1px; border-color:#C6D6E7; border-style:solid;" bgcolor=#63639C>';
}
elseif ($mode == 'full')
{
    echo '<td colspan=2 bgcolor=#63639C>';
}
else
{
    echo "<td colspan=2><table border=1 width=100%><tr><td  bgcolor=#63639C>";
}

if (empty($startdate))
{
    $startdate = date("Y-m-d");
}

// Get logsinfo from database
$logs_gateway = new \Tki\Logs\LogsGateway($pdo_db); // Build a log gateway object to handle the SQL calls
$logs = $logs_gateway->selectLogsInfo($playerinfo['ship_id'], $startdate);

$langvars['l_log_months_temp'] = "l_log_months_" . (int) (substr($startdate, 5, 2));
$entry = $langvars[$langvars['l_log_months_temp']] . " " . substr($startdate, 8, 2) . " " . substr($startdate, 0, 4);

echo "<div id=\"divScroller1\">" .
     "\n<div id=\"dynPage0\" class=\"dynPage\">" .
     "<center>" .
     "<br>" .
     "<font size=2 color=#DEDEEF><strong>" . $langvars['l_log_start'] . " " . $entry . "<strong></font>" .
     "<p>" .
     "<hr width=80% size=1 noshade style=\"color: #040658\">" .
     "</center>\n";

if ($logs !== null)
{
    foreach ($logs as $log)
    {
        $event = \Tki\Log::logParse($langvars, $log);
        $log_months_temp = "l_log_months_" . (int) (substr($log['time'], 5, 2));
        $time = $langvars[$log_months_temp] . " " . substr($log['time'], 8, 2) . " " . substr($log['time'], 0, 4) . " " . substr($log['time'], 11);

        echo "<table border=0 cellspacing=5 width=100%>\n" .
             "  <tr>\n" .
             "    <td style='text-align:left; font-size:12px; color:#040658; font-weight:bold;'>{$event['title']}</td>\n" .
             "    <td style='text-align:right; font-size:12px; color:#040658; font-weight:bold;'>{$time}</td>\n" .
             "  </tr>\n" .
             "  <tr>\n" .
             "    <td colspan=2 style='text-align:left; font-size:12px; color:#DEDEEF;'>{$event['text']}</td>\n" .
             "  </tr>\n" .
             "</table>\n" .
             "<center><hr width='80%' size='1' noshade style='color: #040658;'></center>\n";
    }
}

echo "<center>" .
     "<br>" .
     "<font size=2 color=#DEDEEF><strong>" . $langvars['l_log_end'] . " " . $entry . "<strong></font>" .
     "<p>" .
     "</center>" .
     "</div>\n";

// Convert the supplied date format (YYYY-MM-DD) to a time stamp.
$start_time = strtotime($startdate);

// Calculate timestamp for midnight 1 day ago.
$yd1 = (int) $start_time - (mktime(0, 0, 0, 0, 1, 0) - 943920000);

// Calculate timestamp for midnight tomorrow.
$tm = (int) $start_time + (mktime(0, 0, 0, 0, 1, 0) - 943920000);

$month = substr($startdate, 5, 2);
$day = substr($startdate, 8, 2);
$year = substr($startdate, 0, 4);

$yesterday = mktime(0, 0, 0, (int) $month, ((int) date("j") - 1), (int) $year);
$yesterday = date("Y-m-d", $yd1);

$tomorrow = mktime(0, 0, 0, (int) $month, ((int) date("j") + 1), (int) $year);
$tomorrow = date("Y-m-d", $tm);

if ($mode == 'compat')
{
    echo "</td></tr></table>";
}

if ($mode != 'compat')
{
    $log_months_temp = "l_log_months_" . (int) (substr($yesterday, 5, 2));
    $entry = $$log_months_temp . " " . substr($yesterday, 8, 2) . " " . substr($yesterday, 0, 4);

    unset($logs);
    // Get logsinfo from database
    $logs_gateway = new \Tki\Logs\LogsGateway($pdo_db); // Build a log gateway object to handle the SQL calls
    $logs = $logs_gateway->selectLogsInfo($playerinfo['ship_id'], $yesterday);

    echo "<div id=\"dynPage1\" class=\"dynPage\">" .
         "<center>" .
         "<br>" .
         "<font size=2 color=#DEDEEF><strong>" . $langvars['l_log_start'] . " " . $entry . "<strong></font>" .
         "<p>" .
         "</center>" .
         "<hr width=80% size=1 noshade style=\"color: #040658\">";

    if ($logs !== null)
    {
        foreach ($logs as $log)
        {
            $event = \Tki\Log::logParse($langvars, $log);
            $log_months_temp = "l_log_months_" . (int) (substr($log['time'], 5, 2));
            $time = $$log_months_temp . " " . substr($log['time'], 8, 2) . " " . substr($log['time'], 0, 4) . " " . substr($log['time'], 11);

            echo "<table border=0 cellspacing=5 width=100%>\n" .
                 "  <tr>\n" .
                 "    <td align='left'><font size='2' color='#040658'><strong>{$event['title']}</strong></td>\n" .
                 "    <td align='right'><font size='2' color='#040658'><strong>{$time}</strong></td>\n" .
                 "  <tr><td colspan='2' align='left'><font size='2' color='#DEDEEF'>{$event['text']}</td></tr>\n" .
                 "</table>\n" .
                 "<hr width='80%' size='1' noshade style='color: #040658;'>\n";
        }
    }

    echo "<center>" .
         "<br>" .
         "<font size=2 color=#DEDEEF><strong>" . $langvars['l_log_end'] . " " . $entry . "<strong></font>" .
         "<p>" .
         "</center>" .
         "</div>\n";

    $log_months_temp = "l_log_months_" . (int) (substr($tomorrow, 5, 2));
    $entry = $$log_months_temp . " " . substr($tomorrow, 8, 2) . " " . substr($tomorrow, 0, 4);

    unset($logs);

    // Get logsinfo from database
    $logs_gateway = new \Tki\Logs\LogsGateway($pdo_db); // Build a log gateway object to handle the SQL calls
    $logs = $logs_gateway->selectLogsInfo($playerinfo['ship_id'], $tomorrow);

    echo "<div id=\"dynPage2\" class=\"dynPage\">" .
         "<center>" .
         "<br>" .
         "<font size=2 color=#DEDEEF><strong>" . $langvars['l_log_start'] . " " . $entry . "<strong></font>" .
         "<p>" .
         "</center>" .
         "<hr width=80% size=1 noshade style=\"color: #040658\">";

    if ($logs !== null)
    {
        foreach ($logs as $log)
        {
            $event = \Tki\Log::logParse($langvars, $log);
            $log_months_temp = "l_log_months_" . (int) (substr($log['time'], 5, 2));
            $time = $$log_months_temp . " " . substr($log['time'], 8, 2) . " " . substr($log['time'], 0, 4) . " " . substr($log['time'], 11);

            echo "<table border=0 cellspacing=5 width=100%>\n" .
                 "<tr>\n" .
                 "<td style='text-align:left;'><font size=2 color=#040658><strong>$event[title]</strong></td>\n" .
                 "<td align=right><font size=2 color=#040658><strong>$time</strong></td>\n" .
                 "</tr>\n" .
                 "<tr>\n<td colspan=2 align=left>\n" .
                 "<font size=2 color=#DEDEEF>" .
                 "$event[text]" .
                 "</td>\n</tr>\n" .
                 "</table>\n" .
                 "<hr width=80% size=1 noshade style=\"color: #040658\">";
        }
    }

    echo "<center>" .
         "<br>" .
         "<font size=2 color=#DEDEEF><strong>" . $langvars['l_log_end'] . " " . $entry . "<strong></font>" .
         "<p>" .
         "</center>" .
         "</div>";
}

echo "</div>";

$log_months_short_temp = "l_log_months_short_" . date("n", $yd1); // (int) (substr($startdate, 5, 2));
$date1 = $langvars[$log_months_short_temp] . " " . date("d", $yd1); //substr($yesterday1, 8, 2);

$log_months_short_temp = "l_log_months_short_" . date("n", (int) $start_time); //(int) (substr($startdate, 5, 2));
$date2 = $langvars[$log_months_short_temp] . " " . date("d", (int) $start_time); //substr($startdate, 8, 2);

$log_months_short_temp = "l_log_months_short_" . date("n", $tm); // (int) (substr($startdate, 5, 2));
$date3 = $langvars[$log_months_short_temp] . " " . date("d", $tm); //substr($tomorrow, 8, 2);

$month = substr($startdate, 5, 2);
$day = substr($startdate, 8, 2);
$day = (int) $day;
$day = $day - 3;
$year = substr($startdate, 0, 4);

$backlink = mktime(0, 0, 0, (int) $month, $day, (int) $year);
$backlink = date("Y-m-d", $backlink);

$day = substr($startdate, 8, 2);
$day = (int) $day;
$day = $day + 3;

$nextlink = mktime(0, 0, 0, (int) $month, $day, (int) $year);
$nextlink = date("Y-m-d", $nextlink);

$nonext = 0;
//if ($startdate == date("Y-m-d"))
//{
//   $nonext = 1;
//}
//else
//{
//    $nonext = 0;
//}

if ($swordfish == \Tki\SecureConfig::ADMIN_PASS) // Fix for admin log view
{
    $postlink = "&swordfish=" . urlencode($swordfish) . "&player=" . urlencode($player);
}
else
{
    $postlink = null;
}

if ($mode != 'compat')
{
    echo "<td valign=bottom>" .
         "<tr><td><td align=right>" .
         "<img alt=\"\" style=\"height:296px; width:20px\" src=\"" . $template->getVariables('template_dir') . "/images/bottom_panel.png\">" .
         "<br>" .
         "<div style=\"position:relative; top:-23px;\">" .
         "<font size=2><strong>" .
         "<a href=log.php?startdate={$backlink}$postlink><<</a>&nbsp;&nbsp;&nbsp;" .
         "<a href=\"#\" onclick=\"activate(2); return false;\" onfocus=\"if (this.blur)this.blur()\">$date3</a>" .
         " | " .
         "<a href=\"#\" onclick=\"activate(1); return false;\" onfocus=\"if (this.blur)this.blur()\">$date2</a>" .
         " | " .
         "<a href=\"#\" onclick=\"activate(0); return false;\" onfocus=\"if (this.blur)this.blur()\">$date1</a>";

    if ($nonext != 1)
    {
        echo "&nbsp;&nbsp;&nbsp;<a href=log.php?startdate={$nextlink}$postlink>>>></a>";
    }

    echo "&nbsp;&nbsp;&nbsp;";
}
else
{
    echo "<tr><td><td align=right>" .
         "<a href=log.php?startdate={$backlink}$postlink><font color=white size =3><strong><<</strong></font></a>&nbsp;&nbsp;&nbsp;" .
         "<a href=log.php?startdate={$yesterday}$postlink><font color=white size=3><strong>$date1</strong></font></a>" .
         "&nbsp;|&nbsp;" .
         "<a href=log.php?startdate={$startdate}$postlink><font color=white size=3><strong>$date2</strong></font></a>" .
         " | " .
         "<a href=log.php?startdate={$tomorrow}$postlink><font color=white size=3><strong>$date3</strong></font></a>";

    if ($nonext != 1)
    {
        echo "&nbsp;&nbsp;&nbsp;<a href=log.php?startdate={$nextlink}$postlink><font color=white size=3><strong>>></strong></font></a>";
    }

    echo "&nbsp;&nbsp;&nbsp;";
}

if ($swordfish == \Tki\SecureConfig::ADMIN_PASS)
{
    echo "<tr><td><td>" .
         "<form accept-charset='utf-8' action=admin.php method=post>" .
         "<input type=hidden name=swordfish value=\"$swordfish\">" .
         "<input type=hidden name=menu value='log_viewer.php'>" .
         "<input type=submit value=\"Return to Admin\"></td></tr>";
}
else
{
    $langvars['l_log_click'] = str_replace("[here]", "<a href=main.php><font color=#00ff00>" . $langvars['l_here'] . "</font></a>", $langvars['l_log_click']);
    echo "<tr><td><td style='text-align:left;'><p style='font-size:2;'>" . $langvars['l_log_click'] . "</p></td></tr>";
}

if ($mode != 'compat')
{
    $langvars['l_log_note'] = str_replace("[disable them]", "<a href=options.php><font color=#00FF00>" . $langvars['l_log_note_disable'] . "</font></a>", $langvars['l_log_note']);
    echo "<tr><td><td align=center><br><font size=2 color=white>" . $langvars['l_log_note'] . "</td></tr>";
}

echo "</table></center>";

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
