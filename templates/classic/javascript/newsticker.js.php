<?php
require_once '../../../vendor/autoload.php';           // Load the auto-loader
ob_start (array('Tki\Compress', 'compress'));

$etag = md5_file (__FILE__); // Generate an md5sum and use it as the etag for the file, ensuring that caches will revalidate if the code itself changes
//header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 604800));
header ("Vary: Accept-Encoding");
header ("Content-type: text/javascript");
header ("Connection: Keep-Alive");
header ("Cache-Control: public");
header ('ETag: "' . $etag . '"');
?>
<!--
function newsTicker(inst)
{
// Private / Protected Variables
    var welcome                 = "<span style=\"color:#fff;\">Loading News, Please wait...</span>";
    var title                   = "News Ticker";
    var instance                = Math.random() *1000000;
    var article                 = [];
    var intervalId              = [];
    var tickerHeight            = [];
    var tickerWidth             = [];
    var intervalSec             = [];
    var nextInterval            = [];
    var initialized             = [];
    var started                 = [];
    var ticketArticle           = [];
    var element                 = [];

// Public Variables

// Public Functions
    this.initTicker = function (container)
    {
        if (document.getElementById(container) == null)
        {
            errMSG        = "TICKER ERROR: Container '" + container + "' does not exist!\n";
            errMSG        += "\n";
            errMSG        += "Please use instance.Container('container name');\n";
            errMSG        += "container must be a valid Container name and must not contain spaces.\n";
            errMSG        += "\n";
            errMSG        += "For more information, Please read the documentation.\n";
            window.alert (errMSG);

            return false;
        }

        // Default Settings DO NOT CHANGE THESE VALUES.
        article[instance]               = 0;
        intervalId[instance]            = null;
        tickerHeight[instance]          = '18px';
        tickerWidth[instance]           = '500px';
        intervalSec[instance]           = 5;
        nextInterval[instance]          = 0;
        initialized[instance]           = false;
        started[instance]               = false;
        ticketArticle[instance]         = [];
        element[instance]               = null;

        element[instance]               = document.getElementById(container);
        element[instance].height        = parseInt(tickerHeight[instance]) +"px";
        element[instance].width         = parseInt(tickerWidth[instance]) +"px";
        element[instance].title         = title;
        this.output("<div style='color:#FF0000; font-weight:bold;'>Ticker Not Started!</div>");

        // Clear Articles.
        this.clearArticles();
        initialized[instance]            = true;

        return true;
    }

    this.startTicker = function ()
    {
        var self = this;
        if (initialized[instance] == false)
        {
            this.output("<div style='color:#FF0000; font-weight:bold;'>Error: Ticker Not Initialized!</div>");
            started[instance] = false;

            return false;
        }
        else if (element[instance] && intervalId[instance] == null)
        {
            started[instance] = true;
            article[instance] = 0;
            this.getArticle();
//            intervalId[instance] = setInterval(function () { self.getArticle();}, (ticketArticle[instance].DELAY[article[instance]] * 1000));
//            intervalId[instance] = setInterval(function () { self.getArticle();}, (intervalSec[instance] * 1000));
            intervalId[instance] = setInterval(function () { self.getArticle();}, (100));
        }
    }

    this.stopTicker = function ()
    {
        if (intervalId[instance] != null)
        {
            clearInterval(intervalId[instance]);
            intervalId[instance] = null;
            article[instance] = 0;
        }
    }

    this.addArticle = function (url, text, type, delay)
    {
        // Put back in < and > in the text.
        // we had to have output-escaping enabled to be XHTML 1.1 compliant.
        if (text != null)
            text = text.replace(/&lt;/gi,"<").replace(/&gt;/gi,">");

        if (url != null)
            url = url.replace(/&lt;/gi,"<").replace(/&gt;/gi,">");

        ticketArticle[instance].URL[ticketArticle[instance].NUM]    = url;
        ticketArticle[instance].TEXT[ticketArticle[instance].NUM]    = text;
        ticketArticle[instance].TYPE[ticketArticle[instance].NUM]    = type;
        ticketArticle[instance].DELAY[ticketArticle[instance].NUM]    = delay;
        ticketArticle[instance].NUM ++;
    }

    this.Width = function (width)
    {
        if (typeof width == "undefined")
        {
            return parseInt(tickerWidth[instance]);
        }

        if (started[instance] == false)
        {
            if (typeof width != "undefined")
            {
                tickerWidth[instance] = parseInt(width) +"px";
                element[instance].width = tickerWidth[instance];

                return true;
            }
        }
        else
        {
            return false;
        }
    }

    this.Interval = function (interval)
    {
        if (typeof interval == "undefined")
        {
            return intervalSec[instance];
        }

        if (started == false)
        {
            if (typeof interval != "undefined")
            {
                intervalSec[instance] = (interval);

                return true;
            }
        }
        else
        {
            return false;
        }
    }

    this.clearArticles = function ()
    {
        ticketArticle[instance].URL        = [];
        ticketArticle[instance].TEXT    = [];
        ticketArticle[instance].TYPE    = [];
        ticketArticle[instance].DELAY    = [];
        ticketArticle[instance].NUM        = 0;
    }

    this.getArticle = function ()
    {
        var instant = this;
        var date = new Date();

        if (nextInterval[instance] <= 0)
        {
            nextInterval[instance] = date.getTime() + 1000;
            this.output(welcome);

            return true;
        }
        else if (nextInterval[instance] <= date.getTime())
        {
            nextInterval[instance] = date.getTime() + (ticketArticle[instance].DELAY[article[instance]]*1000);
            if (ticketArticle[instance].TEXT == null || ticketArticle[instance].NUM == 0)
            {
                this.output("<div style='color:#FF0000; font-weight:bold;'>Error: No News Loaded!</div>");
                instant.stopTicker();

                //document.write ("Ret: False<br>\n");
                return false;
            }
            else if(ticketArticle[instance].URL[article[instance]] == null || ticketArticle[instance].URL[article[instance]].length ==0)
            {
                this.output(ticketArticle[instance].TEXT[article[instance]]);
                this.nextArticle();

                return true;
            }
            else
            {
                this.output("<a class='headlines' href='" + ticketArticle[instance].URL[article[instance]] + "'>" + ticketArticle[instance].TEXT[article[instance]] + "</" + "a>");
                this.nextArticle();

                return true;
            }
        }
    }

    this.nextArticle = function ()
    {
        if (article[instance] < (ticketArticle[instance].NUM-1))
        {
            article[instance]++;
        }
        else
        {
            article[instance] = 0;
        }
    }

    this.output = function (data)
    {
        if (typeof data != "undefined")
        {
            element[instance].innerHTML        = data

            return true;
        }
        else
        {
            return false;
        }
    }

    this.DEBUGGER = function (msg)
    {
        document.write("<div style='font-size:12px; color:#FF0000;'>DEBUGGER: " + msg +"</div>\n");
    }

//Private / Protected Functions

}
-->
