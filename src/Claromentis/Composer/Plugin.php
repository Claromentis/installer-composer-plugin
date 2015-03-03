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
		$installer = new FrameworkInstaller($io, $composer);
		$composer->getInstallationManager()->addInstaller($installer);

		$old_installer = new ModuleInstallerV7($io, $composer);
		$composer->getInstallationManager()->addInstaller($old_installer);
	}
}