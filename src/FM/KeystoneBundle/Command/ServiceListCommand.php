<?php

namespace FM\KeystoneBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServiceListCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('keystone:service:list')
            ->setDescription('Lists available services.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command lists all available services.

  <info>php app/console %command.name%</info>

EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $divider = str_pad('', 50, '=');

        $services = $this->getServiceManager()->findAll();
        foreach ($services as $service) {
            $output->writeln(
                sprintf(
                    'Service name: <info>%s</info>, type: <info>%s</info>',
                    $service->getName(),
                    $service->getType()
                )
            );
            $output->writeln($divider);

            foreach ($service->getEndpoints() as $key => $endpoint) {
                $output->writeln(sprintf('Endpoint <comment>%d</comment>:', $key));
                $output->writeln(sprintf('Public url: <comment>%s</comment>', $endpoint->getPublicUrl()));
                $output->writeln(sprintf('Admin url:  <comment>%s</comment>', $endpoint->getAdminUrl()));
                $output->writeln('');
            }
            $output->writeln('');
        }
    }
}
