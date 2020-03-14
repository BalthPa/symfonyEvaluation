<?php

namespace App\Controller;


use App\Entity\Panier;
use App\Form\AjoutPanierType;
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
        $articles = $this->getDoctrine()->getRepository(Panier::class)->findAll();

        $montantTotal = 0;

        foreach ($articles as $article){
            $montantTotal += ($article->getProduit()->getPrix() * $article->getQuantite());
        }

        return $this->render('main/index.html.twig', [
            'articles' => $articles,
            'montantTotal' => $montantTotal,
        ]);
    }

    /**
     * @Route("/removePanier/{id}", name="removePanier")
     */
    public function removePanier($id, Request $request, EntityManagerInterface $entityManager)
    {
        $panierRepository = $this->getDoctrine()->getRepository(Panier::class)->find($id);
        $entityManager->remove($panierRepository);
        $entityManager->flush();

        return $this->redirectToRoute('index');
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
    public function ficheProduit($id, Request $request,EntityManagerInterface $entityManager)
    {
        $produitFiche = $this->getDoctrine()->getRepository(Produit::class)->find($id);

        $produitId = $produitFiche->getId();

        $produitAjout = new Panier();

        $formAjoutPanier = $this->createForm(AjoutPanierType::class);
        $formAjoutPanier->handleRequest($request);

        if($formAjoutPanier->isSubmitted() && $formAjoutPanier->isValid()){
            $produitAjout = $formAjoutPanier->getData();

            $produitAjout->setProduit($produitFiche);
            $produitAjout->setDateAjout(new \DateTime());
            $produitAjout->setEtat(false);

            $entityManager->persist($produitAjout);
            $entityManager->flush();
        }

        return $this->render('main/ficheProduit.html.twig', [
            'produit' => $produitFiche,
            'formPanier' => $formAjoutPanier->createView(),
        ]);
    }

    /**
     * @Route("/removeProduit/{id}", name="removeProduit")
     */
    public function removeProduit($id, Request $request, EntityManagerInterface $entityManager)
    {
        $removeProduit = $this->getDoctrine()->getRepository(Produit::class)->find($id);
        $removeProduit->deleteFile();

        $entityManager->remove($removeProduit);
        $entityManager->flush();

        return $this->redirectToRoute('produits');
    }

}
