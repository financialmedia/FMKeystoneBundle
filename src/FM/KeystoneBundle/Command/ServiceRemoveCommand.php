<?php

namespace FM\KeystoneBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServiceRemoveCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('keystone:service:remove')
            ->setDescription('Removes a service.')
            ->addArgument('id', InputArgument::REQUIRED, 'The service id')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command removes a service.

  <info>php app/console %command.name%</info>

This interactive shell will ask you for a service id.

You can alternatively specify the id as the first argument:

  <info>php app/console %command.name% 123</info>

EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getServiceManager();

        $id = $input->getArgument('id');
        if (null === $service = $manager->findServiceById($id)) {
            $output->writeln(sprintf('Service with id <comment>%s</comment> does not exist', $id));

            return 0;
        }

        $manager->removeService($service);

        $output->writeln(sprintf('Service <info>%s</info> removed', $id));

        return 0;
    }
}
