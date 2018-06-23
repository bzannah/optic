<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Genus;
use AppBundle\Entity\GenusNote;
use AppBundle\Service\MarkdownTransformer;
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

        $note = new GenusNote();
        $note->setUsername('AquaWeaver');
        $note->setUserAvatarFilename('ryan.jpeg');
        $note->setNote('I counted 8 legs... as they wrapped around me');
        $note->setCreatedAt(new \DateTime('-1 month'));
        $note->setGenus($genus);

        $em = $this->getDoctrine()->getManager();
        $em->persist($genus);
        $em->persist($note);
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
            ->findAllPublishedOrderedByRecentlyActive();

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

        $transformer = $this->get('app.markdown_transformer');
        $funFact = $transformer->parse($genus->getFunFact());

        $recentNotes = $em->getRepository('AppBundle:GenusNote')
            ->findAllRecentNotesForGenus($genus);

        return $this->render('genus/show.html.twig', array(
            'genus' => $genus,
            'recentNoteCount' => count($recentNotes),
            'funFact' => $funFact
        ));
        /*
        //VIPNOTE: this demonstrate filtering with ArrayCollection
        $recentNotes = $genus->getNotes()
            ->filter(function(GenusNote $note) {
                return $note->getCreatedAt() > new \DateTime('-3 months');
            });

        */

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
    }

    /**
     * @Route("/genus/{name}/notes", name="genus_show_notes")
     * @Method("GET")
     */
    public function getNotesAction(Genus $genus)
    {
        $notes = [];
        foreach ($genus->getNotes() as $note) {
            $notes[] = [
                'id' => $note->getId(),
                'username' => $note->getUsername(),
                'avatarUri' => '/images/'.$note->getUserAvatarFilename(),
                'note' => $note->getNote(),
                'date' => $note->getCreatedAt()->format('M d, Y')
            ];
        }
        $data = [
            'notes' => $notes
        ];
        return new JsonResponse($data);
    }
}
