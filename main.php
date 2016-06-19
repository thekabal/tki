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
// File: main.php

require_once './common.php';

Tki\Login::checkLogin($pdo_db, $lang, $tkireg, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('combat', 'common', 'main', 'modify_defences', 'admin','footer','global_includes', 'regional'));
$title = $langvars['l_main_title'];
Tki\Header::display($pdo_db, $lang, $template, $title);

$stylefontsize = "12pt";
$picsperrow = 7;

// Get playerinfo from database
$sql = "SELECT * FROM {$pdo_db->prefix}ships WHERE email=:email LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':email', $_SESSION['username']);
$stmt->execute();
$playerinfo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!array_key_exists('command', $_GET))
{
    $_GET['command'] = null;
}

if ($_GET['command'] == "score")
{
    $playerinfo['score'] = Tki\Score::updateScore($pdo_db, $playerinfo['ship_id'], $tkireg, $playerinfo);
}

if ($playerinfo['cleared_defences'] > ' ')
{
    echo $langvars['l_incompletemove'] . " <br>";
    echo "<a href=$playerinfo[cleared_defences]>" . $langvars['l_clicktocontinue'] . "</a>";
    die();
}


// Pull sector info from database
$sql = "SELECT * FROM {$pdo_db->prefix}universe WHERE sector_id=:sector_id";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':sector_id', $playerinfo['sector']);
$stmt->execute();
$sectorinfo = $stmt->fetch(PDO::FETCH_ASSOC);

if ($playerinfo['on_planet'] == "Y")
{
    $res2 = $db->Execute("SELECT planet_id, owner FROM {$pdo_db->prefix}planets WHERE planet_id = ?;", array($playerinfo['planet_id']));
    Tki\Db::LogDbErrors($pdo_db, $res2, __LINE__, __FILE__);
    if ($res2->RecordCount() != 0)
    {
        echo "<a href=planet.php?planet_id=$playerinfo[planet_id]>" . $langvars['l_clickme'] . "</a> " . $langvars['l_toplanetmenu'] . "    <br>";
        header("Location: planet.php?planet_id=" . $playerinfo['planet_id'] . "&id=" . $playerinfo['ship_id']);
        die();
    }
    else
    {
        $db->Execute("UPDATE {$pdo_db->prefix}ships SET on_planet='N' WHERE ship_id = ?;", array($playerinfo['ship_id']));
        echo "<br>" . $langvars['l_nonexistant_pl'] . "<br><br>";
    }
}

$res = $db->Execute("SELECT * FROM {$pdo_db->prefix}links WHERE link_start = ? ORDER BY link_dest ASC;", array($playerinfo['sector']));
Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);

$i = 0;
if ($res !== false)
{
    while (!$res->EOF)
    {
        $links[$i] = $res->fields['link_dest'];
        $i++;
        $res->MoveNext();
    }
}
$num_links = $i;

$res = $db->Execute("SELECT * FROM {$pdo_db->prefix}planets WHERE sector_id = ?;", array($playerinfo['sector']));
Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);

$i = 0;
if ($res !== false)
{
    while (!$res->EOF)
    {
        $planets[$i] = $res->fields;
        $i++;
        $res->MoveNext();
    }
}
$num_planets = $i;

$res = $db->Execute("SELECT * FROM {$pdo_db->prefix}sector_defence, {$pdo_db->prefix}ships WHERE {$pdo_db->prefix}sector_defence.sector_id = ? AND {$pdo_db->prefix}ships.ship_id = {$pdo_db->prefix}sector_defence.ship_id;", array($playerinfo['sector']));
Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);

$i = 0;
if ($res !== false)
{
    while (!$res->EOF)
    {
        $defences[$i] = $res->fields;
        $i++;
        $res->MoveNext();
    }
}
$num_defences = $i;


// Grab zoneinfo from database
$sql = "SELECT zone_id,zone_name FROM {$pdo_db->prefix}zones WHERE zone_id=:zone_id";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':zone_id', $sectorinfo['zone_id']);
$stmt->execute();
$zoneinfo = $stmt->fetch(PDO::FETCH_ASSOC);

$shiptypes[0]= "tinyship.png";
$shiptypes[1]= "smallship.png";
$shiptypes[2]= "mediumship.png";
$shiptypes[3]= "largeship.png";
$shiptypes[4]= "hugeship.png";

$planettypes[0]= "tinyplanet.png";
$planettypes[1]= "smallplanet.png";
$planettypes[2]= "mediumplanet.png";
$planettypes[3]= "largeplanet.png";
$planettypes[4]= "hugeplanet.png";

$signame = Tki\Character::getInsignia($pdo_db, $_SESSION['username'], $langvars);
echo "<div style='width:90%; margin:auto; background-color:#400040; color:#C0C0C0; text-align:center; border:#fff 1px solid; padding:4px;'>\n";
echo "{$signame} <span style='color:#fff; font-weight:bold;'>{$playerinfo['character_name']}</span>{$langvars['l_aboard']} <span style='color:#fff; font-weight:bold;'><a class='new_link' style='font-size:14px;' href='report.php'>{$playerinfo['ship_name']}</a></span>\n";
echo "</div>\n";

$result = $db->Execute("SELECT * FROM {$pdo_db->prefix}messages WHERE recp_id = ? AND notified = ?;", array($playerinfo['ship_id'], "N"));
Tki\Db::LogDbErrors($pdo_db, $result, __LINE__, __FILE__);

if ($result->RecordCount() > 0)
{
    $alert_message = "{$langvars['l_youhave']} {$result->RecordCount()} {$langvars['l_messages_wait']}";
    echo "<script>\n";
    echo "  alert('{$alert_message}');\n";
    echo "</script>\n";

    $res = $db->Execute("UPDATE {$pdo_db->prefix}messages SET notified='Y' WHERE recp_id = ?;", array($playerinfo['ship_id']));
    Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
}

$ply_turns     = number_format($playerinfo['turns'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
$ply_turnsused = number_format($playerinfo['turns_used'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
$ply_score     = number_format($playerinfo['score'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
$ply_credits   = number_format($playerinfo['credits'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);

echo "<table style='width:90%; margin:auto; text-align:center; border:0px;'>\n";
echo "  <tr>\n";
echo "    <td style='width:33%; text-align:left; color:#ccc; font-size:12px;'>&nbsp;{$langvars['l_turns_have']} <span style='color:#fff; font-weight:bold;'>{$ply_turns}</span></td>\n";
echo "    <td style='width:33%; text-align:center; color:#ccc; font-size:12px;'>{$langvars['l_turns_used']} <span style='color:#fff; font-weight:bold;'>{$ply_turnsused}</span></td>\n";
echo "    <td style='width:33%; text-align:right; color:#ccc; font-size:12px;'>{$langvars['l_score']} <span style='color:#fff; font-weight:bold;'><a href='main.php?command=score'>{$ply_score}</a>&nbsp;</span></td>\n";
echo "  </tr>\n";
echo "  <tr>\n";
echo "    <td colspan='3' style='width:33%; text-align:right; color:#ccc; font-size:12px;'>&nbsp;{$langvars['l_credits']}: <span style='color:#fff; font-weight:bold;'>{$ply_credits}</span></td>\n";
echo "  </tr>\n";

echo "  <tr>\n";
echo "    <td style='text-align:left; color:#ccc; font-size:12px;'>&nbsp;{$langvars['l_sector']} <span style='color:#fff; font-weight:bold;'>{$playerinfo['sector']}</span></td>\n";
if (empty ($sectorinfo['beacon']) || mb_strlen(trim($sectorinfo['beacon'])) == 0)
{
    $sectorinfo['beacon'] = null;
}
echo "    <td style='text-align:center; color:#fff; font-size:12px; font-weight:bold;'>&nbsp;{$sectorinfo['beacon']}&nbsp;</td>\n";

if ($zoneinfo['zone_id'] < 5)
{
    $zonevar = "l_zname_" . $zoneinfo['zone_id'];
    $zoneinfo['zone_name'] = $langvars[$zonevar];
}

// Sanitize ZoneName.
$zoneinfo['zone_name'] = preg_replace('/[^A-Za-z0-9\_\s\-\.\']+/', '', $zoneinfo['zone_name']);

echo "    <td style='text-align:right; color:#ccc; font-size:12px; font-weight:bold;'><a class='new_link' href='zoneinfo.php?zone={$zoneinfo['zone_id']}'>{$zoneinfo['zone_name']}</a>&nbsp;</td>\n";
echo "  </tr>\n";
echo "</table>\n";

echo "<br>\n";

echo "<table style='width:90%; margin:auto; border:0px; border-spacing:0px;'>\n";
echo "  <tr>\n";

// Left Side.
echo "    <td style='width:200px; vertical-align:top; text-align:center;'>\n";

if ($tkireg->enable_gravatars)
{
    $gravatar_id = md5($playerinfo['email']);

    echo "<table style='width:140px; border:0px; padding:0px; border-spacing:0px; margin-left:auto; margin-right:auto;'>\n";
    echo "  <tr style='vertical-align:top'>\n";
    echo "    <td style='padding:0px; width:8px;'><img style='border:0px; height:18px; width:8px; float:right;' src='" . $template->getVariables('template_dir') . "/images/lcorner.png' alt=''></td>\n";
    echo "    <td style='padding:0px; background-color:#400040; text-align:center; vertical-align:middle;'><strong style='font-size:0.75em; color:#fff;'>{$langvars['l_avatar']}</strong></td>\n";
    echo "    <td style='padding:0px; width:8px'><img style='border:0px; height:18px; width:8px; float:left;' src='" . $template->getVariables('template_dir') . "/images/rcorner.png' alt=''></td>\n";
    echo "  </tr>\n";
    echo "</table>\n";
    echo "<table style='width:150px; margin:auto; text-align:center; border:0px; padding:0px; border-spacing:0px'>\n";
    echo "  <tr>\n";
    echo "    <td style='white-space:nowrap; border:#fff 1px solid; background-color:#500050; width:150px'>\n";
    echo "<img style='display:block; margin-left:auto; margin-right:auto' height='80' width='80' alt='Player Avatar' src='http://www.gravatar.com/avatar/" . $gravatar_id . "?r=g&amp;d=mm'>";
    echo "    <div style='padding-left:4px; text-align:left'>\n";
    echo "</div>\n";
    echo "    </td>\n";
    echo "  </tr>\n";
    echo "</table>\n";
    echo "<br>\n";
}

// Caption
echo "<table style='width:140px; border:0px; padding:0px; border-spacing:0px; margin-left:auto; margin-right:auto;'>\n";
echo "  <tr style='vertical-align:top'>\n";
echo "    <td style='padding:0px; width:8px;'><img style='border:0px; height:18px; width:8px; float:right;' src='" . $template->getVariables('template_dir') . "/images/lcorner.png' alt=''></td>\n";
echo "    <td style='padding:0px; white-space:nowrap; background-color:#400040; text-align:center; vertical-align:middle;'><strong style='font-size:0.75em; color:#fff;'>{$langvars['l_commands']}</strong></td>\n";
echo "    <td style='padding:0px; width:8px'><img style='border:0px; height:18px; width:8px; float:left;' src='" . $template->getVariables('template_dir') . "/images/rcorner.png' alt=''></td>\n";
echo "  </tr>\n";
echo "</table>\n";

// Menu
echo "<table style='width:150px; margin:auto; text-align:center; border:0px; padding:0px; border-spacing:0px'>\n";
echo "  <tr>\n";
echo "    <td style='white-space:nowrap; border:#fff 1px solid; background-color:#500050;'>\n";

if ($playerinfo['email'] == $tkireg->admin_mail)
{
    echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='admin.php'>{$langvars['l_admin_menu']}</a></div>\n";
}
echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='device.php'>{$langvars['l_devices']}</a></div>\n";
echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='planet_report.php'>{$langvars['l_planets']}</a></div>\n";
echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='ibank.php'>{$langvars['l_ibank']}</a></div>\n";
echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='log.php'>{$langvars['l_log']}</a></div>\n";
echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='defence_report.php'>{$langvars['l_sector_def']}</a></div>\n";
echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='readmail.php'>{$langvars['l_read_msg']}</a></div>\n";
echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='mailto.php'>{$langvars['l_send_msg']}</a></div>\n";
echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='ranking.php'>{$langvars['l_rankings']}</a></div>\n";
echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='settings.php'>{$langvars['l_settings']}</a></div>\n";
echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='teams.php'>{$langvars['l_teams']}</a></div>\n";
echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='self_destruct.php'>{$langvars['l_ohno']}</a></div>\n";
echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='options.php'>{$langvars['l_options']}</a></div>\n";
echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='navcomp.php'>{$langvars['l_navcomp']}</a></div>\n";

if ($tkireg->allow_ksm === true)
{
    echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='galaxy.php'>{$langvars['l_map']}</a></div>\n";
}
echo "    </td>\n";
echo "  </tr>\n";
echo "  <tr>\n";
echo "    <td style='white-space:nowrap; height:2px; background-color:transparent;'></td>\n";
echo "  </tr>\n";
echo "  <tr>\n";
echo "    <td style='white-space:nowrap; border:#fff 1px solid; background-color:#500050;'>\n";
echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='faq.php'>{$langvars['l_faq']}</a></div>\n";
echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='feedback.php'>{$langvars['l_feedback']}</a></div>\n";
//echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='main.php' title='Not implemented'><span style='font-size:8px; color:#ff0; font-style:normal;'>NEW</span> Support</a></div>\n";
//echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='maint_info.php' title='This will display the Scheduled Maintenance information for this game or Core Code.'><span style='font-size:8px; color:#ff0; font-style:normal;'>NEW</span> Maint Info</a></div>\n";
//echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='rules.php' title='These are our Rules that you have agreed to.'><span style='font-size:8px; color:#ff0; font-style:normal;'>NEW</span> Our Rules</a></div>\n";
//echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='mail.php?mail={$_SESSION['username']}' title='Request your login information to be emailed to you.'><span style='font-size:8px; color:#ff0; font-style:normal;'>TMP</span> REQ Password</a></div>\n";
echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='//" . $tkireg->link_forums . "'>{$langvars['l_forums']}</a></div>\n";
echo "    </td>\n";
echo "  </tr>\n";
echo "  <tr>\n";
echo "    <td style='white-space:nowrap; height:2px; background-color:transparent;'></td>\n";
echo "  </tr>\n";
echo "  <tr>\n";
echo "    <td style='white-space:nowrap; border:#fff 1px solid; background-color:#500050;'>\n";
echo "      <div style='padding-left:4px; text-align:left;'><a class='mnu' href='logout.php'>{$langvars['l_logout']}</a></div>\n";
echo "    </td>\n";
echo "  </tr>\n";
echo "</table>\n";
echo "<br>\n";

// Caption
echo "<table style='width:140px; border:0px; padding:0px; border-spacing:0px; margin-left:auto; margin-right:auto;'>\n";
echo "  <tr style='vertical-align:top;'>\n";
echo "    <td style='padding:0px; width:8px;'><img style='width:8px; height:18px; border:0px; float:right;' src='" . $template->getVariables('template_dir') . "/images/lcorner.png' alt=''></td>\n";
echo "    <td style='padding:0px; white-space:nowrap; background-color:#400040; text-align:center; vertical-align:middle;'><strong style='font-size:0.75em; color:#fff;'>{$langvars['l_traderoutes']}</strong></td>\n";
echo "    <td style='padding:0px; width:8px;'><img style='width:8px; height:18px; border:0px; float:left;' src='" . $template->getVariables('template_dir') . "/images/rcorner.png' alt=''></td>\n";
echo "  </tr>\n";
echo "</table>\n";

// Menu
$i = 0;
$num_traderoutes = 0;

// Traderoute querry
$tr_result = $db->Execute("SELECT * FROM {$pdo_db->prefix}traderoutes WHERE source_type = ? AND source_id = ? AND owner = ? ORDER BY dest_id ASC;", array("P", $playerinfo['sector'], $playerinfo['ship_id']));
Tki\Db::LogDbErrors($pdo_db, $tr_result, __LINE__, __FILE__);
while (!$tr_result->EOF)
{
    $traderoutes[$i] = $tr_result->fields;
    $i++;
    $num_traderoutes++;
    $tr_result->MoveNext();
}

// Sector Defense Trade route query - this is still under developement
$sd_tr_result = $db->Execute("SELECT * FROM {$pdo_db->prefix}traderoutes WHERE source_type='D' AND source_id = ? AND owner = ? ORDER BY dest_id ASC;", array($playerinfo['sector'], $playerinfo['ship_id']));
Tki\Db::LogDbErrors($pdo_db, $sd_tr_result, __LINE__, __FILE__);
while (!$sd_tr_result->EOF)
{
    $traderoutes[$i] = $sd_tr_result->fields;
    $i++;
    $num_traderoutes++;
    $sd_tr_result->MoveNext();
}

// Personal planet traderoute type query
$ppl_tr_result = $db->Execute("SELECT * FROM {$pdo_db->prefix}planets, {$pdo_db->prefix}traderoutes WHERE source_type = 'L' AND source_id = {$pdo_db->prefix}planets.planet_id AND {$pdo_db->prefix}planets.sector_id = ? AND {$pdo_db->prefix}traderoutes.owner = ?;", array($playerinfo['sector'], $playerinfo['ship_id']));
Tki\Db::LogDbErrors($pdo_db, $ppl_tr_result, __LINE__, __FILE__);
while (!$ppl_tr_result->EOF)
{
    $traderoutes[$i] = $ppl_tr_result->fields;
    $i++;
    $num_traderoutes++;
    $ppl_tr_result->MoveNext();
}

// Team planet traderoute type query
$tmpl_tr_result = $db->Execute("SELECT * FROM {$pdo_db->prefix}planets, {$pdo_db->prefix}traderoutes WHERE source_type = 'C' AND source_id = {$pdo_db->prefix}planets.planet_id AND {$pdo_db->prefix}planets.sector_id = ? AND {$pdo_db->prefix}traderoutes.owner = ?;", array($playerinfo['sector'], $playerinfo['ship_id']));
Tki\Db::LogDbErrors($pdo_db, $tmpl_tr_result, __LINE__, __FILE__);
while (!$tmpl_tr_result->EOF)
{
    $traderoutes[$i] = $tmpl_tr_result->fields;
    $i++;
    $num_traderoutes++;
    $tmpl_tr_result->MoveNext();
}

echo "<table style='width:150px; margin:auto; text-align:center; border:0px; padding:0px; border-spacing:0px;'>\n";
echo "  <tr>\n";
echo "    <td  style='white-space:nowrap; border:#fff 1px solid; background-color:#500050;'>\n";
if ($num_traderoutes == 0)
{
    echo "  <div style='text-align:center;'><a class='dis'>&nbsp;{$langvars['l_none']} &nbsp;</a></div>";
}
else
{
    $i = 0;
    while ($i < $num_traderoutes)
    {
        echo "<div style='text-align:center;'>&nbsp;<a class=mnu href=traderoute.php?engage={$traderoutes[$i]['traderoute_id']}>";
        if ($traderoutes[$i]['source_type'] == 'P')
        {
            echo $langvars['l_port'] . "&nbsp;";
        }
        elseif ($traderoutes[$i]['source_type'] == 'D')
        {
            echo "Def's ";
        }
        else
        {
            $pl_result = $db->Execute("SELECT name FROM {$pdo_db->prefix}planets WHERE planet_id = ?;", array($traderoutes[$i]['source_id']));
            Tki\Db::LogDbErrors($pdo_db, $pl_result, __LINE__, __FILE__);
            if (!$pl_result || $pl_result->RecordCount() == 0)
            {
                echo $langvars['l_unknown'];
            }
            else
            {
                $planet = $pl_result->fields;
                if ($planet['name'] === null)
                {
                    echo $langvars['l_unnamed'] . " ";
                }
                else
                {
                    echo "$planet[name] ";
                }
            }
        }

        if ($traderoutes[$i]['circuit'] == '1')
        {
            echo "=&gt;&nbsp;";
        }
        else
        {
            echo "&lt;=&gt;&nbsp;";
        }

        if ($traderoutes[$i]['dest_type'] == 'P')
        {
            echo $traderoutes[$i]['dest_id'];
        }
        elseif ($traderoutes[$i]['dest_type'] == 'D')
        {
            echo "Def's in " .  $traderoutes[$i]['dest_id'] . "";
        }
        else
        {
            $pl_dest_result = $db->Execute("SELECT name FROM {$pdo_db->prefix}planets WHERE planet_id = ?;", array($traderoutes[$i]['dest_id']));
            Tki\Db::LogDbErrors($pdo_db, $pl_dest_result, __LINE__, __FILE__);

            if (!$pl_dest_result || $pl_dest_result->RecordCount() == 0)
            {
                echo $langvars['l_unknown'];
            }
            else
            {
                $planet = $pl_dest_result->fields;
                if ($planet['name'] === null)
                {
                    echo $langvars['l_unnamed'];
                }
                else
                {
                    echo $planet['name'];
                }
            }
        }
        echo "</a>&nbsp;<br>";
        $i++;
        echo "</div>\n";
    }
}

echo "    </td>\n";
echo "  </tr>\n";
echo "  <tr>\n";
echo "    <td style='white-space:nowrap; height:2px; background-color:transparent;'></td>\n";
echo "  </tr>\n";
echo "  <tr>\n";
echo "    <td style='white-space:nowrap; border:#fff 1px solid; background-color:#500050;'>\n";
echo "      <div style='padding-left:4px; text-align:center;'><a class='mnu' href='traderoute.php'>{$langvars['l_trade_control']}</a></div>\n";
echo "    </td>\n";
echo "  </tr>\n";
echo "</table>\n";
echo "<br>\n";
echo "</td>\n";

echo "<td style='vertical-align:top;'>\n";
if ($sectorinfo['port_type'] != "none" && mb_strlen($sectorinfo['port_type']) >0)
{
    echo "<div style='color:#fff; text-align:center; font-size:14px;'>\n";
    echo "{$langvars['l_tradingport']}:&nbsp;<span style='color:#0f0;'>". ucfirst(Tki\Ports::getType($sectorinfo['port_type'], $langvars)) . "</span>\n";
    echo "<br><br>\n";
    echo "<a class='new_link' style='font-size:14px;' href='port.php' title='Dock with Space Port'><img style='width:100px; height:70px;' class='mnu' src='" . $template->getVariables('template_dir') . "/images/space_station_port.png' alt='Space Station Port'></a>\n";
    echo "</div>\n";
}
else
{
    echo "<div style='color:#fff; text-align:center;'>{$langvars['l_tradingport']}&nbsp;{$langvars['l_none']}</div>\n";
}

echo "<br>\n";

// Put all the Planets into a div container and center it.
echo "<div style='margin-left:auto; margin-right:auto; text-align:center; border:transparent 1px solid;'>\n";
echo "<div style='text-align:center; font-size:12px; color:#fff; font-weight:bold;'>{$langvars['l_planet_in_sec']} {$sectorinfo['sector_id']}</div>\n";
echo "<table style='height:150px; text-align:center; margin:auto; border:0px'>\n";
echo "  <tr>\n";

if ($num_planets > 0)
{
    $totalcount = 0;
    $curcount = 0;
    $i = 0;

    while ($i < $num_planets)
    {
        if ($planets[$i]['owner'] != 0)
        {
            // Get planet owner from database
            $sql = "SELECT * FROM {$pdo_db->prefix}ships WHERE ship_id=:ship_id LIMIT 1";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':ship_id', $planets[$i]['owner']);
            $stmt->execute();
            $planet_owner = $stmt->fetch(PDO::FETCH_ASSOC);

            $planetavg = Tki\CalcLevels::avgTech($planet_owner, "planet");

            if ($planetavg < 8)
            {
                $planetlevel = 0;
            }
            elseif ($planetavg < 12)
            {
                $planetlevel = 1;
            }
            elseif ($planetavg < 16)
            {
                $planetlevel = 2;
            }
            elseif ($planetavg < 20)
            {
                $planetlevel = 3;
            }
            else
            {
                $planetlevel = 4;
            }
        }
        else
        {
            $planetlevel = 0;
        }

        echo "<td style='margin-left:auto; margin-right:auto; vertical-align:top; width:79px; height:90px; padding:4px;'>";
        echo "<a href='planet.php?planet_id={$planets[$i]['planet_id']}'>";
        echo "<img class='mnu' title='Interact with Planet' src=\"" . $template->getVariables('template_dir') . "/images/$planettypes[$planetlevel]\" style='width:79px; height:90px; border:0' alt=\"planet\"></a><br><span style='font-size:10px; color:#fff;'>";

        if (empty ($planets[$i]['name']))
        {
            echo $langvars['l_unnamed'];
        }
        else
        {
            echo $planets[$i]['name'];
        }

        if ($planets[$i]['owner'] == 0)
        {
            echo "<br>(" . $langvars['l_unowned'] . ")";
        }
        else
        {
            echo "<br>(" . $planet_owner['character_name'] . ")";
        }
        echo "</span></td>";

        $totalcount++;
        if ($curcount == $picsperrow - 1)
        {
            echo "</tr><tr>";
            $curcount = 0;
        }
        else
        {
            $curcount++;
        }
        $i++;
    }
}
else
{
    echo "<td style='margin-left:auto; margin-right:auto; vertical-align:top'>";
    echo "<br><span style='color:white; size:1.25em'>" . $langvars['l_none'] . "</span><br><br>";
}

echo "</tr>\n";
echo "</table>\n";
echo "</div>\n";

// Put all the Planets into a div container and center it.
echo "<div style='text-align:center; border:transparent 1px solid;'>\n";
echo "<div style='text-align:center; font-size:12px; color:#fff; font-weight:bold;'>{$langvars['l_ships_in_sec']} {$sectorinfo['sector_id']}</div>\n";

if ($playerinfo['sector'] != 0)
{
    $sql  = null;
    $sql .= "SELECT {$pdo_db->prefix}ships.*, {$pdo_db->prefix}teams.team_name, {$pdo_db->prefix}teams.id ";
    $sql .= "FROM {$pdo_db->prefix}ships LEFT OUTER JOIN {$pdo_db->prefix}teams ON {$pdo_db->prefix}ships.team = {$pdo_db->prefix}teams.id ";
    $sql .= "WHERE {$pdo_db->prefix}ships.ship_id <> ? AND {$pdo_db->prefix}ships.sector = ? AND {$pdo_db->prefix}ships.on_planet='N' ";
    $sql .= "ORDER BY ?";
    $result4 = $db->Execute($sql, array($playerinfo['ship_id'], $playerinfo['sector'], $db->random));
    Tki\Db::LogDbErrors($pdo_db, $result4, __LINE__, __FILE__);

    if ($result4 !== false)
    {
        $ships_detected = 0;
        $ship_detected = null;
        while (!$result4->EOF)
        {
            $row=$result4->fields;
            $success = Tki\Scan::success($playerinfo['sensors'], $row['cloak']);
            if ($success < 5)
            {
                $success = 5;
            }
            if ($success > 95)
            {
                $success = 95;
            }
            $roll = random_int(1, 100);

            if ($roll < $success)
            {
                $shipavg = Tki\CalcLevels::avgTech($row, "ship");

                if ($shipavg < 8)
                {
                    $shiplevel = 0;
                }
                elseif ($shipavg < 12)
                {
                    $shiplevel = 1;
                }
                elseif ($shipavg < 16)
                {
                    $shiplevel = 2;
                }
                elseif ($shipavg < 20)
                {
                    $shiplevel = 3;
                }
                else
                {
                    $shiplevel = 4;
                }

                $row['shiplevel'] = $shiplevel;
                $ship_detected[] = $row;
                $ships_detected ++;
            }
            $result4->MoveNext();
        }
        if ($ships_detected <= 0)
        {
            echo "<div style='color:#fff;'>{$langvars['l_none']}</div>\n";
        }
        else
        {
            echo "<div style='padding-top:4px; padding-bottom:4px; width:500px; margin:auto; background-color:#303030;'>" . $langvars['l_main_ships_detected'] . "</div>\n";
            echo "<div style='width:498px; margin:auto; overflow:auto; height:145px; scrollbar-base-color: #303030; scrollbar-arrow-color: #fff; padding:0px;'>\n";
            echo "<table style='padding:0px; border-spacing:1px;'>\n";
            echo "  <tr>\n";

            $temp_count = count($ship_detected);
            for ($iPlayer = 0; $iPlayer < $temp_count; $iPlayer++)
            {
                echo "<td style='text-align:center; vertical-align:top; padding:1px;'>\n";
                echo "<div style='width:160px; height:120px; background: URL(" . $template->getVariables('template_dir') . "/images/bg_alpha.png) repeat; padding:1px;'>\n";
                echo "<a href=ship.php?ship_id={$ship_detected[$iPlayer]['ship_id']}>\n";
                echo "  <img class='mnu' title='Interact with Ship' src=\"" . $template->getVariables('template_dir') . "/images/", $shiptypes[$ship_detected[$iPlayer]['shiplevel']],"\" style='width:80px; height:60px; border:0px'>\n";
                echo "</a>\n";
                echo "<div style='font-size:12px; color:#fff; white-space:nowrap;'>{$ship_detected[$iPlayer]['ship_name']}<br>\n";
                echo "(<span style='color:#ff0; white-space:nowrap;'>{$ship_detected[$iPlayer]['character_name']}</span>)<br>\n";
                if ($ship_detected[$iPlayer]['team_name'])
                {
                    echo "(<span style='color:#0f0; white-space:nowrap;'>{$ship_detected[$iPlayer]['team_name']}</span>)\n";
                }
                echo "</div>\n";

                echo "</div>\n";
                echo "</td>\n";
            }
            echo "  </tr>\n";
            echo "</table>\n";
            echo "</div>\n";
        }
    }
    else
    {
        echo "<div style='color:#fff;'>{$langvars['l_none']}</div>\n";
    }
}
else
{
        echo "<div style='color:#fff;'>{$langvars['l_sector_0']}</div>\n";
}
echo "</div>";

if ($num_defences>0)
{
            echo "<div style='padding-top:4px; padding-bottom:4px; width:500px; margin:auto; background-color:#303030; text-align:center;'>" . $langvars['l_sector_def'] . "</div>\n";
            echo "<div style='width:498px; margin:auto; overflow:auto; height:125px; scrollbar-base-color: #303030; scrollbar-arrow-color: #fff; padding:0px; text-align:center;'>\n";
}
echo "<table><tr>";

if ($num_defences > 0)
{
    $totalcount = 0;
    $curcount = 0;
    $i = 0;
    while ($i < $num_defences)
    {
        $defence_id = $defences[$i]['defence_id'];
        echo "<td style='vertical-align:top; background: URL(" . $template->getVariables('template_dir') . "/images/bg_alpha.png) repeat;'><div style=' width:160px; font-size:12px; '>";
        if ($defences[$i]['defence_type'] == 'F')
        {
            echo "<a class='new_link' href='modify_defences.php?defence_id=$defence_id'><img class='mnu' src=\"" . $template->getVariables('template_dir') . "/images/fighters.png\" style='border:0px; width:80px; height:60px' alt='Fighters'></a>\n";
            $def_type = $langvars['l_fighters'];
            $mode = $defences[$i]['fm_setting'];
            if ($mode == 'attack')
            {
                $mode = $langvars['l_md_attack'];
            }
            else
            {
                $mode = $langvars['l_md_toll'];
            }
            $def_type .= $mode;
        }
        elseif ($defences[$i]['defence_type'] == 'M')
        {
            echo "<div><a href='modify_defences.php?defence_id=$defence_id'><img src=\"" . $template->getVariables('template_dir') . "/images/mines.png\" style='border:0px; width:80px; height:60px' alt='Mines'></a></div>\n";
            $def_type = $langvars['l_mines'];
        }

        $char_name = $defences[$i]['character_name'];
        $qty = $defences[$i]['quantity'];
        echo "<div style='font-size:1em; color:#fff;'>$char_name<br>( $qty $def_type )</div>\n";
        echo "</div></td>";

        $totalcount++;
        if ($curcount == $picsperrow - 1)
        {
            echo "</tr><tr>";
            $curcount = 0;
        }
        else
        {
            $curcount++;
        }
        $i++;
    }
    echo "</tr></table>";
    echo "</div>\n";
}
else
{
    echo "<td style='vertical-align:top; text-align:center;'>";
    echo "</td></tr></table>";
}
echo "<br><td style='width:200px; vertical-align:top;'>";
echo "<table style='width:140px; border:0; padding:0px; border-spacing:0px; margin-left:auto; margin-right:auto;'>\n";
echo "  <tr style='vertical-align:top'>\n";
echo "    <td style='padding:0px; width:8px; text-align:right;'><img style='width:8px; height:18px; border:0px; float:right;' src='" . $template->getVariables('template_dir') . "/images/lcorner.png' alt=''></td>\n";
echo "    <td style='padding:0px; white-space:nowrap; background-color:#400040; text-align:center; vertical-align:middle;'><span style='font-size:0.75em; color:#fff;'><strong>" . $langvars['l_cargo'] . "</strong></span></td>\n";
echo "    <td style='padding:0px; width:8px; text-align:left;'><img style='width:8px; height:18px; border:0px; float:right;' src='" . $template->getVariables('template_dir') . "/images/rcorner.png' alt=''></td>\n";
echo "  </tr>\n";
echo "</table>\n";
?>

<table style='width:150px; border:0px; padding:0px; border-spacing:0px; margin-left:auto; margin-right:auto;'>
  <tr>
    <td style='white-space:nowrap; border:#fff 1px solid; background-color:#500050; padding:0px;'>
      <table style='width:100%; border:0px; background-color:#500050; padding:1px; border-spacing:0px; margin-left:auto; margin-right:auto;'>
        <tr>
          <td style='vertical-align:middle; white-space:nowrap; text-align:left;' >&nbsp;<img style='height:12px; width:12px;' alt="<?php echo $langvars['l_ore']; ?>" src="<?php echo $template->getVariables('template_dir'); ?>/images/ore.png">&nbsp;<?php echo $langvars['l_ore']; ?>&nbsp;</td>
        </tr>
        <tr>
          <td style='vertical-align:middle; white-space:nowrap; text-align:right;'><span class=mnu>&nbsp;<?php echo number_format($playerinfo['ship_ore'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']); ?>&nbsp;</span></td>
        </tr>
        <tr>
          <td style='white-space:nowrap; text-align:left'>&nbsp;<img style='height:12px; width:12px;' alt="<?php echo $langvars['l_organics']; ?>" src="<?php echo $template->getVariables('template_dir'); ?>/images/organics.png">&nbsp;<?php echo $langvars['l_organics']; ?>&nbsp;</td>
        </tr>
        <tr>
          <td style='white-space:nowrap; text-align:right'><span class=mnu>&nbsp;<?php echo number_format($playerinfo['ship_organics'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']); ?>&nbsp;</span></td>
        </tr>
        <tr>
          <td style='white-space:nowrap; text-align:left'>&nbsp;<img style='height:12px; width:12px;' alt="<?php echo $langvars['l_goods']; ?>" src="<?php echo $template->getVariables('template_dir'); ?>/images/goods.png">&nbsp;<?php echo $langvars['l_goods']; ?>&nbsp;</td>
        </tr>
        <tr>
          <td style='white-space:nowrap; text-align:right'><span class=mnu>&nbsp;<?php echo number_format($playerinfo['ship_goods'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']); ?>&nbsp;</span></td>
        </tr>
        <tr>
          <td style='white-space:nowrap; text-align:left'>&nbsp;<img style='height:12px; width:12px;' alt="<?php echo $langvars['l_energy']; ?>" src="<?php echo $template->getVariables('template_dir'); ?>/images/energy.png">&nbsp;<?php echo $langvars['l_energy']; ?>&nbsp;</td>
        </tr>
        <tr>
          <td style='white-space:nowrap; text-align:right;'><span class=mnu>&nbsp;<?php echo number_format($playerinfo['ship_energy'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']); ?>&nbsp;</span></td>
        </tr>
        <tr>
          <td style='white-space:nowrap; text-align:left;'>&nbsp;<img style='height:12px; width:12px;' alt="<?php echo $langvars['l_colonists']; ?>" src="<?php echo $template->getVariables('template_dir'); ?>/images/colonists.png">&nbsp;<?php echo $langvars['l_colonists']; ?>&nbsp;</td>
        </tr>
        <tr>
          <td style='white-space:nowrap; text-align:right;'><span class=mnu>&nbsp;<?php echo number_format($playerinfo['ship_colonists'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']); ?>&nbsp;</span></td>
        </tr>
      </table>
    </td>
   </tr>
</table>
<br>

<?php
echo "<table style='width:140px; border:0px; padding:0px; border-spacing:0px; margin-left:auto; margin-right:auto;'>\n";
echo "  <tr style='vertical-align:top'>\n";
echo "    <td style='padding:0px; width:8px; text-align:right'><img style='width:8px; height:18px; border:0px; float:right;' src='" . $template->getVariables('template_dir') . "/images/lcorner.png' alt=''></td>\n";
echo "    <td style='padding:0px; white-space:nowrap; background-color:#400040; text-align:center; vertical-align:middle;'><span style='font-size:0.75em; color:#fff'><strong>" . $langvars['l_realspace'] . "</strong></span></td>\n";
echo "    <td style='padding:0px; width:8px; text-align:left'><img style='width:8px; height:18px; border:0px; float:left;' src='" . $template->getVariables('template_dir') . "/images/rcorner.png' alt=''></td>\n";
echo "  </tr>\n";
echo "</table>\n";

echo "<table style='width:150px; border:0px; padding:0px; border-spacing:0px; margin-left:auto; margin-right:auto;'>";
echo "<tr><td  style='white-space:nowrap; border:#fff 1px solid; background-color:#500050; padding:0px;'>";
echo '<table style="width:100%;">';

// Pull the presets for the player from the db.
$i = 0;
$debug_query = $db->Execute("SELECT * FROM {$pdo_db->prefix}presets WHERE ship_id = ?;", array($playerinfo['ship_id']));
Tki\Db::LogDbErrors($pdo_db, $debug_query, __LINE__, __FILE__);
while (!$debug_query->EOF)
{
    $presetinfo[$i] = $debug_query->fields;
    $debug_query->MoveNext();
    $i++;
}

if ($i==0)
{
    for ($x=0; $x<$tkireg->max_presets; $x++)
    {
        $i++;
        echo "<tr>\n";
        echo '<td style="text-align:left;"><a class=mnu href="rsmove.php?engage=1&amp;destination=1">=&gt;&nbsp;1</a></td>';
        echo '<td style="text-align:right;">[<a class=mnu href=preset.php>' . ucwords($langvars['l_set']) . '</a>]</td>';
        echo "</tr>\n";
        $presetinfo[$i] = '1';
    }
}
else
{
    for ($z=0; $z<$i; $z++)
    {
        echo "<tr>\n";
        echo '<td style="text-align:left;"><a class=mnu href="rsmove.php?engage=1&amp;destination=' . $presetinfo[$z]['preset'] . '">=&gt;&nbsp;' . $presetinfo[$z]['preset'] . '</a></td>';
        echo '<td style="text-align:right;">[<a class=mnu href=preset.php>' . ucwords($langvars['l_set']) . '</a>]</td>';
        echo "</tr>\n";
        $debug_query->MoveNext();
    }
}
echo "</table></td></tr>";
echo "  <tr>\n";
echo "    <td style='white-space:nowrap; height:2px; background-color:transparent;'></td>\n";
echo "  </tr>\n";
echo "<tr><td  style='white-space:nowrap; border:#fff 1px solid; background-color:#500050;'>";
echo '&nbsp;<a class=mnu href="rsmove.php">=&gt;&nbsp;';
echo $langvars['l_main_other'];
echo "</a>&nbsp;<br>";
echo "</td></tr></table><br>";
echo "<table style='width:140px; border:0px; padding:0px; border-spacing:0px;margin-left:auto; margin-right:auto;'>\n";
echo "  <tr style='vertical-align:top'>\n";
echo "    <td style='padding:0px; width:8px; float:right;'><img style='width:8px; height:18px; border:0px; float:right' src='" . $template->getVariables('template_dir') . "/images/lcorner.png' alt=''></td>\n";
echo "    <td style='padding:0px; white-space:nowrap; background-color:#400040; text-align:center; vertical-align:middle;'><span style='font-size:0.75em; color:#fff;'><strong>" . $langvars['l_main_warpto'] . "</strong></span></td>\n";
echo "    <td style='padding:0px; width:8px; float:left;'><img style='width:8px; height:18px; border:0px; float:left;' src='" . $template->getVariables('template_dir') . "/images/rcorner.png' alt=''></td>\n";
echo "  </tr>\n";
echo "</table>\n";

echo "<table style='width:150px; border:0px; padding:0px; border-spacing:0px; margin-left:auto; margin-right:auto;'>";
echo "<tr><td style='white-space:nowrap; border:#fff 1px solid; background-color:#500050; text-align:center; padding:0px;'>";
echo "<div class=mnu>";

if (!$num_links)
{
    echo "&nbsp;<a class=dis>" . $langvars['l_no_warplink'] . "</a>&nbsp;<br>";
}
else
{
    echo "<table style='width:100%;'>\n";
    for ($i = 0; $i < $num_links; $i++)
    {
        //echo "&nbsp;<a class=\"mnu\" href=\"move.php?sector=$links[$i]\">=&gt;&nbsp;$links[$i]</a>&nbsp;<a class=dis href=\"lrscan.php?sector=$links[$i]\">[" . $langvars['l_scan'] . "]</a>&nbsp;<br>";

        echo "<tr>\n";
        echo "  <td style='text-align:left;'><a class='mnu' href='move.php?sector={$links[$i]}'>=&gt;&nbsp;$links[$i]</a></td>\n";
        echo "  <td style='text-align:right;'>[<a class='mnu' href='lrscan.php?sector={$links[$i]}'>" . $langvars['l_scan'] . "</a>]</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
}
echo "</div>";
echo "</td></tr>";
echo "  <tr>\n";
echo "    <td style='white-space:nowrap; height:2px; background-color:transparent;'></td>\n";
echo "  </tr>\n";
echo "<tr><td style='white-space:nowrap; border:#fff 1px solid; background-color:#500050; text-align:center;'>";
echo "<div class=mnu>";
echo "&nbsp;<a class=dis href=\"lrscan.php?sector=*\">[" . $langvars['l_fullscan'] . "]</a>&nbsp;<br>";

echo "</div>
</td></tr>
</table>
</td>
</tr>
</table>";

Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
