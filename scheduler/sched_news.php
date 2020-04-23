<?php declare(strict_types = 1);
/**
 * scheduler/sched_news.php from The Kabal Invasion.
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

// FUTURE: PDO, better output feedback, better debugging
$langvars = Tki\Translate::load($pdo_db, $lang, array('scheduler'));

echo "<strong>" . $langvars['l_sched_news_title'] . "</strong><br>\n";
$sql = $db->Execute("SELECT IF(COUNT(*)>0, SUM(colonists), 0) AS total_colonists, COUNT(owner) AS total_planets,  owner, character_name FROM {$db->prefix}planets, {$db->prefix}ships WHERE owner != '0' AND owner=ship_id GROUP BY owner ORDER BY owner ASC;");
Tki\Db::logDbErrors($pdo_db, $sql, __LINE__, __FILE__);

while (!$sql->EOF)
{
    $row = $sql->fields;

    // Get the owner name.
    $name = $row['character_name'];
    $langvars['l_sched_news_processing'] = str_replace("[name]", $name, $langvars['l_sched_news_processing']);
    $langvars['l_sched_news_processing'] = str_replace("[owner]", $row['owner'], $langvars['l_sched_news_processing']);
    $langvars['l_sched_news_processing'] = str_replace("[planet_row]", number_format($row['total_planets']), $langvars['l_sched_news_processing']);
    $langvars['l_sched_news_processing'] = str_replace("[colonists_row]", number_format($row['total_colonists']), $langvars['l_sched_news_processing']);
    echo "&nbsp;&nbsp;" . $langvars['l_sched_news_processing'] . "<br>\n";

    // Generation of planet amount
    if ($row['total_planets'] >= 1000)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'planet1000';", array($row['owner']));
        Tki\Db::logDbErrors($pdo_db, $sql2, __LINE__, __FILE__);

        if ($sql2->EOF)
        {
            $planetcount = 1000;
            $langvars['l_news_p_headline2'] = str_replace("[player]", $name, $langvars['l_news_p_headline']);
            $headline = $langvars['l_news_p_headline2'] . " " . $planetcount . " " . $langvars['l_news_planets'];
            $langvars['l_news_p_text1002'] = str_replace("[name]", $name, $langvars['l_news_p_text1000']);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'planet1000');", array($headline, $langvars['l_news_p_text1002'], $row['owner']));
            Tki\Db::logDbErrors($pdo_db, $news, __LINE__, __FILE__);
        }
    }
    elseif ($row['total_planets'] >= 500)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'planet500';", array($row['owner']));
        Tki\Db::logDbErrors($pdo_db, $sql2, __LINE__, __FILE__);

        if ($sql2->EOF)
        {
            $planetcount = 500;
            $langvars['l_news_p_headline2'] = str_replace("[player]", $name, $langvars['l_news_p_headline']);
            $headline = $langvars['l_news_p_headline2'] . " " . $planetcount . " " . $langvars['l_news_planets'];
            $langvars['l_news_p_text502'] = str_replace("[name]", $name, $langvars['l_news_p_text500']);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'planet500');", array($headline, $langvars['l_news_p_text502'], $row['owner']));
            Tki\Db::logDbErrors($pdo_db, $news, __LINE__, __FILE__);
        }
    }
    elseif ($row['total_planets'] >= 250)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'planet250';", array($row['owner']));
        Tki\Db::logDbErrors($pdo_db, $sql2, __LINE__, __FILE__);

        if ($sql2->EOF)
        {
            $planetcount = 250;
            $langvars['l_news_p_headline2'] = str_replace("[player]", $name, $langvars['l_news_p_headline']);
            $headline = $langvars['l_news_p_headline2'] . " " . $planetcount . " " . $langvars['l_news_planets'];
            $langvars['l_news_p_text2502'] = str_replace("[name]", $name, $langvars['l_news_p_text250']);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'planet250');", array($headline, $langvars['l_news_p_text2502'], $row['owner']));
            Tki\Db::logDbErrors($pdo_db, $news, __LINE__, __FILE__);
        }
    }
    elseif ($row['total_planets'] >= 100)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'planet100';", array($row['owner']));
        Tki\Db::logDbErrors($pdo_db, $sql2, __LINE__, __FILE__);

        if ($sql2->EOF)
        {
            $planetcount = 100;
            $langvars['l_news_p_headline2'] = str_replace("[player]", $name, $langvars['l_news_p_headline']);
            $headline = $langvars['l_news_p_headline2'] . " " . $planetcount . " " . $langvars['l_news_planets'];
            $langvars['l_news_p_text102'] = str_replace("[name]", $name, $langvars['l_news_p_text100']);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'planet100');", array($headline, $langvars['l_news_p_text102'], $row['owner']));
            Tki\Db::logDbErrors($pdo_db, $news, __LINE__, __FILE__);
        }
    }
    elseif ($row['total_planets'] >= 50)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'planet50';", array($row['owner']));
        Tki\Db::logDbErrors($pdo_db, $sql2, __LINE__, __FILE__);

        if ($sql2->EOF)
        {
            $planetcount = 50;
            $langvars['l_news_p_headline2'] = str_replace("[player]", $name, $langvars['l_news_p_headline']);
            $headline = $langvars['l_news_p_headline2'] . " " . $planetcount . " " . $langvars['l_news_planets'];
            $langvars['l_news_p_text502'] = str_replace("[name]", $name, $langvars['l_news_p_text50']);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'planet50');", array($headline, $langvars['l_news_p_text502'], $row['owner']));
            Tki\Db::logDbErrors($pdo_db, $news, __LINE__, __FILE__);
        }
    }
    elseif ($row['total_planets'] >= 25)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'planet25';", array($row['owner']));
        Tki\Db::logDbErrors($pdo_db, $sql2, __LINE__, __FILE__);

        if ($sql2->EOF)
        {
            $planetcount = 25;
            $langvars['l_news_p_headline2'] = str_replace("[player]", $name, $langvars['l_news_p_headline']);
            $headline = $langvars['l_news_p_headline2'] . " " . $planetcount . " " . $langvars['l_news_planets'];
            $langvars['l_news_p_text252'] = str_replace("[name]", $name, $langvars['l_news_p_text25']);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'planet25');", array($headline, $langvars['l_news_p_text252'], $row['owner']));
            Tki\Db::logDbErrors($pdo_db, $news, __LINE__, __FILE__);
        }
    }
    elseif ($row['total_planets'] >= 10)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'planet10'", array($row['owner']));
        Tki\Db::logDbErrors($pdo_db, $sql2, __LINE__, __FILE__);

        if ($sql2->EOF)
        {
            $planetcount = 10;
            $langvars['l_news_p_headline2'] = str_replace("[player]", $name, $langvars['l_news_p_headline']);
            $headline = $langvars['l_news_p_headline2'] . " " . $planetcount . " " . $langvars['l_news_planets'];
            $langvars['l_news_p_text102'] = str_replace("[name]", $name, $langvars['l_news_p_text10']);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'planet10');", array($headline, $langvars['l_news_p_text102'], $row['owner']));
            Tki\Db::logDbErrors($pdo_db, $news, __LINE__, __FILE__);
        }
    }
    elseif ($row['total_planets'] >= 5)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'planet5';", array($row['owner']));
        Tki\Db::logDbErrors($pdo_db, $sql2, __LINE__, __FILE__);

        if ($sql2->EOF)
        {
            $planetcount = 5;
            $langvars['l_news_p_headline2'] = str_replace("[player]", $name, $langvars['l_news_p_headline']);
            $headline = $langvars['l_news_p_headline2'] . " " . $planetcount . " " . $langvars['l_news_planets'];
            $langvars['l_news_p_text52'] = str_replace("[name]", $name, $langvars['l_news_p_text5']);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'planet5');", array($headline, $langvars['l_news_p_text52'], $row['owner']));
            Tki\Db::logDbErrors($pdo_db, $news, __LINE__, __FILE__);
        }
    } // End generation of planet amount

    // Generation of colonist amount
    if ($row['total_colonists'] >= 1000000000)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'col1000';", array($row['owner']));
        Tki\Db::logDbErrors($pdo_db, $sql2, __LINE__, __FILE__);

        if ($sql2->EOF)
        {
            $colcount = 1000;
            $langvars['l_news_p_headline2'] = str_replace("[player]", $name, $langvars['l_news_p_headline']);
            $headline = $langvars['l_news_p_headline2'] . " " . $colcount . " " . $langvars['l_news_cols'];
            $langvars['l_news_c_text10002'] = str_replace("[name]", $name, $langvars['l_news_c_text1000']);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'col1000');", array($headline, $langvars['l_news_c_text10002'], $row['owner']));
            Tki\Db::logDbErrors($pdo_db, $news, __LINE__, __FILE__);
        }
    }
    elseif ($row['total_colonists'] >= 500000000)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'col500';", array($row['owner']));
        Tki\Db::logDbErrors($pdo_db, $sql2, __LINE__, __FILE__);

        if ($sql2->EOF)
        {
            $colcount = 500;
            $langvars['l_news_p_headline2'] = str_replace("[player]", $name, $langvars['l_news_p_headline']);
            $headline = $langvars['l_news_p_headline2'] . " " . $colcount . " " . $langvars['l_news_cols'];
            $langvars['l_news_c_text5002'] = str_replace("[name]", $name, $langvars['l_news_c_text500']);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'col500');", array($headline, $langvars['l_news_c_text5002'], $row['owner']));
            Tki\Db::logDbErrors($pdo_db, $news, __LINE__, __FILE__);
        }
    }
    elseif ($row['total_colonists'] >= 100000000)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'col100';", array($row['owner']));
        Tki\Db::logDbErrors($pdo_db, $sql2, __LINE__, __FILE__);

        if ($sql2->EOF)
        {
            $colcount = 100;
            $langvars['l_news_p_headline2'] = str_replace("[player]", $name, $langvars['l_news_p_headline']);
            $headline = $langvars['l_news_p_headline2'] . " " . $colcount . " " . $langvars['l_news_cols'];
            $langvars['l_news_c_text1002'] = str_replace("[name]", $name, $langvars['l_news_c_text100']);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'col100');", array($headline, $langvars['l_news_c_text1002'], $row['owner']));
            Tki\Db::logDbErrors($pdo_db, $news, __LINE__, __FILE__);
        }
    }
    elseif ($row['total_colonists'] >= 25000000)
    {
        $sql2 = $db->Execute("SELECT * FROM {$db->prefix}news WHERE user_id = ? AND news_type = 'col25';", array($row['owner']));
        Tki\Db::logDbErrors($pdo_db, $sql2, __LINE__, __FILE__);

        if ($sql2->EOF)
        {
            $colcount = 25;
            $langvars['l_news_p_headline2'] = str_replace("[player]", $name, $langvars['l_news_p_headline']);
            $headline = $langvars['l_news_p_headline2'] . " " . $colcount . " " . $langvars['l_news_cols'];
            $langvars['l_news_c_text252'] = str_replace("[name]", $name, $langvars['l_news_c_text25']);
            $news = $db->Execute("INSERT INTO {$db->prefix}news (headline, newstext, user_id, date, news_type) VALUES (?, ?, ?, NOW(), 'col25');", array($headline, $langvars['l_news_c_text252'], $row['owner']));
            Tki\Db::logDbErrors($pdo_db, $news, __LINE__, __FILE__);
        }
    }

    // End generation of colonist amount
    $sql->MoveNext();
} // while

echo "<strong>" . $langvars['l_sched_news_end'] . "</strong><br><br>";
$multiplier = 0; // No need to run this again
