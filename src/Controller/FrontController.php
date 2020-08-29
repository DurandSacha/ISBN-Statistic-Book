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

            dump($content);

            if($response->getStatusCode() == 200){
                // Find BSR
                preg_match_all("#[1-9]{3}+,[1-9]{3}#", $content, $resultBsr);
                $bsr = $resultBsr[0][42];
                $book->setBSR($bsr);

                //Find title
                dd($content);
                preg_match_all("#title#", $content, $resultTitle);
                dd($resultTitle[0]);


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
