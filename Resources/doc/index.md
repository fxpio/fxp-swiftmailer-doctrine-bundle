Getting Started
===============

## Prerequisites

This version of the bundle requires Symfony 2.6+, and configuring of the Swiftmailer bundle should
be performed (see the [doc](http://symfony.com/doc/current/cookbook/email/email.html), if this isn't the case).

## Installation

Installation is a quick, 6 step process:

1. Download the bundle using composer
2. Enable the bundle
3. Create your SpoolEmail class
4. Configure your application's config.yml
5. Update your database schema
6. Configure the bundle

### Step 1: Download the bundle using composer

Add Sonatra SwiftmailerDoctrineBundle in your composer.json:

```js
{
    "require": {
        "sonatra/swiftmailer-doctrine-bundle": "~1.0"
    }
}
```

Or tell composer to download the bundle by running the command:

```bash
$ php composer.phar update sonatra/swiftmailer-doctrine-bundle
```

Composer will install the bundle to your project's `vendor/sonatra` directory.

### Step 2: Enable the bundle

Enable the bundle in the kernel:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Sonatra\Bundle\SwiftmailerDoctrineBundle\SonatraSwiftmailerDoctrineBundle(),
    );
}
```

### Step 3: Create your SpoolEmail class

#### Create the SpoolEmail class

``` php
// src/Acme/CoreBundle/Entity/SpoolEmail.php

namespace Acme\CoreBundle\Entity;

use Sonatra\Bundle\SwiftmailerDoctrineBundle\Model\SpoolEmail as BaseSpoolEmail;

class SpoolEmail extends BaseSpoolEmail
{
}
```

#### Create the SpoolEmail mapping

```xml
<?xml version="1.0" encoding="UTF-8"?>
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                  http://raw.github.com/doctrine/doctrine2/master/doctrine-mapping.xsd">

    <entity name="Acme\CoreBundle\Entity\SpoolEmail" table="core_spool_email">
        <id name="id" type="integer" column="id">
            <generator strategy="AUTO"/>
        </id>
    </entity>
</doctrine-mapping>
```

### Step 4: Configure your application's config.yml

Add the following configuration to your `config.yml`.

```yaml
# app/config/config.yml
sonatra_swiftmailer_doctrine:
    spool_email_class:  Acme\CoreBundle\Entity\SpoolEmail

swiftmailer:
    spool:
        type: sonatra_doctrine_orm_spool
```

### Step 5: Update your database schema

```bash
$ php app/console doctrine:schema:update --force
```

### Step 6: Configure the bundle

You can override the default configuration adding `sonatra_swiftmailer_doctrine` tree in `app/config/config.yml`.
For see the reference of Sonatra Resource Configuration, execute command:

```bash
$ php app/console config:dump-reference SonatraSwiftmailerDoctrineBundle
```

### Next Steps

Now that you have completed the basic installation and configuration of the
Sonatra SwiftmailerDoctrineBundle, you are ready to learn about usages of the bundle.

You can now use the `mailer` service to send emails in the Doctrine Spool, and use the
command `swiftmailer:spool:send` to send emails to recipients.
