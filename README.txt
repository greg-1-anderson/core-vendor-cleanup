From https://www.drupal.org/project/drupal/issues/3057094#comment-13165992 (#32)

The Drupal Vendor Cleanup Composer Plugin
=========================================

Thanks for using this Drupal component.

You can participate in its development on Drupal.org, through our issue system:
https://www.drupal.org/project/issues/drupal

You can get the full Drupal repo here:
https://www.drupal.org/project/drupal/git-instructions

You can browse the full Drupal repo here:
http://cgit.drupalcode.org/drupal

What does it do?
----------------

This Composer plugin removes extraneous directories from the project's vendor
directory. They're typically directories which might contain executable files,
such as test directories.

This sort of processing is required for projects that have a vendor directory
inside the HTTP server docroot. This is a common layout for Drupal.

By default, the plugin knows how to clean up packages for Drupal core, so you
can require drupal/core-vendor-cleanup in your project and the rest will happen
auto-magically.

The plugin can also be configured using the project's composer.json extra field.

How do I set it up?
-------------------

Require this Composer plugin into your project:

    composer require drupal/core-vendor-cleanup

Then perform a clean install or update:

    $ rm -rf vendor/
    $ composer install

When you install or update, this plugin will look through each package and
remove directories it knows about.

You can see the list of default package cleanups for this plugin in Config.php.
If you discover that this list needs updating, please file an issue about it:
https://www.drupal.org/project/issues/drupal

In addition to the default list of packages, you can configure the plugin using
the root package's composer.json extra field, like this:

    "extra": {
      "drupal-core-vendor-cleanup": {
        "vendor/package": ["test", "documentation"]
      }
    }

The above code will tell the plugin to remove the test/ and documentation/
directories from the 'vendor/package' package when it is installed or updated.
