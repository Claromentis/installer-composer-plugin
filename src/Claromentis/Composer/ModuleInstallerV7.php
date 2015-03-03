<?php
namespace Claromentis\Composer;

use Composer\Composer;
use Composer\Installer\InstallerInterface;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Util\Filesystem;

/**
 * Installer for pre-composer modules - those that have distributives as zip files
 * with application directory in it and probably no composer.json
 *
 * @package Claromentis\Composer
 */
class ModuleInstallerV7 extends BaseInstaller
{
	public function supports($packageType)
	{
		return $packageType === 'claromentis-module-v7' || $packageType === 'claromentis-module';
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

		$this->runPhing($this->getApplicationCode($package), 'install');
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

		$this->runPhing($this->getApplicationCode($target), 'upgrade');
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
		$this->runPhing($this->getApplicationCode($package), 'uninstall');
	}

	/**
	 * {@inheritDoc}
	 */
	public function getInstallPath(PackageInterface $package)
	{
		return 'web/intranet/'.$this->getApplicationCode($package).'/';
	}

	/**
	 * Returns Claromentis application name (folder)
	 *
	 * @param PackageInterface $package
	 *
	 * @return string
	 */
	protected function getApplicationCode(PackageInterface $package)
	{
		$pkg_name = $package->getName();
		list(,$app_name) = explode('/', $pkg_name);
		$app_name = preg_replace("/-(src|obf|php5?|php7)$/", '', $app_name);

		return $app_name;
	}

	protected function removeCode(PackageInterface $package)
	{
		$downloadPath = $this->getInstallPath($package);
		$this->downloadManager->remove($package, $downloadPath);
		$this->filesystem->removeDirectory($downloadPath);
	}

}
