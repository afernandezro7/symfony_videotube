<?php

namespace AppBundle\Services;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class Helpers {
    public $jwt_auth;
    public $em;
    public $rootDir;
    
    public function __construct( $jwt_auth, $manager, $rootDir ) {
        $this->jwt_auth = $jwt_auth;
        $this->em = $manager;
        $this->rootDir = $rootDir;
    }

    public function authCheck($token, $getJwtData=false)
    {
        $jwt_auth = $this->jwt_auth;

        return $jwt_auth->validateJwt($token, $getJwtData);
    }

    public function toJson($data)
    {       
        $normalizers = array(new GetSetMethodNormalizer());
        $encoders = array("json"=> new JsonEncoder());
        $serializer = new Serializer($normalizers,$encoders);
        $json = $serializer->serialize($data, 'json');
        
        $response = new Response();
        $response->setContent($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function currentUser($token)
    {       
        $userIdentity = $this->authCheck($token,true)->sub;
        $user_repo= $this->em->getRepository('BackendBundle:User');
        $user= $user_repo->findOneBy(array('id'=>$userIdentity));
        
        return $user;
    }

    public function removeFile($filename, $folder="")
    {
        $fs = new Filesystem();
        $uploadRootDir = $this->rootDir;
        $file_path = $uploadRootDir . '/../web/' . $folder.'/'.$filename;

        // if(file_exists($file_path)) unlink($file_path);
        if ($fs->exists($file_path)){ $fs->remove($file_path);}

    }
}