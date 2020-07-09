<?php declare(strict_types = 1);
/**
 * warpedit3.php from The Kabal Invasion.
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

$title = $langvars['l_warp_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('warpedit', 'common', 'global_includes', 'global_funcs', 'footer', 'news'));
echo "<h1>" . $title . "</h1>\n";

$bothway = false;
// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
if (isset($_POST['bothway']) && $_POST['bothway'] == 'bothway')
{
    $bothway = true;
}

// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$target_sector = null;
$target_sector = (int) filter_input(INPUT_POST, 'target_sector', FILTER_VALIDATE_INT);
if (strlen((string) $target_sector) === 0)
{
    $target_sector = false;
}
else
{
    $target_sector = (string) $target_sector;
}

// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db); // Build a player gateway object to handle the SQL calls
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

if ($playerinfo['turns'] < 1)
{
    echo $langvars['l_warp_turn'] . "<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang);

    $footer = new Tki\Footer();
    $footer->display($pdo_db, $lang, $tkireg, $template);
    die();
}

if ($playerinfo['dev_warpedit'] < 1)
{
    echo $langvars['l_warp_none'] . "<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang);

    $footer = new Tki\Footer();
    $footer->display($pdo_db, $lang, $tkireg, $template);
    die();
}

if ($target_sector === false)
{
    // This is the best that I can do without adding a new language variable.
    echo $langvars['l_warp_nosector'] . "<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang);
    die();
}

$sql = "SELECT allow_warpedit, ::prefix::universe.zone_id FROM ::prefix::zones, ::prefix::universe WHERE sector_id = :sector_id AND ::prefix::universe.zone_id = ::prefix::zones.zone_id;";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':sector_id', $playerinfo['sector'], PDO::PARAM_INT);
$stmt->execute();
$zoneinfo = $stmt->fetch(PDO::FETCH_ASSOC);

if ($zoneinfo['allow_warpedit'] == 'N')
{
    echo $langvars['l_warp_forbid'] . "<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang);

    $footer = new Tki\Footer();
    $footer->display($pdo_db, $lang, $tkireg, $template);
    die();
}

$target_sector = (int) $target_sector;

$players_gateway = new \Tki\Players\PlayersGateway($pdo_db); // Build a player gateway object to handle the SQL calls
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

$sql = "SELECT allow_warpedit, ::prefix::universe.zone_id FROM ::prefix::zones, ::prefix::universe WHERE sector_id = :sector_id AND ::prefix::universe.zone_id = ::prefix::zones.zone_id;";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':sector_id', $target_sector, PDO::PARAM_INT);
$stmt->execute();
$zoneinfo = $stmt->fetch(PDO::FETCH_ASSOC);

if ($zoneinfo['allow_warpedit'] == 'N' && $bothway)
{
    $langvars['l_warp_forbidtwo'] = str_replace("[target_sector]", (string) $target_sector, $langvars['l_warp_forbidtwo']);
    echo $langvars['l_warp_forbidtwo'] . "<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang);

    $footer = new Tki\Footer();
    $footer->display($pdo_db, $lang, $tkireg, $template);
    die();
}

// Get sectorinfo from database
$sectors_gateway = new \Tki\Sectors\SectorsGateway($pdo_db); // Build a sector gateway object to handle the SQL calls
$sectorinfo = $sectors_gateway->selectSectorInfo($target_sector);

/* FUTURE: This is broken
if (!is_array($sectorinfo))
{
    echo $langvars['l_warp_nosector'] . "<br><br>";
    Tki\Text::gotoMain($pdo_db, $lang);
    die();
}
*/

$sql = "SELECT * FROM ::prefix::links WHERE link_start = :link_start";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':link_start', $playerinfo['sector'], PDO::PARAM_INT);
$stmt->execute();
$linkinfo = $stmt->fetch(PDO::FETCH_ASSOC);

if ($linkinfo !== false)
{
    $flag = 0;
    foreach ($linkinfo as $tmp_link)
    {
        if ($target_sector == $tmp_link['link_dest'])
        {
            $flag = 1;
        }
    }

    if ($flag != 1)
    {
        $langvars['l_warp_unlinked'] = str_replace("[target_sector]", (string) $target_sector, $langvars['l_warp_unlinked']);
        echo $langvars['l_warp_unlinked'] . "<br><br>";
    }
    else
    {
        $sql = "DELETE FROM ::prefix::links WHERE link_start = :link_start AND link_dest = :link_dest";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':link_start', $playerinfo['sector'], PDO::PARAM_INT);
        $stmt->bindParam(':link_dest', $target_sector, PDO::PARAM_INT);
        $stmt->execute();
        $linkinfo = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "UPDATE ::prefix::ships SET dev_warpedit = dev_warpedit - 1, turns = turns - 1, turns_used = turns_used + 1 WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':link_start', $playerinfo['ship_id'], PDO::PARAM_INT);
        $stmt->execute();
        $update_ships = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($bothway === false)
        {
            echo $langvars['l_warp_removed'] . " " . $target_sector . ".<br><br>";
        }
        else
        {
            $sql = "DELETE ::prefix::links link_start = :link_start AND link_dest = :link_dest";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':link_start', $target_sector, PDO::PARAM_INT);
            $stmt->bindParam(':link_start', $playerinfo['sector'], PDO::PARAM_INT);
            $stmt->execute();
            $update_ships = $stmt->fetch(PDO::FETCH_ASSOC);
            echo $langvars['l_warp_removedtwo'] . " " . $target_sector . ".<br><br>";
        }
    }
}

Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
