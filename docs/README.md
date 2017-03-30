# The Kabal Invasion

The Kabal Invasion is a web-based 4X space game. It is coded in PHP/HTML/JS/SQL.

[![Dependency Status](https://www.versioneye.com/user/projects/57796f3468ee07003cb5d764/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/57796f3468ee07003cb5d764)
![PHP7 ready](https://img.shields.io/badge/PHP7-ready-green.svg)
[![CII Best Practices](https://bestpractices.coreinfrastructure.org/projects/124/badge)](https://bestpractices.coreinfrastructure.org/projects/124)
[![GitHub stars](https://img.shields.io/github/stars/thekabal/tki.svg)](https://github.com/thekabal/tki/stargazers)
[![GitHub license](https://img.shields.io/badge/license-AGPL-blue.svg)](https://www.gnu.org/licenses/agpl-3.0.html)
[![GitHub issues](https://img.shields.io/github/issues/thekabal/tki.svg)](https://github.com/thekabal/tki/issues)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thekabal/tki/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/thekabal/tki/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/1efef371-bff2-4809-a330-5470a0e7b9fa/mini.png)](https://insight.sensiolabs.com/projects/1efef371-bff2-4809-a330-5470a0e7b9fa)

## What is it?
    A web based space exploration (4x) game based on the old BBS door games that went
    by many names (Tradewars, Galactic Warzone, Ultimate Universe, and
    many other games like this) but shares no code with them.  It is
    written 100% in PHP/HTML/JS/SQL.

## Why should I run this?
    Web-based games that recreate the door game BBS experience can be fun!
    Since it is Free and open source software, anyone can examine, learn, and contribute.

## Is this game ready to install and play?
    At the moment, we've identified a number of release-blocking issues including broken
    password management, user sign-up, and issues with non-functional database calls. Serious
    effort is underway to fix all of these issues, and we are working towards a release. In the meantime,
    curious developers are encouraged to download, install, and play as the admin user to find issues and report them.
    When we get to a point where the game is stable for players, we will make an announcement, change this note, and release!
    
## License: [Affero GPL v 3](https://www.gnu.org/licenses/agpl-3.0.en.html)

## Requirements:

### Server (generally, the most recent/current version of each is our recommendation, but these should suffice):
- A Linux server. Our primary development platform is Fedora, but most Linux distributions should work, and potentially even OpenBSD.
- A webserver capable of supporting htaccess, and TLS.
- `apache v2.4+` (we have not determined a required minimum).
- `php v7.1.0+` (needed for void return types).
- `mariadb v5.5+ or v10.0+` (needed for utf8mb4 schemas).
- `mbstring` PHP extension.
- `pdo` PHP extension.

### Web:
- Chrome v50+ or Firefox v40+ (recommended).
- Safari `v9.1.2+`.
- IE `v11`.

### Notes:
- TKI will likely run on `lighttpd` and `nginix`, but has not been tested on either. 
- **IIS is NOT supported, please do not ask!** (But we welcome code to make it work on IIS)
- Development "Snapshots" are intended only for developers that are actively involved in the development process, and require additional effort to work (composer, etc).
- We make use of [Smarty templates](http://www.smarty.net/), [HTML Purifier](http://htmlpurifier.org/), [Swiftmailer](http://swiftmailer.org/), and [Adodb](http://adodb.org/dokuwiki/doku.php) (although we are working to replace adodb with PDO).

## Credits:
The Kabal Invasion forked from [Blacknova Traders](https://sourceforge.net/projects/blacknova/), please visit their sourceforge page for more information about their project.

## Installation:
Please see the `/docs/install.md` file.

## Upgrades:
As is typical with releases, we highly recommend a fresh install. Upgrades are not supported at this time.

## Code quality:
The project began in the early PHP4 era, and as a result, is less than ideal. Substantial progress has been made towards modernization, and we are continuing that process. As a general guideline, we follow PSR-1,2,4, and the upcoming 12, with the major exceptions that we use BSD/Allman brace/brackets and do not yet follow line length limits. **Feedback and PR's are welcome and appreciated**.

## Security issue reporting:
Please report all security issues to thekabal@gmail.com.

## Contributing:
Feel free to contribute to the project! We use [GitHub](https://github.com/thekabal/tki/) for our issues tracking (provide feedback!), milestone planning, code storage, and releases.

I hope you enjoy the game!
<br>The Kabal
