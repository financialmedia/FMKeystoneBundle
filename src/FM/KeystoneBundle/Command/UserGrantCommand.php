<?php

namespace FM\KeystoneBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserGrantCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('keystone:user:grant')
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

        if (!in_array($role, $this->getValidRoles())) {
            throw new \InvalidArgumentException(sprintf('<error>Role "%s" is not a valid role, must be one of "%s"</error>', $role, implode(', ', $this->getValidRoles())));
        }

        $userProviderServiceId = $this->getContainer()->getParameter('fm_keystone.security.user_provider.id');

        $user = $this->loadUserByUsername($username);
        $user->addRole($role);
        $this->getUserProvider()->updateUser($user);

        $output->writeln(sprintf('User <info>%s</info> has been granted role <info>%s</info>', $username, $role));
    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('username')) {
            $username = $this->getHelper('dialog')->askAndValidate(
                $output,
                '<question>Please choose a username:</question> ',
                function($username) {
                    if (empty($username)) {
                        throw new \Exception('Username can not be empty');
                    }

                    return $username;
                }
            );
            $input->setArgument('username', $username);
        }

        if (!$input->getArgument('role')) {
            $roles = $this->getValidRoles();
            $role = $this->getHelper('dialog')->select(
                $output,
                '<question>Role:</question> ',
                $roles
            );
            $input->setArgument('role', $roles[$role]);
        }
    }

    public function getValidRoles()
    {
        $roles = array();

        foreach ($this->getContainer()->getParameter('security.role_hierarchy.roles') as $role => $subRoles) {
            if (!in_array($role, $roles)) {
                $roles[] = $role;
            }
            if (!empty($subRoles)) {
                foreach ($subRoles as $subRole) {
                    if (!in_array($subRole, $roles)) {
                        $roles[] = $subRole;
                    }
                }
            }
        }

        return $roles;
    }
}
