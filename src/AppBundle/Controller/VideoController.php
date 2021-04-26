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
            'msg'=>'Video not created'    
        );

        if(!$validateToken){
            $data['msg']='Invalid Authorization';
            return $helpers->toJson($data)->setStatusCode($data['code']);
        }
        
        $json= json_decode($request->getContent(),true);

        if(is_array($json)){

            $user= $helpers->currentUser($token);


            $createdAt = new \DateTime('now');
            $title=isset($json['title']) ? $json['title'] : null;
            $description= isset($json['description']) ? $json['description'] : null;
            $status= isset($json['status']) ? $json['status'] : null;

            if($user != null || $title != null || $description != null || $status != null){
                $video= new Video();
                $video->setCreatedAt($createdAt);
                $video->setUpdatedAt($createdAt);
                $video->setTitle($title);
                $video->setDescription($description);
                $video->setStatus($status);
                $video->SetUser($user);

                $em = $this->getDoctrine()->getManager();
                $em->persist($video);
                $em->flush();

                $data = array(
                    'status'=> 'success',
                    'code'=> 200,   
                    'msg'=>'Video created successfully',
                    'video'=>$video 
                );
            }
        }

        return $helpers->toJson($data)->setStatusCode($data['code']);

    }

    public function editAction(Request $request)
    {
        
        $helpers = $this->get("app.helpers");
        $token = $request->headers->get('x-token',null);
        $validateToken = $helpers->authCheck($token);
        
        $data = array(
            'status'=> 'error',
            'code'=> 400,   
            'msg'=>'Video not created'    
        );
        
        if(!$validateToken){
            $data['msg']='Invalid Authorization';
            return $helpers->toJson($data)->setStatusCode($data['code']);
        }
        
        
        $json= json_decode($request->getContent(),true);
        
        if(is_array($json)){
            
            $user= $helpers->currentUser($token);
            
            
            $id=isset($json['id']) ? $json['id'] : null;
            $updatedAt = new \DateTime('now');
            $title=isset($json['title']) ? $json['title'] : null;
            $description= isset($json['description']) ? $json['description'] : null;
            $status= isset($json['status']) ? $json['status'] : null;

            //Buscar id del usuario activo en db
            $em = $this->getDoctrine()->getManager();
            $video_repo= $em->getRepository('BackendBundle:Video');
            $video= $video_repo->findOneBy(array('id'=>$id));
            
            if(is_object($video)){
                
                if($user == null || $user->getId() != $video->getUser()->getId()){
                    $data['msg']='Invalid Authorization, this asset is not yours';
                    return $helpers->toJson($data)->setStatusCode($data['code']);
                }

                $video->setUpdatedAt($updatedAt);
               
                if($title){
                    $video->setTitle($title);
                }
                if($description){
                    $video->setDescription($description);
                }
                if($status){
                    $video->setStatus($status);
                }


                $em = $this->getDoctrine()->getManager();
                $em->persist($video);
                $em->flush();

                $data = array(
                    'status'=> 'success',
                    'code'=> 200,   
                    'msg'=>'Video updated successfully',
                    'video'=>$video 
                );
            }else{
                $data = array(
                    'status'=> 'error',
                    'code'=> 404,   
                    'msg'=>'Video not found'    
                );
            }
        }

        return $helpers->toJson($data)->setStatusCode($data['code']);

    }


}
