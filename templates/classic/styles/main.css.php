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
.faderlines { margin-left:auto; margin-right:auto; border:#fff solid 1px; text-align:center; background-color:#400040; color:#fff; padding:0px; border-spacing:0px; width:600px}
.footer, .push { clear: both}
.footer, .push { height: 4em}
.headlines { color:white; font-size:8pt; font-weight:bold; text-decoration:none}
.headlines:hover { color:#36f; text-decoration:none}
.map { background-color:#0000ff;  border:#555555 1px solid; color:#fff; float:left; height:20px; padding:0px; position:relative; width:20px}
.map:hover { border:#fff 1px solid}
.none { background-image:url('../../../images/space.png')}
.portcosts1 { background-color:#300030; border-style:none; color:#c0c0c0; font-size:1em; width:7em}
.portcosts2 { background-color:#400040; border-style:none; color:#c0c0c0; font-size:1em; width:7em}
.rank_dev_text { color:#f00; font-size:0.8em; text-decoration:none; vertical-align:middle}
.un { background-image:url('../../../images/uspace.png'); opacity:0.5}
.wrapper { min-height: 100%; height: auto !important; height: 100%; margin: 0 auto -4em}
a:active { color: #f00}
a.dis { color:silver; font-size: 8pt; font-weight:bold; text-decoration:none}
a.dis:hover { color:#36f; font-size: 8pt; font-weight:bold; text-decoration:none}
a.index { border:0; display:block; margin-left:auto; margin-right:auto; text-align:center}
a:link { color: #0f0}
a.new_link { color:#0f0; font-size: 8pt; font-weight:bold}
a.new_link:hover { color:#36f; font-size: 8pt; font-weight:bold}
a:visited { color: #0f0}
body { background-color:#000; background-image: url('../images/bgoutspace1.png'); color:#c0c0c0; font-family: Verdana, "DejaVu Sans", sans-serif; font-size: 85%; line-height:1.125em; height: 100%}
body.error { background: url(../images/error.jpg) no-repeat center center fixed; background-size: cover}
center.term { background-color: #000; border-color:#0f0; color: #0f0; font-size:0.8em}
div.mnu { color:white; font-size: 8pt; font-weight:bold; text-decoration:none}
div.navigation { display:table; margin: 0 auto}
dl.twocolumn-form dd { float:left; height:2em; text-align:left; width:45%; padding:3px}
dl.twocolumn-form dt { float:left; height:2em; text-align:right; width:45%; padding:3px}
dl.twocolumn-form input {width:200px}
h1.index-h1 { font-size:1em; font-weight: normal; margin: 0; padding: 0}
html.error { height: 100%}
html { height: 85%}
img.index { border:0; display:block; height:150px; margin-left:auto; margin-right:auto; width:100%}
img.mnu { border:transparent 2px dashed; padding:4px}
img.mnu:hover { border:#f00 2px dashed; padding:4px}
input.term { background-color: #000; border-color:#0f0; color: #0f0; font-size:0.8em}
p.cookie-warning { font-size:0.7em}
p.error_footer { clear:both}
p.error_return { }
p.error_text { }
pre.term { background-color: #000; border-color:#0f0; color: #0f0; font-size:0.8em}
select.term { background-color: #000; border-color:#0f0; color: #0f0; font-size:0.8em}
span.mnu { color:white; font-size: 8pt; font-weight:bold; text-decoration:none}
table.dis { color:silver; font-size: 8pt; font-weight:bold; text-decoration:none}
table.dis:hover { color:#36f; font-size: 8pt; font-weight:bold; text-decoration:none}
ul.navigation li { display:inline}
ul.navigation { list-style:none}
a.mnu { color:white; font-size: 8pt; font-weight:bold; text-decoration:none}
a.mnu:hover { color: #36f; font-size: 8pt; font-weight:bold; text-decoration:none}
