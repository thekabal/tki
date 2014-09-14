<?php
require_once '../../../vendor/autoload.php';           // Load the auto-loader
ob_start (array('Tki\Compress', 'compress'));

$etag = md5_file (__FILE__); // Generate an md5sum and use it as the etag for the file, ensuring that caches will revalidate if the code itself changes
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 604800));
header ("Vary: Accept-Encoding");
header ("Content-type: text/css");
header ("Connection: Keep-Alive");
header ("Cache-Control: public");
header ('ETag: "' . $etag . '"');
?>
.button:active .shine { opacity: 0}
.button.blue { background: #3a617e}
.button.brown { background: #663300}
.button.gray { background: #555}
.button.green { background: #477343}
.button.orange { background: #624529}
.button.purple { background: #4b3f5e}
.button.red { background: #723131}
.button:hover .shine { left: 24px}
.cookie-warning { font-size:0.7em}
.index-flags { height:auto; left:80%; position:absolute; top:3%; width:auto}
.index-flags img { height:16px}
.index-h1 { font-size:1em; font-weight: normal; margin: 0; padding: 0}
.index-header { border:2px solid white; box-shadow: 3px 3px 6px #000; height: 150px; left:0; margin:2px; top: 0; width:99%}
.index-header-text { color:white; font-size:4em; height:auto; left:30%; line-height:4em; position:absolute; text-shadow: black 2px 2px 0.1em; top:1%; width:auto}
.index-welcome { font-size:1.2em; text-align:center}
a { outline:none; text-decoration:none}
a.nocolor { color:inherit }
a.new_link { color:#000483;}
body.index { background-color:#929292; background-image:none; color:#000; font-family: 'Ubuntu', Verdana, "DejaVu Sans", sans-serif; font-size:75%; text-align:center}
dd { float:left; height:2em; text-align:left; width:45%; padding:3px}
div.navigation { display:table; margin: 0 auto}
dt { float:left; height:2em; text-align:right; width:45%; padding:3px}
img { border:0}
img.index { border:0; display:block; height:150px; margin-left:auto; margin-right:auto; width:100%}
li { display:inline}
ul.navigation { list-style:none}
.button {
    background: #434343;
    border: 1px solid #242424;
    color: #FFF;
    cursor: pointer;
    display: inline-block;
    font-size: 1em;
    letter-spacing: 1px;
    margin: 0 5px 5px 0;
    min-height: 1em;
    padding: 12px 24px;
    opacity: 0.9;
    text-shadow: 0 1px 2px rgba(0,0,0,0.9);
    text-transform: uppercase;
    border-radius: 4px;
    box-shadow: rgba(255,255,255,0.25) 0 1px 0, inset rgba(255,255,255,0.25) 0 1px 0, inset rgba(0,0,0,0.25) 0 0 0, inset rgba(255,255,255,0.03) 0 20px 0, inset rgba(0,0,0,0.15) 0 -20px 20px, inset rgba(255,255,255,0.05) 0 20px 20px;
    transition: all 0.1s linear;
}
.button:hover {
    box-shadow: rgba(0,0,0,0.5) 0 2px 5px, inset rgba(255,255,255,0.25) 0 1px 0, inset rgba(0,0,0,0.25) 0 0 0, inset rgba(255,255,255,0.03) 0 20px 0, inset rgba(0,0,0,0.15) 0 -20px 20px, inset rgba(255,255,255,0.05) 0 20px 20px;
}
.button:active {
    box-shadow: rgba(255,255,255,0.25) 0 1px 0, inset rgba(255,255,255,0) 0 1px 0, inset rgba(0,0,0,0.5) 0 0 5px, inset rgba(255,255,255,0.03) 0 20px 0, inset rgba(0,0,0,0.15) 0 -20px 20px, inset rgba(255,255,255,0.05) 0 20px 20px;
}
.shine {
    display: block;
    height: 1px;
    left: -24px;
    padding: 0 12px;
    position: relative;
    top: -12px;
    box-shadow: rgba(255,255,255,0.2) 0 1px 5px;
    transition: all 0.3s ease-in-out;
    background: linear-gradient(to left, rgba(255,255,255,0) 0%,rgba(255,255,255,1) 50%,rgba(255,255,255,0) 100%);
}
