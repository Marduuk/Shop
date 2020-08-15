<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\Uuid;

/**
 * Class Cart
 * @ORM\Entity()
 * @ORM\Table("carts")
 */
class Cart
{
    /**
     * Cart constructor.
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
     * @var float
     * @ORM\Column(name="totalPrice", type="float", nullable=true)
     */
    private $totalPrice = 0;

    /**
     * @ORM\OneToMany(targetEntity="ProductInCart", mappedBy="cart")
     */
    private $productInCart;

    /**
     * @return Uuid|null
     */
    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param float $totalPrice
     * @return Cart
     */
    public function setTotalPrice(float $totalPrice): self
    {
        $this->totalPrice = $totalPrice;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    /**
     * @return Collection
     */
    public function getProductsInCart(): Collection
    {
        return $this->productInCart;
    }

    /**
     * @param Cart $cart
     * @param Product $product
     * @return Cart
     */
    public function addProductPriceToCart(Cart $cart, Product $product): Cart
    {
        $newTotalPrice = $cart->getTotalPrice() + $product->getPrice();
        $cart->setTotalPrice((float)$newTotalPrice);
        return $cart;
    }

    /**
     * @param Cart $cart
     * @param Product $product
     * @return Cart
     */
    public function subtractProductPriceFromCart(Cart $cart, Product $product): Cart
    {
        $newTotalPrice = $cart->getTotalPrice() - $product->getPrice();
        $cart->setTotalPrice((float)$newTotalPrice);
        return $cart;
    }
}
