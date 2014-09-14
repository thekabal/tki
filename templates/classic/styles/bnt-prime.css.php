<?php
require_once '../../../vendor/autoload.php';           // Load the auto-loader
ob_start (array('Bnt\Compress', 'compress'));

$etag = md5_file (__FILE__); // Generate an md5sum and use it as the etag for the file, ensuring that caches will revalidate if the code itself changes
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 604800));
header ("Vary: Accept-Encoding");
header ("Content-type: text/css");
header ("Connection: Keep-Alive");
header ("Cache-Control: public");
header ('ETag: "' . $etag . '"');
?>
.faderlines { margin-left:auto; margin-right:auto; border:#fff solid 1px; text-align:center; background-color:#400040; color:#fff; padding:0px; border-spacing:0px; width:600px}
.footer { clear: both; height: 4em}
.wrapper { min-height: 100%; height: auto !important; height: 100%; margin: 0 auto -4em}
a:active { color: #f00}
a:link { color: #0f0}
a.new_link { color:#0f0; font-size: 8pt; font-weight:bold}
a.new_link:hover { color:#36f; font-size: 8pt; font-weight:bold}
a:visited { color: #0f0}
body { background-color:#000; background-image: url('../images/bgoutspace1.png'); color:#c0c0c0; font-family: Verdana, "DejaVu Sans", sans-serif; font-size: 85%; line-height:1.125em; height: 100%}
html { height: 85%}
