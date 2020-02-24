<?php /** @noinspection ALL */

namespace AppBundle\Controller;

use AppBundle\Entity\Genus;
use AppBundle\Entity\GenusNote;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GenusController extends Controller
{
    /**
     * @Route("/genus/new")
     */
    public function newAction()
    {
        // Creating a new genus object
        $genus = new Genus();
        $genus->setName('Octopus'.rand(1, 100));
        $genus->setSubFamily('Octopodinae');
        $genus->setSpeciesCount(rand(100, 99999));

        // Creating a new GenusNote object inside the GenusController and linking it to a Genus object
        $genusNote = new GenusNote();
        $genusNote->setUsername('AquaWeaver');
        $genusNote->setUserAvatarFilename('ryan.jpeg');
        $genusNote->setNote('I counted 8 legs... as they wrapped around me');
        $genusNote->setCreatedAt(new \DateTime('-1 month'));
        $genusNote->setGenus($genus); // This line is where we are setting the relationship between this genusNote object and the genus object above

        $em = $this->getDoctrine()->getManager();
        $em->persist($genus);
        $em->persist($genusNote); // Don't forget to persist the genusNote object
        $em->flush();

        return new Response('<html><body>Genus created!</body></html>');
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
     */
    public function showAction($genusName)
    {
        $em = $this->getDoctrine()->getManager();

        $genus = $em->getRepository('AppBundle:Genus')
            ->findOneBy(['name' => $genusName]);

        if (!$genus) {
            throw $this->createNotFoundException('genus not found');
        }

        // todo - add the caching back later
        /*
        $cache = $this->get('doctrine_cache.providers.my_markdown_cache');
        $key = md5($funFact);
        if ($cache->contains($key)) {
            $funFact = $cache->fetch($key);
        } else {
            sleep(1); // fake how slow this could be
            $funFact = $this->get('markdown.parser')
                ->transform($funFact);
            $cache->save($key, $funFact);
        }
        */

        $this->get('logger')
            ->info('Showing genus: '.$genusName);


//        // Gets the all the notes that were created in the last 3 months by using ArrayCollection methods. Only do this if you know you only have a small amount of items in the array, do not use it if you have a lot of items because it will impact performance.
//        $recentNotes = $genus->getNotes() // Since getNotes() returns an ArrayCollection object, it comes with specific methods on it
//            ->filter(function(GenusNote $note){ // Filtering through the GenusNote $note object
//                return $note->getCreatedAt() > new \DateTime('-3 months'); // The ArrayCollection will call the filter method for each $notes object it finds in the array.
//                                                                                // if it returns true (The note was created less than 3 months ago) it stays, if it returns false (The note was created more than 3 months ago), it disappears
//            });

        $recentNotes = $em->getRepository('AppBundle:GenusNote')
            ->findAllRecentNotesForGenus($genus);

        return $this->render('genus/show.html.twig', array( // Adds variables to twig so that we can use them in a twig html file.
            'genus' => $genus,
            'recentNotesCount' => count($recentNotes)
        ));
    }

    /**
     * @Route("/genus/{name}/notes", name="genus_show_notes")
     * @Method("GET")
     */
    public function getNotesAction(Genus $genus) // A $name argument is not being passed here because of param conversion. I am type-hinting this argument and since the wildcard in the route is a property of the Entity class Genus
                                                // Symfony will automatically know that it will be an object of the entity class genus.
    {
        $notes = []; // Creating a variable $notes which is can array
        foreach ($genus->getNotes() as $note) { // Looping over the notes property (which we are getting by using the getter method) as $note which will return a list of genus notes that are related to whatever genus we go to.
            $notes [] = [ // I am putting the returned genus notes into a $notes array, giving the array the same keys that I have in the database and assigning the data returned to each one by using the ArrayCollection functions such as getId()
                'id' => $note->getId(),
                'username' => $note->getUsername(),
                'avatarUri' => '/images/'.$note->getUserAvatarFilename(),
                'note' => $note->getNote(),
                'date' => $note->getCreatedAt()->format('M d, Y')
            ];
        }

        $data = [ // Creating a $data variable which is an array that holds the all the data that the $notes array has.
            'notes' => $notes
        ];

        return new JsonResponse($data);
    }
}
