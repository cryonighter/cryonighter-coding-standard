# Cryonighter PHP CodeSniffer Coding Standard

A coding standard to check against the [cryonighter coding standards](https://github.com/cryonighter/cryonighter-coding-standard/docs/standard-description-ru.md), originally shamelessly copied from the -disappeared- opensky/Symfony2-coding-standard repository.

## Installation via Composer

This standard can be installed with the [Composer](https://getcomposer.org/) dependency manager.

1. Install the coding standard as a dependency of your project

       composer global require cryonighter/cryonighter-coding-standard

2. Add the coding standard to the PHP_CodeSniffer install path

       phpcs --config-set installed_paths ../../../vendor/escapestudios/symfony2-coding-standard,../../../vendor/cryonighter/cryonighter-coding-standard

3. Check the installed coding standards for "Symfony" and "Cryonighter"

       phpcs -i

4. Done!

       phpcs /path/to/code


## Installation Stand-alone

1. Install [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)

2. Checkout this repositories

       git clone git://github.com/djoos/Symfony2-coding-standard.git symfony2-coding-standard && git clone git@github.com:cryonighter/cryonighter-coding-standard.git

3. Add the coding standard to the PHP_CodeSniffer install path

       phpcs --config-set installed_paths /path/to/symfony2-coding-standard,/path/to/cryonighter-coding-standard

   Or move/copy/symlink repositories `Symfony` and `Cryonighter` folders inside the phpcs `Standards` directory

4. Check the installed coding standards for "Symfony" and "Cryonighter"

       phpcs -i

5. Done!

       phpcs /path/to/code
