<?php

namespace AppBundle\Controller;


use BackendBundle\Entity\User;
use BackendBundle\Entity\Video;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class VideoController extends Controller
{
    public function newAction(Request $request)
    {
        
        $helpers = $this->get("app.helpers");
        $token = $request->headers->get('x-token',null);
        $validateToken = $helpers->authCheck($token);

        $data = array(
            'status'=> 'error',
            'code'=> 400,   
            'msg'=>'Image not Uploaded'    
        );

        if(!$validateToken){
            $data['msg']='Invalid Authorization';
            return $helpers->toJson($data)->setStatusCode($data['code']);
        }
        
        $json= json_decode($request->getContent(),true);

        if(is_array($json)){

            $user= $helpers->currentUser($token);
            $em = $this->getDoctrine()->getManager();
            $video_repo= $em->getRepository('BackendBundle:Video');

            $createdAt = new \DateTime('now');
            $title=isset($json['title']) ? $json['title'] : null;
            $description= isset($json['description']) ? $json['description'] : null;
            $status= isset($json['status']) ? $json['status'] : null;

            if($title != null || $description != null || $status != null){
                $video= new Video();
                $video->setCreatedAt($createdAt);
                $video->setUpdatedAt($createdAt);
                $video->setTitle($title);
                $video->setDescription($description);
                $video->setStatus($status);
                $video->SetUser($user);

                var_dump($video);
                die();
            }
        

        }

        return $helpers->toJson($data)->setStatusCode($data['code']);

    }


}
