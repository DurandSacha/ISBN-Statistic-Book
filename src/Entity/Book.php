<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BookRepository::class)
 */
class Book
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $ASIN;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $BSR;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getASIN(): ?int
    {
        return $this->ASIN;
    }

    public function setASIN(int $ASIN): self
    {
        $this->ASIN = $ASIN;

        return $this;
    }

    public function getBSR(): ?int
    {
        return $this->BSR;
    }

    public function setBSR(?int $BSR): self
    {
        $this->BSR = $BSR;

        return $this;
    }
}
