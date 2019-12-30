<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Devoir;
use AppBundle\Entity\Enonce;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class DefaultController extends Controller
{
    public function getnjour($m)
    {
        $jours = ["","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi","Dimanche"];
        return $jours[$m];
    }

    public function getMoises($m)
    {
        $moises = ["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"];
        return $moises[$m];
    }

    /**
     * @Route("/admin", name="admin_homepage")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        // liste des devoirs, order by date ASC
        $lesdevoirs = $this->getDoctrine()->getRepository('AppBundle:Devoir')->findBy([],['date'=>'ASC']);

        return $this->render('@Admin/Default/index.html.twig', [
            'datedujour' => $this->getnjour(date('N')).date(' d ').$this->getMoises(date('m')-1).date(' Y'),
            'frise' => 'ims/frise'.random_int(1,3).'.png',
            'devoirs' => $lesdevoirs
        ]);
    }

    /** 
     * @Route("/admin-ajout", name="admin_ajout")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function ajoutAction(Request $request)
    {
        $devoir = new Devoir();
        $form = $this->createForm('AppBundle\Form\DevoirType', $devoir);

        // POST (submit admin-ajout) qui ajoute $.ajap
        if($request->isXmlHttpRequest())
        {
            $form->handleRequest($request);
            if(!$form->isEmpty() && $form->isSubmitted() && $form->isValid())
            {
                if($form->getData()->getClasse()!==null)
                {
                    if($form->getData()->getTitre()!==null)
                    {
                        $devoir = $form->getData();
                
                        $adddev = $this->getDoctrine()->getManager();
                        $adddev->persist($devoir);
                        $adddev->flush();
        
                        $request->getSession()->getFlashBag()->add('notice', 'Passage étape suivante');
        
                        $reponse = $this->render('@Admin/Default/ajout2.html.twig', ['id_dev' => $devoir->getId()]);
                    }
                    else
                    {
                        //$request->getSession()->getFlashBag()->add('notice', 'Problème formulaire $form->isSubmitted() && $form->isValid()');
                        $reponse = new Response('Erreur : le champ titre est vide...<br/><br/><a href="admin-ajout"><button class="btnadm">◄ Retour</button></a>');
                    }
                }
                else
                {
                    //$request->getSession()->getFlashBag()->add('notice', 'Problème formulaire $form->isSubmitted() && $form->isValid()');
                    $reponse = new Response('Erreur : choisir un bloc de classe dans l´EDT<br/><br/><a href="admin-ajout"><button class="btnadm">◄ Retour</button></a>');
                }
            }
            
            $reponse->prepare($request);
            return $reponse;
        }
        
        return $this->render('@Admin/Default/ajout.html.twig', [
            'datedujour' => $this->getnjour(date('N')).date(' d ').$this->getMoises(date('m')-1).date(' Y'),
            'frise' => 'ims/frise'.random_int(1,3).'.png',
            'form' => $form->createView()
        ]);
    }

    /**
    * @Route("/admin-enonce-modif-{id}", name="admin_enonce_modif")
    * @Security("has_role('ROLE_ADMIN')")
    */
    public function modifEnonceAction($id,Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        // on récup $_GET id
        $enonce = $entityManager->getRepository('AppBundle:Enonce')->find($id);

        if($request !== null)
        {
            // cas d'un submit texte
            $contenu = $request->get('contenu');
            if($contenu !== null)
            {
                $enonce->setContenu($contenu); //get('tata'.$id));
                $entityManager->flush();
            }
            else
            {
                // cas d'un submit fichier/image
                $contenu = $request->files->get('contenu');
                if($contenu !== null)
                {
                    /*$tass = fopen('ims/devoirs/cazz_t.txt','w+');
                    fwrite($tass,$contenu->getClientOriginalName());
                    fclose($tass);*/
                    $nomfich = $contenu->getClientOriginalName();
                    $ext = substr($nomfich,strlen($nomfich)-4,4);
                    try
                    {
                        $tonstring='im-dev-'.$enonce->getIdDev().'-'.$id.$ext;
                        $contenu->move('ims/devoirs/', $tonstring);
                        $enonce->setContenu('ims/devoirs/'.$tonstring); //get('tata'.$id));
                        $entityManager->flush();        
                    }
                    catch(FileException $e)
                    {
                        $this->logger->error('failed to upload image: ' . $e->getMessage());
                        throw new FileException('Failed to upload file');
                    }
                }
            }
        }
        return $this->redirect('/admin-modif-'.$enonce->getIdDev().'#enonce'.$id);
    }

    /**
    * @Route("/admin-modif-{id}", name="admin_modif")
    * @Security("has_role('ROLE_ADMIN')")
    */
    public function modifAction($id,Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm('AppBundle\Form\DevoirType');
        $devoir = $entityManager->getRepository('AppBundle:Devoir')->find($id);

        // POST (submit)
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $devoir->setTitre($form->getData()->getTitre());
            $devoir->setDate($form->getData()->getDate());
            // le flush suffit, pas de persist
            // https://symfony.com/doc/3.4/doctrine.html#updating-an-object
            $entityManager->flush();
        }

        // GET (depuis admin-modif-{iddev} qui modifie)
        // correction : depuis les bouton "modifier" de l'Admin
        $enonces= $entityManager->getRepository('AppBundle:Enonce')->findBy(['idDev'=>$id],['ordre'=>'ASC']);
        $classe = $entityManager->getRepository('AppBundle:Classe')->find($devoir->getClasse());

        $form_editable = $this->createForm('AppBundle\Form\DevoirType',$devoir);
        //$form_editable->add('id', HiddenType::class, ['data' => $id]);
        $form_editable->add('classe', HiddenType::class, ['data' => $classe->getId()]);
        $form_editable->add('titre', TextType::class, ['data' => $devoir->getTitre()]);
        $form_editable->add('date', DateType::class, ['data' => $devoir->getDate()]);

        return $this->render('@Admin/Default/modif.html.twig', [
            'datedujour' => $this->getnjour(date('N')).date(' d ').$this->getMoises(date('m')-1).date(' Y'),
            'frise' => 'ims/frise'.random_int(1,3).'.png',
            'form' => $form_editable->createView(),
            'devoir' => $devoir,
            'classe' => $classe,
            'contenus' => $enonces
        ]);
    }

    /**
     * @Route("/admin-enonce", name="admin_enonce")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function enonceAction(Request $request)
    {
        if($request->isXmlHttpRequest())
        {
            $form = $this->createForm('AppBundle\Form\EnonceType');
            $form->handleRequest($request);

            // cas d'un post avec process/submit, on persist bdd
            if($form->isSubmitted() && $form->isValid())
            {
                $enonce = new Enonce();
                $enonce = $form->getData();
    
                // on persist bdd
                $adddev = $this->getDoctrine()->getManager(); // on se prépare au combat
                $adddev->persist($enonce);
                $adddev->flush();
            }
            else
            {
                // cas d'un post "processData:false" pour l'image uniquement
                // cas Upload image avec téléversement juste avant (le JS apelle le cas upload de l'image) avant le post "processdata:(non indiqué, true par défaut)"
                
                // partie idDev + ordre
                $schtouf=explode("------WebKitForm",$request->getContent());

                $idDev=explode('[idDev]"',$schtouf[1]);
                $ordre=explode('[ordre]"',$schtouf[2]);

                $idDev=explode("\n",$idDev[1]);
                $ordre=explode("\n",$ordre[1]);

                $lidDev=intval($idDev[2]);
                $lordre=intval($ordre[2]);

                // partie contenu(fichier image)
                $datas = explode("Content-Type: ", $request->getContent());
                if(count($datas)>0)
                {
                    $datas = explode("------WebKitForm" , $datas[1]);
                    
                    // upload de l'image/illustration/photo
                    if(count($datas)>0)
                    {
                        $dtim = $datas[0];
                        if(substr($dtim,0,5)=='image')
                        {
                            if(substr($dtim,0,10)=='image/jpeg')
                            {
                                $chemfich="ims/devoirs/im-dev-$lidDev-$lordre.jpg";
                                $randel=fopen($chemfich,'w+');
                                fwrite($randel,substr($dtim,14,strlen($dtim)-15));
                                fclose($randel);
                            }
    
                            if(substr($dtim,0,9)=='image/png')
                            {
                                $chemfich="ims/devoirs/im-dev-$lidDev-$lordre.png";
                                $randel=fopen($chemfich,'w+');
                                fwrite($randel,substr($dtim,13,strlen($dtim)-14));
                                fclose($randel);
                            }
            
                            if(substr($dtim,0,9)=='image/gif')
                            {
                                $chemfich="ims/devoirs/im-dev-$lidDev-$lordre.gif";
                                $randel=fopen($chemfich,'w+');
                                fwrite($randel,substr($dtim,13,strlen($dtim)-14));
                                fclose($randel);
                            }
                        }
                        else
                            $chemfich='(format non pris en charge)';
                    }
                }
                else
                    $chemfich='(erreur Content-Type)';

                $enonce = new Enonce();
                $enonce->setIdDev($lidDev);
                $enonce->setOrdre($lordre);
                $enonce->setTypec('image');
                $enonce->setContenu($chemfich);

                // on persist bdd
                $adddev = $this->getDoctrine()->getManager(); // on se prépare au combat
                $adddev->persist($enonce);
                $adddev->flush();
            }
        }
        
        return new Response('',200);
    }

    /**
     * @Route("/admin-devoir-{id}", name="admin_devoir")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function devoirAction($id, Request $request)
    {
        if($request->isXmlHttpRequest())
        {
            // détails d'un devoir fraichement ajouté
            $devoir = $this->getDoctrine()->getRepository('AppBundle:Devoir')->find($id);
            $enonces = $this->getDoctrine()->getRepository('AppBundle:Enonce')->findBy(['idDev'=>$id],['ordre'=>'ASC']);
    
            $bloc_enonces='';
            foreach($enonces as $key => $value)
            {
                if($enonces[$key]->getTypec() == 'texte')
                    $bloc_enonces.=$value->getContenu();
                else
                    if($enonces[$key]->getTypec() == 'image')
                        $bloc_enonces.='<img src="'.$value->getContenu().'" class="img-fluid" alt="" /><p></p>';
            }

            return $this->render('@Admin/Default/devoir.html.twig',array(
                'devoir' => $devoir,
                'enonces' => $bloc_enonces));
        }
        else
        {
            // liste des devoirs -> voir en détails
            $devoir = $this->getDoctrine()->getRepository('AppBundle:Devoir')->find($id);
            $enonces = $this->getDoctrine()->getRepository('AppBundle:Enonce')->findBy(['idDev'=>$id],['ordre'=>'ASC']);

            $bloc_enonces='';
            foreach($enonces as $key => $value)
            {
                if($enonces[$key]->getTypec() == 'texte')
                    $bloc_enonces.=$value->getContenu();
                else
                    if($enonces[$key]->getTypec() == 'image')
                        $bloc_enonces.='<img src="'.$value->getContenu().'" class="img-fluid" alt="" /><p></p>';
            }
    
            return $this->render('@Admin/Default/det_devoir.html.twig',array(
                'datedujour' => $this->getnjour(date('N')).date(' d ').$this->getMoises(date('m')-1).date(' Y'),
                'frise' => 'ims/frise'.random_int(1,3).'.png',
                'devoir' => $devoir,
                'enonces' => $bloc_enonces));
        }
    }

    /**
     * @Route("/admin-suppr/{id}", name="admin_suppr")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function supprAction($id)
    {
        // suppresion d'un devoir
        $entityManager = $this->getDoctrine()->getManager();
        $dev_a_sup = $entityManager->getRepository('AppBundle:Devoir')->find($id);
        
        $entityManager->remove($dev_a_sup);
        $entityManager->flush();
        return $this->redirectToRoute('admin_homepage');
    }

    /**
     * @Route("/admin-modif", name="hint_modif")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function hintAction()
    {
        $lesdevoirs = $this->getDoctrine()->getRepository('AppBundle:Devoir')->findBy([],['date'=>'ASC']);

        return $this->render('@Admin/Default/hint.html.twig', [
            'datedujour' => $this->getnjour(date('N')).date(' d ').$this->getMoises(date('m')-1).date(' Y'),
            'frise' => 'ims/frise'.random_int(1,3).'.png',
            'devoirs' => $lesdevoirs
        ]);
    }

    /**
     * @Route("/admin-form", name="admin_form")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function formAction(Request $request)
    {
        $form = $this->createForm('AppBundle\Form\EnonceType', new Enonce(), ['allow_file_upload'=>true]);
        $form->add('idDev', HiddenType::class, ['data' => $request->get('iddev')]);
        $form->add('ordre', HiddenType::class, ['data' => $request->get('ordre')]);
        $form->add('typec', HiddenType::class, ['data' => $request->get('typec')]);

        if($request->get('typec')=="image")
        {
            $form->add('contenu', FileType::class, ['allow_file_upload' => true, 'label' => 'Pour ajouter une illustration, appuyer sur :']);
            $conte = 'im';
        }
        else
        {
            $form->add('contenu', HiddenType::class); //, ['label'=>' ']); //, ['disabled' => true]);
            $conte = 'editor'.$request->get('ordre');
        }

        //$form->setData(['name' => 'nom de corne']);
        return $this->render('@Admin/Default/form.html.twig', [
            'form' => $form->createView(),
            'conte' => $conte
        ]);
    }

    /**
     * @Route("/login", name="login")
     */
    public function loginAction()
    {
        if(!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            //throw $this->createAccessDeniedException();
            $authenticationUtils = $this->get('security.authentication_utils');
            //$zbew = 'getUser = '.print_r($request->request,true); //$this->getUser();

            return $this->render('@Admin/Default/login.html.twig',[
                'frise' => 'ims/frise'.random_int(1,3).'.png',
                'last_username' => $authenticationUtils->getLastUsername(),
                'erreur' => $authenticationUtils->getLastAuthenticationError()
                ]);
        }
        else
        {
            // Si le visiteur est déjà identifié, on le redirige vers l'accueil
            if($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
                return $this->redirectToRoute('admin_homepage');
            else
                return new Response("Erreur isGranted('ROLE_ADMIN')");
        }
    }

    /**
     * @Route("/admin-classe-{id}", name="admin_classe")
     */
    public function classeAction($id)
    {
        $classe= $this->getDoctrine()->getRepository('AppBundle:Classe')->find($id);
        $deb=date('H',$classe->getHeureDebut()->getTimestamp()).'h00';
        $fin=date('H',$classe->getHeureFin()->getTimestamp()).'h00';
        
        return new Response('<h3>Classe choisie : '.$classe->getNom().'</h3>
                            <h4><img src="ims/oggi.png" alt=""> Heure début : '.$deb.'</h4>
                            <h4><img src="ims/oggi.png" alt=""> Heure fin : '.$fin.'</h4>');
    }

    /**
    * @Route("/admin-test", name="test")
    * @Security("has_role('ROLE_ADMIN')")
    */
    public function testAction(Request $request)
    {
        //$datas = $request->normalizeQueryString($request->getContent());
        //$datas = explode("Content-Type: ", $request->getContent());
        //$datas = explode("------WebKitForm" , $datas[1]);
        
        //$resultat='<br/><br/>datas= §'.$datas[0].'§<br/><br/>';

        /*
        foreach(json_decode($request->getContent(), true) as $value)
        {
            $resultat.='<br/><br/>value= §'.$value.'§<br/><br/>';
        }
        */
        
        /*
        $resultat.="\n".'** request->files ='.print_r($request->files,true)."\n".'<br/><br/>';
        if(count($request->files)>0)
            $resultat.="\n".'**** request->request->files[0] ='.$request->files[0]."\n".'<br/><br/>';
        //$resultat.=' $request getMethod ='.$request->getMethod().'<br/><br/>';
        //$resultat.=' $request getRealMethod ='.$request->getRealMethod().'<br/><br/>';

        */

        $resultat=' $request initiale ='.print_r($request->request->all(),true).'<br/><br/>';

        if($request->isXmlHttpRequest())
        {
            $datas = explode("Content-Type: ", $request->getContent());
            if(count($datas)>0)
                $datas = explode("------WebKitForm" , $datas[1]);
            else
                $resultat.='pas encore image televersée';
            
            if(count($datas)>0)
                $resultat.='<br/><br/>datas= §'.$datas[0].'§<br/><br/>';

            $dtim = $datas[0];
            if(substr($dtim,0,5)=='image')
            {
                if(substr($dtim,0,10)=='image/jpeg')
                {
                    $randel=fopen('ims/devoirs/ims-dev-1.jpg','w+');
                    fwrite($randel,substr($dtim,14,strlen($dtim)-15));
                    fclose($randel);
                }

                if(substr($dtim,0,9)=='image/png')
                {
                    $randel=fopen('ims/devoirs/ims-dev-1.png','w+');
                    fwrite($randel,substr($dtim,13,strlen($dtim)-14));
                    fclose($randel);
                }

                if(substr($dtim,0,9)=='image/gif')
                {
                    $randel=fopen('ims/devoirs/ims-dev-1.gif','w+');
                    fwrite($randel,substr($dtim,13,strlen($dtim)-14));
                    fclose($randel);
                }

            }
            else
                $resultat.='<br/><br/>datas= ce nest pas une image<br/><br/>';
    
            $resultat.='On est entré ds ($request->isXmlHttpRequest())'.'<br/><br/>';
            $resultat.="\n".'**** request->files->get(contenu ='.$request->files->get('appbundle_enonce[contenu]')."\n".'<br/><br/>';
            //$resultat.='print_r request->files ='.print_r($request->files->all()['image'], true).'<br/><br/>';
            $resultat.='print_r _FILES ='.print_r($_FILES, true).'<br/><br/>';
            if(count($_FILES)>0)
                $resultat.=' _FILES[0] ='.$_FILES[0].'<br/><br/>';
                //$resultat.='2) $request='.$request->__toString().'<br/>';
            $form = $this->createForm('AppBundle\Form\EnonceType');
            $form->handleRequest($request);
            $form->submit($request->request->all(),true);
            //$resultat.='**** var_dump $request->files ='.print_r($request->files->all(), true).'<br/><br/>';
            //$resultat.='$form APRES getNormData='.$form->getNormData().'<br/><br/>';
            //$resultat.='$form APRES getExtraData ='.implode(", ",$form->getExtraData()).'<br/><br/>';
            //$resultat.=' request->get(_name='.print_r($request->request->get('_name'), true).'<br/><br/>';
            //$resultat.='print_r _FILES ='.print_r($_FILES, true).'<br/><br/>';
            //$resultat.=' request->all ='.print_r($request->request->all(), true).'<br/><br/>';

                if($form->isValid())
                {
                    //$resultat.= '$_FILES fuc ='.$_FILES['appbundle_enonce[contenu]'].'<br/>';
                    $resultat.='$form getData ='.print_r($form->getData(),true).'<br/>';
                    //$resultat.='$form getNormData ='.$form->getNormData().'<br/>';
                    //$resultat.='$form getExtraData ='.print_r($form->getExtraData(), true).'<br/>';

                    $uploadedFile = $form->get('contenu')->getData(); // ou bien $form->getData()->getContenu()
                    $resultat.='$form contenu getData (uploadedFile) ='.$uploadedFile.'<br/>';
        
                    /*
                    $uploadedFile->move('ims/devoirs/',$uploadedFile->getClientOriginalName());
                    $ch_img = 'ims/devoirs/'. $uploadedFile->getClientOriginalName();
                    $resultat.='$ch_img='.$ch_img.'<br/>';
                    */
                }
                else
                    $resultat.='isXmlHttpRequest ok, mais pb isSubmitted() && $form->isValid'.'<br/>';


            return new Response("<br/>\n".$resultat);
        }
        else
        {
            /*
            $te=new Enonce();
            $te->setIdDev('12');
            $form = $this->createForm('AppBundle\Form\EnonceType', $te, ['allow_file_upload'=>true]);
            */
            $form = $this->createForm('AppBundle\Form\EnonceType');
            $form->add('idDev', HiddenType::class, ['data' => 9]);
            $form->add('ordre', HiddenType::class, ['data' => 1]);
            $form->add('typec', HiddenType::class, ['data' => 'image']);
            $form->add('contenu', FileType::class, ['allow_file_upload' => true, 'label' => false]);

            if($_POST)
                $resultat.='Post detecté, mais pas la requete'.'<br/>';
            else
                $resultat.='Pas encore de post'.'<br/>';
            
            return $this->render('@Admin/Default/form-test.html.twig', [
                'form' => $form->createView(),
                'hrere' => $resultat,
                'frise' => 'ims/frise'.random_int(1,3).'.png']);
        }
    }
}

/*
    à ajouter dans form.html.twig :
    <div style="border:1px solid red">
        <h3>Résultat televerse :</h3>
        {{ hrere }}
    <div>
*/

/*
            NOTES / DIVERS
*/

        /*
        $contenus=[];
        foreach($enonces as $enonce)
        {
            if($enonce->getTypec()=='texte')
                array_push($contenus,$enonce->getContenu());
            else
                array_push($contenus,'<img src="'.$enonce->getContenu().'" alt="" class="img-fluid"/>');
        }
        */

        //if($form->isSubmitted() && $form->isValid())
            // ne peut pas être "submité" car on vient de $.post ajap qui post sans passer par un input type="submit"
            // re-correction, on est obligé de passer par submit, sinon le isValid bloque aussi avec son retour false si form non submité
            //$form->submit($request->request->get($form->getName())); // ne marche pas... Invalid Token


// On redirige vers la page de visualisation de l'annonce nouvellement créée
                // transition ajap ? Oui, donc commentaire du render d'avant ci dessous
                /*
                return $this->render('@Admin/Default/ajout2.html.twig',
                array('id' => $devoir->getId(),
                'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
                'datedujour' => $this->getnjour(date('N')).date(' d ').$this->getMoises(date('m')-1).date(' Y'),
                'frise' => 'ims/frise'.random_int(1,3).'.png'));
                */

            //$reponse->headers->set('Content-Type', 'application/json');

                //var_dump($request->request->get('compteur'));
            /*
            Exemple retour d'un form "énoncé" :

            object(Symfony\Component\HttpFoundation\ParameterBag)#87 (1)
            {
                ["parameters":protected]=> array(9)
                {
                    ["compteur"]=> string(1) "4"
                    ["appbundle_enonce"]=> array(5)
                    {
                        ["idDev"]=> string(2) "55"
                        ["ordre"]=> string(0) ""
                        ["typec"]=> string(0) ""
                        ["contenu"]=> string(0) ""
                        ["_token"]=> string(43) "vQAVcMncOjLg9Wi53oGLOcLKFzpXw5ro7opxF_oipi4"
                    }
                    ["ordre1"]=> string(1) "1"
                    ["ed1"]=> string(5) "crute"
                    ["ordre2"]=> string(1) "2"
                    ["ordre3"]=> string(1) "3"
                    ["ed3"]=> string(4) "prit"
                    ["ordre4"]=> string(1) "4"
                    ["ed4"]=> string(5) ""
                }
            }
            */

                            /*
                $form2 = $this->createForm('AppBundle\Form\EnonceType', null, ['allow_file_upload'=>true]);
                $form2->add('idDev', HiddenType::class, ['data' => $devoir->getId()]);
                */

                /*

            enonceAction ancienne version
            *****************************

                public function enonceAction(Request $request)
                {
                    $reponse = new Response('Erreur $request');
            
                    if($request->isXmlHttpRequest())
                    {
                        //$enonce = new Enonce();     // se type sur la classe Enonce
                        $form = $this->createForm('AppBundle\Form\EnonceType');
                        $form->handleRequest($request);
                        
                        if($form->isSubmitted() && $form->isValid())
                        {
                            //$reponse = new Response('Ok : form submité et validé');
                            $id_dev = $form->getData()->getIdDev(); // récup l'idDev
                            $adddev = $this->getDoctrine()->getManager(); // on se prépare au combat
                            
                            //var_dump($request->request->get('im1'));
                            for($a=1;$a<=$request->request->get('compteur');$a++)
                            {
                                $enonce = new Enonce();
                                $enonce->setIdDev($id_dev);
                                $enonce->setOrdre($a);
            
                                $erreur=true;
                                if(null !== $request->request->get('ed'.$a))
                                {
                                    $enonce->setTypec('texte');
                                    $enonce->setContenu($request->request->get('ed'.$a));
                                    $erreur=false;
                                }
            
                                if(null !== $request->request->get('im'.$a))
                                {
                                    $enonce->setTypec('image');
                                    $enonce->setContenu($request->request->get('im'.$a));
                                    $erreur=false;
                                }
            
                                if(!$erreur)
                                {
                                    // ajout de l'énoncé
                                    $adddev->persist($enonce);
                                    $adddev->flush();
                                }
                            }
            
                            $devoir = $this->getDoctrine()->getRepository('AppBundle:Devoir')->find($id_dev);
                            $enonces = $this->getDoctrine()->getRepository('AppBundle:Enonce')->findBy(['idDev'=>$id_dev],['ordre'=>'ASC']);
            
                            $reponse = $this->render('@Admin/Default/devoir.html.twig',array(
                                'devoir' => $devoir,
                                'enonces' => $enonces));
                        }
                        else
                            $reponse->setContent('Erreur submité et/ou validé');
                    }
                    return $reponse;
                }
*/            

/*

Toutes les méthodes de UploadedFile :
*************************************

"Symfony\Component\HttpFoundation\File\UploadedFile"

"getATime"
 "getBasename"
  "getCTime"
   "getClientMimeType"
    "getClientOriginalExtension"
     "getClientOriginalName"
      "getClientSize"
       "getError"
        "getErrorMessage"
         "getExtension"
          "getFileInfo"
           "getFilename"
            "getGroup"
             "getInode"
              "getLinkTarget"
               "getMTime"
                "getMaxFilesize"
                 "getMimeType"
                  "getOwner"
                   "getPath"
                    "getPathInfo"
                     "getPathname"
                      "getPerms"
                       "getRealPath"
                        "getSize"
                         "getType"
*/

                
    /*
            $conzol = new ConsoleOutput();
        //$conzol->writeln("\ncontenu requete files->keys : ".$request->files->keys());        //.$request->files->get('appbundle_enonce[contenu]'));

        if($request->isXmlHttpRequest())
        {
            $conzol->writeln('on entre dans request->isXmlHttpRequest');

            foreach($request->request->all() as $key => $value)
            {
                $conzol->writeln("- key=$key , value=$value");
            }

            $form = $this->createForm('AppBundle\Form\EnonceType');
            $form->handleRequest($request);

            $conzol->writeln("form=".print_r($form->all(),true));

            if(count($request->request->all())>0)
                $conzol->writeln("retour=".$request->request->all()[0]);

                $datas = explode("Content-Type: ", $request->getContent());

            if(count($datas)>1)
            {
                $datas = explode("------WebKitForm" , $datas[1]);
                $dtim = $datas[0];
                if(substr($dtim,0,5)=='image')
                {
                    if(substr($dtim,0,10)=='image/jpeg')
                    {
                        $randel=fopen('ims/devoirs/ims-dev-1.jpg','w+');
                        fwrite($randel,substr($dtim,14,strlen($dtim)-15));
                        fclose($randel);
                    }
    
                    if(substr($dtim,0,9)=='image/png')
                    {
                        $randel=fopen('ims/devoirs/ims-dev-1.png','w+');
                        fwrite($randel,substr($dtim,13,strlen($dtim)-14));
                        fclose($randel);
                    }
    
                    if(substr($dtim,0,9)=='image/gif')
                    {
                        $randel=fopen('ims/devoirs/ims-dev-1.gif','w+');
                        fwrite($randel,substr($dtim,13,strlen($dtim)-14));
                        fclose($randel);
                    }
                }
            }

            $enonce = new Enonce();
            $enonce = $form->getData();
            $conzol->writeln('contenu du form submitted : ',$enonce);

            if($enonce->getTypec() == 'image')
            {
                // Upload image
                $conzol->writeln('contenu=image on entre dans uploadedFile');
                $uploadedFile = $form->getData()->getContenu();
                $uploadedFile->move('ims/devoirs/',$uploadedFile->getClientOriginalName());
                $enonce->setContenu($uploadedFile->getClientOriginalName());

                $adddev = $this->getDoctrine()->getManager(); // on se prépare au combat
                $adddev->persist($enonce);
                $adddev->flush();
            }
            else
            {
                $conzol->writeln('le contenu est vide (textarea) mon on persist ok');
                if($enonce->getContenu()==null)
                    $enonce->setContenu('(vide)');
                // ajout de l'énoncé
                $adddev->persist($enonce);
                $adddev->flush();
            }
            
            $conzol->writeln('enonce OK persist bdd ok');
            $conzol->writeln('****** FIN DES MOUVEMENTS ******');
            return new Response('enonce OK persist bdd ok');
        }

        $conzol->writeln('Erreur $request');
        return new Response('Erreur $request');

    */

                /*
                if($enonce->getTypec()=='image')
                {
                    $ext='';

                    // mettre un while tant que le/les fichier temp n'existe pas, on attend
                    //while($ext=='')
                    {
                        if(file_exists("ims/devoirs/temp.jpg"))
                            $ext='.jpg';

                        if(file_exists("ims/devoirs/temp.png"))
                            $ext='.png';

                        if(file_exists("ims/devoirs/temp.gif"))
                            $ext='.gif';
                    }

                    // temporaire, pr tester nouveau depuis re-form de la méthode bug
                    $enonce->setContenu("(ext=$ext)");

                    if($ext!=='')
                    {
                        $idDev=$enonce->getIdDev();
                        $ordre=$enonce->getOrdre();

                        $enonce->setContenu("ims/devoirs/im-dev-$idDev-$ordre$ext");
                        // rename temp.jpg ou png etc. 
                        rename("ims/devoirs/temp$ext","ims/devoirs/im-dev-$idDev-$ordre$ext");
                        @unlink('ims/devoirs/temp.jpg');
                        @unlink('ims/devoirs/temp.png');
                        @unlink('ims/devoirs/temp.gif');
                    }
                }
                */
