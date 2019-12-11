<?php

namespace AdminBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

// Sources : 
// https://symfony.com/doc/3.4/security/entity_provider.html
// https://symfony.com/doc/3.4/security/password_encoding.html

/**
 * Utilisateur
 *
 * @ORM\Table(name="utilisateur")
 * @ORM\Entity(repositoryClass="AdminBundle\Repository\UtilisateurRepository")
 */
class Utilisateur implements UserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255, unique=true)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var array
     *
     * @ORM\Column(name="roles", type="array")
     */
    private $roles;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string")
     */
    private $salt;

    public function eraseCredentials()
    {

    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return Utilisateur
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /*
     * Set password
     *
     * @param string $password
     *
     * @return Utilisateur
     *
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }
*/
    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /*
     * Set salt
     *
     * @param string $salt
     *
     * @return Utilisateur
     *
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }
    Aparemment bcrypt permet de ne pas gérer le zel salt
    https://symfony.com/doc/3.4/security/entity_provider.html
*/
    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        // on retourne null car bcrypt n'a pas besoin de lire ce zel (voir source ci-dessus)
        //return null;
        return $this->salt;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        //return ['ROLE_ADMIN'];
        return $this->roles;
    }

    /**
     * Set roles
     *
     * @param array $roles
     * @return Utilisateur
     */
    public function setRoles($roles)
    {
      $this->roles = $roles;
      return $this;
    }

}

?>