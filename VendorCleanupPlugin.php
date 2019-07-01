<?php

namespace Drupal\Component\VendorCleanup;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Package\CompletePackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;
use Composer\Script\Event;
use Composer\Installer\PackageEvents;

class VendorCleanupPlugin implements PluginInterface, EventSubscriberInterface {

  /**
   * Composer object.
   *
   * @var \Composer\Composer
   */
  protected $composer;

  /**
   * IO object.
   *
   * @var \Composer\IO\IOInterface
   */
  protected $io;

  /**
   * Configuration.
   *
   * @var \Drupal\Component\VendorCleanup\Config
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function activate(Composer $composer, IOInterface $io) {
    $this->composer = $composer;
    $this->io = $io;

    // Set up configuration.
    $this->config = new Config($this->composer->getPackage());
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      ScriptEvents::POST_UPDATE_CMD => 'postCmd',
      ScriptEvents::POST_INSTALL_CMD => 'postCmd',
      PackageEvents::POST_PACKAGE_INSTALL => 'onPostPackageInstall',
      PackageEvents::POST_PACKAGE_UPDATE => 'onPostPackageUpdate',
    ];
  }

  /**
   * Post command event callback.
   *
   * @param \Composer\Script\Event $event
   *   The Composer event.
   */
  public function postCmd(Event $event) {
    $this->io->write(sprintf("Post command"));
  }

  /**
   * POST_PACKAGE_INSTALL event handler.
   *
   * @param \Composer\Installer\PackageEvent $event
   */
  public function onPostPackageInstall(PackageEvent $event) {
    print "post package install for vendor cleanup plugin ===============\n";
    /** @var \Composer\Package\CompletePackage $package */
    $package = $event->getOperation()->getPackage();
    $this->cleanPackage($this->composer->getConfig()->get('vendor-dir'), $package);
  }

  /**
   * POST_PACKAGE_UPDATE event handler.
   *
   * @param \Composer\Installer\PackageEvent $event
   */
  public function onPostPackageUpdate(PackageEvent $event) {
    print "post package update for vendor cleanup plugin ===============\n";
    /** @var \Composer\Package\CompletePackage $package */
    $package = $event->getOperation()->getTargetPackage();
    $this->cleanPackage($this->composer->getConfig()->get('vendor-dir'), $package);
  }

  /**
   * Clean out the package.
   *
   * @param \Composer\Package\CompletePackageInterface $package
   */
  public function cleanPackage($vendor_dir, CompletePackageInterface $package) {
    $package_name = $package->getName();
    $this->io->write(sprintf("Clean %s", $package_name));
    $paths_for_package = $this->config->getPathsForPackage($package_name);
    if ($paths_for_package) {
      $package_dir = $vendor_dir . '/' . $package_name;
      if (is_dir($package_dir)) {
        $this->io->write(sprintf("    Package cleanup for <comment>%s</comment>", $package_name));
        $fs = new Filesystem();
        foreach ($paths_for_package as $cleanup_item) {
          $cleanup_path = $package_dir . '/' . $cleanup_item;
          if (is_dir($cleanup_path)) {
            if ($fs->removeDirectory($cleanup_path)) {
              $this->io->write(sprintf("      <info>Removing directory '%s'</info>", $cleanup_item));
            }
            else {
              // Always display a message if this fails as it means something
              // has gone wrong. Therefore the message has to include the
              // package name as the first informational message might not
              // exist.
              $this->io->write(sprintf("      <error>Failure removing directory '%s'</error> in package <comment>%s</comment>.", $cleanup_item, $package_name), TRUE, IOInterface::NORMAL);
            }
          }
          else {
            // If the package has changed or the --prefer-dist version does not
            // include the directory. This is not an error.
            $this->io->write(sprintf("      <comment>Directory '%s' does not exist.</comment>", $cleanup_path));
          }
        }
        $this->io->write('');
      }
    }
  }

}
