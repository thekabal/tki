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
// File: classes/Bases.php

namespace Tki;

use Symfony\Component\HttpFoundation\Request;

class Bases
{
    public static function buildBase(\PDO $pdo_db, array $langvars, int $planet_id, int $sector_id, Reg $tkireg)
    {
        $request = Request::createFromGlobals();

        echo "<br>";
        echo str_replace("[here]", "<a href='planet_report.php?preptype=1'>" . $langvars['l_here'] . "</a>", $langvars['l_pr_click_return_status']);
        echo "<br><br>";

        // Get playerinfo from database
        $sql = "SELECT * FROM ::prefix::ships WHERE email=:email LIMIT 1";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':email', $_SESSION['username'], \PDO::PARAM_STR);
        $stmt->execute();
        $playerinfo = $stmt->fetch(\PDO::FETCH_ASSOC);

        $sql = "SELECT * FROM ::prefix::planets WHERE planet_id=:planet_id LIMIT 1";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':planet_id', $planet_id, \PDO::PARAM_INT);
        $stmt->execute();
        $planetinfo = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Error out and return if the Player isn't the owner of the Planet
        // Verify player owns the planet which is to have the base created on.
        if ($planetinfo['owner'] != $playerinfo['ship_id'])
        {
            echo "<div style='color:#f00; font-size:16px;'>" . $langvars['l_pr_make_base_failed'] . "</div>\n";
            echo "<div style='color:#f00; font-size:16px;'>" . $langvars['l_pr_invalid_info'] . "</div>\n";

            return (bool) false;
        }

        if (!is_numeric($planet_id) || !is_numeric($sector_id))
        {
            $ip = $request->query->get('REMOTE_ADDR');
            $hack_id = 0x1337;
            $admin_log = new AdminLog;
            $admin_log->writeLog($pdo_db, LOG_ADMIN_PLANETCHEAT, "{$hack_id}|{$ip}|{$planet_id}|{$sector_id}|{$playerinfo['ship_id']}");
            echo "<div style='color:#f00; font-size:16px;'>" . $langvars['l_pr_make_base_failed'] . "</div>\n";

            return (bool) false;
        }  // Build a base

        \Tki\Realspace::realSpaceMove($pdo_db, $langvars, $sector_id, $tkireg);
        echo "<br>";
        echo str_replace("[here]", "<a href='planet.php?planet_id=$planet_id'>" . $langvars['l_here'] . "</a>", $langvars['l_pr_click_return_planet']);
        echo "<br><br>";

        if ($planetinfo['ore'] >= $tkireg->base_ore && $planetinfo['organics'] >= $tkireg->base_organics && $planetinfo['goods'] >= $tkireg->base_goods && $planetinfo['credits'] >= $tkireg->base_credits)
        {
            // Create The Base
            $stmt = $pdo_db->prepare("UPDATE ::prefix::planets SET base='Y', ore = :planetore - :baseore, organics = :planetorg - :baseorg, goods = :planetgoods - :basegoods, credits = :planetcredits - :basecredits WHERE planet_id = :planet_id");
            $stmt->bindParam(':planetore', $planetinfo['ore'], \PDO::PARAM_INT);
            $stmt->bindParam(':baseore', $tkireg->base_ore, \PDO::PARAM_INT);
            $stmt->bindParam(':planetorg', $planetinfo['organics'], \PDO::PARAM_INT);
            $stmt->bindParam(':baseorg', $tkireg->base_organics, \PDO::PARAM_INT);
            $stmt->bindParam(':planetgoods', $planetinfo['goods'], \PDO::PARAM_INT);
            $stmt->bindParam(':basegoods', $tkireg->base_goods, \PDO::PARAM_INT);
            $stmt->bindParam(':planetcredits', $planetinfo['credits'], \PDO::PARAM_INT);
            $stmt->bindParam(':basecredits', $tkireg->base_credits, \PDO::PARAM_INT);
            $stmt->bindParam(':planet_id', $planet_id, \PDO::PARAM_INT);
            $result = $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);

            // Update User Turns
            $stmt = $pdo_db->prepare("UPDATE ::prefix::ships SET turns = turns - 1, turns_used = turns_used + 1 WHERE ship_id = :ship_id");
            $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
            $result = $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $result, __LINE__, __FILE__);

            // Refresh Planet Info
            $sql = "SELECT * FROM ::prefix::planets WHERE planet_id=:planet_id LIMIT 1";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':planet_id', $planet_id, \PDO::PARAM_INT);
            $stmt->execute();
            $planetinfo = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Notify User Of Base Results
            echo $langvars['l_planet_bbuild'] . "<br><br>";

            // Calc Ownership and Notify User Of Results
            $ownership = \Tki\Ownership::calc($pdo_db, $playerinfo['sector'], $tkireg->min_bases_to_own, $langvars);
            if ($ownership !== null)
            {
                echo $ownership . "<p>";
            }

            return $planetinfo;
        }
    }
}
