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
// File: lrscan.php

require_once './common.php';

Tki\Login::checkLogin($pdo_db, $lang, $tkireg, $template);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('main', 'lrscan', 'common', 'global_includes', 'global_funcs', 'combat', 'footer', 'news', 'regional'));
$title = $langvars['l_lrs_title'];
Tki\Header::display($pdo_db, $lang, $template, $title);
echo "<h1>" . $title . "</h1>\n";

if (array_key_exists('sector', $_GET))
{
    $sector = htmlentities($_GET['sector'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
else
{
    $sector = null;
}

// Get playerinfo from database
$sql = "SELECT * FROM {$pdo_db->prefix}ships WHERE email=:email LIMIT 1";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':email', $_SESSION['username']);
$stmt->execute();
$playerinfo = $stmt->fetch(PDO::FETCH_ASSOC);

if ($sector == "*")
{
    $num_links = 0;

    if (!$tkireg->allow_fullscan)
    {
        echo $langvars['l_lrs_nofull'] . "<br><br>";
        Tki\Text::gotomain($pdo_db, $lang);
        Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
        die();
    }

    if ($playerinfo['turns'] < $tkireg->fullscan_cost)
    {
        $langvars['l_lrs_noturns'] = str_replace("[turns]", $tkireg->fullscan_cost, $langvars['l_lrs_noturns']);
        echo $langvars['l_lrs_noturns'] . "<br><br>";
        Tki\Text::gotomain($pdo_db, $lang);
        Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
        die();
    }

    echo $langvars['l_lrs_used'] . " " . number_format($tkireg->fullscan_cost, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_lrs_turns'] . " " . number_format($playerinfo['turns'] - $tkireg->fullscan_cost, 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']) . " " . $langvars['l_lrs_left'] . ".<br><br>";

    // Deduct the appropriate number of turns
    $resx = $db->Execute("UPDATE {$db->prefix}ships SET turns = turns - ?, turns_used = turns_used + ? WHERE ship_id = ?;", array($tkireg->fullscan_cost, $tkireg->fullscan_cost, $playerinfo['ship_id']));
    Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);

    // User requested a full long range scan
    $langvars['l_lrs_reach'] = str_replace("[sector]", $playerinfo['sector'], $langvars['l_lrs_reach']);
    echo $langvars['l_lrs_reach'] . "<br><br>";

    // Get sectors which can be reached from the player's current sector
    $result = $db->Execute("SELECT * FROM {$db->prefix}links WHERE link_start = ? ORDER BY link_dest;", array($playerinfo['sector']));
    Tki\Db::LogDbErrors($pdo_db, $result, __LINE__, __FILE__);
    echo "<table border=0 cellspacing=0 cellpadding=0 width=\"100%\">";
    echo "  <tr bgcolor=\"$tkireg->color_header\">\n";
    echo "    <td><strong>" . $langvars['l_sector'] . "</strong></td>\n";
    echo "    <td></td>\n";
    echo "    <td><strong>" . $langvars['l_lrs_links'] . "</strong></td>\n";
    echo "    <td><strong>" . $langvars['l_lrs_ships'] . "</strong></td>\n";
    echo "    <td colspan=2><strong>" . $langvars['l_port'] . "</strong></td>\n";
    echo "    <td><strong>" . $langvars['l_planets'] . "</strong></td>\n";
    echo "    <td><strong>" . $langvars['l_mines'] . "</strong></td>\n";
    echo "    <td><strong>" . $langvars['l_fighters'] . "</strong></td>";

    if ($playerinfo['dev_lssd'] == 'Y')
    {
        echo "    <td><strong>" . $langvars['l_lss'] . "</strong></td>";
    }

    echo "  </tr>";
    $tkireg->color = $tkireg->color_line1;
    while (!$result->EOF)
    {
        $row = $result->fields;
        // Get number of sectors which can be reached from scanned sector
        $result2 = $db->Execute("SELECT COUNT(*) AS count FROM {$db->prefix}links WHERE link_start = ?;", array($row['link_dest']));
        Tki\Db::LogDbErrors($pdo_db, $result2, __LINE__, __FILE__);
        $row2 = $result2->fields;
        $num_links = $row2['count'];

        // Get number of ships in scanned sector
        $result2 = $db->Execute("SELECT COUNT(*) AS count FROM {$db->prefix}ships WHERE sector = ? AND on_planet = 'N' and ship_destroyed = 'N';", array($row['link_dest']));
        Tki\Db::LogDbErrors($pdo_db, $result2, __LINE__, __FILE__);
        $row2 = $result2->fields;
        $num_ships = $row2['count'];

        // Get sectorinfo from database
        $sql = "SELECT * FROM {$pdo_db->prefix}universe WHERE sector_id=:sector_id LIMIT 1";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':sector_id', array($row['link_dest']));
        $stmt->execute();
        $sectorinfo = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get port type and discover the presence of a planet in scanned sector
        $result3 = $db->Execute("SELECT planet_id FROM {$db->prefix}planets WHERE sector_id = ?;", array($row['link_dest']));
        Tki\Db::LogDbErrors($pdo_db, $result3, __LINE__, __FILE__);
        $resultSDa = $db->Execute("SELECT SUM(quantity) as mines from {$db->prefix}sector_defence WHERE sector_id = ? and defence_type = 'M';", array($row['link_dest']));
        Tki\Db::LogDbErrors($pdo_db, $resultSDa, __LINE__, __FILE__);
        $resultSDb = $db->Execute("SELECT SUM(quantity) as fighters from {$db->prefix}sector_defence WHERE sector_id = ? and defence_type = 'F';", array($row['link_dest']));
        Tki\Db::LogDbErrors($pdo_db, $resultSDb, __LINE__, __FILE__);

        $defM = $resultSDa->fields;
        $defF = $resultSDb->fields;
        $port_type = $sectorinfo['port_type'];
        $has_planet = $result3->RecordCount();
        $has_mines = number_format($defM['mines'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
        $has_fighters = number_format($defF['fighters'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);

        if ($port_type != "none")
        {
            $icon_alt_text = ucfirst(Tki\Ports::getType($port_type, $langvars));
            $icon_port_type_name = $port_type . ".png";
            $image_string = "<img align=absmiddle height=12 width=12 alt=\"$icon_alt_text\" src=\"". $template->getVariables('template_dir') . "/images/$icon_port_type_name\">&nbsp;";
        }
        else
        {
            $image_string = "&nbsp;";
        }

        echo "<tr bgcolor=\"$tkireg->color\"><td><a href='move.php?sector=" . $row['link_dest'] . "'>" . $row['link_dest'] . "</a></td><td><a href='lrscan.php?sector=" . $row['link_dest'] . "'>Scan</a></td><td>$num_links</td><td>$num_ships</td><td width=12>$image_string</td><td>" . Tki\Ports::getType($port_type, $langvars) . "</td><td>$has_planet</td><td>$has_mines</td><td>$has_fighters</td>";
        if ($playerinfo['dev_lssd'] == 'Y')
        {
            $resx = $db->SelectLimit("SELECT * from {$db->prefix}movement_log WHERE ship_id <> ? AND sector_id = ? ORDER BY time DESC", 1, -1, array('ship_id' => $playerinfo['ship_id'], 'sector_id' => $row['link_dest']));
            Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);
            if (!$resx)
            {
                echo "<td>None</td>";
            }
            else
            {
                $myrow = $resx->fields;
                $res = $db->Execute("SELECT character_name FROM {$db->prefix}ships WHERE ship_id = ?;", array($myrow['ship_id']));
                Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
                if ($res)
                {
                    $row = $res->fields;
                    $character_name = $row['character_name'];
                }
                else
                {
                    $character_name = "Unknown";
                }
                echo "<td>" . $character_name . "</td>";
            }
        }

        echo "</tr>";
        if ($tkireg->color == $tkireg->color_line1)
        {
            $tkireg->color = $tkireg->color_line2;
        }
        else
        {
            $tkireg->color = $tkireg->color_line1;
        }
        $result->MoveNext();
    }
    echo "</table>";

    if ($num_links == 0)
    {
        echo $langvars['l_none'];
    }
    else
    {
        echo "<br>" . $langvars['l_lrs_click'];
    }
}
else
{
    // User requested a single sector (standard) long range scan

    // Get playerinfo from database
    $sql = "SELECT * FROM {$pdo_db->prefix}universe WHERE sector_id=:sector_id LIMIT 1";
    $stmt = $pdo_db->prepare($sql);
    $stmt->bindParam(':sector_id', $sector);
    $stmt->execute();
    $sectorinfo = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get sectors which can be reached through scanned sector
    $result3 = $db->Execute("SELECT link_dest FROM {$db->prefix}links WHERE link_start = ? ORDER BY link_dest ASC;", array($sector));
    Tki\Db::LogDbErrors($pdo_db, $result3, __LINE__, __FILE__);
    $i = 0;

    while (!$result3->EOF)
    {
        $links[$i] = $result3->fields['link_dest'];
        $i++;
        $result3->MoveNext();
    }
    $num_links = $i;

    // Get sectors which can be reached from the player's current sector
    $result3a = $db->Execute("SELECT link_dest FROM {$db->prefix}links WHERE link_start = ?;", array($playerinfo['sector']));
    Tki\Db::LogDbErrors($pdo_db, $result3a, __LINE__, __FILE__);
    $i = 0;
    $flag = 0;

    while (!$result3a->EOF)
    {
        if ($result3a->fields['link_dest'] == $sector)
        {
            $flag = 1;
        }
        $i++;
        $result3a->MoveNext();
    }

    if ($flag == 0)
    {
        echo $langvars['l_lrs_cantscan'] . "<br><br>";
        Tki\Text::gotomain($pdo_db, $lang);
        die();
    }

    echo "<table border=0 cellspacing=0 cellpadding=0 width=\"100%\">";
    echo "<tr bgcolor=\"$tkireg->color_header\"><td><strong>" . $langvars['l_sector'] . " " . $sector;
    if ($sectorinfo['sector_name'] !== null)
    {
        echo " ($sectorinfo[sector_name])";
    }
    echo "</strong></tr>";
    echo "</table><br>";

    echo "<table border=0 cellspacing=0 cellpadding=0 width=\"100%\">";
    echo "<tr bgcolor=\"$tkireg->color_line2\"><td><strong>" . $langvars['l_links'] . "</strong></td></tr>";
    echo "<tr><td>";
    if ($num_links == 0)
    {
        echo $langvars['l_none'];
    }
    else
    {
        for ($i = 0; $i < $num_links; $i++)
        {
            echo "$links[$i]";
            if ($i + 1 != $num_links)
            {
                echo ", ";
            }
        }
    }

    echo "</td></tr>";
    echo "<tr bgcolor=\"$tkireg->color_line2\"><td><strong>" . $langvars['l_ships'] . "</strong></td></tr>";
    echo "<tr><td>";
    if ($sector != 0)
    {
        // Get ships located in the scanned sector
        $result4 = $db->Execute("SELECT ship_id, ship_name, character_name, cloak FROM {$db->prefix}ships WHERE sector = ? AND on_planet = 'N';", array($sector));
        Tki\Db::LogDbErrors($pdo_db, $result4, __LINE__, __FILE__);
        if ($result4->EOF)
        {
            echo $langvars['l_none'];
        }
        else
        {
            $num_detected = 0;
            while (!$result4->EOF)
            {
                $row = $result4->fields;
                // Display other ships in sector - unless they are successfully cloaked
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
                    $num_detected++;
                    echo $row['ship_name'] . "(" . $row['character_name'] . ")<br>";
                }
                $result4->MoveNext();
            }

            if (!$num_detected)
            {
                echo $langvars['l_none'];
            }
        }
    }
    else
    {
        echo $langvars['l_lrs_zero'];
    }

    echo "</td></tr>";
    echo "<tr bgcolor=\"$tkireg->color_line2\"><td><strong>" . $langvars['l_port'] . "</strong></td></tr>";
    echo "<tr><td>";
    if ($sectorinfo['port_type'] == "none")
    {
        echo $langvars['l_none'];
    }
    else
    {
        if ($sectorinfo['port_type'] != "none")
        {
            $port_type = $sectorinfo['port_type'];
            $icon_alt_text = ucfirst(Tki\Ports::getType($port_type, $langvars));
            $icon_port_type_name = $port_type . ".png";
            $image_string = "<img align=absmiddle height=12 width=12 alt=\"$icon_alt_text\" src=\"images/$icon_port_type_name\">";
        }
        echo "$image_string " . Tki\Ports::getType($sectorinfo['port_type'], $langvars);
    }
    echo "</td></tr>";
    echo "<tr bgcolor=\"$tkireg->color_line2\"><td><strong>" . $langvars['l_planets'] . "</strong></td></tr>";
    echo "<tr><td>";
    $query = $db->Execute("SELECT name, owner FROM {$db->prefix}planets WHERE sector_id = ?;", array($sectorinfo['sector_id']));
    Tki\Db::LogDbErrors($pdo_db, $query, __LINE__, __FILE__);

    if ($query->EOF)
    {
        echo $langvars['l_none'];
    }

    while (!$query->EOF)
    {
        $planet = $query->fields;
        if (empty ($planet['name']))
        {
            echo $langvars['l_unnamed'];
        }
        else
        {
            echo "$planet[name]";
        }

        if ($planet['owner'] == 0)
        {
            echo " (" . $langvars['l_unowned'] . ")";
        }
        else
        {
            $result5 = $db->Execute("SELECT character_name FROM {$db->prefix}ships WHERE ship_id = ?;", array($planet['owner']));
            Tki\Db::LogDbErrors($pdo_db, $result5, __LINE__, __FILE__);
            $planet_owner_name = $result5->fields;
            echo " ($planet_owner_name[character_name])";
        }
        $query->MoveNext();
    }

    $resultSDa = $db->Execute("SELECT SUM(quantity) as mines from {$db->prefix}sector_defence WHERE sector_id = ? and defence_type = 'M';", array($sector));
    Tki\Db::LogDbErrors($pdo_db, $resultSDa, __LINE__, __FILE__);
    $resultSDb = $db->Execute("SELECT SUM(quantity) as fighters from {$db->prefix}sector_defence WHERE sector_id = ? and defence_type = 'F';", array($sector));
    Tki\Db::LogDbErrors($pdo_db, $resultSDb, __LINE__, __FILE__);
    $defM = $resultSDa->fields;
    $defF = $resultSDb->fields;

    echo "</td></tr>";
    echo "<tr bgcolor=\"$tkireg->color_line1\"><td><strong>" . $langvars['l_mines'] . "</strong></td></tr>";
    $has_mines =  number_format($defM['mines'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
    echo "<tr><td>" . $has_mines;
    echo "</td></tr>";
    echo "<tr bgcolor=\"$tkireg->color_line2\"><td><strong>" . $langvars['l_fighters'] . "</strong></td></tr>";
    $has_fighters =  number_format($defF['fighters'], 0, $langvars['local_number_dec_point'], $langvars['local_number_thousands_sep']);
    echo "<tr><td>" . $has_fighters;
    echo "</td></tr>";
    if ($playerinfo['dev_lssd'] == 'Y')
    {
        echo "<tr bgcolor=\"$tkireg->color_line2\"><td><strong>" . $langvars['l_lss'] . "</strong></td></tr>";
        echo "<tr><td>";
        $resx = $db->SelectLimit("SELECT * FROM {$db->prefix}movement_log WHERE ship_id <> ? AND sector_id = ? ORDER BY time DESC", 1, -1, array('ship_id' => $playerinfo['ship_id'], 'sector_id' => $sector));
        Tki\Db::LogDbErrors($pdo_db, $resx, __LINE__, __FILE__);
        if (!$resx)
        {
            echo "None";
        }
        else
        {
            $myrow = $resx->fields;
            $res = $db->Execute("SELECT character_name FROM {$db->prefix}ships WHERE ship_id = ?;", array($myrow['ship_id']));
            Tki\Db::LogDbErrors($pdo_db, $res, __LINE__, __FILE__);
            if ($res)
            {
                $row = $res->fields;
                $character_name = $row['character_name'];

                echo $character_name;
            }
            else
            {
                echo "Unknown";
            }
        }
    }
    else
    {
        echo "<tr><td>";
    }
    echo "</td></tr>";
    echo "</table><br>";
    echo "<a href='move.php?sector=" . $sector . "'>" . $langvars['l_clickme'] . "</a> " . $langvars['l_lrs_moveto'] . " " . $sector;
}

echo "<br><br>";
Tki\Text::gotomain($pdo_db, $lang);

Tki\Footer::display($pdo_db, $lang, $tkireg, $template);
