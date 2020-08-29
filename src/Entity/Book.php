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
     * @ORM\Column(type="string")
     */
    private $ASIN;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $BSR;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getASIN(): ?string
    {
        return $this->ASIN;
    }

    public function setASIN(string $ASIN): self
    {
        $this->ASIN = $ASIN;

        return $this;
    }

    public function getBSR(): ?string
    {
        return $this->BSR;
    }

    public function setBSR(?string $BSR): self
    {
        $this->BSR = $BSR;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
