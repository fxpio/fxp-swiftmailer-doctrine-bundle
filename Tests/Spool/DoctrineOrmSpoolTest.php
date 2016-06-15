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

use Sonatra\Bundle\SwiftmailerDoctrineBundle\Entity\SpoolEmail;
use Sonatra\Bundle\SwiftmailerDoctrineBundle\Spool\DoctrineOrmSpool;
use Sonatra\Bundle\SwiftmailerDoctrineBundle\SpoolEmailStatus;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Doctrine ORM Spool Tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DoctrineOrmSpoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Sonatra\Bundle\SwiftmailerDoctrineBundle\Exception\InvalidArgumentException
     * @expectedExceptionMessage The "stdClass" class does not extend "Sonatra\Bundle\SwiftmailerDoctrineBundle\Model\SpoolEmailInterface
     */
    public function testInvalidClass()
    {
        /* @var RegistryInterface|\PHPUnit_Framework_MockObject_MockObject $registry */
        $registry = $this->getMockBuilder('Symfony\Bridge\Doctrine\RegistryInterface')->getMock();

        new DoctrineOrmSpool($registry, 'stdClass');
    }

    /**
     * @expectedException \Sonatra\Bundle\SwiftmailerDoctrineBundle\Exception\InvalidArgumentException
     * @expectedExceptionMessage The repository of "Sonatra\Bundle\SwiftmailerDoctrineBundle\Entity\SpoolEmail" must be an instance of "Sonatra\Bundle\SwiftmailerDoctrineBundle\Model\Repository\SpoolEmailRepositoryInterface"
     */
    public function testInvalidRepository()
    {
        $repo = $this->getMockBuilder('Doctrine\ORM\ObjectRepository')->getMock();
        $manager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')->getMock();
        $manager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repo));

        /* @var RegistryInterface|\PHPUnit_Framework_MockObject_MockObject $registry */
        $registry = $this->getMockBuilder('Symfony\Bridge\Doctrine\RegistryInterface')->getMock();
        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($manager))
        ;

        new DoctrineOrmSpool($registry, 'Sonatra\Bundle\SwiftmailerDoctrineBundle\Entity\SpoolEmail');
    }

    public function testBasic()
    {
        $spool = $this->createSpool();

        $this->assertFalse($spool->isStarted());
        $spool->start();

        $this->assertTrue($spool->isStarted());
        $spool->stop();
        $this->assertFalse($spool->isStarted());
    }

    public function testQueueMessage()
    {
        /* @var \Swift_Mime_Message $message */
        $message = $this->getMockBuilder('Swift_Mime_Message')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->assertTrue($this->createSpool()->queueMessage($message));
    }

    public function testFlushQueueEmpty()
    {
        $failedRecipients = array();
        /* @var \Swift_Transport|\PHPUnit_Framework_MockObject_MockObject $transport */
        $transport = $this->getMockBuilder('Swift_Transport')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->assertEquals(0, $this->createSpool()->flushQueue($transport, $failedRecipients));
        $this->assertCount(0, $failedRecipients);
    }

    public function testFlushQueueFailed()
    {
        $failedRecipients = array();
        /* @var \Swift_Transport|\PHPUnit_Framework_MockObject_MockObject $transport */
        $transport = $this->getMockBuilder('Swift_Transport')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        /* @var \Swift_Mime_Message $message */
        $message = $this->getMockBuilder('Swift_Mime_Message')->disableOriginalConstructor()->getMock();
        $email = new SpoolEmail($message);

        $this->assertSame(SpoolEmailStatus::STATUS_WAITING, $email->getStatus());
        $this->assertEquals(0, $this->createSpool(array($email))->flushQueue($transport, $failedRecipients));
        $this->assertCount(0, $failedRecipients);
        $this->assertSame(SpoolEmailStatus::STATUS_FAILED, $email->getStatus());
        $this->assertNull($email->getStatusMessage());
    }

    public function testFlushQueueFailedException()
    {
        $failedRecipients = array();
        /* @var \Swift_Transport|\PHPUnit_Framework_MockObject_MockObject $transport */
        $transport = $this->getMockBuilder('Swift_Transport')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $transport->expects($this->any())
            ->method('send')
            ->will($this->throwException(new \Swift_TransportException('Message exception')));

        /* @var \Swift_Mime_Message $message */
        $message = $this->getMockBuilder('Swift_Mime_Message')->disableOriginalConstructor()->getMock();
        $email = new SpoolEmail($message);

        $this->assertSame(SpoolEmailStatus::STATUS_WAITING, $email->getStatus());
        $this->assertEquals(0, $this->createSpool(array($email))->flushQueue($transport, $failedRecipients));
        $this->assertCount(0, $failedRecipients);
        $this->assertSame(SpoolEmailStatus::STATUS_FAILED, $email->getStatus());
        $this->assertSame('Message exception', $email->getStatusMessage());
    }

    public function testFlushQueueSuccess()
    {
        $failedRecipients = array();
        /* @var \Swift_Transport|\PHPUnit_Framework_MockObject_MockObject $transport */
        $transport = $this->getMockBuilder('Swift_Transport')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $transport->expects($this->any())
            ->method('send')
            ->will($this->returnValue(1));

        /* @var \Swift_Mime_Message $message */
        $message = $this->getMockBuilder('Swift_Mime_Message')->disableOriginalConstructor()->getMock();
        $email = new SpoolEmail($message);

        $this->assertSame(SpoolEmailStatus::STATUS_WAITING, $email->getStatus());
        $this->assertEquals(1, $this->createSpool(array($email))->flushQueue($transport, $failedRecipients));
        $this->assertCount(0, $failedRecipients);
        $this->assertSame(SpoolEmailStatus::STATUS_SUCCESS, $email->getStatus());
        $this->assertNull($email->getStatusMessage());
    }

    public function testFlushQueueTimeout()
    {
        $failedRecipients = array();
        /* @var \Swift_Transport|\PHPUnit_Framework_MockObject_MockObject $transport */
        $transport = $this->getMockBuilder('Swift_Transport')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $transport->expects($this->any())
            ->method('send')
            ->will($this->returnCallback(function () {
                sleep(1);

                return 1;
            }));

        /* @var \Swift_Mime_Message $message1 */
        $message1 = $this->getMockBuilder('Swift_Mime_Message')->disableOriginalConstructor()->getMock();
        $email1 = new SpoolEmail($message1);
        /* @var \Swift_Mime_Message $message2 */
        $message2 = $this->getMockBuilder('Swift_Mime_Message')->disableOriginalConstructor()->getMock();
        $email2 = new SpoolEmail($message2);

        $spool = $this->createSpool(array($email1, $email2));
        $spool->setTimeLimit(1);

        $this->assertSame(SpoolEmailStatus::STATUS_WAITING, $email1->getStatus());
        $this->assertSame(SpoolEmailStatus::STATUS_WAITING, $email2->getStatus());
        $this->assertEquals(1, $spool->flushQueue($transport, $failedRecipients));
        //$spool->flushQueue($transport, $failedRecipients);
        $this->assertCount(0, $failedRecipients);
        $this->assertSame(SpoolEmailStatus::STATUS_SUCCESS, $email1->getStatus());
        $this->assertNull($email1->getStatusMessage());
        $this->assertSame(SpoolEmailStatus::STATUS_FAILED, $email2->getStatus());
        $this->assertSame('The time limit of execution is exceeded', $email2->getStatusMessage());
    }

    public function testRecover()
    {
        $this->createSpool()->recover(900);
    }

    /**
     * @param array $emailsToSend
     *
     * @return DoctrineOrmSpool
     */
    protected function createSpool($emailsToSend = array())
    {
        $repo = $this->getMockBuilder('Sonatra\Bundle\SwiftmailerDoctrineBundle\Model\Repository\SpoolEmailRepositoryInterface')->getMock();
        $repo->expects($this->any())
            ->method('findEmailsToSend')
            ->will($this->returnValue($emailsToSend));

        $manager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')->getMock();
        $manager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repo));

        /* @var RegistryInterface|\PHPUnit_Framework_MockObject_MockObject $registry */
        $registry = $this->getMockBuilder('Symfony\Bridge\Doctrine\RegistryInterface')->getMock();
        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($manager))
        ;

        return new DoctrineOrmSpool($registry, 'Sonatra\Bundle\SwiftmailerDoctrineBundle\Entity\SpoolEmail');
    }
}
