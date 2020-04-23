<?php declare(strict_types = 1);
/**
 * classes/Languages.php from The Kabal Invasion.
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

class Languages
{
    public static function listAvailable(\PDO $pdo_db, string $lang): array
    {
        // Get a list of supported languages
        $sql = "SELECT section, name, value FROM ::prefix::languages WHERE " .
               "category = :category AND (name = :name1 OR name = :name2) ORDER BY section, name;";
        $stmt = $pdo_db->prepare($sql);
        $stmt->bindValue(':category', 'regional', \PDO::PARAM_STR);
        $stmt->bindValue(':name1', 'local_lang_name', \PDO::PARAM_INT);
        $stmt->bindValue(':name2', 'local_lang_flag', \PDO::PARAM_INT);
        $stmt->execute();
        $lang_rs = $stmt->fetchAll();
        $list_of_langs = array();
        if (is_array($lang_rs) === true && count($lang_rs) >= 2)
        {
            foreach ($lang_rs as $langinfo)
            {
                if (array_key_exists($langinfo['section'], $list_of_langs) === false)
                {
                    $list_of_langs[$langinfo['section']] = array();
                }

                switch ($langinfo['name'])
                {
                    case 'local_lang_flag':
                        $list_of_langs[$langinfo['section']] = array_merge(
                            $list_of_langs[$langinfo['section']],
                        array('flag' => $langinfo['value']));
                        break;

                    case 'local_lang_name':
                        $list_of_langs[$langinfo['section']] = array_merge(
                            $list_of_langs[$langinfo['section']],
                        array('lang_name' => $langinfo['value']));
                        break;
                    default: // Future: Handle this better
                        break;
                }
            }

            // Extract our default language, and remove it from the list of supported languages.
            $our_lang = $list_of_langs[$lang];
            unset($list_of_langs[$lang]);

            // Add our default language back in, this should be put at the end of the list.
            $list_of_langs[$lang] = $our_lang;
            unset($our_lang);
        }

        // Return the list of installed languages
        return $list_of_langs;
    }
}
