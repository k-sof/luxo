<?php

namespace Luxo\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Luxo\Entity\Announcement;
use Luxo\Entity\User;

class AnnouncementRepository extends EntityRepository
{
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, $em->getClassMetadata(Announcement::class));
    }

    /**
     * @return Announcement
     */
    public function findLocationAll()
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $query = $queryBuilder
            ->where('a.type = 0')
            ->addOrderBy('a.date', 'DESC')
            ->getQuery();

        return $query->getResult();
    }

    public function findAchatAll()
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $query = $queryBuilder

            ->where('a.type = 1')
            ->addOrderBy('a.date', 'DESC')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @return Announcement
     */
    public function findByLocationWithImages()
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $query = $queryBuilder
            ->andWhere('a.type = 0')
            ->addOrderBy('a.date', 'DESC')
            ->setMaxResults(3)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @return Announcement
     */
    public function findByAchatWithImages()
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $query = $queryBuilder

            ->andWhere('a.type = 1')
            ->addOrderBy('a.date', 'DESC')
            ->setMaxResults(3)
            ->getQuery();

        return $query->getResult();
    }

    public function findListAnnouncement()
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $query = $queryBuilder
            ->innerJoin()
            ->where($queryBuilder->expr()->eq('a.user_id', 'u.id'))
            ->getQuery();

        return $query->getResult();
    }

    public function newAnnouncement($data)
    {
        $announcement = new Announcement();

        $uow = $this->_em->getUnitOfWork();
        $uow->computeChangeSets();
        $changeSet = $uow->getEntityChangeSet($announcement);
        if ($changeSet) {
            $uow->recomputeSingleEntityChangeSet(
                $this->_em->getClassMetadata(Announcement::class),
                $announcement
            );
        }
        $this->_em->persist($announcement);
        $this->_em->flush();
    }

    public function persist(Announcement $announcement)
    {
        $this->_em->persist($announcement);
        $this->_em->flush($announcement);
    }

    /**
     * @param $user User|int
     *
     * @return mixed
     */
    public function findByUser($user)
    {
        if ($user instanceof User) {
            $user = $user->getId();
        }

        if (!is_int($user)) {
            throw new \InvalidArgumentException('');
        }

        $qb = $this->createQueryBuilder('a');

        return $qb
            ->addSelect('u')
            ->where($qb->expr()->eq('a.postedBy', $user))
            ->leftJoin('a.postedBy', 'u')
            ->getQuery()
            ->getResult()
        ;
    }

    public function edit($announcement)
    {
        $uow = $this->_em->getUnitOfWork();
        $uow->computeChangeSets();
        $changeSet = $uow->getEntityChangeSet($announcement);
        $this->_em->persist($announcement);
        $this->_em->flush();

        return $announcement;
    }

    public function delete($id)
    {
        $announcement = $this->_em->getReference(Announcement::class, $id);
        $this->_em->remove($announcement);
        $this->_em->flush();
    }
}
