# Tools

We use a number of additional tools that are useful to development. These can be
found in the `/vendor/bin` directory.

- [`phpcpd`](https://github.com/sebastianbergmann/phpcpd) is a Copy/Paste
  detector (CPD) for PHP code. We have not resolved all errors it identifies yet
  but are making progress towards doing so. We use it like this:

  `php vendor/bin/phpcpd . --exclude=templates --exclude=vendor`

- [`phpcs`](https://github.com/squizlabs/PHP_CodeSniffer) is PHP Code Sniffer.
  PHP_CodeSniffer tokenizes PHP, JavaScript and CSS files and detects violations
  of a defined set of coding standards. We use it like this:

  `php vendor/bin/phpcs --standard=vendor/bin/phpcs.xml . --ignore=templates,vendor,config,languages`

- [`phpmd`](https://phpmd.org/) is PHP Mess Detector. It takes a given PHP
  source code base and looks for several potential problems within that source.
  We use it like this:

  `php vendor/bin/phpmd . text vendor/bin/phpmd.xml --exclude vendor/,templates/`

- [`php-cs-fixer`](http://cs.sensiolabs.org/) strives to automatically correct
  code for specific items in the PSR standard. We do not currently use it, but
  may in the future use it as a pre-commit hook.

There are other command-line options for these tools (try them!), with varying
levels of usefulness to our project.
