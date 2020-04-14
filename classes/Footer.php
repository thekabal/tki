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
// File: classes/Footer.php
//
// FUTURE: This file should only be used when we have not converted a file to use templates.
// Once they use templates, the footer will be loaded correctly by layout.tpl

namespace Tki;

use Symfony\Component\HttpFoundation\Request;

class Footer
{
    public function display(\PDO $pdo_db, string $lang, $tkireg, Smarty $template): void
    {
        $request = Request::createFromGlobals();

        // Now set a container for the variables and langvars and send them off to the template system
        $variables = array();
        $langvars = array();

        $online = 0;
        if (Db::isActive($pdo_db))
        {
            $cur_time_stamp = date("Y-m-d H:i:s", time()); // Now (as seen by PHP)
            $since_stamp = date("Y-m-d H:i:s", time() - 5 * 60); // Five minutes ago

            // Build a player gateway object to handle the SQL calls
            $players_gateway = new Players\PlayersGateway($pdo_db);

            // Online is the (int) count of the numbers of players currently logged in via SQL select
            $online = $players_gateway->selectPlayersLoggedIn($since_stamp, $cur_time_stamp);
        }

        $elapsed = 999; // Default value for elapsed, overridden with an actual value if its available
        if (($tkireg !== null) && (property_exists($tkireg, 'tkitimer')))
        {
            $tkireg->tkitimer->stop();
            $elapsed = $tkireg->tkitimer->elapsed();
        }

        // Suppress the news ticker on the IBANK and index pages
        $news_ticker_active = ((bool) preg_match("/index.php/i", (string) $request->server->get('SCRIPT_NAME')) || (bool) preg_match("/ibank.php/i", (string) $request->server->get('SCRIPT_NAME')) || (bool) preg_match("/new.php/i", (string) $request->server->get('SCRIPT_NAME')));

        // Suppress the news ticker if the database is not active
        if (!Db::isActive($pdo_db))
        {
            $news_ticker_active = false;
        }

        // Update counter
        // Build a scheduler gateway object to handle the SQL calls
        $scheduler_gateway = new Scheduler\SchedulerGateway($pdo_db);

        // Last run is the (int) count of the numbers of players currently
        // logged in via SQL select or false if DB is not active
        $last_run = $scheduler_gateway->selectSchedulerLastRun();
        if (!is_null($last_run))
        {
            $seconds_left = ($tkireg->sched_ticks * 60) - (time() - $last_run);
            $show_update_ticker = true;
        }
        else
        {
            $seconds_left = 0;
            $show_update_ticker = false;
        }

        // End update counter

        if ($news_ticker_active === true)
        {
            // Database driven language entries
            $langvars_temp = Translate::load($pdo_db, $lang, array('news',
                                                                   'common',
                                                                   'footer',
                                                                   'global_includes',
                                                                   'logout'));

            // Use array merge so that we do not clobber the langvars array,
            // and only add to it the items needed for footer
            $langvars = array_merge($langvars, $langvars_temp);

            // Use array unique so that we don't end up with duplicate lang array entries
            // This is resulting in an array with blank values for specific keys,
            // so array_unique isn't entirely what we want
            // $langvars = array_unique ($langvars);

            // SQL call that selects all of the news items between the start date beginning of day, and the end of day.
            $news_gateway = new News\NewsGateway($pdo_db); // Build a scheduler gateway object to handle the SQL calls
            $row = $news_gateway->selectNewsByDay(date('Y-m-d'));
            // Future: Handle bad row return, as it's causing issues for count($row)

            $news_ticker = array();
            if ($row === null)
            {
                array_push($news_ticker, array('url' => null,
                                               'text' => $langvars['l_news_none'],
                                               'type' => null,
                                               'delay' => 5));
            }
            else
            {
                foreach ($row as $item)
                {
                    array_push($news_ticker, array('url' => "news.php",
                                                   'text' => $item['headline'],
                                                   'type' => $item['news_type'],
                                                   'delay' => 5));
                }

                array_push($news_ticker, array('url' => null, 'text' => "End of News", 'type' => null, 'delay' => 5));
            }

            $news_ticker['container'] = "article";
            $template->addVariables("news", $news_ticker);
        }

        $mem_peak_usage = floor(memory_get_peak_usage() / 1024);
        $public_pages = array('ranking.php', 'new.php', 'faq.php', 'settings.php', 'news.php', 'index.php');
        $slash_position = strrpos($request->server->get('SCRIPT_NAME'), '/');
        $slash_position = (int) $slash_position + 1;
        $current_page = substr($request->server->get('SCRIPT_NAME'), $slash_position);
        if (in_array($current_page, $public_pages, true))
        {
            // If it is a non-login required page, such as ranking, new, faq,
            // settings, news, and index use the public SF logo, which increases project stats.
            $variables['suppress_logo'] = false;
        }
        else
        {
            // Else suppress the logo, so it is as fast as possible.
            $variables['suppress_logo'] = true;
        }

        // Set array with all used variables in page
        $variables['update_ticker'] = array("display" => $show_update_ticker,
                                            "seconds_left" => $seconds_left,
                                            "sched_ticks" => $tkireg->sched_ticks);
        $variables['players_online'] = $online;
        $variables['elapsed'] = $elapsed;
        $variables['mem_peak_usage'] = $mem_peak_usage;
        $variables['footer_show_debug'] = $tkireg->footer_show_debug;
        $variables['cur_year'] = date('Y');

        $template->addVariables('langvars', $langvars);
        $template->addVariables('variables', $variables);
        $template->display('footer.tpl');
    }
}
