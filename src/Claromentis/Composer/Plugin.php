<?php
namespace Claromentis\Composer;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\InstallerEvent;
use Composer\Installer\PackageEvent;
use Composer\Script\Event as ScriptEvent;

class Plugin implements \Composer\Plugin\PluginInterface, EventSubscriberInterface
{
	protected $postprocess = [];

	/**
	 * Apply plugin modifications to composer
	 *
	 * @param \Composer\Composer $composer
	 * @param \Composer\IO\IOInterface $io
	 */
	public function activate(\Composer\Composer $composer, \Composer\IO\IOInterface $io)
	{
		$core_installer_v7 = new FrameworkInstaller($io, $composer);
		$composer->getInstallationManager()->addInstaller($core_installer_v7);

		$core_installer_v8 = new FrameworkInstallerV8($io, $composer);
		$composer->getInstallationManager()->addInstaller($core_installer_v8);

		$module_installer = new ModuleInstallerV7($io, $composer);
		$composer->getInstallationManager()->addInstaller($module_installer);
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * The array keys are event names and the value can be:
	 *
	 * * The method name to call (priority defaults to 0)
	 * * An array composed of the method name to call and the priority
	 * * An array of arrays composed of the method names to call and respective
	 *   priorities, or 0 if unset
	 *
	 * For instance:
	 *
	 * * array('eventName' => 'methodName')
	 * * array('eventName' => array('methodName', $priority))
	 * * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
	 *
	 * @return array The event names to listen to
	 */
	public static function getSubscribedEvents()
	{
		return array(
			'pre-dependencies-solving' => 'onPreSolving',
			'post-package-install' => 'onPostInstall',
			'post-package-update' => 'onPostUpdate',
			'pre-package-uninstall' => 'onPreUninstall',
			'post-autoload-dump' => 'onPostAutoloadDump',
		);
	}

	public function onPreSolving(InstallerEvent $event)
	{
		$installed_repo = $event->getInstalledRepo();

		if (!$installed_repo->findPackage(FrameworkInstallerV8::PACKAGE_NAME, '*'))
		{
			$event->getIO()->write("Claromentis framework is not registerd as installed, adding 'local framework' repository");
			$local = FrameworkInstalledRepository::Construct($event->getIO());
			$installed_repo->addRepository($local);
		} else
		{
			$event->getIO()->write("Not adding 'local framework' repository, as package already present");
		}
	}

	public function onPostInstall(PackageEvent $event)
	{
		/** @var InstallOperation $operation */
		$operation = $event->getOperation();
		$package = $operation->getPackage();

		$installer = $event->getComposer()->getInstallationManager()->getInstaller($package->getType());

		if (method_exists($installer, 'onInstall'))
			$installer->onInstall($operation, $this->postprocess);
	}

	public function onPostUpdate(PackageEvent $event)
	{
		/** @var UpdateOperation $operation */
		$operation = $event->getOperation();
		$package = $operation->getTargetPackage();

		$installer = $event->getComposer()->getInstallationManager()->getInstaller($package->getType());

		if (method_exists($installer, 'onUpdate'))
			$installer->onUpdate($operation, $this->postprocess);
	}

	public function onPreUninstall(PackageEvent $event)
	{
		/** @var UninstallOperation $operation */
		$operation = $event->getOperation();
		$package = $operation->getPackage();

		$installer = $event->getComposer()->getInstallationManager()->getInstaller($package->getType());

		if (method_exists($installer, 'onUninstall'))
			$installer->onUninstall($operation, $this->postprocess);
	}

	public function onPostAutoloadDump(ScriptEvent $event)
	{
		if (empty($this->postprocess))
			return;

		$io = $event->getIO();

		$io->write("All code has been downloaded, now running database installation and migrations");
		$io->write("Migrations to run:\n     ".join("     \n", array_map(function ($el) {
			return 'phing -Dapp='.$el[1].' '.$el[0];
		}, $this->postprocess)));

		$phing_runner = new PhingRunner($io, Locator::getBuildXmlPath($io));
		foreach ($this->postprocess as $el)
		{
			list($operation, $app_code) = $el;
			try
			{
				$phing_runner->Run($app_code, $operation);
			} catch (\Exception $e)
			{
				if ($operation === 'install')
				{
					$io->writeError("\n\n<warning>Got exception while running phing task. " . get_class($e) . ': ' . $e->getMessage() . " </warning>");
					$io->writeError("<info>Trying to run upgrade instead</info>");
					$phing_runner->Run($app_code, 'upgrade');
				} else
				{
					$io->writeError("\n\n<error>Got exception while running phing task. " . get_class($e) . ': ' . $e->getMessage() . " </error>");
				}
			}
		}
	}
}