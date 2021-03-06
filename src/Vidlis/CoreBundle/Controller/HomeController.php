<?php

namespace Vidlis\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Vidlis\CoreBundle\Entity\PlayedQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class HomeController extends Controller
{

    /**
     * @Route("/", name="_home")
     * @Route("/fr/")
     * @Route("/share/{idVideo}")
     * @Template()
     * @Cache(expires="tomorrow")
     */
    public function indexAction($idVideo = null)
    {
        $data = array();
        if (!is_null($idVideo)) {
            $youtubeVideoService = $this->get('youtubeVideo');
            $result = $youtubeVideoService->setId($idVideo)->getResult();
            $data['title'] = $result->items[0]->snippet->title . ' - Vidlis';
            $data['og_image'] = $result->items[0]->snippet->thumbnails->medium->url;
            $data['og_title'] = $result->items[0]->snippet->title . ' - Vidlis';
        } else {
            $data['title'] = 'Vidlis';
        }
        if ($this->getRequest()->isMethod('POST')) {
            $data['content'] = $this->renderView('VidlisCoreBundle:Home:content.html.twig', $this->contentAction());
            $response = new Response(json_encode($data));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else {
            if (!is_null($idVideo)) {
                $data['launch'] = $idVideo;
            }
        }
        return $data;
    }
    
    /**
     * @Template()
     * @Cache(expires="tomorrow")
     */
    public function contentAction()
    {
        $em = $this->getDoctrine()->getManager();
        $playedQuery = new PlayedQuery($em);
        $videosPlayed = $playedQuery->getLastPlayed(12);
        $data = array('videosPlayed' => $videosPlayed);
        if ($this->getUser()) {
            $data['user'] = $this->getUser();
            $data['connected'] = true;
        } else {
            $data['connected'] = false;
        }
        return $data;
    }

    /**
     * @Route("/share/popup/{idVideo}", name="_sharePopup")
     * @Template()
     */
    public function sharePopupAction($idVideo = null)
    {
        $data = [];
        $data['title'] = "Partager c'est cool !";
        $data['content'] = $this->renderView('VidlisCoreBundle:Home:sharePopup.html.twig', array('idVideo' => $idVideo));
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/create-group", name="_createGroup")
     */
    public function createGroupAction()
    {
        $data = [];
        $data['title'] = "Créer un groupe d'écoute";
        $data['content'] = $this->renderView('VidlisCoreBundle:Home:createGroup.html.twig');
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/join-group", name="_joinGroup")
     */
    public function joinGroupAction()
    {
        $data = [];
        $data['title'] = "Rejoindre un groupe d'écoute";
        $data['content'] = $this->renderView('VidlisCoreBundle:Home:joinGroup.html.twig');
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
