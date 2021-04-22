<?php

namespace AppBundle\Services;

use Firebase\JWT\JWT;
use Doctrine\ORM\EntityManagerInterface;

class JwtAuth {
    public $em;
    public $key;

    public function __construct(EntityManagerInterface $manager) {
        $this->em = $manager;
        $this->key = 'mypri-vatekey*forsymfony';
    }

    public function signup($email, $password, $getHash = null) {

        $user = $this->em->getRepository('BackendBundle:User')->findOneBy(
            array(
                'email'=>$email,
                'password'=>$password
            )
        );


        $signup = false;
        if (is_object($user)) {
            $signup = true;
        };

        if ($signup) {

            $dataToken = array(
                'sub'=>$user->getId(),
                'email' =>$user->getEmail(),
                'name' =>$user->getName(),
                'surname' =>$user->getSurname(),
                'image' =>$user->getImage(),
                'iat' =>time(),
                'exp' =>time() + (7*24*60*60),
            );

            $token = JWT::encode($dataToken, $this->key);
            $decodedToken = JWT::decode($token, $this->key, array('HS256'));

            if($getHash != null){
                return array(
                    'status'=> $signup,
                    'code'=> 200,   
                    'msg'=>'Login user info',
                    'data' => $decodedToken
                );
            }

            return array(
                'status'=> $signup,
                'code'=> 200,   
                'msg'=>'Login success',
                'token' => $token
            );
        }else {
            return array(
                'status'=> $signup,
                'code'=> 400,   
                'msg'=>'Login failed',
            );
        }
    }

    public function validateJwt($token, $getJwtData=false) {
        $authResponse = false;

        try {
            $decodedToken = JWT::decode($token, $this->key, array('HS256'));
            
        } catch (\UnexpectedValueException $err) {
            $authResponse= false;
        } catch (\DomainException $err) {
            $authResponse= false;
        }

        if(isset($decodedToken) && isset($decodedToken->sub)) {
            $authResponse= true;
        }

        if ($authResponse && $getJwtData) {
            $authResponse = $decodedToken;
        }

        return $authResponse;
    
    }
}