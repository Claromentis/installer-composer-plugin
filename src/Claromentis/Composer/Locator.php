<?php

namespace Claromentis\Composer;
use Composer\IO\IOInterface;

/**
 * Helper class that finds various paths
 *
 * @author Alexander Polyanskikh
 */
class Locator
{
	/**
	 * Returns path where the main build.xml is located, including trailing slash.
	 * On error throws \Exception
	 *
	 * @param IOInterface $io
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function getBuildXmlPath(IOInterface $io)
	{
		clearstatcache(true);
		if (basename(getcwd()) === 'installer' && is_dir('../web') && file_exists('../build.xml')) // v8 started from "installer" folder
		{
			$base_dir = '../';
		} elseif (is_dir('web') && file_exists('build.xml'))     // v8 developer
		{
			$base_dir = './';
		} elseif (is_dir('web') && file_exists('web/build.xml')) // v7 installer
		{
			$base_dir = 'web/';
		} elseif (basename(getcwd()) === 'web' && file_exists('build.xml')) // v7 developer (shouldn't happen as devs don't install modules using composer)
		{
			$base_dir = './';
		} else
		{
			$msg = "Cannot find build.xml (cwd=".getcwd().")";
			$io->writeError($msg);
			throw new \Exception($msg);
		}

		return $base_dir;
	}

	/**
	 * Returns path to "web" folder with trailing slash.
	 * On error throws \Exception
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function getWebFolderPath()
	{
		clearstatcache(true);
		if (basename(getcwd()) === 'installer' && is_dir("../web") && file_exists('../build.xml') && !file_exists('build.xml')) // v8 from 'installer' folder
		{
			return '../web/';
		} elseif (is_dir('web') && file_exists('build.xml') && !file_exists('web/build.xml')) // v8 developer
		{
			return 'web/';
		} elseif (file_exists('web/build.xml')) // v7 installer
		{
			return 'web/';
		} else
		{
			$msg = "Cannot detect location of web folder (cwd=".getcwd().")";
			throw new \Exception($msg);
		}
	}
}
