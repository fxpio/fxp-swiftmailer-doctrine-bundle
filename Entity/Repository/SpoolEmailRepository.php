<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SwiftmailerDoctrineBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Sonatra\Bundle\SwiftmailerDoctrineBundle\Model\Repository\SpoolEmailRepositoryInterface;
use Sonatra\Bundle\SwiftmailerDoctrineBundle\SpoolEmailStatus;

/**
 * Spool email entity repository.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SpoolEmailRepository extends EntityRepository implements SpoolEmailRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findEmailsToSend($limit = null)
    {
        $limit = $limit > 0 ? $limit : null;

        return $this->findBy(
            array('status' => SpoolEmailStatus::STATUS_WAITING),
            array('createdAt' => 'ASC'),
            $limit
        );
    }

    /**
     * {@inheritdoc}
     */
    public function recover($timeout = 900)
    {
        $timeoutDate = new \DateTime();
        $timeoutDate->modify(sprintf('-%s seconds', $timeout));

        $str = sprintf('UPDATE %s se SET se.status = :waitStatus, se.statusMessage = null WHERE se.status = :failedStatus AND se.createdAt <= :timeoutDate', $this->getClassName());
        $query = $this->getEntityManager()->createQuery($str)
            ->setParameter('waitStatus', SpoolEmailStatus::STATUS_WAITING)
            ->setParameter('failedStatus', SpoolEmailStatus::STATUS_FAILED)
            ->setParameter('timeoutDate', $timeoutDate);

        $query->execute();
    }
}
