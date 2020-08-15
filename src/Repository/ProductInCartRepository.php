<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\Product;
use App\Entity\ProductInCart;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * @method ProductInCart|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductInCart|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductInCart[]    findAll()
 * @method ProductInCart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductInCartRepository extends ServiceEntityRepository
{
    /**
     * {@inheritdoc}
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductInCart::class);
    }

    /**
     * @param Cart $cart
     * @param Product $product
     * @return ProductInCart|null
     */
    public function getCurrentProductInCart(Cart $cart, Product $product): ?ProductInCart
    {
        return $this->findOneBy(
            ['cart' => $cart->getId(), 'product' => $product->getId()]);
    }

    /**
     * @param Cart $cart
     * @param Product $product
     * @return ProductInCart
     */
    public function createNewProductInCart(Cart $cart, Product $product): ProductInCart
    {
        $currentProductInCart = new ProductInCart();
        $currentProductInCart->setProduct($product);
        $currentProductInCart->setCart($cart);
        return $currentProductInCart;
    }

    /**
     * @param ProductInCart $productInCart
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(ProductInCart $productInCart): void
    {
        $em = $this->getEntityManager();
        $em->remove($productInCart);
        $em->flush();
    }
}