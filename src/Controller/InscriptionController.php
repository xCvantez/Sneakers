<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
// On accède à doctrine 
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class InscriptionController extends AbstractController
{
    private $entityManager; // On stock tous dans une classe pour sécuriser les données.

    // On accède à doctrine
    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    #[Route('/inscription', name: 'register')]
    // Injection de  dépendance (ManagerRegistry pour envoyer des données en DB)
    // Quand tu exécute cette fonction, tu appelles la request, Userpassqordhasherinterface pour exécute le code suivant.
    public function index(Request $request, UserPasswordHasherInterface $encoder)
    {
        $user = new User(); // J'aurai un nouvel utilisateur
        $form = $this->createForm(RegisterType::class, $user); // Création du formulaire

        // Ecoute la requête
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // On ajoute à notre instance user les données du formulaire
            $user = $form->getData();

            // On hash/Encode le mot de passe
            $password = $encoder->hashPassword($user, $user->getPassword());

            // Tu réinjectes le password encodé
            $user->setPassword($password);

            // Fige la data pour l'enregistrer
            $this->entityManager->persist($user); 

            // Exécute
            $this->entityManager->flush(); 
        }
        
        return $this->render('inscription/index.html.twig', [
            'form' => $form->createView() // Création de la vue 
        ]);
    }
}
