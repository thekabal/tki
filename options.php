<?php declare(strict_types = 1);
/**
 * options.php from The Kabal Invasion.
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
$login->checkLogin($pdo_db, $lang, $tkireg, $tkitimer, $template);

$body_class = 'options';

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('common', 'footer',
                                'insignias', 'options', 'universal'));
$title = $langvars['l_opt_title'];

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title, $body_class);

$players_gateway = new \Tki\Players\PlayersGateway($pdo_db);
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

echo "<body class='options'>\n";
echo "<h1>" . $title . "</h1>\n";
echo "<form accept-charset='utf-8' action=option2.php method=post>\n";
echo "<table>\n";
echo "<tr>\n";
echo "<th colspan=2><strong>" . $langvars['l_opt_chpass'] . "</strong></th>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td>" . $langvars['l_opt_curpass'] . "</td>\n";
echo "<td><input type=password name=oldpass size=20 maxlength=20 value=\"\"></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td>" . $langvars['l_opt_newpass'] . "</td>\n";
echo "<td><input type=password name=newpass1 size=20 maxlength=20 value=\"\"></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td>" . $langvars['l_opt_newpagain'] . "</td>\n";
echo "<td><input type=password name=newpass2 size=20 maxlength=20 value=\"\"></td>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<th colspan=2><strong>" . $langvars['l_opt_lang'] . "</strong></th>\n";
echo "</tr>\n";
echo "<tr>\n";
echo "<td>" . $langvars['l_opt_select'] . "</td><td><select name=newlang>\n";

$avail_langs = Tki\Languages::listAvailable($pdo_db, $lang);
foreach ($avail_langs as $language_list_item_name => $language_list_item)
{
    if ($language_list_item_name == $playerinfo['lang'])
    {
        $selected = " selected";
    }
    else
    {
        $selected = null;
    }

    echo "<option value='" . $language_list_item_name . "'" . $selected . ">" . $language_list_item['lang_name'] . "</option>\n";
}

echo "</select></td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "<br>\n";
echo "<input type=submit value=" . $langvars['l_opt_save'] . ">\n";
echo "</form><br>\n";

Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $tkitimer, $template);
