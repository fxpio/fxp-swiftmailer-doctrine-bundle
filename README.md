Sonatra Swiftmailer Doctrine Bundle
===================================

[![Latest Version](https://img.shields.io/packagist/v/sonatra/swiftmailer-doctrine-bundle.svg)](https://packagist.org/packages/sonatra/swiftmailer-doctrine-bundle)
[![Build Status](https://img.shields.io/travis/sonatra/SonatraSwiftmailerDoctrineBundle/master.svg)](https://travis-ci.org/sonatra/SonatraSwiftmailerDoctrineBundle)
[![Coverage Status](https://img.shields.io/coveralls/sonatra/SonatraSwiftmailerDoctrineBundle/master.svg)](https://coveralls.io/r/sonatra/SonatraSwiftmailerDoctrineBundle?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/sonatra/SonatraSwiftmailerDoctrineBundle/master.svg)](https://scrutinizer-ci.com/g/sonatra/SonatraSwiftmailerDoctrineBundle?branch=master)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/dc82c2cc-2c80-40d2-853e-deb0bbc228ac.svg)](https://insight.sensiolabs.com/projects/dc82c2cc-2c80-40d2-853e-deb0bbc228ac)

The Sonatra SwiftmailerDoctrineBundle add a doctrine spool for Swiftmailer.

Features include:

- Doctrine Spool
- Spool Email model and entity class for Doctrine
- Doctrine orm mapping
- Overriding of the `swiftmailer:spool:send` command for use the `--recover-timeout` option with doctrine spool (switched off)

Documentation
-------------

The bulk of the documentation is stored in the `Resources/doc/index.md`
file in this bundle:

[Read the Documentation](Resources/doc/index.md)

Installation
------------

All the installation instructions are located in [documentation](Resources/doc/index.md).

License
-------

This bundle is under the MIT license. See the complete license in the bundle:

[Resources/meta/LICENSE](Resources/meta/LICENSE)

About
-----

Sonatra SwiftmailerDoctrineBundle is a [sonatra](https://github.com/sonatra) initiative.
See also the list of [contributors](https://github.com/sonatra/SonatraSwiftmailerDoctrineBundle/graphs/contributors).

Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/sonatra/SonatraSwiftmailerDoctrineBundle/issues).

When reporting a bug, it may be a good idea to reproduce it in a basic project
built using the [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
to allow developers of the bundle to reproduce the issue by simply cloning it
and following some steps.
