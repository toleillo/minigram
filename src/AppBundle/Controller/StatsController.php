<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Post;
use AppBundle\Form\PostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StatsController extends Controller
{

    /**
     * @Route("/ajax-stats", options={"expose"=true}, name="ajax_stats")
     */
    public function indexAction(Request $request)
    {
        $data = [];
        $em = $this->getDoctrine()->getManager();


        $data['posts_count'] = $em->getRepository('AppBundle:Post')->getPostCount();
        $data['views_count'] = rand(1000, 1000000); // Get real data     from GA API

        return new JsonResponse($data);
    }


}
