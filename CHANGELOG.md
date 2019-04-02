# Change log #

## Version 1.2.4
 * Added explicit symfony/polyfill-ctype dependency to avoid conflicts with Core 8.6.0+

## Versions 1.2.3
 * Fixed not registering updates for packages installed from git

## Versions 1.2.2
 * Removing contents of _init folder before applying new update - ensures that no migrations are left from earlier versions

## Versions 1.2.1
 * Fixed detecting return code of phing task on Windows (was preventing "retry with upgrade" introduced in 1.0.7)
 * Removed fake local repository used for source installation as it's not working as should
 * Added this change log

## Versions 1.2.0
 * Changed the flow to run phing tasks after autoloader has been created

## Version 1.1.1 - 1.1.6
 * Various bugfixes, including one BC break

## Version 1.1.0
 * Initial release with support installation Claromenti 8 and modules

## Version 1.0.10
 * Fixed "build.xml doesn't exist" when installing a module for Cla 8

## Version 1.0.9
 * Changed composer API dependency to be less strict

## Version 1.0.8
 * Added support for installing modules from source code from git

## Version 1.0.7
 * Not failing hard if application already installed - trying upgrade instead

## Version 1.0.6
 * Improved reliability by adding custom error handler to prevent warnings from aborting installation

## Version 1.0.5
 * First real release
