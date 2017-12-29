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

use Fxp\Bundle\SwiftmailerDoctrineBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * Configuration Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ConfigurationTest extends TestCase
{
    public function testEmptyConfiguration()
    {
        $process = new Processor();
        $configs = [];
        $validConfig = [
            'spool_email_class' => 'Fxp\Component\SwiftmailerDoctrine\Model\SpoolEmailInterface',
            'override_send_command' => true,
        ];

        $config = new Configuration();
        $res = $process->process($config->getConfigTreeBuilder()->buildTree(), $configs);
        $this->assertEquals($validConfig, $res);
    }
}
