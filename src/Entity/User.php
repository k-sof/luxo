<?php

namespace Luxo\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="Luxo\Repository\UserRepository")
 * @ORM\Table(name="users")
 */
class User implements UserInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    private $id;

    /**
     * @var Announcement[]
     * @ORM\OneToMany(targetEntity="Announcement", mappedBy="postedBy")
     */
    private $announcements;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $firstName;

    /**
     * @var
     * @ORM\Column(type="string")
     */
    private $lastName;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @var \DateTimeInterface
     * @ORM\Column(type="date")
     */
    private $birth;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $password;

    public function __construct()
    {
        $this->announcements = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return self
     */
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName(string $lastName): User
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getBirth()
    {
        return $this->birth;
    }

    /**
     * @param \DateTimeInterface $birth
     *
     * @return User
     */
    public function setBirth(\DateTimeInterface $birth): User
    {
        $this->birth = $birth;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return User
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return Announcement[]|ArrayCollection|PersistentCollection
     */
    public function getAnnouncements()
    {
        return $this->announcements;
    }

    /**
     * @param Announcement $announcement
     *
     * @return User
     */
    public function addAnnouncement(Announcement $announcement)
    {
        if (!$this->announcements->contains($announcement)) {
            $this->announcements->add($announcement);
        }

        return $this;
    }

    /**
     * @param Announcement[] $announcements
     *
     * @return User
     */
    public function setAnnouncements(array $announcements)
    {
        foreach ($announcements as $announcement) {
            $this->addAnnouncement($announcement);
        }

        return $this;
    }

    public function getRoles()
    {
        return ['USER', 'ADMIN'];
    }

    public function getSalt()
    {
        return serialize(array(
            $this->id,
        ));
    }

    public function getUsername()
    {
        return $this->getEmail();
    }

    public function eraseCredentials()
    {
        return null;
    }
}
