<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    #[Route('/tous-nos-produits', name: 'app_product')]
    public function index(): Response
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        // dd($products);

        return $this->render('product/index.html.twig', [
            'products' => $products
        ]);
    }

 // On passe le slug en paramètre dans l'URL
    #[Route('/produit/{slug}', name: 'product')]

    public function show($slug): Response {
     $product = $this->entityManager->getRepository(Product::class)->findOneBySlug($slug); // je récupère mes données à l'aide du repository et je recherche un produit à la place de son slug
     if(!$product){
         return $this->redirectToRoute('products'); // redirectionne vers la page de production en cas d'erreur dans l'URL
     }
     return $this->render('product/show.html.twig', [
         'product' => $product,
     ]);
 }
}
