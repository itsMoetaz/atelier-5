<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    #[Route('/book', name: 'app_book')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }
    #[Route('/afficher', name:'afficher')]
    public function fetch(BookRepository $repo)
    {$result=$repo->findAll();
    return $this->render('book/afficherbook.html.twig',['response'=>$result]);
    
    }
    #[Route('/afficher1', name:'afficher1')]
    public function fetch1(BookRepository $repo)
    {$result=$repo->findBy(['published' => true]);
    return $this->render('book/afficherbook.html.twig',['response'=>$result]);
    
    }
    #[Route('/addbook',name:'addb')]
    public function add(ManagerRegistry $mr,BookRepository $rep,Request $req):Response
    {   
        $s=new Book();//1 instance    update 
        $form=$this->createForm(BookType::class,$s);
        $form->handleRequest($req);
        if($form->isSubmitted()){
      
      $author = $s->getAuthor();
    $author->setNbBooks($author->getNbBooks()+1);
        $em=$mr->getManager();//3 persist+flush
        $em->persist($s);
        $em->flush();
        return $this->redirectToRoute('afficher');   }
        return $this->render('book/ajouterbook.html.twig',[
            'f'=>$form->createView() //update ttbadel
        ]);     
}
#[Route('/afficherpublish', name: 'afficherpublish')]
public function fetchpublished(BookRepository $repo): Response
{
    $result = $repo->findBy(['published' => 1]);
    
    return $this->render('book/afficherbook.html.twig', ['response' => $result]);
}

#[Route('/show/{ref}', name: 'showbook')]
public function authordetail( Book $book): Response
    {  
        return $this->render('book/showbook.html.twig', ['book' => $book]);
    }
#[Route('/update/{ref}',name:'update')]
public function modif(int $ref,Request $req,EntityManagerInterface $em):Response

{
    $Book = $em->getRepository(Book::class)->find($ref);

    $form = $this->createForm(BookType::class, $Book);
    $form->handleRequest($req);
    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();

        return $this->redirectToRoute('afficher');
    }

    return $this->render('book/update.html.twig', [
        'f' => $form->createView(),
    ]);}
    #[Route('/deletebook/{ref}', name: 'deletebook')]
    public function delete(Book $book, ManagerRegistry $mr): Response
    {
        $em = $mr->getManager();
        $em->remove($book);
        $em->flush();
        return $this->redirectToRoute('afficher');
    }
//qb
#[Route('/dql1', name: 'dql1')]
    public function dql1Student(BookRepository $repo,Request $request):Response
{//on utilise la fonction li aamalneha fl repository
     $result=$repo->findAll();
    if($request->isMethod('post')){
        $value=$request->get('search');
        $result=$repo->searchBookByRef($value);
     //   dd($result);
}return $this->render('book/afficherbook.html.twig', [
    'response' => $result,
]);}
#[Route('/booka', name: 'booka')]

public function listAuthorsByEmail(BookRepository $br): Response
{
    $br = $br->BooklistAuthor();

    return $this->render('book/afficherbook.html.twig', [
        'response' => $br,
    ]);
}
#[Route('/list', name: 'list')]

public function listBooksPublishedBefore2023WithAuthorsHavingMoreThan10Books(BookRepository $bookRepository): Response
{
    $books = $bookRepository->createQueryBuilder('b')
        ->join('b.author', 'a')
        ->where('b.publicationDate < :date')
        ->andWhere('a.nb_books > 1')

        ->setParameter('date', new \DateTime('2023-01-01'))
        ->getQuery()
        ->getResult();
    return $this->render('book/afficherbook.html.twig', ['response' => $books]);
}
#[Route('/up', name: 'up')]

public function updateCategoryFromScienceFictionToRomance(EntityManagerInterface $entityManager): Response
{
    // Utilisez le repository pour récupérer les livres de catégorie "Science Fiction"
    $bookRepository = $entityManager->getRepository(Book::class);
    $sfBooks = $bookRepository->createQueryBuilder('b')
        ->where('b.category = :oldCategory')
        ->setParameter('oldCategory', 'Science-Fiction')
        ->getQuery()
        ->getResult();

    // Modifiez la catégorie de chaque livre
    foreach ($sfBooks as $book) {
        $book->setCategory('Romance');
    }

    // Enregistrez les modifications en base de données
    $entityManager->flush();

    return $this->redirectToRoute('afficher'); // Redirigez vers la liste des livres
}
#[Route('/count', name: 'count')]
public function count(EntityManagerInterface $entityManager): Response
{$dql = "SELECT COUNT(b.ref) as bookCount FROM App\Entity\Book b WHERE b.category = 'Romance'";
        $query = $entityManager->createQuery($dql);
        $result = $query->getSingleScalarResult();
        //dd($result);

        //return $this->redirectToRoute('afficher');
        return $this->render('book/afficherbook1.html.twig', ['bookCount' => $result]);
}
#[Route('/dql2', name: 'dql2')]
    public function dql2(EntityManagerInterface $em):Response
{ $startDate =  new \DateTimeImmutable('2014-01-01');
    $endDate =  new \DateTimeImmutable('2018-01-01');
   // $req=$em->createQuery('select s.nom from App\Entity\Student s Order by s.nom DESC');
    //$req=$em->createQuery('select s.nom ,c.name from App\Entity\Student s join s.Classroom c');
    $req=$em->createQuery("SELECT b FROM App\Entity\Book b WHERE b.publicationDate BETWEEN :startDate AND :endDate")
    ->setParameter('startDate', $startDate)
    ->setParameter('endDate', $endDate);
    $result=$req->getResult();
    //dd($result);
    return $this->render('book/afficherbook.html.twig', ['response' => $result]);

}


}