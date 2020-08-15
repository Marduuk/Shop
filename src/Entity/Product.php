<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Product
 * @ORM\Entity()
 * @ORM\Table("products")
 * @UniqueEntity("title")
 */
class Product
{
    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->productInCart = new ArrayCollection();
        $this->uuid = Uuid::uuid4();
    }

    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer", unique=true)
     */
    private $id;

    /**
     * @var Uuid
     * @ORM\Column(type="uuid", unique=true)
     */
    private $uuid;

    /**
     * @var string
     * @ORM\Column(name="title", type="string", nullable=false)
     * @Assert\NotNull
     * @Assert\NotBlank(message="Value cant be empty")
     * @Assert\Type("string")
     */
    private $title;

    /**
     * @var float
     * @ORM\Column(name="price", type="float", nullable=false)
     * @Assert\NotNull
     * @Assert\NotBlank(message="Value cant be empty")
     * @Assert\Type("float")
     */
    private $price;

    /**
     * @var string
     * @ORM\Column(name="currency", type="string", nullable=false)
     * @Assert\NotBlank(message="Value cant be empty")
     * @Assert\NotNull
     * @Assert\Type("string")
     */
    private $currency;

    /**
     * @ORM\OneToMany(targetEntity="ProductInCart", mappedBy="product")
     */
    private $productInCart;

    /**
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return self
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Uuid|null
     */
    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    /**
     * @param int $id
     * @return Product
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param float $price
     * @return Product
     */
    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @return Collection
     */
    public function getProductInCart(): Collection
    {
        return $this->productInCart;
    }
}
