<?php
namespace Claromentis\Composer;

use Composer\Composer;
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Util\Filesystem;

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

	public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
	{
		if (!$repo->hasPackage($package)) {
			throw new \InvalidArgumentException('Package is not installed: '.$package);
		}

		$repo->removePackage($package);

		$installPath = $this->getInstallPath($package);
		$removeResult = $this->filesystem->removeDirectory($installPath);
		$this->io->write(sprintf('Deleting %s - %s', $installPath, $removeResult ? '<comment>deleted</comment>' : '<error>not deleted</error>'));
	}
}
