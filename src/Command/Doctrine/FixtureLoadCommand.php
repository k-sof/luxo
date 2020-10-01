<?php

namespace Luxo\Command\Doctrine;

use Doctrine\ORM\EntityManager;
use Luxo\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class FixtureLoadCommand extends Command
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EncoderFactory
     */
    private $encoderFactory;

    public function __construct(EntityManager $entityManager, EncoderFactory $encoderFactory)
    {
        $this->entityManager = $entityManager;
        $this->encoderFactory = $encoderFactory;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('doctrine:fixture:load');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loader = new \Nelmio\Alice\Loader\NativeLoader();
        $objectSet = $loader->loadData([
            \Luxo\Entity\Image::class => [
                'image_{1..2}' => [
                    'name' => '<username()>',
                    'path' => '<imageUrl(640, 480)>',
                ],
            ],
            \Luxo\Entity\User::class => [
                'user_{1..5}' => [
                    'firstName' => '<username()>',
                    'lastName' => '<username()>',
                    'birth' => "<dateTimeBetween('-30 years', '-18 years')>",
                    'email' => '<email()>',
                    'password' => 'test',
                ],
            ],
            \Luxo\Entity\Announcement::class => [
                'announcement_{1..9}' => [
                    'title' => '<username()>',
                    'description' => '<text()>',
                    'city' => '<city()>',
                    'zipCode' => '<numberBetween(00000, 99999)>',
                    'type' => '<numberBetween(0, 1)>',
                    'category' => '<numberBetween(0, 3)>',
                    'price' => '<numberBetween(300, 250000)>',
                    'area' => '<numberBetween(10, 350)>',
                    'room' => '<numberBetween(1, 5)>',
                    'images' => ['@image_1', '@image_2'],
                    'energy' => '<numberBetween(0, 1)>',
                    'floor' => '<numberBetween(1, 3)>',
                    'sold' => 'false',
                    'bedroom' => '<numberBetween(1, 3)>',
                    'postedBy' => '@user_<numberBetween(1, 5)>',
                ],
            ],
        ]);

        foreach ($objectSet->getObjects() as $item) {
            $metadata = $this->entityManager->getClassMetadata(get_class($item));

            if (false === $metadata->isMappedSuperclass && false === (isset($metadata->isEmbeddedClass) && $metadata->isEmbeddedClass)) {
                if ($item instanceof User) {
                    $item->setPassword($this->encoderFactory->getEncoder($item)->encodePassword($item->getPassword(), null));
                }

                $this->entityManager->persist($item);
            }
        }

        $this->entityManager->flush();

        return 0;
    }
}
