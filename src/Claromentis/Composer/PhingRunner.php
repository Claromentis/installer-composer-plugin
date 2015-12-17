<?php

namespace Claromentis\Composer;
use Composer\IO\IOInterface;
use Phing;
use Symfony\Component\Process\Process;

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
		if (!file_exists($base_dir.'/build.xml'))
			$this->io->write("<error>$base_dir/build.xml doesn't exist there, failure imminent</error>");
	}

	public function Run($app_code, $action)
	{
		if (is_dir("../vendor_core/phing")) // installation from "claromentis/installer" folder
			$phing_path = "../vendor_core/phing";
		elseif (is_dir("vendor/phing"))     // installation from "claromentis" folder (usually, by a developer or Cla 7)
			$phing_path = "vendor/phing";
		elseif (is_dir("../vendor/phing"))  // should not be needed, but still included
			$phing_path = "../vendor/phing";
		else
			throw new \Exception("Cannot find phing");

		$phing_path = realpath($phing_path);

		$process = new Process($phing_path.DIRECTORY_SEPARATOR."phing -Dapp=$app_code $action", $this->base_dir);
		$process->setPty(true);
		$process->run();

		restore_error_handler();
	}

}
