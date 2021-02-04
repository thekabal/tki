<?php declare(strict_types = 1);
/**
 * classes/PlanetProduction.php from The Kabal Invasion.
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

namespace Tki;

class PlanetProduction
{
    public static function productionChange(\PDO $pdo_db, $old_db, string $lang, array $prodpercentarray, Registry $tkireg): void
    {
        $langvars = Translate::load($pdo_db, $lang, array('common', 'planet_report'));
        //  Declare default production values from the config.php file
        //
        //  We need to track what the player_id is and what team they belong to if they belong to a team,
        //    these two values are not passed in as arrays
        //    ship_id = the owner of the planet          ($ship_id = $prodpercentarray['ship_id'])
        //    team_id = the team creators ship_id ($team_id = $prodpercentarray['team_id'])
        //
        //  First we generate a list of values based on the commodity
        //    (ore, organics, goods, energy, fighters, torps, team, sells)
        //
        //  Second we generate a second list of values based on the planet_id
        //  Because team and ship_id are not arrays we do not pass them through the second list command.
        //  When we write the ore production percent we also clear the selling and team values out of the db
        //  When we pass through the team array we set the value to $team we grabbed out of the array.
        //  in the sells and team the prodpercent = the planet_id.
        //
        //  We run through the database checking to see if any planet production is greater than 100, or possibly negative
        //    if so we set the planet to the default values and report it to the player.
        //
        //  There has got to be a better way, but at this time I am not sure how to do it.
        //  Off the top of my head if we could sort the data passed in, in order of planets we could check before we do the writes
        //  This would save us from having to run through the database a second time checking our work.

        $sql = "SELECT ship_id from ::prefix::ships WHERE email = :session_username";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':session_username', $_SESSION['username'], \PDO::PARAM_STR);
        $stmt->execute();
        $ship_id = $stmt->fetch(\PDO::FETCH_COLUMN);
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        echo str_replace("[here]", "<a href='planet_report.php?preptype=2'>" . $langvars['l_here'] . "</a>", $langvars['l_pr_click_return_prod']);
        echo "<br><br>";

        foreach ($prodpercentarray as $commod_type => $valarray)
        {
            if ($commod_type != "team_id" && $commod_type != "ship_id")
            {
                foreach ($valarray as $planet_id => $prodpercent)
                {
                    if ($commod_type == "prod_ore" || $commod_type == "prod_organics" || $commod_type == "prod_goods" || $commod_type == "prod_energy" || $commod_type == "prod_fighters" || $commod_type == "prod_torp")
                    {
                        $sql = "SELECT COUNT(*) AS owned_planet FROM " .
                               "::prefix::planets WHERE planet_id = " .
                               ":planet_id AND owner = :owner";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindParam(':planet_id', $planet_id, \PDO::PARAM_INT);
                        $stmt->bindParam(':owner', $ship_id, \PDO::PARAM_INT);
                        $stmt->execute();
                        $ship_id = $stmt->fetch(\PDO::FETCH_COLUMN);
                        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                        $sql = "UPDATE ::prefix::planets SET " .
                               $commod_type . " = :commodity_type " .
                               "WHERE planet_id = :planet_id " .
                               "AND owner = :owner";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindParam(':commodity_type', $prodpercent, \PDO::PARAM_STR);
                        $stmt->bindParam(':planet_id', $planet_id, \PDO::PARAM_INT);
                        $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
                        $stmt->execute();
                        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                        $sql = "UPDATE ::prefix::planets SET " .
                               "sells = :sells " .
                               "WHERE planet_id = :planet_id " .
                               "AND owner = :owner";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindValue(':sells', 'N', \PDO::PARAM_STR);
                        $stmt->bindParam(':planet_id', $planet_id, \PDO::PARAM_INT);
                        $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
                        $stmt->execute();
                        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                        $sql = "UPDATE ::prefix::planets SET team = :team " .
                               "WHERE planet_id = :planet_id " .
                               "AND owner = :owner";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindValue(':team', 0, \PDO::PARAM_INT);
                        $stmt->bindParam(':planet_id', $planet_id, \PDO::PARAM_INT);
                        $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
                        $stmt->execute();
                        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                    }
                    elseif ($commod_type == "sells")
                    {
                        $sql = "UPDATE ::prefix::planets SET sells = :sells " .
                               "WHERE planet_id = :planet_id " .
                               "AND owner = :owner";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindValue(':sells', 'Y', \PDO::PARAM_STR);
                        $stmt->bindParam(':planet_id', $planet_id, \PDO::PARAM_INT);
                        $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
                        $stmt->execute();
                        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                    }
                    elseif ($commod_type == "team")
                    {
                        // Compare entered team_id and one in the db, if different then use one from db
                        $res = $old_db->Execute("SELECT {$old_db->prefix}ships.team as owner FROM {$old_db->prefix}ships, {$old_db->prefix}planets WHERE ( {$old_db->prefix}ships.ship_id = {$old_db->prefix}planets.owner ) AND ( {$old_db->prefix}planets.planet_id = ?);", array($prodpercent));
                        \Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
                        if ($res)
                        {
                            $team_id = $res->fields['owner'];
                        }
                        else
                        {
                            $team_id = 0;
                        }

                        $sql = "UPDATE ::prefix::planets SET team = :team " .
                               "WHERE planet_id = :planet_id " .
                               "AND owner = :owner";
                        $stmt = $pdo_db->prepare($sql);
                        $stmt->bindParam(':team', $team_id, \PDO::PARAM_INT);
                        $stmt->bindParam(':planet_id', $planet_id, \PDO::PARAM_INT);
                        $stmt->bindParam(':ship_id', $ship_id, \PDO::PARAM_INT);
                        $stmt->execute();
                        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                    }
                }
            }
        }

        echo "<br>";
        echo $langvars['l_pr_prod_updated'] . "<br><br>";
        echo $langvars['l_pr_checking_values'] . "<br><br>";

        $res = $old_db->Execute("SELECT * FROM {$old_db->prefix}planets WHERE owner = ? ORDER BY sector_id;", array($ship_id));
        \Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
        $counter = 0;
        $planets = array();
        if ($res)
        {
            while (!$res->EOF)
            {
                $planets[$counter] = $res->fields;
                $counter++;
                $res->MoveNext();
            }

            foreach ($planets as $planet)
            {
                if (empty($planet['name']))
                {
                    $planet['name'] = $langvars['l_unnamed'];
                }

                if ($planet['prod_ore'] < 0)
                {
                    $planet['prod_ore'] = 110;
                }

                if ($planet['prod_organics'] < 0)
                {
                    $planet['prod_organics'] = 110;
                }

                if ($planet['prod_goods'] < 0)
                {
                    $planet['prod_goods'] = 110;
                }

                if ($planet['prod_energy'] < 0)
                {
                    $planet['prod_energy'] = 110;
                }

                if ($planet['prod_fighters'] < 0)
                {
                    $planet['prod_fighters'] = 110;
                }

                if ($planet['prod_torp'] < 0)
                {
                    $planet['prod_torp'] = 110;
                }

                if ($planet['prod_ore'] + $planet['prod_organics'] + $planet['prod_goods'] + $planet['prod_energy'] + $planet['prod_fighters'] + $planet['prod_torp'] > 100)
                {
                    if (!empty($planet['name']) && !empty($planet['sector_id']))
                    {
                        $temp1 = str_replace("[planet_name]", (string) $planet['name'], (string) $langvars['l_pr_value_reset']);
                        $temp2 = str_replace("[sector_id]", (string) $planet['sector_id'], $temp1);
                        echo $temp2 . "<br>";
                    }

                    $sql = "UPDATE ::prefix::planets SET " .
                           "prod_ore = :prod_ore " .
                           "WHERE planet_id = :planet_id";
                    $stmt = $pdo_db->prepare($sql);
                    $prod_ore = $tkireg->default_prod_ore;
                    $stmt->bindParam(':prod_ore', $prod_ore, \PDO::PARAM_STR);
                    $stmt->bindParam(':planet_id', $planet['planet_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                    $sql = "UPDATE ::prefix::planets SET " .
                           "prod_organics = :prod_organics " .
                           "WHERE planet_id = :planet_id";
                    $stmt = $pdo_db->prepare($sql);
                    $prod_organics = $tkireg->default_prod_organics;
                    $stmt->bindParam(':prod_organics', $prod_organics, \PDO::PARAM_STR);
                    $stmt->bindParam(':planet_id', $planet['planet_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                    $sql = "UPDATE ::prefix::planets SET " .
                           "prod_goods = :prod_goods " .
                           "WHERE planet_id = :planet_id";
                    $stmt = $pdo_db->prepare($sql);
                    $prod_goods = $tkireg->default_prod_goods;
                    $stmt->bindParam(':prod_goods', $prod_goods, \PDO::PARAM_STR);
                    $stmt->bindParam(':planet_id', $planet['planet_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                    $sql = "UPDATE ::prefix::planets SET " .
                           "prod_energy = :prod_energy " .
                           "WHERE planet_id = :planet_id";
                    $stmt = $pdo_db->prepare($sql);
                    $prod_energy = $tkireg->default_prod_energy;
                    $stmt->bindParam(':prod_energy', $prod_energy, \PDO::PARAM_STR);
                    $stmt->bindParam(':planet_id', $planet['planet_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                    $sql = "UPDATE ::prefix::planets SET " .
                           "prod_fighters = :prod_fighters " .
                           "WHERE planet_id = :planet_id";
                    $stmt = $pdo_db->prepare($sql);
                    $prod_fighters = $tkireg->default_prod_fighters;
                    $stmt->bindParam(':prod_fighters', $prod_fighters, \PDO::PARAM_STR);
                    $stmt->bindParam(':planet_id', $planet['planet_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

                    $sql = "UPDATE ::prefix::planets SET " .
                           "prod_torp = :prod_torp " .
                           "WHERE planet_id = :planet_id";
                    $stmt = $pdo_db->prepare($sql);
                    $prod_torp = $tkireg->default_prod_torp;
                    $stmt->bindParam(':prod_torp', $prod_torp, \PDO::PARAM_STR);
                    $stmt->bindParam(':planet_id', $planet['planet_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                    \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);
                }
            }
        }
    }
}
