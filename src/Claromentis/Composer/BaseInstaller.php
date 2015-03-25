<?php
namespace Claromentis\Composer;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\Installer\InstallerInterface;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Util\Filesystem;
use Composer\Util\ProcessExecutor;
use Phing;

/**
 * Base class for common functions of framework and modules installers
 *
 * @author Alexander Polyanskikh
 */
abstract class BaseInstaller implements InstallerInterface
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

	/**
	 * {@inheritDoc}
	 */
	public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package)
	{
		return $repo->hasPackage($package) && is_readable($this->getInstallPath($package));
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
		}
	}

	public function updateCode(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
	{
		if (!$repo->hasPackage($initial)) {
			throw new \InvalidArgumentException('Package is not installed: '.$initial);
		}

		if ($initial->getInstallationSource() == 'source' && $initial->getSourceType() == 'git' && $target->getSourceType() == 'git')
		{
			$this->downloadManager->update($initial, $target, $this->getInstallPath($target));
		} else
		{
			$this->installCode($target);
			$repo->removePackage($initial);
			if (!$repo->hasPackage($target))
			{
				$repo->addPackage(clone $target);
			}
		}
	}

	/**
	 * Run phing action for the specified module
	 *
	 * @param string $app_code
	 * @param string $action
	 */
	protected function runPhing($app_code, $action)
	{
		$phing_runner = new PhingRunner($this->io);
		$phing_runner->Run($app_code, $action);

		//$this->io->write('    <warning>===Please run this command===</warning>');
		//$this->io->write("    phing -Dapp={$app_code} $action");
	}
}