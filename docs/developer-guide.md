# Developer Guide

_**This file is a brief introduction for aspiring developers to help you find your place in the code.**_

TKI forked from a codebase started in pre-PHP4 days (over a decade of development now!), and many things are a work in 
progress. We actively work to improve the code, and that process takes time. We are now (in 2013) getting to a 
relatively good position for most of the major initiatives. Global variables have been eliminated, classes are being 
auto-loaded where possible, files are being converted to use templates for output.. the list goes on.

**With that out of the way, here is a general guide to most of the code:**

- For any given file, which represents a location in game (ibank, main menu, planet, etc), a file will (usually)
  include common.php.
    1. common.php will in turn include the database settings, the global defines, and the autoloader.
    2. After common is complete, the file will continue its processing. 
- We try to stay close to psr 1, 2, 4, and 12, with the notable exceptions of using the BSD/Allman
  brace/bracket style and do not follow line-length limits yet.
- In a templated file, header-t loads the needed HTML headers. Similarly, footer-t loads the needed HTML closing
  statements, copyright notices, and so on. This is a temporary measure until all of the game is templated.
- Translation support is improving in game. Language outputs should be done via the $langvars array, with
  corresponding entries and categories (which match file names usually) in languages/language.ini
- Methods and older functions should have needed variables defined in their calling in the 
  order ($pdo_db, $db, $langvars, $config, others).
- Anywhere you are unconditionally including a class file, use require_once(). Anywhere you are conditionally 
  including a class file, use include_once().
- All PDO calls with bindParam/bindValue must use PDO datatype constants (Like PDO::PARAM_INT/PDO::PARAM_STR)
- Never use global variables.
