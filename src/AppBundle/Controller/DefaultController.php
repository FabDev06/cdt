<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Component\HttpFoundation\Response;

/*
use AdminBundle\Entity\Utilisateur; 
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
*/

class DefaultController extends Controller
{
    private $moises = ["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"];
    
    /**
     * @Route("/", name="index")
     */
    public function indexAction($id=0)
    {
        $doc = $this->getDoctrine();

        if($id>0)
        {
            $devoirs = $doc->getManager()->createQuery(
                'SELECT e FROM AppBundle:Devoir e WHERE e.id = '.$id)->getResult();
            $texte_seance = 'Séance du';
            $mois=$devoirs[0]->getDate()->format('n');  // n = le format du mois sans le "0" si <10 ** réf : https://www.php.net/manual/fr/function.date.php
            $annee=$devoirs[0]->getDate()->format('Y');
        }
        else
        {
            $devoirs = $doc->getManager()->createQuery(
                'SELECT e FROM AppBundle:Devoir e WHERE e.date <= CURRENT_DATE() ORDER BY e.date DESC')->getResult();
            $id=$devoirs[0]->getId();
            $texte_seance = 'Dernière séance, le';
            $mois=0;
            $annee=0;
        }

        if(count($devoirs)>0)
            $enonces = $doc->getRepository('AppBundle:Enonce')->findBy(['idDev'=>$id],['ordre'=>'ASC']);
		else
            $enonces = 'Aucune séance à afficher.';

        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'datedujour' => date('d ').$this->moises[date('m')-1].date(' Y'),
            'frise' => 'ims/frise'.random_int(1,3).'.png',
            'devoir' => $devoirs[0],
            'enonces' => $enonces,
            'texte_seance' => $texte_seance,
            'mois' => $mois,
            'annee' => $annee
            ]);
    }
}

/*

$enonces = $this->getDoctrine()->getRepository('AppBundle:Enonce')->findBy(['idDev'=>$devoirs[0]->getId()],['ordre'=>'ASC']);
- $devoirs[0]->getId() remplacé par $id

    public function devAction($id)
    {
        if($id>0)
            $devoirs = $this->getDoctrine()->getManager()
            ->createQuery('SELECT e FROM AppBundle:Devoir e WHERE e.id = '.$id)
            ->getResult();
        else
            $devoirs = $this->getDoctrine()->getManager()
            ->createQuery('SELECT e FROM AppBundle:Devoir e WHERE e.date <= CURRENT_DATE() ORDER BY e.date DESC')
            ->getResult();

        if(count($devoirs)>0)
        {
            $enonces = $this->getDoctrine()->getRepository('AppBundle:Enonce')->findBy(['idDev'=>$devoirs[0]->getId()],['ordre'=>'ASC']);

            return $this->render('default/devoir.html.twig', [
                'devoir' => $devoirs[0],
                'enonces' => $enonces
                ]);
        }
        else
            return new Response('Aucune séance à afficher.',200);
    }
*/

        /*if(count($devoir)>0)
            $devoir = $devoir[0];
        else
            $devoir = '';*/
        //foreach ($devoirs as $devoir)

    /*
     * @Route("/pass", name="pass")
     *
    public function passAction(UserPasswordEncoderInterface $encoder)
    {
        // whatever *your* User object is
        $user = new Utilisateur();
        $plainPassword = 'test';
        $encoded = $encoder->encodePassword($user, $plainPassword);
    
        //$user->setPassword($encoded);
        return new Response('mot de passe ="'.$encoded.'"');
    }
*/
?>