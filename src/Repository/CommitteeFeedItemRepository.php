<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CommitteeFeedItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CommitteeFeedItemRepository extends ServiceEntityRepository
{
    use AuthorTrait;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CommitteeFeedItem::class);
    }

    public function findPaginatedMostRecentFeedItems(
        string $committeeUuid,
        int $limit,
        int $firstResultIndex = 0
    ): Paginator {
        $qb = $this
            ->createCommitteeTimelineQueryBuilder($committeeUuid)
            ->setFirstResult($firstResultIndex)
            ->setMaxResults($limit)
        ;

        return new Paginator($qb);
    }

    public function findMostRecentFeedMessage(string $committeeUuid = null, ?bool $published = null): ?CommitteeFeedItem
    {
        return $this->findMostRecentFeedItem(CommitteeFeedItem::MESSAGE, $committeeUuid, $published);
    }

    public function findMostRecentFeedEvent(string $committeeUuid = null, ?bool $published = null): ?CommitteeFeedItem
    {
        return $this->findMostRecentFeedItem(CommitteeFeedItem::EVENT, $committeeUuid, $published);
    }

    private function findMostRecentFeedItem(
        string $type,
        string $committeeUuid = null,
        ?bool $published = null
    ): ?CommitteeFeedItem {
        $qb = $this
            ->createQueryBuilder('i')
            ->select('i, a')
            ->leftJoin('i.author', 'a')
            ->where('i.itemType = :type')
            ->setMaxResults(1)
            ->setParameter('type', $type)
            ->orderBy('i.createdAt', 'DESC')
            ->addGroupBy('i.id, a.id')
        ;

        if (null !== $published) {
            $qb->andWhere('i.published = :published')->setParameter('published', $published);
        }

        if (CommitteeFeedItem::EVENT === $type) {
            $qb
                ->addSelect('e')
                ->leftJoin('i.event', 'e')
                ->having('e.published = true')
                ->addGroupBy('e.id')
            ;
        }

        if ($committeeUuid) {
            $qb
                ->leftJoin('i.committee', 'c')
                ->andWhere('c.uuid = :committee')
                ->setParameter('committee', $committeeUuid)
            ;
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    private function createCommitteeTimelineQueryBuilder(string $committeeUuid): QueryBuilder
    {
        $qb = $this->createQueryBuilder('i');

        $qb
            ->select('i, a, e')
            ->leftJoin('i.author', 'a')
            ->leftJoin('i.event', 'e', Join::WITH, 'e.id = :e_null OR e.published = true')
            ->leftJoin('i.committee', 'c')
            ->where('c.uuid = :committee')
            ->andWhere('i.published = true')
            ->orderBy('i.id', 'DESC')
            ->setParameter('committee', $committeeUuid)
            ->setParameter('e_null', null)
        ;

        return $qb;
    }
}
