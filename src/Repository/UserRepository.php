<?php

namespace Luxo\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Luxo\Entity\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class UserRepository extends EntityRepository
{
    /**
     * @var EncoderFactory
     */
    private $encoderFactory;
    private $em;


    public function __construct(EntityManager $em, EncoderFactory $encoderFactory)
    {
        parent::__construct($em, $em->getClassMetadata(User::class));
        $this->encoderFactory = $encoderFactory;
        $this->em = $em;
    }

    /**
     * @param $email
     *
     * @return User
     */
    public function findByEmail($email)
    {

        return $this->findBy(['email' => $email]);
    }

    /**
     * @see https://stackoverflow.com/questions/21578539/symfony2-doctrine-preupdate-event-to-encode-password
     * @see https://stackoverflow.com/questions/9057558/is-there-a-built-in-way-to-get-all-of-the-changed-updated-fields-in-a-doctrine-2
     *
     * @param $user
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateUser(User $user)
    {
        $uow = $this->_em->getUnitOfWork();
        $uow->computeChangeSets();
        $changeSet = $uow->getEntityChangeSet($user);
        if (isset($changeSet['password']) && strlen($changeSet['password'][1] > 0)) {
            $user->setPassword($this->encoderFactory->getEncoder($user)->encodePassword($changeSet['password'][1], null));
            $uow->recomputeSingleEntityChangeSet(
                $this->_em->getClassMetadata(User::class),
                $user
            );
        };
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * @return ClassMetadata
     */
    public function getClassMetadata()
    {
        return $this->_class;
    }

    public function findListAnnouncement(int $id)
    {
        return $this->find($id);
    }

    public function refresh(User $user)
    {
        $this->em->refresh($user);
    }


}

