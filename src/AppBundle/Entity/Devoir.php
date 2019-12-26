<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * c'est la classe Devoir
 *
 * @ORM\Table(name="devoir")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DevoirRepository")
 */
class Devoir
{
    /** 
     * @var int
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="classe", type="integer")
     */
    private $classe;

    /**
     * @var string
     */
    private $titre;

    /**
     * @var \DateTime
     */
    private $date;

    // contructeur ajouté APRES la génération par ligne de commande avec doctrine
    // permet d'avoir la date du jour déjà remplie dans les select d'un form de nouveau devoir
    // https://openclassrooms.com/fr/courses/3619856-developpez-votre-site-web-avec-le-framework-symfony/3623841-creer-des-formulaires-avec-symfony
    public function __construct()
    {
      $this->date = new \Datetime();
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
     * Get classe
     *
     * @return int
     */
    public function getClasse()
    {
        return $this->classe;
    }

    /**
     * Set classe
     *
     * @return int
     */
    public function setClasse($classe)
    {
        $this->classe = $classe;

        return $this;
    }

    /**
     * Set titre
     *
     * @param string $titre
     *
     * @return Devoir
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Devoir
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}

