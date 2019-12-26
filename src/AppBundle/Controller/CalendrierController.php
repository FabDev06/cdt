<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CalendrierController extends Controller
{
    /** 
     * @Route("/cal_ajap") 
    */
    public function calAction(Request $request,$mois=null,$annee=null)
    {
        $moises = ["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"];
        
        // https://symfony.com/doc/3.4/components/http_foundation.html
        if($request->isXmlHttpRequest())
        {
            $mois = $request->request->get('mois');
            $annee = $request->request->get('annee');
            //die('mois='+$mois+' annee='+$annee);
            if($mois<=0)
            {
                $mois=12;
                $annee--;
            }
    
            if($mois>=13)
            {
                $mois=1;
                $annee++;
            }

            $nb_days = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
            $timestamp = mktime(0, 0, 0, $mois, 1, $annee); //Donne le timestamp correspondant à cette date 
            $start=date('N', $timestamp);

            // scan BDD du mois en cours
            //$seance = $this->getDoctrine()->getRepository('AppBundle:Devoir')->find($id);
            if($mois<10)
                $quer="SELECT e FROM AppBundle:Devoir e WHERE e.date LIKE '$annee-0$mois-%' ORDER BY e.date DESC";
            else
                $quer="SELECT e FROM AppBundle:Devoir e WHERE e.date LIKE '$annee-$mois-%' ORDER BY e.date DESC";

            $seances = $this->getDoctrine()->getManager()->createQuery($quer)->getResult();

            $response = new Response(json_encode(array('contenu' => $this->render('default/calendrier.html.twig', [
                'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
                'start' => $start,
                'date' =>
                [
                    'mois' => $mois,
                    'tmois' => $moises[$mois-1],
                    'annee' => $annee,
                    'nbdays' => $nb_days
                ],
                'oggi' => ['day'=>date('d'), 'mois'=>date('m'), 'an' =>date('Y')],
                'seances' => $seances
            ])->getContent())));
            
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        else
        {
            if($mois==null && $annee==null)
            {
                $mois = date('m');
                $annee = date('Y');
            }

            $nb_days = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
            $timestamp = mktime(0, 0, 0, $mois, 1, $annee); //Donne le timestamp correspondant à cette date 
            $start=date('N', $timestamp);

            // scan BDD du mois en cours
            if($mois<10)
                $quer="SELECT e FROM AppBundle:Devoir e WHERE e.date LIKE '%-0$mois-%' ORDER BY e.date DESC";
            else
                $quer="SELECT e FROM AppBundle:Devoir e WHERE e.date LIKE '%-$mois-%' ORDER BY e.date DESC";

            $seances = $this->getDoctrine()->getManager()->createQuery($quer)->getResult();
            /*
            $rtrt=fopen('cazz.txt','w+');
            fwrite($rtrt,print_r($seances,true));
            fclose($rtrt);
            */

            return $this->render('default/calendrier.html.twig', [
                'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
                'start' => $start,
                'date' =>
                    [
                        'mois' => $mois,
                        'tmois' => $moises[$mois-1],
                        'annee' => $annee,
                        'nbdays' => $nb_days
                    ],
                'oggi' => ['day'=>date('d'), 'mois'=>date('m'), 'an' =>date('Y')],
                'seances' => $seances
                ]);
        }
    }
}

            //$response->headers->set('Content-Type', 'text/html');
            
            /*new Response(
                'caca',
                Response::HTTP_OK,
                ['content-type' => 'text/html']
            );
            $response->prepare($request);
            $response->send();
            */

                        /*
            $prec=$mois-1;
            if($prec<=0)
            {
                $prec=12;
                $annee--;
            }
            $suiv=$mois+1;
            if($suiv>=13)
            {
                $suiv=1;
                $annee++;
            }
            */

?>