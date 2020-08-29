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
                preg_match_all("#[1-9]{3}+,[1-9]{3}#", $content, $resultBsr);
                $bsr = $resultBsr[0][42];
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
                preg_match_all("#<span id=\"productTitle\" class=\"a-size-extra-large\">\n([a-zA-Z0-9é'_\s]){15,}#", $content, $resultTitle);
                $title = str_replace('<span id="productTitle" class="a-size-extra-large">', '', $resultTitle[0]);
                //$title = str_replace('\n', '', $title);
                //dd($title[0]);
                //dd($title);
                $book->setTitle($title);
                
                $em->persist($book);
                $em->flush();
            }
            else{
                $book->setBSR('no data');
                $em->persist($book);
                $em->flush();
            }
        }

        return $this->render('index.html.twig', [
            'books' => $books
        ]);
    }
}
