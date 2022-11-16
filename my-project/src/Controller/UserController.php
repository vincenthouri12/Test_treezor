<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\LogRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class UserController extends AbstractController
{
    private function createLog($ip, $route, $date): Log
    {
        $log = new Log();
        $log->setIpAddress($ip);
        $log->setRoute($route);
        $log->setDate($date);
        return $log;
    }

    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, LogRepository $logRepository, Request $request): Response
    {
        $response = new Response($this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
            'logs' => $logRepository->findAll()
        ]));
        
        $response->headers->set('X-ROUTE-APP','/user');

        $log = $this->createLog($request->getClientIp(), '/user', new DateTime());
        $logRepository->save($log, true);

        return $response;

    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository, LogRepository $logRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $user->setStatus(true);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);
            $response = new Response($this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER));
        
            $response->headers->set('X-ROUTE-APP','/user/new');

            $log = $this->createLog($request->getClientIp(), '/user/new', new DateTime());
            $logRepository->save($log, true);
            
            return $response;
        }

        $response = new Response($this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]));
        
        $response->headers->set('X-ROUTE-APP','/user/new');
        
        $log = $this->createLog($request->getClientIp(), '/user/new', new DateTime());
        $logRepository->save($log, true);

        return $response;
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository, LogRepository $logRepository): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            $response = new Response($this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER));
            
            $response->headers->set('X-ROUTE-APP','/' . strval($user->getId()) . '/edit');

            $log = $this->createLog($request->getClientIp(), '/' . strval($user->getId()) . '/edit', new DateTime());
            $logRepository->save($log, true);

            return $response;

        }

        $response = new Response($this->renderForm('user/edit.html.twig', [
                'user' => $user,
                'form' => $form,
            ]));
            
        $response->headers->set('X-ROUTE-APP','/' . strval($user->getId()) . '/edit');

        $log = $this->createLog($request->getClientIp(), '/' . strval($user->getId()) . '/edit', new DateTime());
        $logRepository->save($log, true);

        return $response;

    }

     #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
     public function show(User $user, LogRepository $logRepository, Request $request): Response
     {
         $response = new Response( $this->render('user/show.html.twig', [
             'user' => $user,
         ]));
         
         $response->headers->set('X-ROUTE-APP','/' . strval($user->getId()));
         
         $log = $this->createLog($request->getClientIp(), '/' . strval($user->getId()), new DateTime());
         $logRepository->save($log, true);

         return $response;
 
     }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository, LogRepository $logRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $user->setStatus(false);
            $log = $this->createLog($request->getClientIp(), '/' . strval($user->getId()), new DateTime());
            $userRepository->remove($user, true);
        }

        $response = new Response($this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER));
        
        $response->headers->set('X-ROUTE-APP',  '/' . strval($user->getId()));

        $logRepository->save($log, true);

        return $response;

    }

}
