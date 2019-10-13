<?php

namespace AppBundle\Entity;

/**
 * Enonce
 */
class Enonce
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $idDev;

    /**
     * @var int
     */
    private $ordre;

    /**
     * @var string
     */
    private $typec;

    /**
     * @var string
     */
    private $contenu;


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
     * Set idDev
     *
     * @param integer $idDev
     *
     * @return Enonce
     */
    public function setIdDev($idDev)
    {
        $this->idDev = $idDev;

        return $this;
    }

    /**
     * Get idDev
     *
     * @return int
     */
    public function getIdDev()
    {
        return $this->idDev;
    }

    /**
     * Set ordre
     *
     * @param integer $ordre
     *
     * @return Enonce
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return int
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * Set typec
     *
     * @param string $typec
     *
     * @return Enonce
     */
    public function setTypec($typec)
    {
        $this->typec = $typec;

        return $this;
    }

    /**
     * Get typec
     *
     * @return string
     */
    public function getTypec()
    {
        return $this->typec;
    }

    /**
     * Set contenu
     *
     * @param string $contenu
     *
     * @return Enonce
     */
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;

        return $this;
    }

    /**
     * Get contenu
     *
     * @return string
     */
    public function getContenu()
    {
        return $this->contenu;
    }
}

