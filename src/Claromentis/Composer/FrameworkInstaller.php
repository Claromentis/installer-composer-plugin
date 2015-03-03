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
class FrameworkInstaller implements InstallerInterface
{
	protected $composer;
	protected $downloadManager;
	protected $io;
	protected $filesystem;

	/**
	 * Initializes library installer.
	 *
	 * @param IOInterface $io
	 * @param Composer    $composer
	 * @param Filesystem  $filesystem
	 */
	public function __construct(IOInterface $io, Composer $composer, Filesystem $filesystem = null)
	{
		$this->composer = $composer;
		$this->downloadManager = $composer->getDownloadManager();
		$this->io = $io;

		$this->filesystem = $filesystem ?: new Filesystem();
	}

	public function supports($packageType)
	{
		return $packageType === 'claromentis-framework';
	}

	/**
	 * {@inheritDoc}
	 */
	public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package)
	{
		return $repo->hasPackage($package) && is_readable($this->getInstallPath($package));
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

		$this->runPhing('core', 'install');
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

		$this->runPhing('core', 'upgrade');
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

	protected function installCode(PackageInterface $package)
	{
		$installPath = $this->getInstallPath($package);

		$this->filesystem->ensureDirectoryExists($installPath);
		if ($this->filesystem->isDirEmpty($installPath))
		{
			$this->downloadManager->download($package, $installPath);
		} else
		{
			$downloadPath = $installPath . '.1';

			$this->downloadManager->download($package, $downloadPath);
			$this->io->write("    Download finished, copying the code");
			$this->filesystem->copyThenRemove($downloadPath, $installPath);
			$this->filesystem->rmdir($downloadPath);
		}
	}

	protected function removeCode(PackageInterface $package)
	{
		$downloadPath = $this->getInstallPath($package);
		$this->downloadManager->remove($package, $downloadPath);
		$this->filesystem->removeDirectory($downloadPath);
	}

	/**
	 * Run phing action for the specified module
	 *
	 * @param string $app_code
	 * @param string $action
	 */
	protected function runPhing($app_code, $action)
	{
		$this->io->write('    <warning>===Please run this command===</warning>');
		$this->io->write("    phing -Dapp={$app_code} $action");
	}
}
