<?php

namespace FM\KeystoneBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServiceCreateCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('keystone:service:create')
            ->addArgument('type', InputArgument::REQUIRED, 'The service type')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the service')
            ->addArgument('public-url', InputArgument::REQUIRED, 'Public url of the default endpoint')
            ->addArgument('admin-url', InputArgument::REQUIRED, 'Admin url of the default endpoint')
            ->setDescription('Create a service with a default endpoint.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command creates a service which uses keystone
tokens for authentication:

  <info>php app/console %command.name%</info>

This interactive shell will ask you for a service type and name. Additionally,
you need to provide a public and admin url where the service is available.

You can alternatively specify the type and name as the first and second arguments:

  <info>php app/console %command.name% object-store acme-cdn</info>

EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');
        $name = $input->getArgument('name');
        $publicUrl = $input->getArgument('public-url');
        $adminUrl = $input->getArgument('admin-url');

        $manager = $this->getServiceManager();
        $service = $manager->createService($type, $name);
        $manager->addEndpoint($service, $publicUrl, $adminUrl);

        $output->writeln(sprintf('Service <info>%s</info> created', $name));
    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('type')) {
            $types = $this->getContainer()->getParameter('fm_keystone.service_types');
            $key = $this->getHelper('dialog')->select(
                $output,
                '<question>Service type:</question> ',
                $types,
                0
            );
            $input->setArgument('type', $types[$key]);
        }

        if (!$input->getArgument('name')) {
            $name = $this->getHelper('dialog')->ask($output, '<question>Service name:</question> ');
            $input->setArgument('name', $name);
        }

        if (!$input->getArgument('public-url')) {
            $publicUrl = $this->getHelper('dialog')->askAndValidate(
                $output,
                '<question>Public url:</question> ',
                function($url) {
                    if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
                        throw new \Exception(
                            sprintf('Invalid url: "%s"', $url)
                        );
                    }

                    return $url;
                }
            );
            $input->setArgument('public-url', $publicUrl);
        }

        if (!$input->getArgument('admin-url')) {
            $publicUrl = $input->getArgument('public-url');
            $adminUrl = $this->getHelper('dialog')->askAndValidate(
                $output,
                '<question>Admin url (defaults to public url):</question> ',
                function($url) use ($publicUrl) {
                    if (empty($url)) {
                        $url = $publicUrl;
                    }
                    if (!filter_var($url, FILTER_VALIDATE_URL)) {
                        throw new \Exception(
                            sprintf('Invalid url: "%s"', $url)
                        );
                    }

                    return $url;
                }
            );
            $input->setArgument('admin-url', $adminUrl);
        }
    }
}
