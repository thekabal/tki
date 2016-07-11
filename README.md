# The Kabal Invasion

The Kabal Invasion is a web-based 4X space game. It is coded in PHP/HTML/JS/SQL.

[![Dependency Status](https://www.versioneye.com/user/projects/57796f3468ee07003cb5d764/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/57796f3468ee07003cb5d764)
![PHP7 ready](https://img.shields.io/badge/PHP7-ready-green.svg)
[![CII Best Practices](https://bestpractices.coreinfrastructure.org/projects/124/badge)](https://bestpractices.coreinfrastructure.org/projects/124)
[![GitHub stars](https://img.shields.io/github/stars/thekabal/tki.svg)](https://github.com/thekabal/tki/stargazers)
[![GitHub license](https://img.shields.io/badge/license-AGPL-blue.svg)](https://www.gnu.org/licenses/agpl-3.0.html)
[![GitHub issues](https://img.shields.io/github/issues/thekabal/tki.svg)](https://github.com/thekabal/tki/issues)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thekabal/tki/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/thekabal/tki/?branch=master)

##What is it?
    A web based space exploration (4x) game based on the old BBS door games that went
    by many names (Tradewars, Galactic Warzone, Ultimate Universe, and
    many other games like this) but shares no code with them.  It is
    written 100% in PHP/HTML/JS/SQL.
    
##Requirements:
- MySQL version 5.5.3 minimum is required (needed for utf8mb4 schemas).
- PHP's mbstring extension must be installed (used in common.php)
- PHP's pdo extension must be installed (used throughout the game)
- Web browser - Firefox and Chrome (v30+ for both) are best, while Safari (v6+) is also good. Internet Explorer needs to be at least (v9+).
- Apache version 2.2.22+ is supported, we have not determined a required minimum. TKI will likely run on lighttpd and nginix, but has not been tested on either.
- Development "Snapshots" are intended only for developers that are actively involved in the development process, and require additional effort to work (composer, etc)
- PHP version 7+ is required (random_int used throughout).
- IIS is NOT supported, please do not ask. (But we welcome code to make it work on IIS!)

##Credits:
The Kabal Invasion forked from [Blacknova Traders](https://sourceforge.net/projects/blacknova/), please visit their sourceforge page for more information about their project.

##Installation:
Please see the docs/install file.

##Upgrades:
As is typical with releases, we highly recommend a fresh install. Upgrades are not supported at this time.

##Code quality:
The project began in the early PHP4 era, and as a result, is less than ideal. Substantial progress has been made towards modernization, and we are continuing that process. **Feedback and PR's are welcome and appreciated**.

I hope you enjoy the game!,
<br>The Kabal
