<?php
namespace Claromentis\Composer;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

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
		$web = Locator::GetWebFolderPath();
		return $web.'intranet/'.$this->getApplicationCode($package).'/';
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

	public function onInstall(InstallOperation $operation)
	{
		$package = $operation->getPackage();
		try
		{
			$this->runPhing($this->getApplicationCode($package), 'install');
		} catch (\Exception $e)
		{
			$this->io->writeError("\n\n<warning>Got exception while running phing task. ".get_class($e).': '.$e->getMessage()." </warning>");
			$this->io->writeError("<info>Trying to run upgrade instead</info>");
			$this->runPhing($this->getApplicationCode($package), 'upgrade');
		}
	}

	public function onUpdate(UpdateOperation $operation)
	{
		$package = $operation->getTargetPackage();
		$this->runPhing($this->getApplicationCode($package), 'upgrade');
	}

	public function onUninstall(UninstallOperation $operation)
	{
		$package = $operation->getPackage();
		$code = $this->getApplicationCode($package);
		if ($this->io->askConfirmation("Do you want to delete all database tables of application '$code'?", false))
			$this->runPhing($code, 'uninstall');
		else
			$this->io->write("Application code is going to be removed, but the database still has all data");

		$this->io->write("<warning>Please manually delete all references to $code from the core config file</warning>");
	}
}
