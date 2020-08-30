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
                // Find BSR
                preg_match_all("#[1-9]{3}+,[1-9]{3} en Livre#", $content, $resultBsr);
                $bsr =str_replace(' en Livre', '', $resultBsr[0][0]);
                //dd($bsr);
                if($bsr){
                    $book->setBSR($bsr);
                }
                else{
                    $book->setBSR($bsr); 
                }

                /*
                <span id="productTitle" class="a-size-extra-large"> \n
                Carnet de santé cochon d'inde: Livre de santé pour suivre son cochon d'inde \n
                </span> \n
                */

                //Find title ( before : and after <span id=\"productTitle\" class=\"a-size-extra-large\"> )
                preg_match("#<span id=\"productTitle\" class=\"a-size-extra-large\">\n([a-zA-Z0-9é'_\s]){15,}#", $content, $resultTitle);
                $title = str_replace('<span id="productTitle" class="a-size-extra-large">', '', $resultTitle[0]);
                $titleSolo = str_replace('\n', '', $title);
                //dd($title[0]);
                //dd($titleSolo);
                if($titleSolo){
                    $book->setTitle($titleSolo);
                    $em->persist($book);
                    $em->flush();
                }
                else{
                    $book->setTitle('no title');
                    $em->persist($book);
                    $em->flush();
                }
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
