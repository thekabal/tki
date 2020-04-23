<?php declare(strict_types = 1);
/**
 * classes/TraderouteDie.php from The Kabal Invasion.
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

class TraderouteDie
{
    public static function die(
        \PDO $pdo_db,
        string $lang,
        Reg $tkireg,
        Smarty $template,
        ?string $error_msg = null
    ): void
    {
        echo "<p>" . $error_msg . "<p>";
        echo "<div style='text-align:left;'>\n";
        \Tki\Text::gotoMain($pdo_db, $lang);
        echo "</div>\n";

        $footer = new \Tki\Footer();
        $footer->display($pdo_db, $lang, $tkireg, $template);
        die();
    }
}
