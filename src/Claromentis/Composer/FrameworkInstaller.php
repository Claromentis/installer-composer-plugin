<?php
namespace Claromentis\Composer;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

/**
 * Installer for pre-composer modules - those that have distributives as zip files
 * with application directory in it and probably no composer.json
 *
 * @package Claromentis\Composer
 */
class FrameworkInstaller extends BaseInstaller
{
	public function supports($packageType)
	{
		return $packageType === 'claromentis-framework';
	}

	/**
	 * {@inheritDoc}
	 */
	public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
	{
		$this->installCode($package);
		if (!$repo->hasPackage($package)) {
			$repo->addPackage(clone $package);
		}

		$this->filesystem->ensureDirectoryExists('data');
		$this->filesystem->ensureDirectoryExists('local_data');
	}

	/**
	 * {@inheritDoc}
	 */
	public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
	{
		if (!$repo->hasPackage($initial)) {
			throw new \InvalidArgumentException('Package is not installed: '.$initial);
		}

		//$this->updateCode($initial, $target);
		$this->installCode($target);
		$repo->removePackage($initial);
		if (!$repo->hasPackage($target)) {
			$repo->addPackage(clone $target);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
	{
		if (!$repo->hasPackage($package)) {
			throw new \InvalidArgumentException('Package is not installed: '.$package);
		}

		$this->removeCode($package);
		$repo->removePackage($package);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getInstallPath(PackageInterface $package)
	{
		return 'web';
	}

	protected function removeCode(PackageInterface $package)
	{
		$downloadPath = $this->getInstallPath($package);
		$this->downloadManager->remove($package, $downloadPath);
		$this->filesystem->removeDirectory($downloadPath);
	}

	public function onInstall(InstallOperation $operation)
	{
		$this->runPhing('core', 'install');
	}

	public function onUpdate(UpdateOperation $operation)
	{
		$this->runPhing('core', 'upgrade');
	}
}
