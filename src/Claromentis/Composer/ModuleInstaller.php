<?php
namespace Claromentis\Composer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

class ModuleInstaller extends LibraryInstaller
{
	public function supports($packageType)
	{
		return $packageType === 'claromentis-module';
	}

	public function getPackageBasePath(PackageInterface $package)
	{
		$name = $package->getName();
		if ($name == 'claromentis/framework')
			return 'web/';

		$parts = explode('/', $name);
		if (count($parts) !== 2)
			throw new \InvalidArgumentException(sprintf("Unexpected package name '%s' without vendor", $name));

		return 'web/intranet/' . $parts[1] . '/';
	}

	public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
	{
		if ($package->getName() == 'claromentis/framework')
			return;
		
		parent::uninstall($repo, $package);
	}
}
