<?php declare(strict_types = 1);
/**
 * scheduler/sched_degrade.php from The Kabal Invasion.
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

// FUTURE: Better debugging and output for all paths
$langvars = Tki\Translate::load($pdo_db, $lang, array('scheduler'));

echo "<strong>" . $langvars['l_degrade_title'] . "</strong><br><br>";
$res = $db->Execute("SELECT * FROM {$db->prefix}sector_defense WHERE defense_type = 'F'");
Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);

while (!$res->EOF)
{
    $row = $res->fields;
    $res3 = $db->Execute("SELECT * FROM {$db->prefix}ships WHERE ship_id = ?;", array($row['ship_id']));
    Tki\Db::logDbErrors($pdo_db, $res3, __LINE__, __FILE__);
    $sched_playerinfo = $res3->fields;
    $res2 = $db->Execute("SELECT * FROM {$db->prefix}planets WHERE (owner = ? OR (team = ? AND ? <> 0)) AND sector_id = ? AND energy > 0;", array($row['ship_id'], $sched_playerinfo['team'], $sched_playerinfo['team'], $row['sector_id']));
    Tki\Db::logDbErrors($pdo_db, $res2, __LINE__, __FILE__);
    if ($res2->EOF)
    {
        $resa = $db->Execute("UPDATE {$db->prefix}sector_defense SET quantity = quantity - GREATEST(ROUND(quantity * ?),1) WHERE defense_id = ? AND quantity > 0;", array($defense_degrade_rate, $row['defense_id']));
        Tki\Db::logDbErrors($pdo_db, $resa, __LINE__, __FILE__);
        $degrade_rate = $defense_degrade_rate * 100;
        Tki\PlayerLog::writeLog($pdo_db, $row['ship_id'], \Tki\LogEnums::DEFENSE_DEGRADE, $row['sector_id'] . "|" . $degrade_rate);
    }
    else
    {
        $energy_required = round($row['quantity'] * $energy_per_fighter);
        $res4 = $db->Execute("SELECT IFNULL(SUM(energy),0) AS energy_available FROM {$db->prefix}planets WHERE (owner = ? OR (team = ? AND ? <> 0)) AND sector_id = ?", array($row['ship_id'], $sched_playerinfo['team'], $sched_playerinfo['team'], $row['sector_id']));
        Tki\Db::logDbErrors($pdo_db, $res4, __LINE__, __FILE__);
        $planet_energy = $res4->fields;
        $energy_available = $planet_energy['energy_available'];
        $langvars['l_degrade_note'] = str_replace("[energy_avail]", (string) $energy_available, $langvars['l_degrade_note']);
        $langvars['l_degrade_note'] = str_replace("[energy_required]", (string) $energy_required, $langvars['l_degrade_note']);
        echo $langvars['l_degrade_note'];
        if ($energy_available > $energy_required)
        {
            while (!$res2->EOF)
            {
                $degrade_row = $res2->fields;
                $resb = $db->Execute("UPDATE {$db->prefix}planets SET energy = energy - GREATEST(ROUND(? * (energy / ?)),1)  WHERE planet_id = ?", array($energy_required, $energy_available, $degrade_row['planet_id']));
                Tki\Db::logDbErrors($pdo_db, $resb, __LINE__, __FILE__);
                $res2->MoveNext();
            }
        }
        else
        {
            $resc = $db->Execute("UPDATE {$db->prefix}sector_defense SET quantity = quantity - GREATEST(ROUND(quantity * ?),1) WHERE defense_id = ?;", array($defense_degrade_rate, $row['defense_id']));
            Tki\Db::logDbErrors($pdo_db, $resc, __LINE__, __FILE__);
            $degrade_rate = $defense_degrade_rate * 100;
            Tki\PlayerLog::writeLog($pdo_db, $row['ship_id'], \Tki\LogEnums::DEFENSE_DEGRADE, $row['sector_id'] . "|" . $degrade_rate);
        }
    }

    $res->MoveNext();
}

$resx = $db->Execute("DELETE FROM {$db->prefix}sector_defense WHERE quantity <= 0");
Tki\Db::logDbErrors($pdo_db, $resx, __LINE__, __FILE__);
