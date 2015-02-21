<?php
namespace Claromentis\Composer;

class Plugin implements \Composer\Plugin\PluginInterface
{
	/**
	 * Apply plugin modifications to composer
	 *
	 * @param \Composer\Composer $composer
	 * @param \Composer\IO\IOInterface $io
	 */
	public function activate(\Composer\Composer $composer, \Composer\IO\IOInterface $io)
	{
		$installer = new ModuleInstaller($io, $composer, "claromentis-module");
		$composer->getInstallationManager()->addInstaller($installer);
	}
}