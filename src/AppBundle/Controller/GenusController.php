<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Genus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class GenusController extends Controller
{
    /**
     * @Route("genus/new")
     */
    public function newAction()
    {
        $subFamilies = ['Octopodinea', 'Cordata', 'Anaklida'];
        $genus = new Genus();
        $genus->setName('Octopus-'.mt_rand(5, 99));
        $genus->setSpeciesCount(mt_rand(1000, 1999));
        $genus->setSubFamily($subFamilies[array_rand($subFamilies)]);

        $em = $this->getDoctrine()->getManager();
        $em->persist($genus);
        $em->flush();

        return new Response('<html> <body> <h1> Genus created .... '. $genus->getName() .'</h1></html> </body>');
    }

    /**
     * @Route("/genus")
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();
        $genuses = $em->getRepository('AppBundle:Genus')
            ->findAllPublishedOrderedBySize();

        return $this->render('genus/list.html.twig', [
            'genuses' => $genuses
        ]);
    }

    /**
     * @Route("/genus/{genusName}", name="genus_show")
     * @param $genusName
     * @return Response
     */
    public function showAction($genusName)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Genus $genus */
        $genus = $em->getRepository('AppBundle:Genus')
            ->findOneBy(['name' =>$genusName]);

        if (!$genus) {
            throw new NotFoundResourceException();
        }

        /* Caching
        $cache = $this->get('doctrine_cache.providers.my_markdown_cache');

        $cacheKey = md5($funFact);
        if($cache->contains($cacheKey)) {
            $funFact = $cache->fetch($cacheKey);
        } else {
            sleep(1);
            $funFact = $this->get('knp\bundle\markdownbundle\markdownparserinterface')
                ->transformMarkdown($funFact);
            $cache->save($cacheKey, $funFact);
        }
        */

        return $this->render('genus/show.html.twig', array(
            'genus' => $genus,
        ));
    }

    /**
     * @Route("/genus/{genusName}/notes", name="genus_show_notes")
     * @Method("GET")
     */
    public function getNotesAction($genusName)
    {
        $notes = [
            ['id' => 1, 'username' => 'AquaPelham', 'avatarUri' => '/images/leanna.jpeg', 'note' => 'Octopus asked me a riddle, outsmarted me', 'date' => 'Dec. 10, 2015'],
            ['id' => 2, 'username' => 'AquaWeaver', 'avatarUri' => '/images/ryan.jpeg', 'note' => 'I counted 8 legs... as they wrapped around me', 'date' => 'Dec. 1, 2015'],
            ['id' => 3, 'username' => 'AquaPelham', 'avatarUri' => '/images/leanna.jpeg', 'note' => 'Inked!', 'date' => 'Aug. 20, 2015'],
        ];
        $data = [
            'notes' => $notes
        ];

        return new JsonResponse($data);
    }
}
