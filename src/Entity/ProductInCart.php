<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * Class ProductInCart
 * @ORM\Entity()
 * @ORM\Table("products_in_cart")
 * @ORM\Table(
 *    name="product_in_cart",
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="cart_unique", columns={"cart_id", "product_id"})
 *    }
 * )
 */
class ProductInCart
{
    /**
     * ProductInCart constructor.
     */
    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    /**
     * @var int
     *
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
     * @var int
     * @ORM\Column(name="product_count", type="integer")
     */
    private $productCount = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="productInCart", cascade={"remove"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="Cart", inversedBy="productInCart", cascade={"remove"})
     * @ORM\JoinColumn(name="cart_id", referencedColumnName="id")
     */
    protected $cart;

    /**
     * @return Uuid|null
     */
    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    /**
     * @param int $count
     * @return $this
     */
    public function setProductCount(int $count): self
    {
        $this->productCount = $count;
        return $this;
    }

    /**
     * @param Product $product
     * @return $this
     */
    public function setProduct(Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param Cart $cart
     * @return $this
     */
    public function setCart(Cart $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * @return Cart
     */
    public function getCart(): Cart
    {
        return $this->cart;
    }

    /**
     * @return int
     */
    public function getProductCount(): int
    {
        return $this->productCount;
    }

    /**
     * @param ProductInCart $productInCart
     * @return ProductInCart
     */
    public function incrementTotalProducts(ProductInCart $productInCart): ProductInCart
    {
        return $productInCart->setProductCount($productInCart->getProductCount() + 1);
    }

    /**
     * @param ProductInCart $productInCart
     * @return ProductInCart
     */
    public function decrementTotalProducts(ProductInCart $productInCart): ProductInCart
    {
        return $productInCart->setProductCount($productInCart->getProductCount() - 1);
    }
}
