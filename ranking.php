<?php declare(strict_types = 1);
/**
 * ranking.php from The Kabal Invasion.
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

// FUTURE: Remove color controls out to css

require_once './common.php';

// Always make sure we are using empty vars before use.
$variables = null;

$variables['body_class'] = 'tki';
$variables['lang'] = $lang;
$variables['link'] = 'ranking.php';
$variables['title'] = $langvars['l_ranks_title'];

// These should be set within the template config, and be css driven using nth + 1 selectors.
$variables['color_header'] = $tkireg->color_header;
$variables['color_line1'] = $tkireg->color_line1;
$variables['color_line2'] = $tkireg->color_line2;

// Load required language variables for the ranking page.
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'footer',
                                'insignias', 'main', 'ranking', 'teams',
                                'universal'));
// Get requested ranking order.
// Detect if this variable exists, and filter it. Returns false if anything wasn't right.
$sort = null;
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING);
if (($sort === null) || (strlen(trim($sort)) === 0))
{
    $sort = false;
}

switch ($sort)
{
    case 'turns':
        $by = 'turns_used DESC, character_name ASC';
        break;
    case 'login':
        $by = 'last_login DESC, character_name ASC';
        break;
    case 'good':
        $by = 'rating DESC, character_name ASC';
        break;
    case 'bad':
        $by = 'rating ASC, character_name ASC';
        break;
    case 'team':
        $by = "::prefix::teams.team_name DESC, character_name ASC";
        break;
    case 'efficiency':
        $by = 'efficiency DESC';
        break;
    default:
        $by = 'score DESC, character_name ASC';
        break;
}

$variables['num_players'] = 0;

$sql = "SELECT {$old_db->prefix}ships.ship_id, {$old_db->prefix}ships.email, {$old_db->prefix}ships.ip_address, " .
"{$old_db->prefix}ships.score, {$old_db->prefix}ships.character_name, {$old_db->prefix}ships.turns_used, " .
"{$old_db->prefix}ships.last_login, UNIX_TIMESTAMP({$old_db->prefix}ships.last_login) as online, " .
"{$old_db->prefix}ships.rating, {$old_db->prefix}teams.team_name, {$old_db->prefix}teams.admin AS team_admin, " .
"if ({$old_db->prefix}ships.turns_used < 150, 0, ROUND({$old_db->prefix}ships.score/{$old_db->prefix}ships.turns_used)) " .
"AS efficiency FROM {$old_db->prefix}ships LEFT JOIN {$old_db->prefix}teams ON " .
"{$old_db->prefix}ships.team = {$old_db->prefix}teams.id WHERE ship_destroyed='N' and email NOT LIKE '%@kabal' " .
"AND turns_used > 0 ORDER BY :order_by LIMIT :limit";

$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':order_by', $by, PDO::PARAM_STR);
$stmt->bindParam(':limit', $tkireg->max_ranks, PDO::PARAM_INT);
$stmt->execute();
$rankings = $stmt->fetchAll(PDO::FETCH_ASSOC);
$variables['num_players'] = count($rankings);
$player_list = array();
$xx = 1;

if ($rankings !== null && ($variables['num_players'] > 0))
{
    foreach ($rankings as $row)
    {
        // Set the players rank number.
        $row['rank'] = count($player_list) + 1;

        // Calculate the players rating.
        $rating = round(sqrt(abs($row['rating'])));
        if (abs($row['rating']) != $row['rating'])
        {
            $rating = -1 * $rating;
        }

        $row['rating'] = $rating;

        // Calculate the players online status.
        $curtime = time();
        $time = $row['online'];
        $difftime = ($curtime - $time) / 60;
        $temp_turns = $row['turns_used'];
        if ($temp_turns <= 0)
        {
            $temp_turns = 1;
        }

        // Set the players online/offline status.
        $row['online'] = false;
        if ($difftime <= 5)
        {
            $row['online'] = true;
        }

        // Set the characters Insignia.
        $insignia = new Tki\Character();
        $row['insignia'] = $insignia->getInsignia($pdo_db, $row['email'], $langvars);

        // This is just to show that we can set the type of player.
        // like: banned, admin, player, npc etc.
        if ($row['email'] == $tkireg->admin_mail || $row['team_admin'] === 'Y')
        {
            $row['type'] = 'admin';
        }
        else
        {
            $row['type'] = 'player';
        }

        // Check for banned players.
        $ban_result = Tki\CheckBan::isBanned($pdo_db, $row);

        if ($ban_result === null)
        {
            $row['banned'] = false;
            $row['ban_info'] = null;
        }
        else
        {
            $row['banned'] = true;
            $row['ban_info'] = array('type' => $ban_result['ban_type'],
                'public_info' => $langvars['l_ranks_ban_reason'] . "\n" . $ban_result['public_info']);
        }

        array_push($player_list, $row);
    }

    $template->addVariables('players', $player_list);
}

if (empty($_SESSION['username']))
{
    $variables['loggedin'] = true;
    $variables['linkback'] = array('caption' => $langvars['l_universal_main_login'], 'link' => 'index.php');
}
else
{
    $variables['loggedin'] = false;
    $variables['linkback'] = array('caption' => $langvars['l_universal_main_menu'], 'link' => 'main.php');
}

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $variables['title'], $variables['body_class']);
$template->addVariables('variables', $variables);

// Load required language variables for the ranking page.
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'footer',
                                'insignias', 'main', 'news', 'ranking',
                                'teams', 'universal'));
// Modify the requires language variables here.
$langvars['l_ranks_title'] = str_replace('[max_ranks]', $tkireg->max_ranks, $langvars['l_ranks_title']);

// Now add the loaded language variables into Smarty.
$template->addVariables('langvars', $langvars);

// Now we tell Smarty to output the page
$template->display('ranking.tpl');

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
