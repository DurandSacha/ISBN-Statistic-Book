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
    
            dump($statusCode = $response->getStatusCode());
            dump('Trouver la regex BSR');
            $content = $response->getContent();
            //dump($content);
            /*
            <tr id="amazon-sales-rank-detail">
            <td class="a-span3">
            Classement des meilleures ventes
            </td>
            <td class="a-span9">
            <span>

            <span>553,058 en Livres (<a href='/gp/bestsellers/books/ref=pd_zg_ts_books'>Voir les 100 premiers en Livres</a>)</span>
            <br>
            */
            $bsr = preg_match_all("#^<span>[1-999],[1-999]en Livres#", $content);
            dd($bsr[0]);

            $book->setBSR($bsr);
            $em->persist($book);
            $em->flush();

            return $content;

            // update Database
        }

        return $this->render('index.html.twig', [
            'books' => $books
        ]);
    }
}
