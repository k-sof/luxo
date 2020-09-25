<?php

namespace Luxo\Command\Doctrine;

use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateSchemaCommand extends CreateCommand
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
      ->setName('doctrine:schema:create')
      ->setDescription('Executes (or dumps) the SQL needed to generate the database schema')
    ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helperSet = $this->getApplication()->getHelperSet();
        $helperSet->set(new ConnectionHelper($this->entityManager->getConnection()), 'db');
        $helperSet->set(new EntityManagerHelper($this->entityManager), 'em');

        return parent::execute($input, $output);
    }
}
