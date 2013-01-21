<?php

namespace FM\KeystoneBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserRevokeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('keystone:user:revoke')
            ->setDefinition(array(
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
                new InputArgument('role', InputArgument::REQUIRED, 'The role'),
            ));
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $role = $input->getArgument('role');

        $manipulator = $this->getContainer()->get('fm_cdn.security.user_manipulator');
        $manipulator->removeRole($username, $role);

        $output->writeln(sprintf('User <info>%s</info> has been revoked role <info></info>', $username, $role));
    }

    /**
     * @see Command
     */
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
