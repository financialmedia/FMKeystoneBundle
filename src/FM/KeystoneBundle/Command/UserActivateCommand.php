<?php

namespace FM\KeystoneBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserActivateCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('keystone:user:activate')
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

        $user = $this->loadUserByUsername($username);
        $user->setEnabled(true);
        $this->getUserProvider()->updateUser($user);

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
