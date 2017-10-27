<?php

namespace OC\PlatformBundle\Purger;

use Doctrine\ORM\EntityManagerInterface;

class AdvertPurger
{
    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function purge($days)
    {
        $advertRepository =   $this->em->getRepository('OCPlatformBundle:Advert');
        $advertSkillRepository = $this->em->getRepository("OCPlatformBundle:AdvertSkill");

        $date = new \DateTime($days. " days ago");

        $listAdverts = $advertRepository->getAdvertsWithDate($date);

        foreach ($listAdverts as $advert){
            $advertSkills = $advertSkillRepository->findBy(array("advert" => $advert));

            foreach ($advertSkills as $advertSkill){
                $this->em->remove($advertSkill);
            }
            $this->em->remove($advert);
        }

        $this->em->flush();
    }
}