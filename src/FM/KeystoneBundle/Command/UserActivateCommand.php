<?php

namespace FM\KeystoneBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserActivateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cdn:user:activate')
            ->setDescription('Activate a user')
            ->setDefinition(array(
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
            ))
            ->setHelp(<<<EOT
The <info>cdn:user:activate</info> command activates a user (so they will be able to log in):

  <info>php app/console cdn:user:activate peter</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');

        $manipulator = $this->getContainer()->get('fm_cdn.security.user_manipulator');
        $manipulator->activate($username);

        $output->writeln(sprintf('User "%s" has been activated.', $username));
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('username')) {
            $username = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose a username:',
                function($username) {
                    if (empty($username)) {
                        throw new \Exception('Username can not be empty');
                    }

                    return $username;
                }
            );
            $input->setArgument('username', $username);
        }
    }
}
