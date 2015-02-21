<?php
namespace Claromentis\Composer;

class Plugin implements Composer\Plugin\PluginInterface
{
    /**
     * Apply plugin modifications to composer
     *
     * @param Composer    $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
	{
		echo "===========Plugin activated=========\n";
	}
}