<?php

namespace AppBundle\Controller;


use BackendBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller
{
    public function newAction(Request $request)
    {
        $helpers = $this->get("app.helpers");

        $json= json_decode($request->getContent(),true);

        $data = array(
            'status'=> 'error',
            'code'=> 400,   
            'msg'=>'User not created'    
        );



        if(is_array($json)){
            $createdAt = new \DateTime('now');
            $role='user';
            $email= isset($json['email']) ? $json['email'] : null;
            $name= (isset($json['name']) ) ? $json['name'] : null;
            $surname= (isset($json['surname'])) ? $json['surname'] : null;
            $password= isset($json['password']) ? $json['password'] : null;

            $emailConstraint = new Email();
            $emailConstraint->message = "This email is nost valid";
            $validate_email = $this->get('validator')->validate($email,$emailConstraint);
            


            if($email != null && count($validate_email)==0 && $password != null && $name != null && $surname != null){
                $user= new User();
                $user->setCreatedAt($createdAt);
                $user->setRole($role);
                $user->setName($name);
                $user->setSurname($surname);
                $user->setEmail($email);
                $user->setPassword( hash('sha256', $password) );

                $em = $this->getDoctrine()->getManager();
                $user_repo= $em->getRepository('BackendBundle:User');
                $isset_userDb= $user_repo->findBy(array('email'=>$email));

                

                if(count($isset_userDb) == 0){
                    $em->persist($user);
                    $em->flush();

                    $data = array(
                        'status'=> 'success',
                        'code'=> 200,   
                        'msg'=>'New User created',
                        'user'=>$user  
                    );
                }else {
                    $data = array(
                        'status'=> 'error',
                        'code'=> 400,   
                        'msg'=>'User is already registered',
                    );
                }
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
            'msg'=>'User not Updated'    
        );
        
        if(!$validateToken){
            $data['msg']='Invalid Authorization';
            return $helpers->toJson($data)->setStatusCode($data['code']);
        }
        
        $json= json_decode($request->getContent(),true);

        if(is_array($json)){
            //Instanciar info del body del request
            $email= isset($json['email']) ? $json['email'] :null;
            $name= isset($json['name']) ? $json['name']:null;
            $surname= isset($json['surname']) ? $json['surname']:null;
            $password= isset($json['password']) ? $json['password']: null;
            
            //validar email
            $emailConstraint = new Email();
            $emailConstraint->message = "This email is nost valid";
            $validate_email = $this->get('validator')->validate($email,$emailConstraint);

            if($email != null && count($validate_email)==0){
                $userIdentity = $helpers->authCheck($token,true)->sub;
                //Buscar id del usuario activo en db
                $em = $this->getDoctrine()->getManager();
                $user_repo= $em->getRepository('BackendBundle:User');
                $user= $user_repo->findOneBy(array('id'=>$userIdentity));
                
                
                if(count($user) > 0){
 
                    if($name){
                        $user->setName($name);
                    }
    
                    if($surname){
                        $user->setSurname($surname);
                    }
    
                    if($password){
                        $user->setPassword( hash('sha256', $password) );
                    }

                    if($email && $email != $user->getEmail()){
                        $userAlreadyRegistered= $user_repo->findOneBy(array('email'=>$email));

                        if(is_object($userAlreadyRegistered) == 0){
                            $user->setEmail($email);
                        }else{
                            $data['msg']='Invalid Email';
                            return $helpers->toJson($data)->setStatusCode($data['code']);
                        }
                    }
    

                    //Update User in db
                    $em->persist($user);
                    $em->flush();

                    $data = array(
                        'status'=> 'success',
                        'code'=> 200,   
                        'msg'=>'User updated Successfully'
                    );
                }else {
                    $data['code']=404;
                    $data['msg']='User not found';
                }
            }
        }
        
        return $helpers->toJson($data)->setStatusCode($data['code']);
    }

    public function uploadAvatarAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user_repo= $em->getRepository('BackendBundle:User');
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

        //Buscar id del usuario activo en db y @return null || User
        $user= $helpers->currentUser($token);

        //upload image
        $file = $request->files->get("image");

        if(!empty($file) && $file != null){
            $ext = $file->guessExtension();
            if($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif'){

                $file_name = time() . ".". $ext;
                $file->move('uploads/users', $file_name);
    
                //Delete old image
                $helpers->removeFile($user->getImage(),'uploads/users');
    
                $user->setImage($file_name);
    
                //Update User in db
                $em->persist($user);
                $em->flush();
    
                $data = array(
                    'status'=> 'success',
                    'code'=> 200,   
                    'msg'=>'Image uploaded'    
                );
            }else{
                $data['msg']='Invalid Image Type';
            }
        }


        return $helpers->toJson($data)->setStatusCode($data['code']);
    }
    
    public function channelAction(Request $request, $user_id)
    {
        $helpers = $this->get("app.helpers");

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('BackendBundle:User')->findOneBy(array('id'=> $user_id));

        $data = array(
            'status'=> 'error',
            'code'=> 400,   
            'msg'=>'file not uploded'    
        );

        if (!is_object($user)) {
            $data['msg']='Channel not found';
            $data['code']=404;
            return $helpers->toJson($data)->setStatusCode($data['code']);
        }

        $dql = "SELECT v FROM BackendBundle:Video v WHERE v.user='$user_id' ORDER BY v.id DESC";
        $query = $em->createQuery($dql);

        $page = $request->query->getInt("page",1);
        $paginator = $this->get('knp_paginator');
        $items_per_page = 6;

        $pagination = $paginator->paginate($query, $page, $items_per_page);
        $total_items_count = $pagination->getTotalItemCount();

        $user_info = array(
            'name' => $user->getName(),
            'surname' => $user->getSurName(),
            'email' => $user->getEmail(),
            'image' => $user->getImage(),
        );

        $data = array(
            'status'=> 'success',
            'code'=> 200,   
            'msg'=>'Video List',
            'page'=> $page,
            'Total_Items'=> $total_items_count,
            'Items_per_Page'=> $items_per_page,
            'Items'=> count($pagination),
            "total_pages"=> ceil($total_items_count/$items_per_page),
            "user_info"=> $user_info,
            'data'=> $pagination  
        );


        return $helpers->toJson($data)->setStatusCode($data['code']);

    }
}
