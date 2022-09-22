<?php

namespace App\Classe;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

Class Cart
{

    private $requestStack;
    private $entityManager;
    // private$entityManager;
    public function __construct(RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
    }


    // Affiche le panier 
    public function get(){

        $session=$this->requestStack->getSession();

        return $session->get('cart');
    }

    // Supprime le panier
    public function remove(){

        $session=$this->requestStack->getSession();

        return $session->remove('cart');
    }


    public function add($id)
    {

        $session=$this->requestStack->getSession();

        $cart=$session->get('cart',[]);

        if(!empty($cart[$id])){

            $cart[$id]++;

        }else{
           
            $cart[$id]=1;
        }



        $session->set('cart',$cart);

    }

    public function decrease($id)
    {

        $session=$this->requestStack->getSession();

        $cart=$session->get('cart',[]);

        if($cart[$id] > 1){

            $cart[$id]--;

        }else {
            unset($cart[$id]);
        }
        

        $session->set('cart',$cart);

    }

    



    // je lance une session 


    public function delete($id)
    {
                
        $session=$this->requestStack->getSession();

        // je recup les info de ma session dans une nouvelle variable $cart
        $cart=$session->get('cart',[]);
        
        // Unset permet de supprimé l'élément correspondant dans le  tableau
        unset($cart[$id]);
        // définir la nouvelle valeur dans la session :D
       $session->set('cart',$cart);

    }

    public function getFull()
  {
    $cartComplete = [];

    if($this->get()) {
      foreach ($this->get() as $id => $quantity) {
        // Je récupère l'ID du produit en base de données
        $product_object = $this->entityManager->getRepository(Product::class)->findOneById($id);

        // SI le produit n'existe pas
        if (!$product_object) {
          // On le supprime du panier
          $this->delete($id);
          continue;
        }

        $cartComplete[] = [
          'product' => $product_object,
          'quantity' => $quantity
        ];
      } 
    }

    return $cartComplete;

  }

}

?>