<?php declare(strict_types = 1);
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
// File: preset.php

require_once './common.php';

$login = new Tki\Login;
$login->checkLogin($pdo_db, $lang, $tkireg, $template);

$langvars = Tki\Translate::load($pdo_db, $lang, array('presets'));
$title = $langvars['l_pre_title'];
$body_class = 'tki';

$header = new Tki\Header();
$header->display($pdo_db, $lang, $template, $title, $body_class);

// Database driven language entries
$langvars = Tki\Translate::load($pdo_db, $lang, array('presets', 'common', 'global_includes', 'global_funcs', 'combat', 'footer', 'news'));
echo "<h1>" . $title . "</h1>\n";
echo "<body class ='" . $body_class . "'>";

// Get playerinfo from database
$players_gateway = new \Tki\Players\PlayersGateway($pdo_db); // Build a player gateway object to handle the SQL calls
$playerinfo = $players_gateway->selectPlayerInfo($_SESSION['username']);

// Pull the presets for the player from the db.
$preset_list = array();
$sql = "SELECT * FROM ::prefix::presets WHERE ship_id=:ship_id";
$stmt = $pdo_db->prepare($sql);
$stmt->bindParam(':ship_id', $playerinfo['ship_id'], PDO::PARAM_INT);
$stmt->execute();
$preset_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Filter the array of presets from the form submission - first pass
filter_var_array($_POST['preset'], FILTER_VALIDATE_INT);

// Filter the array of presets from the form submission
if (array_key_exists('preset', $_POST))
{
    foreach ($_POST['preset'] as $key => $value)
    {
        // Returns null if it doesn't have it set, bool false if its set but fails to validate and the actual value if it all passes.
        $key = filter_var($key, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => $tkireg->max_presets)));

        // Returns null if it doesn't have it set, bool false if its set but fails to validate and the actual value if it all passes.
        $preset_list[$key] = filter_var($_POST['preset'][$key], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => $tkireg->max_sectors)));
    }
}

filter_var_array($preset_list, FILTER_VALIDATE_INT);

$change = filter_input(INPUT_POST, 'change', FILTER_VALIDATE_INT, array('options' => array('min_range' => 0, 'max_range' => 1)));
foreach ($preset_list as $index => $preset)
{
    if ($preset === false)
    {
        $change = 0;
        $result = str_replace("[preset]", $_POST['preset'][$index], $langvars['l_pre_exceed']);
        $result = str_replace("[max_sectors]", $tkireg->max_sectors, $result);
        $result = htmlentities($result, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        echo $result . "<br>\n";
    }
}

echo "<br>\n";
if ($change !== 1)
{
    echo "<form accept-charset='utf-8' action='preset.php' method='post'>";
    for ($x = 0; $x < $tkireg->max_presets; $x++)
    {
        echo "<div style='padding:2px;'>Preset " . ($x + 1) . ": <input type='text' name='preset[$x]' size='6' maxlength='6' value='" . $preset_list[$x]['preset'] . "'></div>";
    }

    echo "<input type='hidden' name='change' value='1'>";
    echo "<div style='padding:2px;'><input type='submit' value=" . $langvars['l_pre_save'] . "></div>";
    echo "</form>";
    echo "<br>\n";
}
else
{
    foreach ($_POST['preset'] as $key => $value)
    {
        if ($key < $tkireg->max_presets)
        {
            $new_id = $key + 1;
            $sql = "UPDATE ::prefix::presets SET preset=:preset WHERE preset_id=:preset_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':preset', $value, PDO::PARAM_INT);
            $stmt->bindParam(':preset_id', $new_id, PDO::PARAM_INT);
            $stmt->execute();

            $preset_result_echo = str_replace("[preset]", "<a href=rsmove.php?engage=1&destination=$preset_list[$key]>$preset_list[$key]</a>", $langvars['l_pre_set_loop']);
            $preset_result_echo = str_replace("[num]", $key + 1, $preset_result_echo);
            echo $preset_result_echo . "<br>";
        }
    }
}

Tki\Text::gotoMain($pdo_db, $lang);

$footer = new Tki\Footer();
$footer->display($pdo_db, $lang, $tkireg, $template);
