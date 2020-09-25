<?php

namespace Luxo\Command\Doctrine;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $cacheCommand = $this->getApplication()->find('doctrine:cache:clear-metadata');

        $cacheCommandReturnCode = $cacheCommand->run(new ArrayInput([
            'command' => $cacheCommand->getName(),
        ]), $output);

        $dropCommand = $this->getApplication()->find('doctrine:database:drop');

        $dropCommandReturnCode = $dropCommand->run(new ArrayInput([
      'command' => $dropCommand->getName(),
      '--if-exists' => true,
      '--force' => true,
    ]), $output);

        $createCommand = $this->getApplication()->find('doctrine:database:create');

        $createCommandReturnCode = $createCommand->run(new ArrayInput([
      'command' => $createCommand->getName(),
      '--if-not-exists' => true,
    ]), $output);

        $schemaCreateCommand = $this->getApplication()->find('doctrine:schema:create');

        $schemaCreateCommandReturnCode = $schemaCreateCommand->run(new ArrayInput([
      'command' => $schemaCreateCommand->getName(),
    ]), $output);

        $fixtureLoadCommand = $this->getApplication()->find('doctrine:Fixture:load');

        $fixtureLoadCommandReturnCode = $fixtureLoadCommand->run(new ArrayInput([
      'command' => $fixtureLoadCommand->getName(),
    ]), $output);

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('doctrine:database:reset')
        ;
    }
}
