<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SwiftmailerDoctrineBundle\Tests\DependencyInjection;

use Fxp\Bundle\SwiftmailerDoctrineBundle\DependencyInjection\FxpSwiftmailerDoctrineExtension;
use Fxp\Bundle\SwiftmailerDoctrineBundle\FxpSwiftmailerDoctrineBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Bundle Extension Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FxpDoctrineConsoleExtensionTest extends TestCase
{
    /**
     * @var string
     */
    protected $cacheDir;

    protected function setUp()
    {
        $this->cacheDir = sys_get_temp_dir().'/fxp_swift_mailer_doctrine_tests';
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->cacheDir);
    }

    public function testCompileContainerWithExtension()
    {
        $container = $this->getContainer();
        $this->assertTrue($container->hasDefinition('swiftmailer.spool.fxp_doctrine_orm_spool'));
        $this->assertTrue($container->hasAlias('swiftmailer.mailer.default.spool.fxp_doctrine_orm_spool'));

        $this->assertTrue($container->hasParameter('fxp_swiftmailer_doctrine.spool_email_class'));
        $this->assertEquals('Fxp\Component\SwiftmailerDoctrine\Model\SpoolEmailInterface', $container->getParameter('fxp_swiftmailer_doctrine.spool_email_class'));

        $this->assertTrue($container->hasDefinition('fxp_swiftmailer_doctrine.command.send_email'));
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

        $bundle = new FxpSwiftmailerDoctrineBundle();
        $bundle->build($container); // Attach all default factories

        $extension = new FxpSwiftmailerDoctrineExtension();
        $container->registerExtension($extension);
        $config = array();
        $extension->load(array($config), $container);

        return $container;
    }
}
