<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    private $moises = ["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"];
    
    /**
     * @Route("/", name="index")
     */
    public function indexAction()
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'datedujour' => date('d ').$this->moises[date('m')-1].date(' Y'),
            'frise' => 'ims/frise'.random_int(1,3).'.png'
        ]);
    }

    public function devAction()
    {
        $devoirs = $this->getDoctrine()->getManager()
        ->createQuery('SELECT e FROM AppBundle:Devoir e WHERE e.date >= CURRENT_DATE() ORDER BY e.date ASC')
        ->getResult();

        /*if(count($devoir)>0)
            $devoir = $devoir[0];
        else
            $devoir = '';*/
        //foreach ($devoirs as $devoir)
        if(count($devoirs)>0)
        {
            $enonces = $this->getDoctrine()->getRepository('AppBundle:Enonce')->findBy(['idDev'=>$devoirs[0]->getId()],['ordre'=>'ASC']);

            return $this->render('default/devoir.html.twig', [
                'devoir' => $devoirs[0],
                'enonces' => $enonces
                ]);
        }
        else
            return new Response('(aucun devoir pour le moment)',200);
    }

}

?>