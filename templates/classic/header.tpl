{*
    The Kabal Invasion - A web-based 4X space game
    Copyright © 2014 The Kabal Invasion development team, Ron Harwood, and the BNT development team.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

    File: header.tpl
*}
<!DOCTYPE html>
<html lang="{$langvars['l_lang_attribute']}">
<!-- START OF HEADER -->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="Description" content="A free online game - Open source, web game, with multiplayer space exploration">
    <meta name="Keywords" content="Free, online, game, Open source, web game, multiplayer, space, exploration, 4x">
    <meta name="Rating" content="General">
    <link rel="shortcut icon" href="{$template_dir}/images/bntfavicon.ico">
    <link rel="stylesheet" type="text/css" href="{$template_dir}/styles/bnt-prime.css.php">
{if $variables['body_class'] != 'bnt'}
    <link rel="stylesheet" type="text/css" href="{$template_dir}/styles/{$variables['body_class']}.css.php">
{else}
    <link rel="stylesheet" type="text/css" href="{$template_dir}/styles/main.css.php">
{/if}
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Ubuntu">
{if isset($variables['title'])}
    <title>{block name=title}{$variables['title']}{/block}</title>
{/if}
{if isset($variables['include_ckeditor'])}
    <script src="{$template_dir}/javascript/ckeditor/ckeditor.js"></script>
{/if}
    <script async src="{$template_dir}/javascript/framebuster.js.php"></script>
  </head>
<!-- END OF HEADER -->
