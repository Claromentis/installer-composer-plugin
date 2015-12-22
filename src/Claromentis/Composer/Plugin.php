<?php
namespace Claromentis\Composer;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;

class Plugin implements \Composer\Plugin\PluginInterface, EventSubscriberInterface
{
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
			'post-package-install' => 'onPostInstall',
			'post-package-update' => 'onPostUpdate',
			'pre-package-uninstall' => 'onPreUninstall',
		);
	}


	public function onPostInstall(PackageEvent $event)
	{
		/** @var InstallOperation $operation */
		$operation = $event->getOperation();
		$package = $operation->getPackage();

		$installer = $event->getComposer()->getInstallationManager()->getInstaller($package->getType());

		if (method_exists($installer, 'onInstall'))
			$installer->onInstall($operation);
	}

	public function onPostUpdate(PackageEvent $event)
	{
		/** @var UpdateOperation $operation */
		$operation = $event->getOperation();
		$package = $operation->getTargetPackage();

		$installer = $event->getComposer()->getInstallationManager()->getInstaller($package->getType());

		if (method_exists($installer, 'onUpdate'))
			$installer->onUpdate($operation);
	}

	public function onPreUninstall(PackageEvent $event)
	{
		/** @var UninstallOperation $operation */
		$operation = $event->getOperation();
		$package = $operation->getPackage();

		$installer = $event->getComposer()->getInstallationManager()->getInstaller($package->getType());

		if (method_exists($installer, 'onUninstall'))
			$installer->onUninstall($operation);
	}
}