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
// File: sched_ranking.php

// FUTURE: Use a single FetchAll to grab all users at once, process as an array, and switch to PDO, improve debug/output handling
$langvars = Tki\Translate::load($pdo_db, $lang, array('scheduler'));

echo "<strong>" . $langvars['l_sched_ranking_title'] . "</strong><br><br>";
$res = $db->Execute("SELECT ship_id FROM {$db->prefix}ships WHERE ship_destroyed='N'");
Tki\Db::logDbErrors($pdo_db, $res, __LINE__, __FILE__);
while (!$res->EOF)
{
    Tki\Score::updateScore($pdo_db, $res->fields['ship_id'], $tkireg, $playerinfo);
    $res->MoveNext();
}

echo "<br>";
$multiplier = 0;
