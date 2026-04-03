<?php

namespace App\Repository;

use App\Entity\QueueItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QueueItem>
 */
class QueueItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QueueItem::class);
    }

    /**
     * Find pending items ordered by creation date.
     *
     * @return QueueItem[]
     */
    public function findPendingItems(int $limit = 10): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.status = :status')
            ->setParameter('status', QueueItem::STATUS_PENDING)
            ->orderBy('q.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count pending items in queue.
     */
    public function countPending(): int
    {
        return (int) $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->where('q.status = :status')
            ->setParameter('status', QueueItem::STATUS_PENDING)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
