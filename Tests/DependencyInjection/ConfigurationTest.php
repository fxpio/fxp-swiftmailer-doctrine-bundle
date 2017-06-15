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
use Sonatra\Bundle\SwiftmailerDoctrineBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

/**
 * Configuration Tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ConfigurationTest extends TestCase
{
    public function testEmptyConfiguration()
    {
        $process = new Processor();
        $configs = array();
        $validConfig = array(
            'spool_email_class' => 'Sonatra\Component\SwiftmailerDoctrine\Model\SpoolEmailInterface',
            'override_send_command' => true,
        );

        $config = new Configuration();
        $res = $process->process($config->getConfigTreeBuilder()->buildTree(), $configs);
        $this->assertEquals($validConfig, $res);
    }
}
