<?php


namespace OC\PlatformBundle\Controller;

use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Category;
use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Form\AdvertEditType;
use OC\PlatformBundle\Form\CategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;


class AdvertController extends Controller
{
    public function indexAction($page)
    {
        if($page < 1) {
            throw new NotFoundHttpException("Page " . $page . " inexistante.");
        }
        // Ici je fixe le nombre d'annonces par page à 3
        // Mais bien sûr il faudrait utiliser un paramètre,
        // et y accéder via $this->container->getParameter('nb_per_page')
        $nbPerPage = 3;
        // On récupère notre objet Paginator
        $listAdverts = $this->getDoctrine()
            ->getManager()
            ->getRepository("OCPlatformBundle:Advert")
            ->getAdverts($page, $nbPerPage);
        // On calcul le nombre total de pages grâce au count($listAdverts)
        // qui retourne le nombre total d'annonce
        $nbPages = ceil(count($listAdverts) / $nbPerPage);
        // Si la page n'existe pas, on retourne un erreur 404
        if($page > $nbPages){
            throw $this->createNotFoundException("la page " . $page . " n'existe pas");
        }
        return $this->render(
            'OCPlatformBundle:Advert:index.html.twig',
            array(
                'listAdverts'   => $listAdverts,
                'nbPages'        => $nbPages,
                'page'          => $page
            )
        );
    }

    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $advert = $em->getRepository("OCPlatformBundle:Advert")->find($id);
        if(null === $advert){
            throw new NotFoundHttpException("L'annonce d'id " . $id . " n'existe pas.");
        }
        // On récupère la liste des candidatures de cette annonce
        $listApp = $em->getRepository("OCPlatformBundle:Application")
            ->findBy(array("advert" => $advert));
        // On récupère la liste des AdvertSkill
        $listAdvertSkill = $em
            ->getRepository('OCPlatformBundle:AdvertSkill')
            ->findBy(array("advert" => $advert));
        return $this->render(
            'OCPlatformBundle:Advert:view.html.twig',
            array(
                "advert" => $advert,
                "listApp" => $listApp,
                "listAdvertSkills" => $listAdvertSkill
            )
        );
    }

    public function addAction(Request $request)
    {
        // On crée un objet Advert
        $advert = new Advert();

        $form = $this->createForm(AdvertType::class, $advert);

        // Si la requête est en POST
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            // Le reste de la méthode reste inchangé
            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

            // On redirige vers la page de visualisation de l'annonce nouvellement créée
            return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));

        }

        // À ce stade, le formulaire n'est pas valide car :
        // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
        // - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau
        return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        $form = $this->get('form.factory')->create(AdvertEditType::class, $advert);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            // Inutile de persister ici, Doctrine connait déjà notre annonce
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

            return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
        }

        return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
            'advert' => $advert,
            'form'   => $form->createView(),
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression d'annonce contre cette faille
        $form = $this->get('form.factory')->create();

//        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->remove($advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée.");

            return $this->redirectToRoute('oc_platform_home');
//        }

//        return $this->render('OCPlatformBundle:Advert:delete.html.twig', array(
//            'advert' => $advert,
//            'form'   => $form->createView(),
//        ));
    }

    public function menuAction($limit)
    {
        $em = $this->getDoctrine()->getManager();
        $listAdverts = $em->getRepository("OCPlatformBundle:Advert")->findBy(
            array(),                 // Pas de critères
            array('date' => "desc"), // On trie par date décroissante
            $limit,                  // On sélectionne le nombre d'annonce à afficher avec $limit
            0                  // A partir du premier
        ) ;
        return $this->render(
            "OCPlatformBundle:Advert:menu.html.twig",
            array(
                'listAdverts' => $listAdverts
            )
        );
    }

    public function purgeAction($days, Request $request)
    {
        $purge = $this->get("oc_platform.purger.advert");

        $purge->purge($days);

        return $this->redirectToRoute('oc_platform_home');
    }

    public function testAction()

    {

        $advert = new Advert;


        $advert->setDate(new \Datetime());  // Champ « date » OK

        $advert->setTitle('abc');           // Champ « title » incorrect : moins de 10 caractères

        //$advert->setContent('blabla');    // Champ « content » incorrect : on ne le définit pas

        $advert->setAuthor('A');            // Champ « author » incorrect : moins de 2 caractères


        // On récupère le service validator

        $validator = $this->get('validator');


        // On déclenche la validation sur notre object

        $listErrors = $validator->validate($advert);


        // Si $listErrors n'est pas vide, on affiche les erreurs

        if (count($listErrors) > 0) {

            // $listErrors est un objet, sa méthode __toString permet de lister joliement les erreurs

            return new Response((string)$listErrors);

        } else {

            return new Response("L'annonce est valide !");

        }
    }
}
