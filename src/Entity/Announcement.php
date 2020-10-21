<?php

namespace Luxo\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Luxo\Repository\AnnouncementRepository")
 * @ORM\Table(name="announcements")
 */
class Announcement
{
    const TYPES = [ 'studio', 'house', 'appartment', 'villa'];
    const CATEGORIES = ['buying', 'Rental'];
    const ENERGIES = ['gas', 'electric'];

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    private $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="announcements")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $postedBy;

    /**
     * @var Image[]
     * @ORM\ManyToMany(targetEntity="Image", cascade={"persist","remove"})
     * @ORM\JoinTable(
     *     name="announcement_has_images",
     *     joinColumns={@ORM\JoinColumn(name="announcement_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="image_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private $images;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $city;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $zipCode;

    /**
     * @var string
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $category;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $area;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $room;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $bedroom;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $energy;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $floor;

    /**
     * @var string
     * @ORM\Column(type="boolean")
     */
    private $sold;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->images = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Announcement
     */
    public function setTitle($title): Announcement
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Announcement
     */
    public function setDescription(string $description): Announcement
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     *
     * @return Announcement
     */
    public function setCity(string $city): Announcement
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return int
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * @param int $zipCode
     *
     * @return Announcement
     */
    public function setZipCode(int $zipCode): Announcement
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $date
     *
     * @return Announcement
     */
    public function setDate(string $date): Announcement
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Announcement
     */
    public function setType(string $type): Announcement
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeName()
    {
        return self::TYPES[$this->type];
    }

    /**
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param string $price
     *
     * @return Announcement
     */
    public function setPrice(string $price): Announcement
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return string
     */
    public function getSold()
    {
        return $this->sold;
    }

    /**
     * @param string $sold
     *
     * @return Announcement
     */
    public function setSold(string $sold): Announcement
    {
        $this->sold = $sold;

        return $this;
    }

    /**
     * @return int
     */
    public function getFloor()
    {
        return $this->floor;
    }

    /**
     * @param int $floor
     *
     * @return Announcement
     */
    public function setFloor(int $floor): Announcement
    {
        $this->floor = $floor;

        return $this;
    }

    /**
     * @return int
     */
    public function getEnergy()
    {
        return $this->energy;
    }

    public function getEnergyString()
    {
        return self::ENERGIES[$this->energy];
    }

    /**
     * @param int $energy
     *
     * @return Announcement
     */
    public function setEnergy(int $energy): Announcement
    {
        $this->energy = $energy;

        return $this;
    }

    /**
     * @return int
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * @param int $room
     *
     * @return Announcement
     */
    public function setRoom(int $room): Announcement
    {
        $this->room = $room;

        return $this;
    }

    /**
     * @return int
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param int $area
     *
     * @return Announcement
     */
    public function setArea(int $area): Announcement
    {
        $this->area = $area;

        return $this;
    }

    /**
     * @return int
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param int $category
     *
     * @return Announcement
     */
    public function setCategory(int $category): Announcement
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return User
     */
    public function getPostedBy(): User
    {
        return $this->postedBy;
    }

    /**
     * @param  $user
     *
     * @return Announcement
     */
    public function setPostedBy(User $user): Announcement
    {
        $this->postedBy = $user->addAnnouncement($this);

        return $this;
    }

    public function getImages()
    {
        return $this->images;
    }

    public function addImage(Image $image)
    {
        $this->images->add($image);

        return $this;
    }

    public function setImages(array $images)
    {
        foreach ($images as $image) {
            $this->addImage($image);
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getBedroom(): ?int
    {
        return $this->bedroom;
    }

    /**
     * @param int $bedroom
     *
     * @return Announcement
     */
    public function setBedroom(int $bedroom): Announcement
    {
        $this->bedroom = $bedroom;

        return $this;
    }

}
