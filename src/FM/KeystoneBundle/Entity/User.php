<?php

namespace FM\KeystoneBundle\Entity;

use FM\KeystoneBundle\Model\User as AbstractUser;

/**
 * Create a User class in your own bundle that extends this one.
 *
 * <code>
 *     <?php
 *     // src/Acme/YourBundle/Entity/User.php
 *
 *     namespace Acme\YourBundle\Entity;
 *
 *     use FM\KeystoneBundle\Entity\User as BaseUser;
 *     use Doctrine\ORM\Mapping as ORM;
 *
 *     /**
 *      * (@)ORM\Entity
 *      * (@)ORM\Table(name="keystone_user")
 *      * /
 *     class User extends BaseUser
 *     {
 *         /**
 *          * (@)ORM\Id
 *          * (@)ORM\Column(type="integer")
 *          * (@)ORM\GeneratedValue(strategy="AUTO")
 *          * /
 *         protected $id;
 *
 *         public function __construct()
 *         {
 *             parent::__construct();
 *             // your own logic
 *         }
 *     }
 * </code>
 *
 * @see ../Resources/config/doctrine/User.orm.xml for mapping information
 */
abstract class User extends AbstractUser
{
}
