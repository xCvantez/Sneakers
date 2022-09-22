<?php

namespace App\Controller;

use DateTime;
use App\Classe\Cart;
use App\Entity\Order;
use App\Form\OrderType;
use App\Entity\OrderDetails;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/commande', name: 'order')]
    public function index(Cart $cart): Response
    {
        // Si l'utilisateur n'a pas d'adresses ALORS
        if (!$this->getUser()->getAddresses()->getValues()) {
            // On le redirige vers la page d'ajout d'adresse
            return $this->redirectToRoute('account_address_add');
        }

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);




        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'cart' => $cart->getFull()
        ]);
        // var_dump($cart);
    }

    #[Route('/commande/recapitulatif', name: 'order_recap')]
    public function add(Cart $cart, Request $request): Response
    {
        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        // Ecoute la requête
        $form->handleRequest($request);

        // SI le formulaire est soumis ET le formulaire est valide ALORS
        if ($form->isSubmitted() && $form->isValid()) {
            $order = new Order();
            $date = new DateTime();

            // Récupère le champ du formulaire
            $carriers = $form->get('carriers')->getData();
            $delivery = $form->get('addresses')->getData();

            $delivery_content = $delivery->getFirstname().' '.$delivery->getLastname();
            $delivery_content .= '<br>'.$delivery->getPhone();

            if ($delivery->getCompany()) {
                $delivery_content .= '<br>'.$delivery->getCompany();
            }

            $delivery_content .= '<br>'.$delivery->getAddress();
            $delivery_content .= '<br>'.$delivery->getPostal().' '.$delivery->getCity();
            $delivery_content .= '<br>'.$delivery->getCountry();

            // dd($delivery_content);

            // dd($carriers);


            $reference = $date->format('dmY').'-'.uniqid();
            $order->setReference($reference);

            // Enregistrer ma commande Order()
            $order->setUser($this->getUser());

            $order->setCreatedAt($date);

            // Définit le nom de carriers
            $order->setCarrierName($carriers->getName());

            // Définit le prix de carriers
            $order->setCarrierPrice($carriers->getPrice());

            $order->setDelivery($delivery_content);

            $order->setIsPaid(0);

            // Fige la data
            $this->entityManager->persist($order);

            // Pour chaque produit que j'ai dans mon panier
           

            foreach($cart->getFull() as $product){

                // Enregistrer mes produits OrderDetails()
                $orderDetails = new OrderDetails();
                $orderDetails->setMyOrder($order);
                $orderDetails->setProduct($product['product']->getName());
                $orderDetails->setQuantity($product['quantity']);
                $orderDetails->setPrice($product['product']->getPrice());
                $orderDetails->setTotal($product['product']->getPrice() * $product['quantity']);
               
                // Fige la data
                $this->entityManager->persist($orderDetails);


            }

                $this->entityManager->flush();

                // STRIP fait le lien entre la banque et notre site :D

    
                    
                    // dump($checkout_session->id);
                    // dd($checkout_session);
           
            return $this->render('order/add.html.twig', [
                'cart' => $cart->getFull(),
                'carrier' => $carriers,
                'delivery' => $delivery_content,
                'reference' => $order->getReference()
            ]);
            
        }

        return $this->redirectToRoute('cart');
            
    }
        
}