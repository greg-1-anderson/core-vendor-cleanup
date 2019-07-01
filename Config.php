<?php

namespace Drupal\Component\VendorCleanup;

use Composer\Package\RootPackageInterface;

/**
 * Determine configuration.
 *
 * Default configuration is merged with the root package's
 * extra:drupal-core-vendor-cleanup configuration.
 */
class Config {

  /**
   * The default configuration which will always be merged with user config.
   *
   * @var array
   */
  protected static $defaultConfig = [
    'behat/mink' => ['tests', 'driver-testsuite'],
    'behat/mink-browserkit-driver' => ['tests'],
    'behat/mink-goutte-driver' => ['tests'],
    'behat/mink-selenium2-driver' => ['tests'],
    'brumann/polyfill-unserialize' => ['tests'],
    'composer/composer' => ['bin'],
    'drupal/coder' => [
      'coder_sniffer/Drupal/Test',
      'coder_sniffer/DrupalPractice/Test',
    ],
    'doctrine/cache' => ['tests'],
    'doctrine/collections' => ['tests'],
    'doctrine/common' => ['tests'],
    'doctrine/inflector' => ['tests'],
    'doctrine/instantiator' => ['tests'],
    'easyrdf/easyrdf' => ['scripts'],
    'egulias/email-validator' => ['documentation', 'tests'],
    'fabpot/goutte' => ['Goutte/Tests'],
    'guzzlehttp/promises' => ['tests'],
    'guzzlehttp/psr7' => ['tests'],
    'instaclick/php-webdriver' => ['doc', 'test'],
    'jcalderonzumba/gastonjs' => ['docs', 'examples', 'tests'],
    'jcalderonzumba/mink-phantomjs-driver' => ['tests'],
    'justinrainbow/json-schema' => ['demo'],
    'masterminds/html5' => ['bin', 'test'],
    'mikey179/vfsStream' => ['src/test'],
    'myclabs/deep-copy' => ['doc'],
    'paragonie/random_compat' => ['tests'],
    'pear/archive_tar' => ['docs', 'tests'],
    'pear/console_getopt' => ['tests'],
    'pear/pear-core-minimal' => ['tests'],
    'pear/pear_exception' => ['tests'],
    'phar-io/manifest' => ['examples', 'tests'],
    'phar-io/version' => ['tests'],
    'phpdocumentor/reflection-docblock' => ['tests'],
    'phpspec/prophecy' => ['fixtures', 'spec', 'tests'],
    'phpunit/php-code-coverage' => ['tests'],
    'phpunit/php-timer' => ['tests'],
    'phpunit/php-token-stream' => ['tests'],
    'phpunit/phpunit' => ['tests'],
    'phpunit/phpunit-mock-objects' => ['tests'],
    'sebastian/code-unit-reverse-lookup' => ['tests'],
    'sebastian/comparator' => ['tests'],
    'sebastian/diff' => ['tests'],
    'sebastian/environment' => ['tests'],
    'sebastian/exporter' => ['tests'],
    'sebastian/global-state' => ['tests'],
    'sebastian/object-enumerator' => ['tests'],
    'sebastian/object-reflector' => ['tests'],
    'sebastian/recursion-context' => ['tests'],
    'seld/jsonlint' => ['tests'],
    'squizlabs/php_codesniffer' => ['tests'],
    'stack/builder' => ['tests'],
    'symfony/browser-kit' => ['Tests'],
    'symfony/class-loader' => ['Tests'],
    'symfony/console' => ['Tests'],
    'symfony/css-selector' => ['Tests'],
    'symfony/debug' => ['Tests'],
    'symfony/dependency-injection' => ['Tests'],
    'symfony/dom-crawler' => ['Tests'],
    'symfony/filesystem' => ['Tests'],
    'symfony/finder' => ['Tests'],
    'symfony/event-dispatcher' => ['Tests'],
    'symfony/http-foundation' => ['Tests'],
    'symfony/http-kernel' => ['Tests'],
    'symfony/phpunit-bridge' => ['Tests'],
    'symfony/process' => ['Tests'],
    'symfony/psr-http-message-bridge' => ['Tests'],
    'symfony/routing' => ['Tests'],
    'symfony/serializer' => ['Tests'],
    'symfony/translation' => ['Tests'],
    'symfony/validator' => ['Tests', 'Resources'],
    'symfony/yaml' => ['Tests'],
    'symfony-cmf/routing' => ['Test', 'Tests'],
    'theseer/tokenizer' => ['tests'],
    'twig/twig' => ['doc', 'ext', 'test'],
    'zendframework/zend-escaper' => ['doc'],
    'zendframework/zend-feed' => ['doc'],
    'zendframework/zend-stdlib' => ['doc'],
  ];

  /**
   * The root package.
   *
   * @var Composer\Package\RootPackageInterface
   */
  protected $rootPackage;

  /**
   * Configuration gleaned from the root package.
   *
   * @var array
   */
  protected $configData = [];

  public function __construct(RootPackageInterface $root_package) {
    $this->rootPackage = $root_package;
  }

  /**
   * Get the configured list of directories to remove from the root package.
   *
   * This is stored in composer.json extra:drupal-core-vendor-cleanup.
   *
   * @return array[]
   *   An array keyed by package name. Each array value is an array of paths,
   *   relative to the package.
   */
  public function getAllCleanupPaths() {
    if ($this->configData) {
      return $this->configData;
    }
    // Get the root package config.
    $package_config = $this->rootPackage->getExtra();
    if (isset($package_config['drupal-core-vendor-cleanup'])) {
      $this->configData = $package_config['drupal-core-vendor-cleanup'];
    }
    // Ensure the values are arrays.
    foreach ($this->configData as $package => $paths) {
      if (!is_array($paths)) {
        $this->configData[$package] = [$paths];
      }
    }
    // Merge root config with defaults.
    foreach (static::$defaultConfig as $package => $paths) {
      if (isset($this->configData[$package])) {
        $this->configData[$package] = array_merge($this->configData[$package], $paths);
      }
      else {
        $this->configData[$package] = $paths;
      }
    }
    return $this->configData;
  }

  /**
   * Get a list of paths to remove for the given package.
   *
   * @param string $package
   *   The package name.
   *
   * @return string[]
   *   Array of paths to remove, relative to the package.
   */
  public function getPathsForPackage($package) {
    $paths = [];
    $config = $this->getAllCleanupPaths();
    if (isset($config[$package])) {
      $paths = $config[$package];
    }
    else {
      // Handle any mismatch in case between the package name and array key. For
      // example, the array key 'mikey179/vfsStream' needs to be found when
      // composer returns a package name of 'mikey179/vfsstream'.
      foreach (array_keys($config) as $key) {
        if (strtolower($key) == strtolower($package)) {
          $paths = $config[$key];
          break;
        }
      }
    }
    return $paths;
  }

  /**
   * If there is no configuration in the root package, it is unconfigured.
   *
   * @return bool
   *   TRUE if the root package does not have a list of packages with
   *   directories to clean.
   */
  public function isUnconfigured() {
    return empty($this->getAllCleanupPaths());
  }

}
