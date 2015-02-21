<?php
namespace Claromentis\Composer;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;

class ModuleInstaller extends LibraryInstaller
{
	public function getInstallPath(PackageInterface $package)
	{
		$name = $package->getName();
		$parts = explode('/', $name);

		if (count($parts) !== 2)
			throw new \InvalidArgumentException(sprintf("Unexpected package name '%s' without vendor", $name));

		if ($name == 'claromentis/framework')
			return 'web/';

		return 'web/intranet/' . $parts[1] . '/';
	}
}
