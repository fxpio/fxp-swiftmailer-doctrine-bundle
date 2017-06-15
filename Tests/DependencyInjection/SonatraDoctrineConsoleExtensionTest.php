<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SwiftmailerDoctrineBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Sonatra\Bundle\SwiftmailerDoctrineBundle\DependencyInjection\SonatraSwiftmailerDoctrineExtension;
use Sonatra\Bundle\SwiftmailerDoctrineBundle\SonatraSwiftmailerDoctrineBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Bundle Extension Tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SonatraDoctrineConsoleExtensionTest extends TestCase
{
    /**
     * @var string
     */
    protected $cacheDir;

    protected function setUp()
    {
        $this->cacheDir = sys_get_temp_dir().'/sonatra_swift_mailer_doctrine_tests';
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->cacheDir);
    }

    public function testCompileContainerWithExtension()
    {
        $container = $this->getContainer();
        $this->assertTrue($container->hasDefinition('swiftmailer.spool.sonatra_doctrine_orm_spool'));
        $this->assertTrue($container->hasAlias('swiftmailer.mailer.default.spool.sonatra_doctrine_orm_spool'));

        $this->assertTrue($container->hasParameter('sonatra_swiftmailer_doctrine.spool_email_class'));
        $this->assertEquals('Sonatra\Component\SwiftmailerDoctrine\Model\SpoolEmailInterface', $container->getParameter('sonatra_swiftmailer_doctrine.spool_email_class'));

        $this->assertTrue($container->hasDefinition('sonatra_swiftmailer_doctrine.command.send_email'));
    }

    /**
     * Gets the container.
     *
     * @return ContainerBuilder
     */
    protected function getContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.cache_dir' => $this->cacheDir,
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.name' => 'kernel',
            'kernel.root_dir' => __DIR__,
            'kernel.charset' => 'UTF-8',
            'assetic.debug' => false,
            'kernel.bundles' => array(),
            'locale' => 'en',
        )));

        $bundle = new SonatraSwiftmailerDoctrineBundle();
        $bundle->build($container); // Attach all default factories

        $extension = new SonatraSwiftmailerDoctrineExtension();
        $container->registerExtension($extension);
        $config = array();
        $extension->load(array($config), $container);

        return $container;
    }
}
