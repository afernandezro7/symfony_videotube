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
        echo 'video';
        die();
    }


}
