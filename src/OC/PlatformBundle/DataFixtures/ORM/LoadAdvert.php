<?php

namespace OC\PlatformBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Skill;

class LoadAdvert implements FixtureInterface
{
    // Dans l'argument de la méthode load, l'objet $manager est l'EntityManager
    public function load(ObjectManager $manager)
    {
        $date = new \Datetime();
        // Liste des noms de catégorie à ajouter
        $listAdverts = array(
            array(
                'title'   => 'Recherche développpeur Symfony',
                'id'      => 1,
                'author'  => 'Alexandre',
                'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
                'date'    => $date->modify('-3 day')),
            array(
                'title'   => 'Mission de webmaster',
                'id'      => 2,
                'author'  => 'Hugo',
                'content' => 'Nous recherchons un webmaster capable de maintenir notre site internet. Blabla…',
                'date'    => new \Datetime()),
            array(
                'title'   => 'Offre de stage webdesigner',
                'id'      => 3,
                'author'  => 'Mathieu',
                'content' => 'Nous proposons un poste pour webdesigner. Blabla…',
                'date'    => $date->modify('-3 day'))
        );

        foreach ($listAdverts as $advert) {
            // On crée la catégorie
            $newAdvert = new Advert();
            $newAdvert->setTitle($advert['title']);
            $newAdvert->setAuthor($advert['author']);
            $newAdvert->setContent($advert['content']);
            $newAdvert->setDate($advert['date']);


            $newSkill = new Skill();

            // On la persiste
            $manager->persist($newAdvert);
        }

        // On déclenche l'enregistrement de toutes les catégories
        $manager->flush();
    }
}