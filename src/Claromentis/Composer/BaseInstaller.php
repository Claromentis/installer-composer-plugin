<?php
namespace Claromentis\Composer;

use Composer\Composer;
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

	/**
	 * Run phing action for the specified module
	 *
	 * @param string $app_code
	 * @param string $action
	 */
	protected function runPhing($app_code, $action)
	{
		set_error_handler(function ($errno, $errmsg, $filename, $linenum) {
			if (!(error_reporting() & $errno)) return false;
			$errors = array (
				E_ERROR           => "Error",
				E_WARNING         => "Warning",
				E_NOTICE          => "Notice",
				E_USER_ERROR      => "User error",
				E_USER_WARNING    => "User warning",
				E_USER_NOTICE     => "User notice",
				E_STRICT          => "Runtime Notice"
			);
			echo $errors[$errno].": $errmsg at $filename:$linenum\n";
			return true;
		});

		$old_pwd = getcwd();
		chdir('web');

		$phing_path = realpath("../vendor/phing/phing/classes");
		set_include_path(
			$phing_path .
			PATH_SEPARATOR .
			get_include_path()
		);
		require_once($phing_path.'/phing/Phing.php');
		Phing::startup();
		$args = array(
			'-Dapp='.$app_code,
			$action,
		);
		Phing::fire($args);
		Phing::shutdown();

		chdir($old_pwd);

		restore_error_handler();
		/*
		$this->io->write('    <warning>===Please run this command===</warning>');
		$this->io->write("    phing -Dapp={$app_code} $action");
		*/
	}

	protected function getProcess()
	{
		return new ProcessExecutor($this->io);
	}
}