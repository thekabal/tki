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
// File: admin/planet_editor.php

if (strpos($_SERVER['PHP_SELF'], 'planet_editor.php')) // Prevent direct access to this file
{
    die('The Kabal Invasion - General error: You cannot access this file directly.');
}

echo "<h2>" . $langvars['l_admin_planet_editor'] . "</h2>";
echo "<form accept-charset='utf-8' action='admin.php' method='post'>";
if (empty($planet))
{
    echo "<select size='15' name='planet'>";
    $res = $db->Execute("SELECT planet_id, name, sector_id FROM {$db->prefix}planets ORDER BY sector_id");
    Bnt\Db::logDbErrors($db, $res, __LINE__, __FILE__);
    while (!$res->EOF)
    {
        $row = $res->fields;
        if ($row['name'] == null)
        {
            $row['name'] = $langvars['l_unnamed'];
        }

        echo "<option value='" . $row['planet_id'] . "'> " . $row['name'] . " " . $langvars['l_admin_in_sector'] . " " . $row['sector_id'] . "</option>";
        $res->MoveNext();
    }

    echo "</select>";
    echo "&nbsp;<input type='submit' value='" . $langvars['l_edit'] . "'>";
}
else
{
    if (empty($operation))
    {
        $res = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE planet_id = ?", array($planet));
        Bnt\Db::logDbErrors($db, $res, __LINE__, __FILE__);
        $row = $res->fields;

        echo "<table border='0' cellspacing='2' cellpadding='2'>";
        echo "<tr><td><tt>" . $langvars['l_admin_planet_id'] . "</tt></td><td><font color='#6f0'>" . $planet . "</font></td>";
        echo "<td align='right'><tt>" . $langvars['l_admin_sector_id'] . "</tt><input type='text' size='5' name='sector_id' value=\"" . $row['sector_id'] . "\"></td>";
        echo "<td align='right'><tt>" . $langvars['l_admin_defeated'] . "</tt><input type='checkbox' name='defeated' value='ON' " . checked($row['defeated']) . "></td></tr>";
        echo "<tr><td><tt>" . $langvars['l_admin_planet_name'] . "</tt></td><td><input type='text' size='15' name='name' value=\"" . $row['name'] . "\"></td>";
        echo "<td align='right'><tt>" . $langvars['l_base'] . "</tt><input type='checkbox' name='base' value='ON' " . checked($row['base']) . "></td>";
        echo "<td align='right'><tt>" . $langvars['l_admin_sells'] . "</tt><input type='checkbox' name='sells' value='ON' " . checked($row['sells']) . "></td></tr>";
        echo "<tr><td colspan='4'><hr></td></tr>";
        echo "</table>";

        echo "<table border='0' cellspacing='2' cellpadding='2'>";
        echo "<tr><td><tt>" . $langvars['l_admin_planet_owner'] . "</tt></td><td>";
        echo "<select size='1' name='owner'>";
        $ressuba = $db->Execute("SELECT ship_id,character_name FROM {$db->prefix}ships ORDER BY character_name");
        Bnt\Db::logDbErrors($db, $ressuba, __LINE__, __FILE__);
        echo "<option value='0'>" . $langvars['l_admin_no_one'] . "</option>";
        while (!$ressuba->EOF)
        {
            $rowsuba = $ressuba->fields;
            if ($rowsuba['ship_id'] == $row['owner'])
            {
                echo "<option selected='" . $rowsuba['ship_id'] . "' value='" . $rowsuba['ship_id'] . "'>" . $rowsuba['character_name'] . "</option>";
            }
            else
            {
                echo "<option value='" . $rowsuba['ship_id'] . "'>" . $rowsuba['character_name'] . "</option>";
            }
            $ressuba->MoveNext();
        }

        echo "</select></td>";
        echo "<td align='right'><tt>" . $langvars['l_organics'] . "</tt></td><td><input type='text' size='9' name='organics' value=\"" . $row['organics'] . "\"></td>";
        echo "<td align='right'><tt>" . $langvars['l_ore'] . "</tt></td><td><input type='text' size='9' name='ore' value=\"" . $row['ore'] . "\"></td>";
        echo "<td align='right'><tt>" . $langvars['l_goods'] . "</tt></td><td><input type='text' size='9' name='goods' value=\"" . $row['goods'] . "\"></td>";
        echo "<td align='right'><tt>" . $langvars['l_energy'] . "</tt></td><td><input type='text' size='9' name='energy' value=\"" . $row['energy'] . "\"></td></tr>";
        echo "<tr><td><tt>" . $langvars['l_admin_planet_corp'] . "</tt></td><td><input type='text' size=5 name='corp' value=\"" . $row['corp'] . "\"></td>";
        echo "<td align='right'><tt>" . $langvars['l_colonists'] . "</tt></td><td><input type='text' size='9' name='colonists' value=\"" . $row['colonists'] . "\"></td>";
        echo "<td align='right'><tt>" . $langvars['l_credits'] . "</tt></td><td><input type='text' size='9' name='credits' value=\"" . $row['credits'] . "\"></td>";
        echo "<td align='right'><tt>" . $langvars['l_fighters'] . "</tt></td><td><input type='text' size='9' name='fighters' value=\"" . $row['fighters'] . "\"></td>";
        echo "<td align='right'><tt>" . $langvars['l_torps'] . "</tt></td><td><input type='text' size='9' name='torps' value=\"" . $row['torps'] . "\"></td></tr>";
        echo "<tr><td colspan='2'><tt>" . $langvars['l_admin_planet_production'] . "</tt></td>";
        echo "<td align='right'><tt>" . $langvars['l_organics'] . "</tt></td><td><input type='text' size='9' name='prod_organics' value=\"" . $row['prod_organics'] . "\"></td>";
        echo "<td align='right'><tt>" . $langvars['l_ore'] . "</tt></td><td><input type='text' size='9' name='prod_ore' value=\"" . $row['prod_ore'] . "\"></td>";
        echo "<td align='right'><tt>" . $langvars['l_goods'] . "</tt></td><td><input type='text' size='9' name='prod_goods' value=\"" . $row['prod_goods'] . "\"></td>";
        echo "<td align='right'><tt>" . $langvars['l_energy'] . "</tt></td><td><input type='text' size='9' name='prod_energy' value=\"" . $row['prod_energy'] . "\"></td></tr>";
        echo "<tr><td colspan='6'><tt>" . $langvars['l_admin_planet_production'] . "</tt></td>";
        echo "<td align='right'><tt>" . $langvars['l_fighters'] . "</tt></td><td><input type='text' size='9' name='prod_fighters' value=\"" . $row['prod_fighters'] . "\"></td>";
        echo "<td align='right'><tt>" . $langvars['l_torps'] . "</tt></td><td><input type='text' size='9' name='prod_torp' value=\"" . $row['prod_torp'] . "\"></td></tr>";
        echo "<tr><td colspan=10><hr></td></tr>";
        echo "</table>";

        echo "<br>";
        echo "<input type='hidden' name='planet' value='$planet'>";
        echo "<input type='hidden' name='operation' value='save'>";
        echo "<input type='submit' size='1' value='" . $langvars['l_save'] . "'>";
    }
    elseif ($operation == "save")
    {
        // Update database
        $_defeated = empty($defeated) ? "N" : "Y";
        $_base = empty($base) ? "N" : "Y";
        $_sells = empty($sells) ? "N" : "Y";
        $planupdate = $db->Execute("UPDATE {$db->prefix}planets SET sector_id = ?, defeated = ?, name = ?, base = ?, sells = ?, owner = ?, organics = ?, ore = ?, goods = ?, energy = ?, corp = ?, colonists = ?,credits = ? ,fighters = ?, torps = ?, prod_organics= ? , prod_ore = ?, prod_goods = ?, prod_energy = ?, prod_fighters = ?, prod_torp = ? WHERE planet_id = ?", array($sector_id, $_defeated, $name, $_base, $_sells, $owner, $organics, $ore, $goods, $energy, $corp, $colonists, $credits, $fighters, $torps, $prod_organics, $prod_ore, $prod_goods, $prod_energy, $prod_fighters, $prod_torp, $planet));
        Bnt\Db::logDbErrors($db, $planupdate, __LINE__, __FILE__);
        if (!$planupdate)
        {
            echo $langvars['l_admin_changes_failed'] . "<br><br>";
            echo $db->ErrorMsg() . "<br>";
        }
        else
        {
            echo $langvars['l_admin_changes_saved'] . "<br><br>";
        }

        echo "<input type='submit' value=\"" . $langvars['l_admin_return_planet_editor'] . "\">";
        $button_main = false;
    }
    else
    {
        echo $langvars['l_admin_invalid_operation'];
    }
}

echo "<input type='hidden' name='menu' value='planet_editor.php'>";
echo "<input type='hidden' name='swordfish' value=" . $_POST['swordfish'] . ">";
echo "</form>";
?>
