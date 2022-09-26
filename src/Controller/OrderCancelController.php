<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderCancelController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/commande/erreur/{stripeSessionId}', name: 'order_cancel')]
    public function index($stripeSessionId ): Response
    {
        $order = $this->entityManager->getRepository(Order::class)
        ->findOneByStripeSessionId($stripeSessionId);
        

        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }
            // SI la commande est en statut NON payé
        
       
        //Afficher les quelques informations de la commande de l'utilisateur
        // dd($order);

        return $this->render('order_cancel/index.html.twig', [
            'order' => $order
        ]);
    }
}
