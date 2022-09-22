<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Address;
use App\Form\AddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountAddressController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    #[Route('/compte/adresses', name: 'app_account_address')]
    public function index(): Response
    {
        // dd($this->getuser());
        return $this->render('account/address.html.twig');
    }

    #[Route('/compte/ajouter-une-adresse', name: 'app_address_add')]
    public function add(Cart $cart, Request $request): Response
    {
        //$address est l'objet de L'antité Address
        //Instance de classe = Objet
        $address = new Address;
        // Je passe en paramètres à ma fonction createForm() Le type du formulaire et L'objet
        $form = $this->createForm(AddressType::class, $address);
        // Ecoute la Requête
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setUser($this->getUser());
            //Fige la data
            $this->entityManager->persist($address);

            //Exécute 
            $this->entityManager->flush();
            // S'il y a un produit dans le panier
            if ($cart->get()) {
                // JE redirige vers commande
                return $this->redirectToRoute('order');
            } else {
                return $this->redirectToRoute('account_address');
            }
            
            return $this->redirectToRoute('app_account_address');
            // dd($address);
            
        }
        



        return $this->render('account/address_form.html.twig',[
            'form' => $form->createView()
        ]);
    }

    #[Route('/compte/modifier-une-adresse/{id}', name: 'app_address_edit')]
    public function edit(Request $request,$id): Response
    {
       //Je récupère l'adresse concerné à l'aide de doctrine en base de données
        $address = $this->entityManager->getRepository(Address::class)->findOneById($id);

            //Si il n'y a aucune adresse et ne correspont pas à celui qui est actuellement connecté à l'utilisateur
        if (!$address || $address->getUser() != $this->getUser()){
            return $this->redirectToRoute('app_account_address');
        }

        // Je passe en paramètres à ma fonction createForm() Le type du formulaire et L'objet
        $form = $this->createForm(AddressType::class, $address);
        // Ecoute la Requête
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setUser($this->getUser());
            //Fige la data

            //Exécute 
            $this->entityManager->flush();

            return $this->redirectToRoute('app_account_address');
            // dd($address);
        }
        



        return $this->render('account/address_form.html.twig',[
            'form' => $form->createView()
        ]);
    }
    //Je passe en paramètre à mon url l'id de l'adresse
    #[Route('/compte/supprimer-une-adresse/{id}', name: 'app_address_delete')]
    public function delete($id): Response
    {
        // je recup l'adresse concerné à l'aide de doctrine en base de donnés (id)
        $address = $this->entityManager->getRepository(Address::class)->findOneById($id);

        // Si il n'y a aucune adresse ou que l'utilisateur ne correspond pas à celui actuellement connecté
        if (!$address || $address->getUser() == $this->getUser()) {
            $this->entityManager->remove($address);
        }

            // dd($address);

            // Exécute
            $this->entityManager->Flush();
           return $this->redirectToRoute('app_account_address');

        }
    }
