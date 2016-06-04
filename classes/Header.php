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
// File: classes/Header.php
//
// FUTURE: This file should only be used when we have not converted a file to use templates.
// Once they use templates, the header will be loaded correctly by layout.tpl

namespace Tki;

class Header
{
    public static function display($pdo_db, $lang, Smarty $template, $title = null, $body_class = 'tki', $include_ckeditor = false)
    {
        $langvars = Translate::load($pdo_db, $lang, array('common'));

        $variables = null;
        $variables['lang'] = $lang;

        // Body class defines a css file for a specific page, if one isn't defined, it defaults to tki, which is
        // nulled by the template.
        $variables['body_class'] = $body_class;

        if ($title !== null)
        {
            $variables['title'] = $title;
        }

        // Some pages (like mailto) include ckeditor js, check if this is one of those.
        $variables['include_ckeditor'] = $include_ckeditor;

        $template->addVariables('langvars', $langvars);
        $template->addVariables('variables', $variables);
        $template->display('header.tpl');
        // Perhaps this should return the template instead of kicking off display. I'm not sure.
    }
}

