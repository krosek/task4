<?php


namespace App\Controller;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class MainController extends BaseController
{
    /**
     * @Route("/main", name="main")
     */
    public function index()
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        $forRender = parent::renderDefault();
        $forRender['title'] = 'Users';
        $forRender['users'] = $users;

        $user = $this->getUser();
        $user->setLastlogin(new \DateTime('now'));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->render('main/index.html.twig', $forRender);
    }
    /**
     * @Route("/blocked", name="blocked")
     */
    public function blocked(Request $request)
    {
        $params = $request->request->all();
        $currentUser = $this->getUser();
        foreach ($params as $param){
            $user = $this->getDoctrine()->getRepository(User::class)->find($param);
            $user->setStatus('blocked');
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            if($user == $currentUser)
            {
                return $this->redirect('logout');
            }
        }

        return $this->redirect('main');
    }
    /**
     * @Route("/unblocked", name="unblocked")
     */
    public function unblocked(Request $request)
    {
        $params = $request->request->all();
        foreach ($params as $param){
            $user = $this->getDoctrine()->getRepository(User::class)->find($param);
            $user->setStatus('not blocked');
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
        }
        return $this->redirect('main');
    }
    /**
     * @Route("/delete", name="delete")
     */
    public function delete(Request $request, SessionInterface $session)
    {
        $params = $request->request->all();
        $currentUser = $this->getUser();
        foreach ($params as $param){
            $item = $this->getDoctrine()->getManager();
            $user = $item->getRepository(User::class)->find($param);
            if($user == $currentUser) {
                $item->remove($user);
                $item->flush();
                $this->get('security.token_storage')->setToken(null);
                $session->invalidate();
            }
            $item->remove($user);
            $item->flush();
        }
        return $this->redirect('main');
    }
}