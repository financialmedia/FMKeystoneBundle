<?php

namespace FM\KeystoneBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserGrantCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cdn:user:grant')
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

        $manipulator = $this->getContainer()->get('fm_cdn.security.user_manipulator');
        $manipulator->addRole($username, $role);

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
