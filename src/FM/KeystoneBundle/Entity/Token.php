<?php

namespace FM\KeystoneBundle\Entity;

use FM\KeystoneBundle\Model\Token as AbstractToken;

/**
 * Create a Token class in your own bundle that extends this one.
 *
 * <code>
 *     <?php
 *     // src/Acme/YourBundle/Entity/Token.php
 *
 *     namespace Acme\YourBundle\Entity;
 *
 *     use FM\KeystoneBundle\Entity\Token as BaseToken;
 *     use Doctrine\ORM\Mapping as ORM;
 *
 *     /**
 *      * @ORM\Entity
 *      * @ORM\Table(name="keystone_token")
 *      * /
 *     class Token extends BaseToken
 *     {
 *         /**
 *          * @ORM\Id
 *          * @ORM\Column(type="string")
 *          * @ORM\GeneratedValue(strategy="UUID")
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
 * @see ../Resources/config/doctrine/Token.orm.xml for mapping information
 */
abstract class Token extends AbstractToken
{
}