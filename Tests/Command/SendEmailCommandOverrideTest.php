<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SwiftmailerDoctrineBundle\Tests\Command;

use Fxp\Bundle\SwiftmailerDoctrineBundle\Command\SendEmailCommandOverride;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Send Email Command Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class SendEmailCommandOverrideTest extends TestCase
{
    /**
     * @var Application|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $application;
    /**
     * @var Definition|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $definition;
    /**
     * @var KernelInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $kernel;
    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;
    /**
     * @var SendEmailCommandOverride
     */
    protected $command;

    /**
     * @var HelperSet|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperSet;

    public function setUp()
    {
        if (!class_exists('Symfony\Component\Console\Application')) {
            $this->markTestSkipped('Symfony Console is not available.');
        }

        $this->application = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Console\Application')
            ->disableOriginalConstructor()
            ->getMock();
        $this->definition = $this->getMockBuilder('Symfony\Component\Console\Input\InputDefinition')
            ->disableOriginalConstructor()
            ->getMock();
        $this->kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\KernelInterface')->getMock();
        $this->helperSet = $this->getMockBuilder('Symfony\Component\Console\Helper\HelperSet')->getMock();
        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')->getMock();

        $this->application->expects($this->any())
            ->method('getDefinition')
            ->will($this->returnValue($this->definition));
        $this->definition->expects($this->any())
            ->method('getArguments')
            ->will($this->returnValue([]));
        $this->definition->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue([
                new InputOption('--verbose', '-v', InputOption::VALUE_NONE, 'Increase verbosity of messages.'),
                new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'),
                new InputOption('--no-debug', null, InputOption::VALUE_NONE, 'Switches off debug mode.'),
            ]));
        $this->application->expects($this->any())
            ->method('getKernel')
            ->will($this->returnValue($this->kernel));
        $this->application->expects($this->once())
            ->method('getHelperSet')
            ->will($this->returnValue($this->helperSet));
        $this->kernel->expects($this->any())
            ->method('getContainer')
            ->will($this->returnValue($this->container));

        /* @var Application $application */
        $application = $this->application;

        $this->command = new SendEmailCommandOverride();
        $this->command->setApplication($application);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The mailer "invalid" does not exist
     */
    public function testInvalidMailerName()
    {
        $this->container->expects($this->any())
            ->method('getParameter')
            ->with($this->equalTo('swiftmailer.mailers'))
            ->will($this->returnValue([]));

        $this->command->run(new ArrayInput(['--mailer' => 'invalid']), new NullOutput());
    }

    public function testDisabledMailer()
    {
        $this->container->expects($this->any())
            ->method('has')
            ->with($this->equalTo('swiftmailer.mailer.disabled'))
            ->will($this->returnValue(true));

        $this->command->run(new ArrayInput(['--mailer' => 'disabled']), new NullOutput());
    }

    public function testDisabledMailers()
    {
        $this->container->expects($this->at(0))
            ->method('getParameter')
            ->with($this->equalTo('swiftmailer.mailers'))
            ->will($this->returnValue(['disabled' => 'disabled']));

        $this->container->expects($this->any())
            ->method('has')
            ->with($this->equalTo('swiftmailer.mailer.disabled'))
            ->will($this->returnValue(true));

        $this->command->run(new ArrayInput([]), new NullOutput());
    }

    public function getTimeout()
    {
        return [
            [null],
            [50],
        ];
    }

    /**
     * @dataProvider getTimeout
     *
     * @param int|null $timeout
     */
    public function testEnabledMailer($timeout)
    {
        $this->container->expects($this->at(0))
            ->method('getParameter')
            ->with($this->equalTo('swiftmailer.mailers'))
            ->will($this->returnValue(['enabled' => 'enabled']));

        $this->container->expects($this->any())
            ->method('has')
            ->with($this->equalTo('swiftmailer.mailer.enabled'))
            ->will($this->returnValue(true));

        $this->container->expects($this->at(2))
            ->method('getParameter')
            ->with($this->equalTo('swiftmailer.mailer.enabled.spool.enabled'))
            ->will($this->returnValue(true));

        $spool = $this->getMockBuilder('Fxp\Component\SwiftmailerDoctrine\Spool\DoctrineSpool')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $spool->expects($this->any())
            ->method('flushQueue')
            ->will($this->returnValue(42));

        $transport = $this->getMockBuilder('Swift_Transport_SpoolTransport')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $transport->expects($this->any())
            ->method('getSpool')
            ->will($this->returnValue($spool));

        $mailer = $this->getMockBuilder('Swift_Mailer')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $mailer->expects($this->any())
            ->method('getTransport')
            ->will($this->returnValue($transport));

        $this->container->expects($this->at(3))
            ->method('get')
            ->with($this->equalTo('swiftmailer.mailer.enabled'))
            ->will($this->returnValue($mailer));

        $this->container->expects($this->at(4))
            ->method('get')
            ->with($this->equalTo('swiftmailer.mailer.enabled.transport.real'))
            ->will($this->returnValue($transport));

        $options = null !== $timeout
            ? ['--recover-timeout' => $timeout]
            : [];

        $this->command->run(new ArrayInput($options), new NullOutput());
    }
}
