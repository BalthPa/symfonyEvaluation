<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Produit;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    /**
     * @Route("/produits", name="produits")
     */
    public function produits(Request $request, EntityManagerInterface $entityManager)
    {
        $produit = new Produit();

        $produitRepository = $this->getDoctrine()->getRepository(Produit::class)->findAll();

        $formProduit = $this->createForm(ProduitType::class, $produit);
        $formProduit->handleRequest($request);

        if($formProduit->isSubmitted() && $formProduit->isValid()){
            $produit = $formProduit->getData();

            $image = $produit->getPhoto();
            $imageName = md5(uniqid()).'.'.$image->guessExtension();
            $image->move($this->getParameter('upload_files') , $imageName);
            $produit ->setPhoto($imageName);

            $entityManager->persist($produit);
            $entityManager->flush();
        }

        return $this->render('main/produits.html.twig', [
            'produits' => $produitRepository,
            'formProduits' => $formProduit->createView(),

        ]);
    }

    /**
     * @Route("/ficheProduit/{id}", name="ficheProduit")
     */
    public function ficheProduit()
    {
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

}
