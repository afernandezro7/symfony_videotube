<?php

namespace AppBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Validator\Constraints\Email;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }

    public function loginAction(Request $request){
        $helpers = $this->get("app.helpers");
        $jwt_auth = $this->get("app.jwt_auth");

        //$json = $request->get('json',null);
        $json= json_decode($request->getContent(),true);

        $data = array(
            'status'=> false,
            'code'=> 400,   
            'msg'=>'Login incorrect or invalid'    
        );


        if(is_array($json)){
            $email= isset($json['email']) ? $json['email'] :null;
            $password= isset($json['password']) ? $json['password']: null;
            $getHash= isset($json['getHash']) ? $json['getHash']: null;

            $emailConstraint = new Email();
            $emailConstraint->message = "This email is nost valid";
            $validate_email = $this->get('validator')->validate($email,$emailConstraint);

            if($email != null && count($validate_email)==0 && $password != null ){
                $pwd = hash('sha256', $password);
                $data = $jwt_auth->signup($email, $pwd, $getHash);                                  
                
            }
        }

        return new JsonResponse($data, $data['code']);
    }
    
    public function pruebasAction(Request $request)
    {
        $helpers = $this->get("app.helpers");
        $token = $request->headers->get('x-token',null);

        
        $resp = $helpers->authCheck($token, true);
        var_dump($resp);
        die();
        // return new JsonResponse($data, $data['code']);
    }

    
}
