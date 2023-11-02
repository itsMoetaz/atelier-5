<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    #[Route('/author', name: 'app_author')]
    public function index(): Response
    {
        return $this->render('author/index.html.twig', [
            'controller_name' => 'AuthorController',
        ]);
    }

    #[Route('/fetch', name:'fetch')]
public function fetch(AuthorRepository $repo)
{$result=$repo->findAll();
return $this->render('author/afficher.html.twig',['response'=>$result]);

}
#[Route('/ajoutstat', name:'ajoutstat')]

public function ajouterstatique(EntityManagerInterface $em)
{
    // Créez un nouvel auteur avec des données statiques
    $auteur = new Author();
    $auteur->setUsername("Doe");
    $auteur->setEmail("Doe@mail..com");

    $em->persist($auteur);
    $em->flush();

    return $this->redirectToRoute('fetch'); // Redirigez vers la liste des auteurs
}

#[Route('/add',name:'add')]
public function add(ManagerRegistry $mr,Request $req):Response
{   
    $s=new Author();//1 instance    update 
    $form=$this->createForm(AuthorType::class,$s);
    $form->add('save_me',SubmitType::class);
    $form->handleRequest($req);
    if($form->isSubmitted()){
    $em=$mr->getManager();//3 persist+flush
    $em->persist($s);
    $em->flush();
    return $this->redirectToRoute('fetch');   
}       

    return $this->render('author/ajouter.html.twig',[
        'f'=>$form->createView() //update ttbadel
    ]);
   
}
#[Route('/modifier/{id}',name:'modifier_Author')]
public function modif(int $id,Request $req,EntityManagerInterface $em):Response

{
    $Author = $em->getRepository(Author::class)->find($id);

    $form = $this->createForm(AuthorType::class, $Author);
    $form->handleRequest($req);
    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();

        return $this->redirectToRoute('fetch');
    }

    return $this->render('author/modif.html.twig', [
        'f' => $form->createView(),
    ]);}
    #[Route('/delete/{id}', name: 'deleteAuthor')]
    public function delete(Author $author, ManagerRegistry $mr): Response
    {
        $em = $mr->getManager();
        $em->remove($author);
        $em->flush();
        return $this->redirectToRoute('fetch');
    }
    #[Route('/qb', name: 'qb')]

    public function listAuthorsByEmail(AuthorRepository $authorRepository): Response
    {
        $authors = $authorRepository->listAuthormail();

        return $this->render('author/afficher.html.twig', [
            'response' => $authors,
        ]);
    }


    #[Route('/deletedql', name: 'deletedql')]

    public function deleteAuthorsWithNoBooks(EntityManagerInterface $entityManager): Response
    {
        $dql = "DELETE FROM App\Entity\Author a WHERE a.nb_books = 0";
        $query = $entityManager->createQuery($dql);
        $query->execute();

        return $this->redirectToRoute('fetch'); // Redirigez vers la liste des auteurs
    }
    #[Route('/minmax', name: 'minmax')]
    public function minmax(EntityManagerInterface $em,Request $request,AuthorRepository $repo):Response
    { $result = $repo->findAll();
        
        if ($request->isMethod('POST')) {
            $minValue = $request->get('min');
            $maxValue = $request->get('max');
    
            $dql = "SELECT a FROM App\Entity\Author a WHERE a.nb_books BETWEEN :min AND :max";
            $query = $em->createQuery($dql)
                ->setParameter('min', $minValue)
                ->setParameter('max', $maxValue);
    
            $result = $query->getResult();
        }
        dd($result);
    
        return $this->render('author/afficher.html.twig', ['response' => $result]);
    
    }
    
}

