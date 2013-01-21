<?php

namespace FM\KeystoneBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserChangePasswordCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('keystone:user:change-password')
            ->setDescription('Change the password of a user.')
            ->setDefinition(array(
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
                new InputArgument('password', InputArgument::REQUIRED, 'The password'),
            ))
            ->setHelp(<<<EOT
The <info>cdn:user:change-password</info> command changes the password of a user:

  <info>php app/console cdn:user:change-password peter</info>

This interactive shell will first ask you for a password.

You can alternatively specify the password as a second argument:

  <info>php app/console cdn:user:change-password peter mypassword</info>

EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        $manipulator = $this->getContainer()->get('fm_cdn.security.user_manipulator');
        $manipulator->changePassword($username, $password);

        $output->writeln(sprintf('Changed password for user <comment>%s</comment>', $username));
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('username')) {
            $username = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please give the username:',
                function($username) {
                    if (empty($username)) {
                        throw new \Exception('Username can not be empty');
                    }

                    return $username;
                }
            );
            $input->setArgument('username', $username);
        }

        if (!$input->getArgument('password')) {
            $password = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please enter the new password:',
                function($password) {
                    if (empty($password)) {
                        throw new \Exception('Password can not be empty');
                    }

                    return $password;
                }
            );
            $input->setArgument('password', $password);
        }
    }
}
