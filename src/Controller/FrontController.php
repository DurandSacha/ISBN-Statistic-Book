<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FrontController extends AbstractController
{
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @Route("/", name="front")
     */
    public function index(EntityManagerInterface $em, BookRepository $BookRepository, HttpClientInterface $client)
    {
        $books = $BookRepository->findAll();
        // link with ASIN: https://www.amazon.fr/dp/B08DSS7YYC

        foreach($books as $book){

            $response = $this->client->request(
                'GET',
                'https://www.amazon.fr/dp/' . $book->getASIN()
            );

            $content = $response->getContent();

            //dump($content);

            if($response->getStatusCode() == 200 || $response->getStatusCode() == 301){
                // Find BSR broché
                preg_match("#<span>[1-9]{3}+,[1-9]{3} en Livres#", $content, $resultBsr);
                $bsr = str_replace(' en Livres', '', $resultBsr);
                $bsr = str_replace('<span>', '', $bsr);
                $bsr = implode($bsr);
                // ASIN Book culture immergée : B07KPT4VJX
                // carnet de santé gecko : B089M41Y91
                if($bsr){
                    $book->setBSR($bsr);
                    $em->persist($book);
                    $em->flush();
                }
                else{

                    // rechercher avec les Ebooks
                    preg_match("#<span>[1-9]{3}+,[1-9]{3} en Boutique Kindle#", $content, $resultBsrEbook);
                    //dd($resultBsr);
                    $bsr = str_replace(' en Boutique Kindle', '', $resultBsrEbook);
                    $bsr = str_replace('<span>', '', $bsr);
                    $bsr = implode($bsr);
                    if($bsr){
                        $book->setBSR($bsr);
                    }
                    else{
                        $book->setBSR('no BSR'); 
                    }
                    $em->persist($book);
                    $em->flush();
                }

                //Find title ( before : and after <span id=\"productTitle\" class=\"a-size-extra-large\"> )
                preg_match("#<span id=\"productTitle\" class=\"a-size-extra-large\">\n([a-zA-Z0-9é'_\s]){15,}#", $content, $resultTitle);
                $title = str_replace('<span id="productTitle" class="a-size-extra-large">', '', $resultTitle);
                //dd($resultTitle);
                $titleSolo = str_replace('\n', '', $title);
                $titleSolo = implode($titleSolo);
                if($titleSolo){
                    $book->setTitle($titleSolo);
                }
                else{
                    $book->setTitle('no title');
                }
                $em->persist($book);
                $em->flush();
            }
            else{
                $book->setBSR('Request 404');
                $book->setTitle('No title ( 404 )');
                $em->persist($book);
                $em->flush();
            }
        }

        return $this->render('index.html.twig', [
            'books' => $books
        ]);
    }
}
