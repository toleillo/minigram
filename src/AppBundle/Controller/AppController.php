<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Post;
use AppBundle\Form\PostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AppController extends Controller
{
    const LATEST_POST_LIMIT = 10;
    const XSL_POSITION_START = 2;

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $posts = [];
        $em = $this->getDoctrine()->getManager();

        $post = new Post();
        $form = $this->createForm(new PostType(), $post);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $file = $post->getImage();
                $fileName = $this->get('app.image_uploader')->upload($file);

                $post->setImage($fileName);

                $em->persist($post);
                $em->flush();

                $posts = $em->getRepository('AppBundle:Post')->getLatestPost(self::LATEST_POST_LIMIT);
            }
        }

        return $this->render('app/index.html.twig', array(
            'posts' => $posts,
            'form' => $form->createView(),
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..'),
        ));
    }

    /**
     * @Route("/ajax_post_list", options={"expose"=true}, name="ajax_post_list")
     */
    public function ajaxPostsAction(Request $request)
    {
        $limit = self::LATEST_POST_LIMIT;
        $offset = $request->get('page') * $limit;

        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository('AppBundle:Post')->findBy(
            [],
            ['id' => 'DESC'],
            $limit,
            $offset
        );

        return $this->render('app/ajaxPosts.html.twig', array(
            'posts' => $posts
        ));
    }

    /**
     * @Route("/export_xsl", name="homepage_export_xls")
     */
    public function exportAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $htmlHelper = $this->get('phpexcel')->createHelperHTML();
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', $htmlHelper->toRichTextObject('<b>Title</b>'))
            ->setCellValue('B1', $htmlHelper->toRichTextObject('<b>Filename</b>'));
        $phpExcelObject->getActiveSheet()->setTitle('Images');

        $posts = $em->getRepository('AppBundle:Post')->findAll();
        foreach($posts as $key => $post) {
            $position = $key + self::XSL_POSITION_START;

            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A'. $position, $post->getTitle())
                ->setCellValue('B'. $position, 'uploads/images/' . $post->getImage());
        }

        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);

        // adding headers
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename=images-file.xls');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;
    }

    /**
     * @Route("/export_csv", name="homepage_export_csv")
     */
    public function exportCsvAction(Request $request)
    {
        $response = new StreamedResponse();
        $response->setCallback(function() {
            $handle = fopen('php://output', 'w+');

            fputcsv($handle, ['Title', 'Filename'],';');

            $em = $this->getDoctrine()->getManager();
            $posts = $em->getRepository('AppBundle:Post')->findAll();
            foreach($posts as $post) {
                fputcsv($handle, [$post->getTitle(), 'uploads/images/' . $post->getImage()], ';');
            }

            fclose($handle);
        });

        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="images-file.csv"');

        return $response;
    }
}
