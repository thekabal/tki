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
// File: traderoute.php

require_once './common.php';
Tki\Login::checkLogin($pdo_db, $lang, $tkireg, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('traderoutes', 'common', 'global_includes', 'global_funcs', 'footer', 'bounty', 'regional'));
$title = $langvars['l_tdr_title'];
Tki\Header::display($pdo_db, $lang, $template, $title);

echo "<h1>" . $title . "</h1>\n";

$portfull = null; // This fixes an error of undefined variables on 1518

// Get playerinfo from database
$sql = "SELECT * FROM ::prefix::ships WHERE email=:email LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':email', $_SESSION['username'], \PDO::PARAM_STR);
$stmt->execute();
$playerinfo = $stmt->fetch(PDO::FETCH_ASSOC);

$result = $db->Execute("SELECT * FROM {$db->prefix}traderoutes WHERE owner = ?;", array($playerinfo['ship_id']));
Tki\Db::LogDbErrors($pdo_db, $result, __LINE__, __FILE__);
$num_traderoutes = $result->RecordCount();

unset($traderoutes);
$traderoutes = array();
$i = 0;
while (!$result->EOF)
{
    $i = array_push($traderoutes, $result->fields);
    // $traderoutes[$i] = $result->fields;
    // $i++;
    $result->MoveNext();
}

$freeholds = Tki\CalcLevels::holds($playerinfo['hull'], $tkireg) - $playerinfo['ship_ore'] - $playerinfo['ship_organics'] - $playerinfo['ship_goods'] - $playerinfo['ship_colonists'];
$maxholds = Tki\CalcLevels::holds($playerinfo['hull'], $tkireg);
$maxenergy = Tki\CalcLevels::energy($playerinfo['power'], $tkireg);
if ($playerinfo['ship_colonists'] < 0 || $playerinfo['ship_ore'] < 0 || $playerinfo['ship_organics'] < 0 || $playerinfo['ship_goods'] < 0 || $playerinfo['ship_energy'] < 0 || $freeholds < 0)
{
    if ($playerinfo['ship_colonists'] < 0 || $playerinfo['ship_colonists'] > $maxholds)
    {
        Tki\AdminLog::writeLog($pdo_db, LOG_ADMIN_ILLEGVALUE, "$playerinfo[ship_name]|$playerinfo[ship_colonists]|colonists|$maxholds");
        $playerinfo['ship_colonists'] = 0;
    }

    if ($playerinfo['ship_ore'] < 0 || $playerinfo['ship_ore'] > $maxholds)
    {
        Tki\AdminLog::writeLog($pdo_db, LOG_ADMIN_ILLEGVALUE, "$playerinfo[ship_name]|$playerinfo[ship_ore]|ore|$maxholds");
        $playerinfo['ship_ore'] = 0;
    }

    if ($playerinfo['ship_organics'] < 0 || $playerinfo['ship_organics'] > $maxholds)
    {
        Tki\AdminLog::writeLog($pdo_db, LOG_ADMIN_ILLEGVALUE, "$playerinfo[ship_name]|$playerinfo[ship_organics]|organics|$maxholds");
        $playerinfo['ship_organics'] = 0;
    }

    if ($playerinfo['ship_goods'] < 0 || $playerinfo['ship_goods'] > $maxholds)
    {
        Tki\AdminLog::writeLog($pdo_db, LOG_ADMIN_ILLEGVALUE, "$playerinfo[ship_name]|$playerinfo[ship_goods]|goods|$maxholds");
        $playerinfo['ship_goods'] = 0;
    }

    if ($playerinfo['ship_energy'] < 0 || $playerinfo['ship_energy'] > $maxenergy)
    {
        Tki\AdminLog::writeLog($pdo_db, LOG_ADMIN_ILLEGVALUE, "$playerinfo[ship_name]|$playerinfo[ship_energy]|energy|$maxenergy");
        $playerinfo['ship_energy'] = 0;
    }

    if ($freeholds < 0)
    {
        $freeholds = 0;
    }

    $update1 = $db->Execute("UPDATE {$db->prefix}ships SET ship_ore=?, ship_organics=?, ship_goods=?, ship_energy=?, ship_colonists=? WHERE ship_id=?;", array($playerinfo['ship_ore'], $playerinfo['ship_organics'], $playerinfo['ship_goods'], $playerinfo['ship_energy'], $playerinfo['ship_colonists'], $playerinfo['ship_id']));
    Tki\Db::LogDbErrors($pdo_db, $update1, __LINE__, __FILE__);
}

// Default to 1 run if we don't get a valid repeat value.
$tr_repeat = 1;

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$tr_repeat = null;
$tr_repeat = (int) filter_input(INPUT_POST, 'tr_repeat', FILTER_SANITIZE_NUMBER_INT);
if (mb_strlen(trim($tr_repeat)) === 0)
{
    $tr_repeat = 0;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$command = null;
$command = filter_input(INPUT_GET, 'command', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($command)) === 0)
{
    $command = false;
}

$engage = null;
$engage = filter_input(INPUT_POST, 'engage', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($engage)) === 0)
{
    $engage = false;
}

$ptype1 = null;
$ptype1 = filter_input(INPUT_POST, 'ptype1', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($ptype1)) === 0)
{
    $ptype1 = false;
}

$ptype2 = null;
$ptype2 = filter_input(INPUT_POST, 'ptype2', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($ptype2)) === 0)
{
    $ptype2 = false;
}

$port_id1 = null;
$port_id1 = filter_input(INPUT_POST, 'port_id1', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($port_id1)) === 0)
{
    $port_id1 = false;
}

$port_id2 = null;
$port_id2 = filter_input(INPUT_POST, 'port_id2', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($port_id2)) === 0)
{
    $port_id2 = false;
}

$team_planet_id1 = null;
$team_planet_id1 = filter_input(INPUT_POST, 'team_planet_id1', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($team_planet_id1)) === 0)
{
    $team_planet_id1 = false;
}

$team_planet_id2 = null;
$team_planet_id2 = filter_input(INPUT_POST, 'team_planet_id2', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($team_planet_id2)) === 0)
{
    $team_planet_id2 = false;
}

$planet_id1 = null;
$planet_id1 = filter_input(INPUT_POST, 'planet_id1', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($planet_id1)) === 0)
{
    $planet_id1 = false;
}

$planet_id2 = null;
$planet_id2 = filter_input(INPUT_POST, 'planet_id2', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($planet_id2)) === 0)
{
    $planet_id2 = false;
}

$move_type = null;
$move_type = filter_input(INPUT_POST, 'move_type', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($move_type)) === 0)
{
    $move_type = false;
}

$circuit_type = null;
$circuit_type = filter_input(INPUT_POST, 'circuit_type', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($circuit_type)) === 0)
{
    $circuit_type = false;
}

$editing = null;
$editing = filter_input(INPUT_POST, 'editing', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($editing)) === 0)
{
    $editing = false;
}

$traderoute_id = null;
$traderoute_id = filter_input(INPUT_GET, 'traderoute_id', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($traderoute_id)) === 0)
{
    $traderoute_id = false;
}

$confirm = null;
$confirm = filter_input(INPUT_GET, 'confirm', FILTER_SANITIZE_STRING);
if (mb_strlen(trim($confirm)) === 0)
{
    $confirm = false;
}


if ($command == 'new')
{
    // Displays new trade route form
    \Tki\TraderouteBuild::new($pdo_db, $db, $lang, $tkireg, $template, $num_traderoutes, $playerinfo, null);
}
elseif ($command == 'create')
{
    // Enters new route in db
    \Tki\TraderouteBuild::create($pdo_db, $db, $lang, $tkireg, $template, $playerinfo, $num_traderoutes, $ptype1, $ptype2, $port_id1, $port_id2, $team_planet_id1, $team_planet_id2, $move_type, $circuit_type, $editing, $planet_id1, $planet_id2);
}
elseif ($command == 'edit')
{
    // Displays new trade route form, edit
    \Tki\TraderouteBuild::new($pdo_db, $db, $lang, $tkireg, $template, $num_traderoutes, $playerinfo, $traderoute_id);
}
elseif ($command == 'delete')
{
    // Displays delete info
    \Tki\TraderouteDelete::prime($pdo_db, $db, $lang, $langvars, $tkireg, $template, $playerinfo, $confirm, $traderoute_id);
}
elseif ($command == 'settings')
{
    // Global traderoute settings form
    \Tki\TraderouteSettings::before($pdo_db, $lang, $tkireg, $template, $playerinfo);
}
elseif ($command == 'setsettings')
{
    // Enters settings in db
    \Tki\TraderouteSettings::after($pdo_db, $db, $lang, $tkireg, $template, $playerinfo, $colonists, $fighters, $torps, $energy);
}
elseif ($engage !== null)
{
    // Perform trade route
    $i = $tr_repeat;
    while ($i > 0)
    {
        $result = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE email = ?;", array($_SESSION['username']));
        Tki\Db::LogDbErrors($pdo_db, $result, __LINE__, __FILE__);
        $playerinfo = $result->fields;
        \Tki\Traderoute::engage($pdo_db, $db, $lang, $i, $langvars, $tkireg, $playerinfo, $engage, $dist, $traderoutes, $portfull, $template);
        $i--;
    }
}

if ($command != 'delete')
{
    $langvars['l_tdr_newtdr'] = str_replace("[here]", "<a href='traderoute.php?command=new'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_newtdr']);
    echo "<p>" . $langvars['l_tdr_newtdr'] . "<p>";
    $langvars['l_tdr_modtdrset'] = str_replace("[here]", "<a href='traderoute.php?command=settings'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_modtdrset']);
    echo "<p>" . $langvars['l_tdr_modtdrset'] . "<p>";
}
else
{
    $langvars['l_tdr_confdel'] = str_replace("[here]", "<a href='traderoute.php?command=delete&amp;confirm=yes&amp;traderoute_id=" . $traderoute_id . "'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_confdel']);
    echo "<p>" . $langvars['l_tdr_confdel'] . "<p>";
}

if ($num_traderoutes == 0)
{
    echo $langvars['l_tdr_noactive'] . "<p>";
}
else
{
    echo '<table border=1 cellspacing=1 cellpadding=2 width="100%" align="center">' .
         '<tr bgcolor=' . $tkireg->color_line2 . '><td align="center" colspan=7><strong><font color=white>
         ';

    if ($command != 'delete')
    {
        echo $langvars['l_tdr_curtdr'];
    }
    else
    {
        echo $langvars['l_tdr_deltdr'];
    }

    echo "</font></strong>" .
         "</td></tr>" .
         "<tr align='center' bgcolor='" . $tkireg->color_line2 . "'>" .
         "<td><font size=2 color=white><strong>" . $langvars['l_tdr_src'] . "</strong></font></td>" .
         "<td><font size=2 color=white><strong>" . $langvars['l_tdr_srctype'] . "</strong></font></td>" .
         "<td><font size=2 color=white><strong>" . $langvars['l_tdr_dest'] . "</strong></font></td>" .
         "<td><font size=2 color=white><strong>" . $langvars['l_tdr_desttype'] . "</strong></font></td>" .
         "<td><font size=2 color=white><strong>" . $langvars['l_tdr_move'] . "</strong></font></td>" .
         "<td><font size=2 color=white><strong>" . $langvars['l_tdr_circuit'] . "</strong></font></td>" .
         "<td><font size=2 color=white><strong>" . $langvars['l_tdr_change'] . "</strong></font></td>" .
         "</tr>";

    $i = 0;
    $curcolor = $tkireg->color_line1;
    while ($i < $num_traderoutes)
    {
        echo "<tr bgcolor='" . $curcolor . "'>";
        if ($curcolor == $tkireg->color_line1)
        {
            $curcolor = $tkireg->color_line2;
        }
        else
        {
            $curcolor = $tkireg->color_line1;
        }

        echo "<td><font size=2 color=white>";
        if ($traderoutes[$i]['source_type'] == 'P')
        {
            echo "&nbsp;" . $langvars['l_tdr_portin'] . " <a href=rsmove.php?engage=1&destination=" . $traderoutes[$i]['source_id'] . ">" . $traderoutes[$i]['source_id'] . "</a></font></td>";
        }
        else
        {
            $result = $db->Execute("SELECT name, sector_id FROM {$db->prefix}planets WHERE planet_id=?;", array($traderoutes[$i]['source_id']));
            Tki\Db::LogDbErrors($pdo_db, $result, __LINE__, __FILE__);
            if ($result)
            {
                $planet1 = $result->fields;
                echo "&nbsp;" . $langvars['l_tdr_planet'] . " <strong>$planet1[name]</strong>" . $langvars['l_tdr_within'] . "<a href=\"rsmove.php?engage=1&destination=" . $planet1['sector_id'] . "\">" . $planet1['sector_id'] . "</a></font></td>";
            }
            else
            {
                echo "&nbsp;" . $langvars['l_tdr_nonexistance'] . "</font></td>";
            }
        }

        echo "<td align='center'><font size=2 color=white>";
        if ($traderoutes[$i]['source_type'] == 'P')
        {
            $result = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id = ?;", array($traderoutes[$i]['source_id']));
            Tki\Db::LogDbErrors($pdo_db, $result, __LINE__, __FILE__);
            $port1 = $result->fields;
            echo "&nbsp;" . Tki\Ports::getType($port1['port_type'], $langvars) . "</font></td>";
        }
        else
        {
            if (empty($planet1))
            {
                echo "&nbsp;" . $langvars['l_tdr_na'] . "</font></td>";
            }
            else
            {
                echo "&nbsp;" . $langvars['l_tdr_cargo'] . "</font></td>";
            }
        }

        echo "<td><font size=2 color=white>";

        if ($traderoutes[$i]['dest_type'] == 'P')
        {
            echo "&nbsp;" . $langvars['l_tdr_portin'] . " <a href=\"rsmove.php?engage=1&destination=" . $traderoutes[$i]['dest_id'] . "\">" . $traderoutes[$i]['dest_id'] . "</a></font></td>";
        }
        else
        {
            $result = $db->Execute("SELECT name, sector_id FROM {$db->prefix}planets WHERE planet_id=?;", array($traderoutes[$i]['dest_id']));
            Tki\Db::LogDbErrors($pdo_db, $result, __LINE__, __FILE__);
            if ($result)
            {
                $planet2 = $result->fields;
                echo "&nbsp;" . $langvars['l_tdr_planet'] . " <strong>$planet2[name]</strong>" . $langvars['l_tdr_within'] . "<a href=\"rsmove.php?engage=1&destination=" . $planet2['sector_id'] . "\">" . $planet2['sector_id'] . "</a></font></td>";
            }
            else
            {
                echo "&nbsp;" . $langvars['l_tdr_nonexistance'] . "</font></td>";
            }
        }

        echo "<td align='center'><font size=2 color=white>";
        if ($traderoutes[$i]['dest_type'] == 'P')
        {
            $result = $db->Execute("SELECT * FROM {$db->prefix}universe WHERE sector_id = ?;", array($traderoutes[$i]['dest_id']));
            Tki\Db::LogDbErrors($pdo_db, $result, __LINE__, __FILE__);
            $port2 = $result->fields;
            echo "&nbsp;" . Tki\Ports::getType($port2['port_type'], $langvars) . "</font></td>";
        }
        else
        {
            if (empty($planet2))
            {
                echo "&nbsp;" . $langvars['l_tdr_na'] . "</font></td>";
            }
            else
            {
                echo "&nbsp;";
                if ($playerinfo['trade_colonists'] == 'N' && $playerinfo['trade_fighters'] == 'N' && $playerinfo['trade_torps'] == 'N')
                {
                    echo $langvars['l_tdr_none'];
                }
                else
                {
                    if ($playerinfo['trade_colonists'] == 'Y')
                    {
                        echo $langvars['l_tdr_colonists'];
                    }

                    if ($playerinfo['trade_fighters'] == 'Y')
                    {
                        if ($playerinfo['trade_colonists'] == 'Y')
                        {
                            echo ", ";
                        }

                        echo $langvars['l_tdr_fighters'];
                    }

                    if ($playerinfo['trade_torps'] == 'Y')
                    {
                        echo "<br>" . $langvars['l_tdr_torps'];
                    }
                }

                echo "</font></td>";
            }
        }

        echo "<td align='center'><font size=2 color=white>";
        if ($traderoutes[$i]['move_type'] == 'R')
        {
            echo "&nbsp;RS, ";

            if ($traderoutes[$i]['source_type'] == 'P')
            {
                $src = $port1;
            }
            else
            {
                $src = $planet1['sector_id'];
            }

            if ($traderoutes[$i]['dest_type'] == 'P')
            {
                $dst = $port2;
            }
            else
            {
                $dst = $planet2['sector_id'];
            }

            $dist = \Tki\TraderouteDistance::calc($pdo_db, $traderoutes[$i]['source_type'], $traderoutes[$i]['dest_type'], $src, $dst, $traderoutes[$i]['circuit'], $playerinfo, $tkireg);

            $langvars['l_tdr_escooped_temp'] = str_replace("[tdr_dist_triptime]", $dist['triptime'], $langvars['l_tdr_escooped']);
            $langvars['l_tdr_escooped2_temp'] = str_replace("[tdr_dist_scooped]", $dist['scooped'], $langvars['l_tdr_escooped2']);
            echo $langvars['l_tdr_escooped_temp'] . "<br>" . $langvars['l_tdr_escooped2_temp'];

            echo "</font></td>";
        }
        else
        {
            echo "&nbsp;" . $langvars['l_tdr_warp'];

            if ($traderoutes[$i]['circuit'] == '1')
            {
                echo ", 2 " . $langvars['l_tdr_turns'];
            }
            else
            {
                echo ", 4 " . $langvars['l_tdr_turns'];
            }

            echo "</font></td>";
        }

        echo "<td align='center'><font size=2 color=white>";

        if ($traderoutes[$i]['circuit'] == '1')
        {
            echo "&nbsp;1 " . $langvars['l_tdr_way'] . "</font></td>";
        }
        else
        {
            echo "&nbsp;2 " . $langvars['l_tdr_ways'] . "</font></td>";
        }

        echo "<td align='center'><font size=2 color=white>";
        echo "<a href=\"traderoute.php?command=edit&traderoute_id=" . $traderoutes[$i]['traderoute_id'] . "\">";
        echo $langvars['l_edit'] . "</a><br><a href=\"traderoute.php?command=delete&traderoute_id=" . $traderoutes[$i]['traderoute_id'] . "\">";
        echo $langvars['l_tdr_del'] . "</a></font></td></tr>";

        $i++;
    }

    echo "</table><p>";
}

echo "<div style='text-align:left;'>\n";
Tki\Text::gotoMain($pdo_db, $lang);
echo "</div>\n";

Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
