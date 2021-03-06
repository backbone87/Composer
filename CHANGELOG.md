CHANGELOG
=========

This changelog references the relevant changes (bug and security fixes and new features and improvements) done.

Version 0.8.2
-------------

* Bugfix: #112, disable all TL_HOOKS, while in the composer client backend.

Version 0.8.1
-------------

* Bugfix: Re-run all runonces when the contao version has changes.

Version 0.8
-----------

* Feature: #115, it is now possible to pin packages to a specific version.
* Bugfix: #117, fix issue with internal cache.
* Bugfix: #111, fix removing packages via UI now works again.
* Internal: The backend views are now split into controllers. Thanks to backbone97 for this inspiration.

Version 0.7.14
--------------

* Bugfix: Fix that APC is suggested as enabled (even if it is disabled), when the ini_set function is disabled.

Version 0.7.13
--------------

* Bugfix: Detect APCU correctly and not complain that APC - which not exists - is enabled.

Version 0.7.12
--------------

* Improvement: The generated constraints in version selection will now use *-dev as upper border. This prevent installing of next-major dev packages.

Version 0.7.11
--------------

* Improvement: The replacement information in the package list is now better placed.
* Bugfix: Remove packages where a successor/replacement package is installed is now possible.
* Bugfix: #105, long description lines are now wrapped in the search listing and will not break layout anymore.
* Bugfix: Fix that the (not installed) required package and the (installed) replacement packages are shown in the package list.

Version 0.7.10
--------------

* Improvement: Update the visual of the #tl_buttons bar according to new contao 3 layout.
* Improvement: Show confirmation messages instead of error messages, when the installer update the composer config.
* Bugfix: Fix that the remove button is shown for dependencies in the package list.

Version 0.7.9
-------------

* Bugfix: Fix endless recursion issue with replacement packages.

Version 0.7.6
-------------

* Bugfix: Display the correct require constraint for replacement packages.

Version 0.7.5
-------------

* Improvement: In package list hide packages that are replaced by a successor and show which package replace another.

Version 0.7.3
-------------

* Improvement: Add a new method to hack the contao 2 classes cache.

Version 0.7.2
-------------

* Bugfix: Keep the files of the ER2 "composer" package on migration.
* Bugfix: Fix some missing global namespace prefixes.

Version 0.7.1
-------------

* Bugfix: Skip the ER2 "composer" package on migration.

Version 0.7.0
-------------

* Feature: Check for suhosin and show an compatibility error that suhosin is not supported.
* Internal: Rework the classes into namespaces and introduce a custom minimalistic class loader.
* Internal: Rework the internal classes and introduce a Runtime class that hold a lot of convenience and runtime related methods.
