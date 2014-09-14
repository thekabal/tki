<?php
// This file is used for styling *both* the faq and the new player guide.
require_once '../../../vendor/autoload.php';           // Load the auto-loader
ob_start (array('Bnt\Compress', 'compress'));

$etag = md5_file (__FILE__); // Generate an md5sum and use it as the etag for the file, ensuring that caches will revalidate if the code itself changes
//header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 604800));
header ("Vary: Accept-Encoding");
header ("Content-type: text/css");
header ("Connection: Keep-Alive");
header ("Cache-Control: public");
header ('ETag: "' . $etag . '"');
?>
body.faq a { color:#ffffff; text-decoration: none}
body.faq { color:#c0c0c0; font-size:14px; height:14px}
body.faq table { border:0px; width:100%; border-spacing:0px}
body.faq table.navbar { border-spacing:0px}
body.faq td.firstbar { background-color:#500050; color:#eeeeee; font-size:36px; height:36px; text-align:center}
body.faq td.header { background-color:#400040; color:#eeeeee; font-size:18px; font-weight:bold; height:18px; width:25%; text-align:center}
body.faq td.lists { text-align:center; width:20%}
body.faq td.secondbar { background-color:#400040; color:#eeeeee; font-size:14px; height:14px; text-align:center}
body.faq td.spacer { background-color:#300030; width:5%}
body.faq td.subheader { background-color:#400040; color:#eeeeee; font-size:16px; font-weight:bold; height:16px; width:90%}
