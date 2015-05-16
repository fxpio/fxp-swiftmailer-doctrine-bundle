<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SwiftmailerDoctrineBundle\Model;

use Sonatra\Bundle\SwiftmailerDoctrineBundle\SpoolEmailStatus;

/**
 * Spool email model.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class SpoolEmail implements SpoolEmailInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var string|null
     */
    protected $statusMessage;

    /**
     * Constructor.
     *
     * @param \Swift_Mime_Message $message The swift message
     */
    public function __construct(\Swift_Mime_Message $message)
    {
        $this->setMessage($message);
        $this->createdAt = new \DateTime();
        $this->updatedAt = clone $this->createdAt;
        $this->status = SpoolEmailStatus::STATUS_WAITING;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setMessage(\Swift_Mime_Message $message)
    {
        $this->message = base64_encode(serialize($message));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return unserialize(base64_decode($this->message));
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        $this->status = $status;
        $this->setStatusMessage(null);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function setStatusMessage($message)
    {
        $this->statusMessage = $message;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }
}
