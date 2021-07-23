# Change log #

## Version 1.2.8 - 23rd July 2021
* Updated explicit symfony/polyfill-ctype dependency from 1.22.1 to 1.23.0 to allow Core 8.13.0+ to be installed

## Version 1.2.7 - 24th May 2021
* Updated explicit symfony/polyfill-ctype dependency from 1.18.1 to 1.22.1 to allow Core 8.13.0+ to be installed

## Version 1.2.6 - 17th September 2020
* Updated explicit symfony/polyfill-ctype dependency from 1.11.0 to 1.18.1 to allow Core 8.11.0+ to be installed

## Version 1.2.5 - 10th July 2019
 * Updated explicit symfony/polyfill-ctype dependency from 1.10.0 to 1.11.0 to allow Core 8.8.0+ to be installed

## Version 1.2.4 - 2nd April 2019
 * Added explicit symfony/polyfill-ctype dependency to avoid conflicts with Core 8.6.0+

## Version 1.2.3 - 6th April 2016
 * Fixed not registering updates for packages installed from git

## Version 1.2.2 - 5th April 2016
 * Removing contents of _init folder before applying new update - ensures that no migrations are left from earlier versions

## Version 1.2.1 - 3rd March 2016
 * Fixed detecting return code of phing task on Windows (was preventing "retry with upgrade" introduced in 1.0.7)
 * Removed fake local repository used for source installation as it's not working as should
 * Added this change log

## Version 1.2.0 - 28th January 2016
 * Changed the flow to run phing tasks after autoloader has been created

## Versions 1.1.1...1.1.6 - 22nd December 2015 to 27th January 2016
 * Various bugfixes, including one BC break

## Version 1.1.0 - 21st December 2015
 * Initial release with support installation Claromenti 8 and modules

## Version 1.0.10 - 15th December 2015
 * Fixed "build.xml doesn't exist" when installing a module for Cla 8

## Version 1.0.9 - 16th June 2015
 * Changed composer API dependency to be less strict

## Version 1.0.8 - 25th March 2015
 * Added support for installing modules from source code from git

## Version 1.0.7 - 24th March 2015
 * Not failing hard if application already installed - trying upgrade instead

## Version 1.0.6 - 4th March 2015
 * Improved reliability by adding custom error handler to prevent warnings from aborting installation

## Version 1.0.5 - 4th March 2015
 * First real release
