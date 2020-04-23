<?php declare(strict_types = 1);
/**
 * classes/KabalRegen.php from The Kabal Invasion.
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

class KabalRegen
{
    public static function regen(\PDO $pdo_db, array $playerinfo, int $kabal_unemployment, Reg $tkireg): void
    {
        $gena = null;
        $gene = null;
        $genf = null;
        $gent = null;

        // Kabal Unempoyment Check
        $playerinfo['credits'] = $playerinfo['credits'] + $kabal_unemployment;
        $maxenergy = \Tki\CalcLevels::energy($playerinfo['power'], $tkireg); // Regenerate energy
        if ($playerinfo['ship_energy'] <= ($maxenergy - 50))  // Stop regen when within 50 of max
        {
            $playerinfo['ship_energy'] = $playerinfo['ship_energy'] + round(($maxenergy - $playerinfo['ship_energy']) / 2); // Regen half of remaining energy
            $gene = "regenerated Energy to $playerinfo[ship_energy] units,";
        }

        $maxarmor = \Tki\CalcLevels::abstractLevels($playerinfo['armor'], $tkireg); // Regenerate armor
        if ($playerinfo['armor_pts'] <= ($maxarmor - 50))  // Stop regen when within 50 of max
        {
            $playerinfo['armor_pts'] = $playerinfo['armor_pts'] + round(($maxarmor - $playerinfo['armor_pts']) / 2); // Regen half of remaining armor
            $gena = "regenerated Armor to $playerinfo[armor_pts] points,";
        }

        // Buy fighters & torpedos at 6 credits per fighter
        $available_fighters = \Tki\CalcLevels::abstractLevels($playerinfo['computer'], $tkireg) - $playerinfo['ship_fighters'];
        if (($playerinfo['credits'] > 5) && ($available_fighters > 0))
        {
            if (round($playerinfo['credits'] / 6) > $available_fighters)
            {
                $purchase = ($available_fighters * 6);
                $playerinfo['credits'] = $playerinfo['credits'] - $purchase;
                $playerinfo['ship_fighters'] = $playerinfo['ship_fighters'] + $available_fighters;
                $genf = "purchased $available_fighters fighters for $purchase credits,";
            }

            if (round($playerinfo['credits'] / 6) <= $available_fighters)
            {
                $purchase = (round($playerinfo['credits'] / 6));
                $playerinfo['ship_fighters'] = $playerinfo['ship_fighters'] + $purchase;
                $genf = "purchased $purchase fighters for $playerinfo[credits] credits,";
                $playerinfo['credits'] = 0;
            }
        }

        // Kabal pay 3 credits per torpedo
        $available_torpedoes = \Tki\CalcLevels::abstractLevels($playerinfo['torp_launchers'], $tkireg) - $playerinfo['torps'];
        if (($playerinfo['credits'] > 2) && ($available_torpedoes > 0))
        {
            if (round($playerinfo['credits'] / 3) > $available_torpedoes)
            {
                $purchase = ($available_torpedoes * 3);
                $playerinfo['credits'] = $playerinfo['credits'] - $purchase;
                $playerinfo['torps'] = $playerinfo['torps'] + $available_torpedoes;
                $gent = "purchased $available_torpedoes torpedoes for $purchase credits,";
            }

            if (round($playerinfo['credits'] / 3) <= $available_torpedoes)
            {
                $purchase = (round($playerinfo['credits'] / 3));
                $playerinfo['torps'] = $playerinfo['torps'] + $purchase;
                $gent = "purchased $purchase torpedoes for $playerinfo[credits] credits,";
                $playerinfo['credits'] = 0;
            }
        }

        // Update Kabal record
        $sql = "UPDATE ::prefix::ships SET ship_energy = :ship_energy, armor_pts = :armor_pts, ship_fighters = :ship_fighters, torps = :torps, credits = :credits WHERE ship_id = :ship_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':ship_energy', $playerinfo['ship_energy'], \PDO::PARAM_INT);
        $stmt->bindParam(':armor_pts', $playerinfo['armor_pts'], \PDO::PARAM_INT);
        $stmt->bindParam(':ship_fighters', $playerinfo['ship_fighters'], \PDO::PARAM_INT);
        $stmt->bindParam(':torps', $playerinfo['torps'], \PDO::PARAM_INT);
        $stmt->bindParam(':credits', $playerinfo['credits'], \PDO::PARAM_INT);
        $stmt->bindParam(':ship_id', $playerinfo['ship_id'], \PDO::PARAM_INT);
        $stmt->execute();

        if ($gene !== null || $gena !== null || $genf !== null || $gent !== null)
        {
            \Tki\PlayerLog::writeLog($pdo_db, $playerinfo['ship_id'], LogEnums::RAW, "Kabal $gene $gena $genf $gent and has been updated.");
        }
    }
}
