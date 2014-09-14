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
// File: classes/Mines.php

namespace Tki;

class Mines
{
    public static function explode($db, $sector, $num_mines)
    {
        $secdef_result = $db->Execute("SELECT * FROM {$db->prefix}sector_defence WHERE sector_id = ? AND defence_type ='M' ORDER BY QUANTITY ASC", array($sector));
        Db::logDbErrors($db, $secdef_result, __LINE__, __FILE__);

        // Put the defence information into the array "defenceinfo"
        if ($secdef_result instanceof ADORecordSet)
        {
            while (!$secdef_result->EOF && $num_mines > 0)
            {
                $row = $secdef_result->fields;
                if ($row['quantity'] > $num_mines)
                {
                    $update_res = $db->Execute("UPDATE {$db->prefix}sector_defence SET quantity = quantity - ? WHERE defence_id = ?", array($num_mines, $row['defence_id']));
                    Db::logDbErrors($db, $update_res, __LINE__, __FILE__);
                    $num_mines = 0;
                }
                else
                {
                    $update_res = $db->Execute("DELETE FROM {$db->prefix}sector_defence WHERE defence_id = ?", array($row['defence_id']));
                    Db::logDbErrors($db, $update_res, __LINE__, __FILE__);
                    $num_mines -= $row['quantity'];
                }
                $secdef_result->MoveNext();
            }
        }
    }
}
