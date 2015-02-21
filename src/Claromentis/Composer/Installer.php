<?php
namespace Claromentis\Composer;
use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

class Installer extends LibraryInstaller
{
	public function getInstallPath(PackageInterface $package)
	{
		$name = $package->getName();
		$parts = explode('/', $name);

		if (count($parts) !== 2)
			throw new \InvalidArgumentException(sprintf("Unexpected package name '%s' without vendor", $name));

		return $parts[1].'/';
	}
}
