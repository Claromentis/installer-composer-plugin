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
		$pkg_name = $package->getName();
		if ($pkg_name == 'claromentis/framework')
			return 'web/';

		$parts = explode('/', $pkg_name);
		if (count($parts) !== 2)
			throw new \InvalidArgumentException(sprintf("Unexpected package name '%s' without vendor", $pkg_name));

		$app_name = $parts[1];

		$pos = strrpos($app_name, '-');
		if ($pos)
		{
			$name_suffix = substr($app_name, $pos+1);
			if (in_array($name_suffix, array('src', 'obf', 'php53', 'php54', 'php55', 'php56', 'php7')))
				$app_name = substr($app_name, 0, $pos);
		}

		return 'web/intranet/' . $app_name . '/';
	}

	public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
	{
		if ($package->getName() == 'claromentis/framework')
			return;

		parent::uninstall($repo, $package);
	}
}
