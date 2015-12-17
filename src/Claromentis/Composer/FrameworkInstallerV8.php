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
class FrameworkInstallerV8 extends BaseInstaller
{
	public function supports($packageType)
	{
		return $packageType === 'claromentis-framework-v8';
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
	}

	/**
	 * {@inheritDoc}
	 */
	public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
	{
		$this->updateCode($repo, $initial, $target);
	}

	/**
	 * {@inheritDoc}
	 */
	public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
	{
		if (!$repo->hasPackage($package)) {
			throw new \InvalidArgumentException('Package is not installed: '.$package);
		}

		//$this->removeCode($package);
		$repo->removePackage($package);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getInstallPath(PackageInterface $package)
	{
		return '../';
	}

	protected function installCode(PackageInterface $package)
	{
		$installPath = $this->getInstallPath($package);

		$this->filesystem->ensureDirectoryExists($installPath);

		$downloadPath = $installPath . 'framework.temp';

		$this->downloadManager->download($package, $downloadPath);
		$this->io->write("    Download finished, copying the code");
		if ($this->CoreConfigExists())
		{
			//$this->filesystem->rename($downloadPath.'/vendor', $downloadPath.'/vendor_core');
			$this->filesystem->removeDirectory($downloadPath.'/vendor');
			//$this->downloadManager->download($package, $installPath);
		}
		$this->filesystem->copyThenRemove($downloadPath, $installPath);
	}

	protected function removeCode(PackageInterface $package)
	{
		$downloadPath = $this->getInstallPath($package);
		$this->downloadManager->remove($package, $downloadPath);
		$this->filesystem->removeDirectory($downloadPath);
	}

	public function onInstall(InstallOperation $operation)
	{
		if ($this->CoreConfigExists())
		{
			$this->io->write('<warning>Core config file already exists, so assuming this is an upgrade from pre-installer version. Running "phing upgrade"');
			$this->runPhing('core', 'upgrade');
		} else
		{
			$this->runPhing('core', 'install');
		}
	}

	public function onUpdate(UpdateOperation $operation)
	{
		$this->runPhing('core', 'upgrade');
	}

	private function CoreConfigExists()
	{
		return file_exists('../web/intranet/common/config.php');
	}
}
