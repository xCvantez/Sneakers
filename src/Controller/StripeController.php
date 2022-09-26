<?php

namespace App\Controller;

use Stripe\Stripe;
use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\Product;
use Stripe\Checkout\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{
    #[Route('/commande/create-session{reference}', name: 'stripe_create_session')]
    public function index(EntityManagerInterface $entityManager, Cart $cart, $reference): Response
    {
        
        

        // le domaine 
        $products_for_stripe = [];
        $YOUR_DOMAIN = 'http://127.0.0.1:8000';

        $order = $entityManager->getRepository(Order::class)->findOneByReference($reference);
        if (!$order) {
            new JsonResponse(['error' => 'order']);
        }
        // On peut chercher orderDetails dans order comme il y'a une relation entre les deux
        foreach($order->getOrderDetails()->getValues() as $product){
            $product_objet = $entityManager->getRepository(Product::class)->findOneByName($product->getProduct());
        
        }
        
        // Produit
        foreach($cart->getFull() as $product){
            $products_for_stripe[] = [

                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => ($product['product']->getPrice()),
                    'product_data' => [
                        'name' => ($product['product']->getName()),
                        'images' => [$YOUR_DOMAIN."/uploads/".$product_objet->getIllustration()],
                    ],
            ],
            'quantity' => $product['quantity']

            ];
        }

        // Transporteur
        
            $products_for_stripe[] = [

                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $order->getCarrierPrice(),
                    'product_data' => [
                        'name' => $order->getCarrierName(),
                        'images' => ["https://i.imgur.com/EHyR2nP.png0"],
                    ],
            ],
            'quantity' => $product['quantity']

            ];

            

        Stripe::setApiKey('sk_test_51LmFv3J3ANsCVhy2zBr8UIx377JHgyTqKH4Cytu5d3YAcUmA9pDCCuEDsDsq0s8tnctblCyBeWIVrMdkXolJxgBX00EVnOAxnT');

        //Object
        $checkout_session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => [
                $products_for_stripe
                 ],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
          ]);
          //On ajoute à notre objet $order la session de stripe
          $order->setStripeSessionId($checkout_session->id);

          //Exécute
          $entityManager->flush();
        //   $response = new JsonResponse(['id' => $checkout_session->id]);
        //   return $response;

        return $this->redirect($checkout_session->url);
    }
}