<?php declare(strict_types = 1);
/**
 * classes/TraderouteDelete.php from The Kabal Invasion.
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

class TraderouteDelete
{
    public static function prime(
        \PDO $pdo_db,
        string $lang,
        array $langvars,
        Reg $tkireg,
        Smarty $template,
        array $playerinfo,
        string $confirm,
        ?int $traderoute_id = null
    ): void
    {
        $sql = "SELECT * FROM ::prefix::traderoutes WHERE traderoute_id=:traderoute_id";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindParam(':traderoute_id', $traderoute_id, \PDO::PARAM_INT);
        $result = $stmt->execute();
        \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

        // This is nonsense code to keep from testing as if playerinfo isn't used
        if ($playerinfo['ship_id'] == -1)
        {
            $stmt = 'blah';
        }

        /*
        if (!$query || $query->EOF)
        {
            \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $template, $langvars['l_tdr_doesntexist']);
        }

        $delroute = $query->fields;

        if ($delroute['owner'] != $playerinfo['ship_id'])
        {
            \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $template, $langvars['l_tdr_notowntdr']);
        }*/

        if ($confirm === "yes")
        {
            $sql = "DELETE FROM ::prefix::traderoutes WHERE traderoute_id=:traderoute_id";
            $stmt = $pdo_db->prepare($sql);
            $stmt->bindParam(':traderoute_id', $traderoute_id, \PDO::PARAM_INT);
            $result = $stmt->execute();
            \Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

            $langvars['l_tdr_returnmenu'] = str_replace("[here]", "<a href='traderoute.php'>" . $langvars['l_here'] . "</a>", $langvars['l_tdr_returnmenu']);
            echo $langvars['l_tdr_deleted'] . " " . $langvars['l_tdr_returnmenu'];
            \Tki\TraderouteDie::die($pdo_db, $lang, $tkireg, $template, null);
        }
    }
}
