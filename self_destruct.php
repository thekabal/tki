<?php declare(strict_types = 1);
/**
 * self_destruct.php from The Kabal Invasion.
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

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'footer',
                                'insignias', 'news', 'ranking',
                                'self_destruct', 'universal'));
$title = $langvars['l_die_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title);

echo "<h1>" . $title . "</h1>\n";

// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

if (array_key_exists('sure', $_GET))
{
    $sure = $_GET['sure'];
}

if (!isset($sure))
{
    echo "<font color=red><strong>" . $langvars['l_die_rusure'] . "</strong></font><br><br>";
    echo $langvars['l_die_lose_planets'] . "<br>\n";
    echo "<a href='main.php'>" . $langvars['l_die_nonono'] . "</a> " . $langvars['l_die_what'] . "<br><br>";
    echo "<a href=self_destruct.php?sure=1>" . $langvars['l_yes'] . "!</a> " . $langvars['l_die_goodbye'] . "<br><br>";
}
elseif ($sure == 1)
{
    echo "<font color=red><strong>" . $langvars['l_die_check'] . "</strong></font><br><br>";
    echo $langvars['l_die_lose_planets'] . "<br>\n";
    echo "<a href='main.php'>" . $langvars['l_die_nonono'] . "</a> " . $langvars['l_die_what'] . "<br><br>";
    echo "<a href=self_destruct.php?sure=2>" . $langvars['l_yes'] . "!</a> " . $langvars['l_die_goodbye'] . "<br><br>";
}
elseif ($sure == 2)
{
    echo $langvars['l_die_count'] . "<br>";
    echo $langvars['l_die_vapor'] . "<br><br>";
    $langvars['l_die_please'] = str_replace("[logout]", "<a href='logout.php'>" . $langvars['l_logout'] . "</a>", $langvars['l_die_please']);
    echo $langvars['l_die_please'] . "<br>";
    $character_object = new Tki\Character();
    $character_object->kill($pdo_db, $playerinfo['ship_id'], $langvars, $tkireg);

    // Delete planets - this used to be part of "kill", but that violated the single responsibility principle
    $sql = "DELETE FROM ::prefix::planets WHERE owner = :owner";
    $stmt = $pdo_db->prepare($sql);
    $stmt->bindParam(':owner', $playerinfo['ship_id'], \PDO::PARAM_INT);
    $stmt->execute();

    $bounty = new Tki\Bounty();
    $bounty->cancel($pdo_db, $playerinfo['ship_id']);

    $admin_log = new Tki\AdminLog();
    $admin_log->writeLog($pdo_db, \Tki\LogEnums::ADMIN_HARAKIRI, "$playerinfo[character_name]|" . $request->server->get('REMOTE_ADDR') . "");
    Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], \Tki\LogEnums::HARAKIRI, $request->server->get('REMOTE_ADDR'));
    echo $langvars['l_die_lost_planets'] . "<br>\n";
}
else
{
    echo $langvars['l_die_exploit'] . "<br><br>";
}

Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
