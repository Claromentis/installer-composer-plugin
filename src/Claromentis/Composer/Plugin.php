<?php
namespace Claromentis\Composer;

class Plugin implements \Composer\Plugin\PluginInterface
{
    /**
     * Apply plugin modifications to composer
     *
     * @param Composer    $composer
     * @param IOInterface $io
     */
    public function activate(\Composer\Composer $composer, \Composer\IO\IOInterface $io)
	{
		echo "===========Plugin activated=========\n";

		$installer = new Installer($io, $composer, "claromentis-module");
        $composer->getInstallationManager()->addInstaller($installer);
	}
}