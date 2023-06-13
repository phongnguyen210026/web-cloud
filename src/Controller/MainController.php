<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function home(UserRepository $repo, CategoryRepository $cat_repo, ProductRepository $pro_repo): Response
    {
        $firstName = null;
        $user = $this->getUser();
        if($user != null){
            $firstName = $user->getFirstName();
        }
        $cat = $cat_repo->findAll();
        $product = $pro_repo->findBy([], null, 8);
        return $this->render('main/home.html.twig', ['firstName'=>$firstName, 'cat'=>$cat, 'product'=>$product]);
    }

    /**
     * @Route("/search", name="app_search")
     */
    public function search(Request $req, CategoryRepository $cat_repo, ProductRepository $repo): Response
    {
        $param = $req->request->get('content');
        $result = $repo->searchProduct($param);
        $user = $this->getUser();
        $firstName = $user->getFirstName();
        $cat = $cat_repo->findAll();
        return $this->render('main/search.html.twig', ['search_content'=>$param, 'result'=>$result,
        'firstName'=>$firstName, 'cat'=>$cat]);
    }
}
