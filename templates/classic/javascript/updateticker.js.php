<?php
require_once '../../../vendor/autoload.php';           // Load the auto-loader
ob_start (array('Bnt\Compress', 'compress'));

$etag = md5_file (__FILE__); // Generate an md5sum and use it as the etag for the file, ensuring that caches will revalidate if the code itself changes
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 604800));
header ("Vary: Accept-Encoding");
header ("Content-type: text/javascript");
header ("Connection: Keep-Alive");
header ("Cache-Control: public");
header ('ETag: "' . $etag . '"');
?>
<!--
    var seconds = 0;
    var nextInterval = new Date().getTime();
    var maxTicks = 0;

    function NextUpdate()
    {
        var date = new Date();

        if (nextInterval <= 0)
        {
            nextInterval = date.getTime()+1000;
            Display();
        }
        else if (nextInterval <= date.getTime())
        {
            if (seconds <= 0)
            {
                seconds = maxTicks-1;
            }
            else
            {
                seconds -= 1;
            }

            Display();

            nextInterval = date.getTime() + 1000;
        }
        setTimeout("NextUpdate();", 100);
    }

    function Display()
    {
        if (seconds == 0)
        {
            document.getElementById('update_ticker').innerHTML = l_running_update;
        }
        else
        {
            document.getElementById('update_ticker').innerHTML = seconds +' '+ l_footer_until_update;
        }
    }
-->
