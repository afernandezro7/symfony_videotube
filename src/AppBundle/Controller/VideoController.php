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

    public function editAction(Request $request, $video_id)
    {

        $helpers = $this->get("app.helpers");
        $token = $request->headers->get('x-token',null);
        $validateToken = $helpers->authCheck($token);
        
        $data = array(
            'status'=> 'error',
            'code'=> 400,   
            'msg'=>'Video not created'    
        );
        
        if(!$video_id){
            $data['msg']='Video not found';
            $data['code']=404;
            return $helpers->toJson($data)->setStatusCode($data['code']);
        }
        
        if(!$validateToken){
            $data['msg']='Invalid Authorization';
            return $helpers->toJson($data)->setStatusCode($data['code']);
        }
        
        
        
        $json= json_decode($request->getContent(),true);
        
        if(is_array($json)){
            
            $user= $helpers->currentUser($token);
            
            
            $updatedAt = new \DateTime('now');
            $title=isset($json['title']) ? $json['title'] : null;
            $description= isset($json['description']) ? $json['description'] : null;
            $status= isset($json['status']) ? $json['status'] : null;

            //Buscar id del usuario activo en db
            $em = $this->getDoctrine()->getManager();
            $video_repo= $em->getRepository('BackendBundle:Video');
            $video= $video_repo->findOneBy(array('id'=>$video_id));
            
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

    public function uploadAction(Request $request, $video_id)
    {
        $helpers = $this->get("app.helpers");
        $token = $request->headers->get('x-token',null);
        $validateToken = $helpers->authCheck($token);
        
        $data = array(
            'status'=> 'error',
            'code'=> 400,   
            'msg'=>'file not uploded'    
        );
        
        if(!$video_id){
            $data['msg']='Video not found';
            $data['code']=404;
            return $helpers->toJson($data)->setStatusCode($data['code']);
        }
        
        if(!$validateToken){
            $data['msg']='Invalid Authorization';
            return $helpers->toJson($data)->setStatusCode($data['code']);
        }

        //Buscar id del usuario activo en db
        $user= $helpers->currentUser($token);
        $em = $this->getDoctrine()->getManager();
        $video_repo= $em->getRepository('BackendBundle:Video');
        $video= $video_repo->findOneBy(array('id'=>$video_id));

        if(is_object($video)){
                
            if($user == null || $user->getId() != $video->getUser()->getId()){
                $data['msg']='Invalid Authorization, this asset is not yours';
                return $helpers->toJson($data)->setStatusCode($data['code']);
            }

            //upload video
            $file = $request->files->get("video");
            $videofile = false;

            if(!empty($file) && $file != null){

                $ext = $file->guessExtension();
                if($ext == 'mp4' || $ext == 'mpg' || $ext == 'mpeg' || $ext == 'mkv' || $ext == 'avi'){
                   
                    $file_name = $user->getId()."-".time() . ".". $ext;
                    $videofile = true;
                    $videoName = $file_name;
                    $file->move('uploads/videos', $file_name);

                    //Delete old image
                    $helpers->removeFile($video->getVideoPath(),'uploads/videos');
    
                    $video->setVideoPath($file_name);

                    //Update User in db
                    $em->persist($video);
                    $em->flush();
    
                    $data = array(
                        'status'=> 'success',
                        'code'=> 200,   
                        'msg'=>'Video uploaded',
                        'video'=> $file_name    
                    );
    

                }else {
                    $data['msg']='Video format not supported';
                    return $helpers->toJson($data)->setStatusCode($data['code']);
                }
            }

            //upload image
            $file = $request->files->get("image");

            if(!empty($file) && $file != null){
                $ext = $file->guessExtension();
                if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif'){
                   
                    $file_name = $user->getId()."-".time() . ".". $ext;
                    $file->move('uploads/images', $file_name);

                    //Delete old image
                    $helpers->removeFile($video->getImage(),'uploads/images');
    
                    $video->setImage($file_name);

                    //Update User in db
                    $em->persist($video);
                    $em->flush();
    
                    $data = array(
                        'status'=> 'success',
                        'code'=> 200,   
                        'msg'=>'Image uploaded',
                        'image'=> $file_name    
                    );

                    if($videofile){
                        $data = array(
                            'status'=> 'success',
                            'code'=> 200,   
                            'msg'=>'Video and Image uploaded',
                            'video'=> $videoName,    
                            'image'=> $file_name,    
                        );
                    }
    

                }else {
                    $data['msg']='Image format not supported';
                    return $helpers->toJson($data)->setStatusCode($data['code']);
                }
            }
        }

        return $helpers->toJson($data)->setStatusCode($data['code']);
    }

    

}
