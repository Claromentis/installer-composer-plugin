<?php

namespace Claromentis\Composer;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Repository\ArrayRepository;
use Composer\Semver\VersionParser;

/**
 * Repository for composer that provides information about locally installed version of core
 *
 * @author Alexander Polyanskikh
 */
class FrameworkInstalledRepository
{
	static public function Construct(IOInterface $io)
	{
		$repo = new ArrayRepository();
		try
		{
			$web_folder = Locator::getWebFolderPath();

			$version_file = $web_folder.'/intranet/setup/_init/version.txt';
			if (!file_exists($version_file))
				throw new \Exception("No version.txt for core found - assuming framework is not installed");
			$version_data = file($version_file);
			$core_version = $version_data[1];

			$normalizer = new VersionParser();
			$core_version_normalized = $normalizer->normalize($core_version);
			$io->write("Detected core version $core_version ($core_version_normalized)");
			$core_package = new Package(FrameworkInstallerV8::PACKAGE_NAME, $core_version_normalized, $core_version);
			$repo->addPackage($core_package);
		} catch (\Exception $e)
		{
			$io->write($e->getMessage());
			// if can't determine location of 'web' folder, not adding the core package therefore letting
			// composer install it
		}

		return $repo;
	}
}