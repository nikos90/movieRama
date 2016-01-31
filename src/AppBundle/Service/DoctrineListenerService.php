<?php
/**
 * Created by PhpStorm.
 * User: macbook51
 * Date: 31/01/16
 * Time: 04:05
 */

namespace AppBundle\Service;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Entity\MoviesLikes;
use AppBundle\Entity\MoviesHates;

class DoctrineListenerService implements EventSubscriber
{

    protected $container;
    protected $em;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

    }

    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'postRemove',
        );
    }

    /**
     * This method will called on Doctrine postPersist event
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->em = $args->getEntityManager();

        if ($entity instanceof MoviesLikes){
            $this->incrementLikes($entity);
        }

        if ($entity instanceof MoviesHates){
            $this->incrementHates($entity);
        }


    }

    /**
     * Increment movie likes
    * @param MoviesLikes $entity
     */
    protected function incrementLikes(MoviesLikes $entity){
        $movie = $entity->getMovie();
        $movie->setLikes($movie->getLikes()+1);
        $this->em->persist($movie);
        $this->em->flush();
    }

    /**
     * Increment movie likes
     * @param MoviesHates $entity
     */
    protected function incrementHates(MoviesHates $entity){
        $movie = $entity->getMovie();
        $movie->setHates($movie->getHates()+1);
        $this->em->persist($movie);
        $this->em->flush();
    }


    /**
     * This method will called on Doctrine postPersist event
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        # Avoid to log the logging process
        $entity = $args->getEntity();
        $this->em = $args->getEntityManager();
        if ($entity instanceof MoviesLikes){
            $this->decreaseLikes($entity);
        }

        if ($entity instanceof MoviesHates){
            $this->decreaseHates($entity);
        }
    }

    /**
     * Decrease movie likes
     * @param MoviesLikes $entity
     */
    protected function decreaseLikes(MoviesLikes $entity){
        $movie = $entity->getMovie();
        $movie->setLikes($movie->getLikes()-1);
        $this->em->persist($movie);
        $this->em->flush();
    }

    /**
     * Decrease movie likes
     * @param MoviesHates $entity
     */
    protected function decreaseHates(MoviesHates $entity){
        $movie = $entity->getMovie();
        $movie->setHates($movie->getHates()-1);
        $this->em->persist($movie);
        $this->em->flush();
    }


}