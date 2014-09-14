<?php
require_once '../../../vendor/autoload.php';           // Load the auto-loader
ob_start (array('Tki\Compress', 'compress'));

$etag = md5_file (__FILE__); // Generate an md5sum and use it as the etag for the file, ensuring that caches will revalidate if the code itself changes
//header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 604800));
header ("Vary: Accept-Encoding");
header ("Content-type: text/css");
header ("Connection: Keep-Alive");
header ("Cache-Control: public");
header ('ETag: "' . $etag . '"');
?>
body.options table { border:0; border-spacing:0px; color:#fff; padding:2px}
body.options th { background-color:#500050; text-align:left}
body.options tr:nth-child(even) { background-color:#300030}
body.options tr:nth-child(odd) { background-color:#400040}
