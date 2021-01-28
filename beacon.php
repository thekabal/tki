<?php declare(strict_types = 1);
/**
 * beacon.php from The Kabal Invasion.
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

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('beacon', 'combat',
                                'common', 'footer', 'insignias', 'news',
                                'universal'));
$title = $langvars['l_beacon_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

echo "<h1>" . $title . "</h1>\n";

$login = new Tki\Login();
$login->checkLogin($pdo_db, $lang, $tkireg, $tkitimer, $template);

// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

// Get sectorinfo from database
$sectors_gateway = new \Tki\Sectors\SectorsGateway($pdo_db);
$sectorinfo = $sectors_gateway->selectSectorInfo($playerinfo['sector']);

$allowed_rsw = "N";

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$beacon_text = null;
$beacon_text = filter_input(INPUT_POST, 'beacon_text', FILTER_SANITIZE_STRING);
if ($beacon_text === 0)
{
    $beacon_text = false;
}

// Get zoneinfo from database
$zones_gateway = new \Tki\Zones\ZonesGateway($pdo_db);
$zoneinfo = $zones_gateway->selectZoneInfo($sectorinfo['zone_id']);

if (!empty($zoneinfo))
{
    if ($playerinfo['dev_beacon'] > 0)
    {
        if ($zoneinfo['allow_beacon'] == 'N')
        {
            echo $langvars['l_beacon_notpermitted'] . "<br><br>";
        }
        elseif ($zoneinfo['allow_beacon'] == 'L')
        {
            $sql = "SELECT team FROM ::prefix::ships WHERE ship_id = :ship_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':sector_id', $zoneinfo['owner'], PDO::PARAM_INT);
            $stmt->execute();
            $zoneteam = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($zoneinfo['owner'] != $playerinfo['ship_id'])
            {
                if (($zoneteam['team'] != $playerinfo['team']) || ($playerinfo['team'] == 0))
                {
                    echo $langvars['l_beacon_notpermitted'] . "<br><br>";
                }
                else
                {
                    $allowed_rsw = "Y";
                }
            }
            else
            {
                $allowed_rsw = "Y";
            }
        }
        else
        {
            $allowed_rsw = "Y";
        }

        if ($allowed_rsw == "Y")
        {
            if ($beacon_text === null)
            {
                if ($sectorinfo['beacon'] !== null)
                {
                    echo $langvars['l_beacon_reads'] . ": " . $sectorinfo['beacon'] . "<br><br>";
                }
                else
                {
                    echo $langvars['l_beacon_none'] . "<br><br>";
                }

                echo "<form accept-charset='utf-8' action=beacon.php method=post>";
                echo "<table>";
                echo "<tr><td>" . $langvars['l_beacon_enter'];
                echo ":</td><td><input type=text name=beacon_text size=40 maxlength=80></td></tr>";
                echo "</table>";
                echo "<input type=submit value=" . $langvars['l_submit'] . ">";
                echo "<input type=reset value=" . $langvars['l_reset'] . ">";
                echo "</form>";
            }
            else
            {
                $beacon_text = trim(htmlentities($beacon_text, ENT_HTML5, 'UTF-8'));
                echo $langvars['l_beacon_nowreads'] . ": " . $beacon_text . ".<br><br>";

                $sql = "UPDATE ::prefix::universe SET beacon = :beacon WHERE sector_id = :sector_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':beacon', $beacon_text, PDO::PARAM_STR);
                $stmt->bindParam(':sector_id', $sectorinfo['sector_id'], PDO::PARAM_INT);
                $stmt->execute();

                $sql = "UPDATE ::prefix::ships SET dev_beacon = dev_beacon - 1 WHERE ship_id = :ship_id";
                $stmt = $pdo_db->prepare($sql);
                $stmt->bindParam(':ship_id', $playerinfo['ship_id'], PDO::PARAM_STR);
                $stmt->execute();
            }
        }
    }
    else
    {
        echo $langvars['l_beacon_donthave'] . "<br><br>";
    }
}

Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
