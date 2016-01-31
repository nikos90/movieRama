<?php
/**
 * Created by PhpStorm.
 * User: macbook51
 * Date: 31/01/16
 * Time: 01:55
 */


namespace AppBundle\Controller;

use AppBundle\Entity\MoviesHates;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Custom\Helpers;
use AppBundle\Entity\Movies;
use AppBundle\Entity\MoviesLikes;

class MoviesController extends Controller
{
    /**
     * Create new movie
    * @param Request $request
     * @return RedirectResponse
     */
    public function createAction(Request $request)
    {
       $em = $this->getDoctrine()->getManager();
       $params = $request->request->all();
       if(isset($params['title']) && $params['title']!=null && isset($params['description']) && $params['description']!=null){
           $movie = new Movies();
           $movie->setTitle($params['title']);
           $movie->setDescription($params['description']);
           $movie->setUser(
               $this->get('security.context')->getToken()->getUser()
           );
           $movie->setLikes(0);
           $movie->setHates(0);
           $em->persist($movie);
           $em->flush();
           $this->addFlash(
               'success',
               'Your movie was saved!'
           );

       }else{
           $this->addFlash(
               'error',
               'Your movie was not saved. Please try again.'
           );
       }

        return new RedirectResponse($this->generateUrl('homepage'));
    }

    /**
     * Get movies list
    * @param Request $request
    * @param $limit
    * @param $offset
    * @return JsonResponse
     */
    public function movieListAction(Request $request,$limit,$offset,$sort,$direction,$author=null){
        $em = $this->getDoctrine()->getManager();

        $repo = $em->getRepository('AppBundle:Movies');

        $query = $repo->createQueryBuilder('m');
        $query->select('m');
        if(is_numeric($author) && $author>0){
            $query->where('m.user = '.$author);
        }
        $query->orderBy('m.'.$sort,$direction);
        $query->setFirstResult( $offset )->setMaxResults( $limit );
        $results = $query->getQuery()->getResult();

        $response = new \stdClass();
        $response->movies = [];

        $loggedUser = $this->get('security.context')->getToken()->getUser();
        if(isset($results) && is_array($results) && count($results)>0){
            foreach($results as $result):
                $movie = new \stdClass();
                $movie->id = $result->getId();
                $movie->title = $result->getTitle();
                $movie->description = $result->getDescription();
                $movie->created = $result->getCreated()->getTimestamp();
                $movie->likes = $result->getLikes();
                $movie->hates = $result->getHates();
                $movie->author = $result->getUser()->getUsername();
                $movie->author_id = $result->getUser()->getId();
                $movie->is_mine = is_object($loggedUser) && $loggedUser->getId() == $result->getUser()->getId() ? 'yes' : 'no';
                $movie->me = is_object($loggedUser) && $loggedUser->getId()  ?  $loggedUser->getId()  : 0;
                if(is_object($loggedUser)){
                    $hasLiked =  $em->getRepository('AppBundle:MoviesLikes')->findOneBy(array('user'=>$loggedUser->getId(),'movie'=>$result->getId()));
                    $hashated =  $em->getRepository('AppBundle:MoviesHates')->findOneBy(array('user'=>$loggedUser->getId(),'movie'=>$result->getId()));
                    $movie->hasLiked = $hasLiked instanceof MoviesLikes ? 1 : 0;
                    $movie->hasHated = $hashated instanceof MoviesHates ? 1 : 0;
                }
                $response->movies[] = $movie;
            endforeach;

        }
        $response->count = count($response->movies);

        return new JsonResponse($response);

    }

    /**
     * Perform like action
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function likeAction(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $me = $this->get('security.context')->getToken()->getUser();

        $movie = $em->getRepository('AppBundle:Movies')->find($id);

        $response = new \stdClass();
        $response->saved = 0;

        if($movie instanceof Movies && $movie->getUser()->getId() != $me->getId()){

            $this->delete_hate($me->getId(),$id);

            $like = new MoviesLikes();
            $like->setMovie($movie);
            $like->setUser($me);

            $em->persist($like);
            $em->flush();

            if($like->getId()>0){
                $response->saved = 1;
            }
        }

        return new JsonResponse($response);
    }

    /**
     * Perform hate action
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function hateAction(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $me = $this->get('security.context')->getToken()->getUser();

        $movie = $em->getRepository('AppBundle:Movies')->find($id);

        $response = new \stdClass();
        $response->saved = 0;

        if($movie instanceof Movies && $movie->getUser()->getId() != $me->getId()){

            $this->delete_like($me->getId(),$id);

            $hate = new MoviesHates();
            $hate->setMovie($movie);
            $hate->setUser($me);

            $em->persist($hate);
            $em->flush();

            if($hate->getId()>0){
                $response->saved = 1;
            }
        }

        return new JsonResponse($response);
    }

    /**
     * Delete hate
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function unhateAction(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $me = $this->get('security.context')->getToken()->getUser();

        $movie = $em->getRepository('AppBundle:Movies')->find($id);

        $response = new \stdClass();
        $response->saved = 0;

        if($movie instanceof Movies && $movie->getUser()->getId() != $me->getId()){

            $this->delete_hate($me->getId(),$id);
            $response->saved = 1;
        }

        return new JsonResponse($response);
    }


    /**
     * Delete like
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function unlikeAction(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $me = $this->get('security.context')->getToken()->getUser();

        $movie = $em->getRepository('AppBundle:Movies')->find($id);

        $response = new \stdClass();
        $response->saved = 0;

        if($movie instanceof Movies && $movie->getUser()->getId() != $me->getId()){

            $this->delete_like($me->getId(),$id);
            $response->saved = 1;
        }

        return new JsonResponse($response);
    }

    /**
     * Delete movie
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function deleteAction(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $me = $this->get('security.context')->getToken()->getUser();

        $movie = $em->getRepository('AppBundle:Movies')->find($id);

        $response = new \stdClass();
        $response->saved = 0;

        if($movie instanceof Movies && $movie->getUser()->getId() == $me->getId()){

            $em->remove($movie);
            $em->flush();
            $response->saved = 1;
        }

        return new JsonResponse($response);
    }

    public function editMovieAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $me = $this->get('security.context')->getToken()->getUser();
        $post_data = json_decode(file_get_contents('php://input'));
        $response = new \stdClass();
        $response->saved = 0;
        if(isset($post_data->id) && isset($post_data->title) && isset($post_data->description) && $post_data->title!=null && $post_data->title!='' && $post_data->description!=null && $post_data->description!=''){
            $movie = $em->getRepository('AppBundle:Movies')->find($post_data->id);
            if($movie instanceof Movies && $movie->getUser()->getId() == $me->getId()){
                $movie->setTitle($post_data->title);
                $movie->setDescription($post_data->description);
                $em->persist($movie);
                $em->flush();
                $response->saved = 1;
            }

        }
        return new JsonResponse($response);

    }


    /**
     * Check if there is a hate and delete it
     * @param $user_id
     * @param $id
     */
    protected function delete_hate($user_id,$id){
        $em = $this->getDoctrine()->getManager();
        $hate = $em->getRepository('AppBundle:MoviesHates')->findOneBy(array('user'=>$user_id,'movie'=>$id));
        if($hate instanceof MoviesHates){
            $em->remove($hate);
            $em->flush();
        }
    }

    /**
     * Check if there is a like and delete it
     * @param $user_id
     * @param $id
     */
    protected function delete_like($user_id,$id){
        $em = $this->getDoctrine()->getManager();
        $like = $em->getRepository('AppBundle:MoviesLikes')->findOneBy(array('user'=>$user_id,'movie'=>$id));
        if($like instanceof MoviesLikes){
            $em->remove($like);
            $em->flush();
        }
    }

}
