<?php

namespace Claromentis\Composer;
use Composer\IO\IOInterface;
use Phing;

/**
 * Class that starts Phing operation
 *
 * @author Alexander Polyanskikh
 */
class PhingRunner
{
	private $io;
	private $base_dir;

	public function __construct(IOInterface $io, $base_dir)
	{
		$this->io = $io;
		$this->base_dir = $base_dir;
	}

	public function Run($app_code, $action)
	{
		$io = $this->io;
		set_error_handler(function ($errno, $errmsg, $filename, $linenum) use ($io) {
			if (!(error_reporting() & $errno)) return true;
			$errors = array (
				E_ERROR           => "Error",
				E_WARNING         => "Warning",
				E_NOTICE          => "Notice",
				E_USER_ERROR      => "User error",
				E_USER_WARNING    => "User warning",
				E_USER_NOTICE     => "User notice",
				E_STRICT          => "Runtime Notice"
			);
			$io->write('<warning>'.$errors[$errno].": $errmsg at $filename:$linenum</warning>");
			return true;
		});

		$phing_path = is_dir("../vendor/phing/phing/classes") ? realpath("../vendor/phing/phing/classes") : realpath("vendor/phing/phing/classes");
		set_include_path(
			$phing_path .
			PATH_SEPARATOR .
			get_include_path()
		);

		$old_pwd = getcwd();
		chdir($this->base_dir);

		$e = null;
		try
		{
			require_once($phing_path . '/phing/Phing.php');
			Phing::startup();
			$args = array(
				'-Dapp=' . $app_code,
				$action,
			);
			Phing::fire($args);
			Phing::shutdown();
		} catch (\BuildException $e)
		{
		}

		chdir($old_pwd);

		restore_error_handler();

		if ($e !== null)
			throw $e;
	}

}
