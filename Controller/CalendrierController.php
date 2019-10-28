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
    public function calAction(Request $request)
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

            $response = new Response(json_encode(array('contenu' => $this->render('default/calendrier.html.twig', [
                'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
                'start' => $start,
                'date' => [
                    'mois' => $mois,
                    'tmois' => $moises[$mois-1],
                    'annee' => $annee,
                    'nbdays' => $nb_days
                    ]
                ])->getContent())));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        else
        {
            $mois = date('m');
            $annee = date('Y');

            $nb_days = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
            $timestamp = mktime(0, 0, 0, $mois, 1, $annee); //Donne le timestamp correspondant à cette date 
            $start=date('N', $timestamp);

            //return $this->render('AppBundle::calendrier.html.twig', [
            return $this->render('default/calendrier.html.twig', [
                'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
                'start' => $start,
                'date' => [
                    'mois' => $mois,
                    'tmois' => $moises[$mois-1],
                    'annee' => $annee,
                    'nbdays' => $nb_days
                    ]
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