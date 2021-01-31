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

    File: footer.tpl
*}
<!-- START OF FOOTER -->
<div class="footer">
{if isset($news)}
<br>
<script type="text/javascript" src="{$template_dir}/javascript/newsticker.js.php"></script>
<p id="news_ticker" class="faderlines" style="width:602px; margin:auto; text-align:center;">{$langvars['l_news_broken']}</p>
<script>
// News Ticker Constructor.
news = new newsTicker();

// I have put in some safaty precautions, but just in case always check the return value from initTicker().
if (news.initTicker("news_ticker") == true)
{
    // Set the width of the Ticker (in pixles)
    news.Width(500);

    // Sets the Interval/Update Time in seconds.
    news.Interval(5);

    // I have decided on adding single news articles at a time due to it makes it more easier to add when using PHP or XSL.
    // We can supply the information by either of the following ways:
    // 1: Supply the information from a Database and inserting it with PHP.
    // 2: Supply the information from a Database and convert it into XML (for formatting) and have the XSLT Stylesheet extract the information and insert it.
{* Cycle through the player list *}
{foreach $news as $article}
    news.addArticle('{$article['url']}', '{$article['text']}', '{$article['type']}', {$article['delay']});
{/foreach}

    // Starts the Ticker.
    news.startTicker();

    // If for some reason you need to stop the Ticker use the following line.
    // news.stopTicker();
}
</script>

{/if}

{* Items to the left (SF logo) and to the right (mem, copyright, news) *}
    <p style='font-size:smaller; float:left; text-align:left'><a class="new_link" href='https://github.com/thekabal/tki'>Github project page for The Kabal Invasion</a>
    <br><a class="new_link" href="copyright.php">&copy; {$cur_year} The TKI development team</a></p>
    <p style="font-size:smaller; float:right; text-align:right"><a class="new_link" href="news.php">{$langvars['l_local_news']}</a>

    <br>{$elapsed} {$langvars['l_seconds']} {$langvars['l_time_gen_page']} / {$mem_peak_usage}{$langvars['l_peak_mem']}
</p>
<p style="text-align:center;">

{* Handle the Servers Update Ticker here *}
{if isset($update_ticker['display']) && $update_ticker['display'] == true}
    <script type='text/javascript' src='{$template_dir}/javascript/updateticker.js.php'></script>
    <script>
        var seconds = {$update_ticker['seconds_left']};
        var nextInterval = new Date().getTime();
        var maxTicks = ({$update_ticker['sched_ticks']} * 60);
        var l_running_update = '{$langvars['l_running_update']}';
        var l_footer_until_update = '{$langvars['l_footer_until_update']}';

        setTimeout("NextUpdate();", 100);
    </script>
    <span id=update_ticker>{$langvars['l_please_wait']}</span>
{/if}

{* End of Servers Update Ticker *}

<br>
{* Handle the Online Players Counter *}
{if isset($players_online) && $players_online == 1}
{$langvars['l_footer_one_player_on']}
{else}
{$langvars['l_footer_players_on_1']} {$players_online} {$langvars['l_footer_players_on_2']}
{/if}
</p>
{* End of Online Players Counter *}

<!-- END OF FOOTER -->
    </div>
  </body>
</html>
