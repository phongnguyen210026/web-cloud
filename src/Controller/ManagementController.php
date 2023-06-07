<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Form\CategoryType;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ManagementController extends AbstractController
{
    /**
     * @Route("/category-manage", name="show_cat_manage")
     */
    public function showCatManage(CategoryRepository $repo): Response
    {
        $cat = $repo->findAll();
        $user = $this->getUser();
        $firstName = $user->getFirstName();

        return $this->render('management/index.html.twig', ['firstName'=>$firstName, 'category'=>$cat, 'cat'=>$cat]);
    }

    /**
     * @Route("/cat-add", name="cat_insert")
     */
    public function addCat(Request $req, CategoryRepository $repo): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid()){
            $repo->save($category, true);
            return $this->redirectToRoute('show_cat_manage');
        }
        $cat = $repo->findAll(); 
        $user = $this->getUser();
        $firstName = $user->getFirstName();
        return $this->render('management/categoryForm.html.twig', ['firstName'=>$firstName, 'catForm'=>$form->createView()
        , 'cat'=>$cat]);
    }

    /**
     * @Route("/cat-edit/{id}", name="cat_update")
     */
    public function editCat(Request $req, Category $category, CategoryRepository $repo): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid()){
            $repo->save($category, true);
            return $this->redirectToRoute('show_cat_manage');
        }

        $cat = $repo->findAll(); 
        $user = $this->getUser();
        $firstName = $user->getFirstName();
        return $this->render('management/categoryForm.html.twig', ['catForm'=>$form->createView(), 
        'cat'=>$cat, 'firstName'=>$firstName]);
    }

    /**
     * @Route("/cat-remove/{id}", name="cat_delete")
     */
    public function deleteCat(Category $cat, CategoryRepository $repo): Response
    {
        $repo->remove($cat, true);
        return $this->redirectToRoute('show_cat_manage');
    }

    /**
     * @Route("/cat-search", name="cat_search")
     */
    public function searchCat(Request $req, CategoryRepository $repo): Response
    {
        $param = $req->request->get('content');
        $category = $repo->searchCat($param);
        $cat = $repo->findAll();
        $user = $this->getUser();
        $firstName = $user->getFirstName();
        return $this->render('management/index.html.twig', ['cat'=>$cat, 'category'=>$category, 'firstName'=>$firstName]);
    }

    /**
     * @Route("/product-manage", name="show_product_manage")
     */
    public function showProductManage(CategoryRepository $cat_repo, ProductRepository $repo): Response
    {
        $product = $repo->findAllProduct();
        $cat = $cat_repo->findAll();
        $user = $this->getUser();
        $firstName = $user->getFirstName();
        return $this->render('management/product.html.twig', ['cat'=>$cat, 'firstName'=>$firstName, 'product'=>$product]);
    }

    /**
     * @Route("/product-add", name="product_insert")
     */
    public function addProduct(Request $req, SluggerInterface $slugger, ProductRepository $repo, CategoryRepository $cat_repo): Response
    {
        $p = new Product();
        $form = $this->createForm(ProductType::class,$p);

        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid()){
            $imgFile = $form->get('file')->getData();
            if ($imgFile) {
                $newFilename = $this->uploadImage($imgFile,$slugger);
                $p->setImage($newFilename);
            }
            $repo->save($p,true);
            return $this->redirectToRoute('show_product_manage', [], Response::HTTP_SEE_OTHER);
        }
        $cat = $cat_repo->findAll();
        $user = $this->getUser();
        $firstName = $user->getFirstName();
        return $this->render('management/productForm.html.twig', ['cat'=>$cat, 'firstName'=>$firstName, 
        'productForm'=>$form->createView()]);
    }

    public function uploadImage($imgFile, SluggerInterface $slugger): ?string{
        $originalFilename = pathinfo($imgFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$imgFile->guessExtension();
        try {
            $imgFile->move(
                $this->getParameter('image_dir'),
                $newFilename
            );
        } catch (FileException $e) {
            echo $e;
        }
        return $newFilename;
    }

    /**
     * @Route("/product-edit/{id}", name="product_update")
     */
    public function editProduct(Request $req, Product $p, CategoryRepository $cat_repo, SluggerInterface $slugger, ProductRepository $repo): Response
    {
        $form = $this->createForm(ProductType::class,$p);

        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid()){
            $imgFile = $form->get('file')->getData();
            if ($imgFile) {
                $newFilename = $this->uploadImage($imgFile,$slugger);
                $p->setImage($newFilename);
            }
            $repo->save($p,true);
            return $this->redirectToRoute('show_product_manage', [], Response::HTTP_SEE_OTHER);
        }
        $cat = $cat_repo->findAll();
        $user = $this->getUser();
        $firstName = $user->getFirstName();
        return $this->render('management/productForm.html.twig', ['cat'=>$cat, 'firstName'=>$firstName, 
        'productForm'=>$form->createView()]);
    }

    /**
     * @Route("/product-remove/{id}", name="product_delete")
     */
    public function deleteProduct(Product $p, ProductRepository $repo): Response
    {
        $repo->remove($p, true);
        return $this->redirectToRoute('show_product_manage');
    }

    /**
     * @Route("/product-search", name="product_search")
     */
    public function searchProduct(Request $req, CategoryRepository $cat_repo, ProductRepository $repo): Response
    {
        $param = $req->request->get('content');
        $result = $repo->searchProduct($param);
        $cat = $cat_repo->findAll();
        $user = $this->getUser();
        $firstName = $user->getFirstName();
        return $this->render('management/product.html.twig', ['cat'=>$cat, 'firstName'=>$firstName, 'product'=>$result]);
    }
}
