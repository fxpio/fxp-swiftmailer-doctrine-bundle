<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SwiftmailerDoctrineBundle\Spool;

use Doctrine\Common\Persistence\ObjectManager;
use Sonatra\Bundle\SwiftmailerDoctrineBundle\Exception\InvalidArgumentException;
use Sonatra\Bundle\SwiftmailerDoctrineBundle\Model\Repository\SpoolEmailRepositoryInterface;
use Sonatra\Bundle\SwiftmailerDoctrineBundle\Model\SpoolEmailInterface;
use Sonatra\Bundle\SwiftmailerDoctrineBundle\SpoolEmailStatus;
use Swift_Mime_Message;
use Swift_Transport;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Doctrine ORM Spool for Swiftmailer.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DoctrineOrmSpool extends \Swift_ConfigurableSpool
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var SpoolEmailRepositoryInterface
     */
    protected $repo;

    /**
     * @var string
     */
    protected $class;

    /**
     * Constructor.
     *
     * @param RegistryInterface $registry The doctrine registry
     * @param string            $class    The class name of spool email entity
     *
     * @throws InvalidArgumentException When the class has not the interface Sonatra\Bundle\SwiftmailerDoctrineBundle\Model\SpoolEmailInterface
     * @throws InvalidArgumentException When the repository is not an instance of Sonatra\Bundle\SwiftmailerDoctrineBundle\Model\Repository\SpoolEmailRepositoryInterface
     */
    public function __construct(RegistryInterface $registry, $class)
    {
        $validClass = 'Sonatra\Bundle\SwiftmailerDoctrineBundle\Model\SpoolEmailInterface';
        $ref = new \ReflectionClass($class);

        if (!in_array($validClass, $ref->getInterfaceNames())) {
            $msg = sprintf('The "%s" class does not extend "%s"', $class, $validClass);
            throw new InvalidArgumentException($msg);
        }

        $this->om = $registry->getManagerForClass($class);
        $this->repo = $this->om->getRepository($class);
        $this->class = $class;

        if (!$this->repo instanceof SpoolEmailRepositoryInterface) {
            $msg = sprintf('The repository of "%s" must be an instance of "%s"', $class, 'Sonatra\Bundle\SwiftmailerDoctrineBundle\Model\Repository\SpoolEmailRepositoryInterface');
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        // skip action.
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        // skip action.
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function queueMessage(\Swift_Mime_Message $message)
    {
        $entity = new $this->class($message);
        $this->om->persist($entity);
        $this->om->flush();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function flushQueue(\Swift_Transport $transport, &$failedRecipients = null)
    {
        if (!$transport->isStarted()) {
            $transport->start();
        }

        $emails = $this->repo->findEmailsToSend($this->getMessageLimit());

        return count($emails) > 0
            ? $this->sendEmails($transport, $failedRecipients, $emails)
            : 0;
    }

    /**
     * Execute a recovery if for any reason a process is sending for too long.
     *
     * @param int $timeout In second, Defaults is for very slow smtp responses
     */
    public function recover($timeout = 900)
    {
        $this->repo->recover($timeout);
    }

    /**
     * Send the emails message.
     *
     * @param \Swift_Transport      $transport        The swift transport
     * @param string[]|null         $failedRecipients The failed recipients
     * @param SpoolEmailInterface[] $emails           The spool emails
     *
     * @return int The count of sent emails
     */
    protected function sendEmails(\Swift_Transport $transport, &$failedRecipients, array $emails)
    {
        $count = 0;
        $time = time();
        $emails = $this->prepareEmails($emails);

        foreach ($emails as $email) {
            try {
                if ($transport->send($email->getMessage(), $failedRecipients)) {
                    $email->setStatus(SpoolEmailStatus::STATUS_SUCCESS);
                    $count++;
                } else {
                    $email->setStatus(SpoolEmailStatus::STATUS_FAILED);
                }
            } catch (\Swift_TransportException $e) {
                $email->setStatus(SpoolEmailStatus::STATUS_FAILED);
                $email->setStatusMessage($e->getMessage());
            }

            $email->setUpdatedAt(new \DateTime());
            $this->om->persist($email);
            $this->om->flush();

            if (SpoolEmailStatus::STATUS_SUCCESS === $email->getStatus()) {
                $this->om->remove($email);
                $this->om->flush();
            }

            if ($this->getTimeLimit() && (time() - $time) >= $this->getTimeLimit()) {
                break;
            }
        }

        return $count;
    }

    /**
     * Prepare the spool emails.
     *
     * @param SpoolEmailInterface[] $emails The spool emails
     *
     * @return SpoolEmailInterface[]
     */
    protected function prepareEmails(array $emails)
    {
        foreach ($emails as $email) {
            $email->setStatus(SpoolEmailStatus::STATUS_SENDING);
            $email->setUpdatedAt(new \DateTime());
            $this->om->persist($email);
        }

        $this->om->flush();
        reset($emails);

        return $emails;
    }
}
