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

	public function onInstall(InstallOperation $operation, &$postinstall_queue)
	{
		if ($this->CoreConfigExists())
		{
			$this->io->write('<warning>Core config file already exists, so assuming this is an upgrade from pre-installer version. Will run "phing upgrade"');
			//$this->runPhing('core', 'upgrade');
			$postinstall_queue[] = ['upgrade', 'core'];
		} else
		{
			//$this->runPhing('core', 'install');
			$postinstall_queue[] = ['install', 'core'];
		}
	}

	public function onUpdate(UpdateOperation $operation, &$postinstall_queue)
	{
		//$this->runPhing('core', 'upgrade');
		$postinstall_queue[] = ['upgrade', 'core'];
	}

	private function CoreConfigExists()
	{
		return file_exists('web/intranet/common/config.php');
	}
}
