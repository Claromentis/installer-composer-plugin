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

	public function __construct(IOInterface $io, $base_dir = null)
	{
		$this->io = $io;

		// this is added for compatibility with installer 1.0 when it's upgraded to 1.1
		if (is_null($base_dir))
			$base_dir = realpath(Locator::getBuildXmlPath($io));

		if (!file_exists($base_dir.'/build.xml'))
			$this->io->write("<error>$base_dir/build.xml doesn't exist there, failure imminent</error>");
		$this->base_dir = $base_dir;
	}

	public function Run($app_code, $action)
	{
		if (is_dir("../vendor_core/phing/phing")) // installation from "claromentis/installer" folder
			$phing_path = "../vendor_core/phing/phing";
		elseif (is_dir("vendor/phing/phing"))     // installation from "claromentis" folder (usually, by a developer or Cla 7)
			$phing_path = "vendor/phing/phing";
		elseif (is_dir("../vendor/phing/phing"))  // should not be needed, but still included
			$phing_path = "../vendor/phing/phing";
		else
			throw new \Exception("Cannot find phing");

		$phing_path = realpath($phing_path);

		$old_pwd = getcwd();
		chdir($this->base_dir);

		$cmd = $phing_path . DIRECTORY_SEPARATOR . "bin" . DIRECTORY_SEPARATOR . "phing -Dapp=$app_code $action";
		$this->io->write("Running command: $cmd");
		$ret = -1;
		passthru($cmd, $ret);

		chdir($old_pwd);

		if ($ret !== 0)
		{
			$msg = "Phing returned non-zero code - $ret";
			$this->io->writeError($msg);
			throw new \Exception($msg);
		}
	}

}
