<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Cart;
use App\Entity\Product;
use App\Entity\ProductInCart;
use App\Repository\CartRepository;
use App\Repository\ProductInCartRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Doctrine\ORM\EntityManagerInterface;

class CartService
{
    /** @var ProductInCartRepository  */
    private $productInCartRepository;

    /** @var CartRepository  */
    private $cartRepository;

    /** @var EntityManagerInterface  */
    private $entityManager;

    /**
     * CartService constructor.
     * @param CartRepository $cartRepository
     * @param ProductInCartRepository $productInCartRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(CartRepository $cartRepository,
                                ProductInCartRepository $productInCartRepository,
                                EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->cartRepository = $cartRepository;
        $this->productInCartRepository = $productInCartRepository;
    }

    /**
     * @param Cart $cart
     * @param Product $product
     * @return Cart
     */
    public function addProductToCart(Cart $cart, Product $product): Cart
    {
        if (count($cart->getProductsInCart()) >= 3) {
            throw new BadRequestException('Reached maximum number of products in cart');
        }

        $currentProductInCart = $this->productInCartRepository->getCurrentProductInCart($cart, $product);
        if ($currentProductInCart === null) {
            $currentProductInCart = $this->productInCartRepository->createNewProductInCart($cart, $product);

        } else {
            if ($currentProductInCart->getProductCount() >= 10) {
                throw new BadRequestException('Reached limit of same product in cart');
            }
        }

        $currentProductInCart->incrementTotalProducts($currentProductInCart);
        $cart->addProductPriceToCart($cart, $product);

        $this->entityManager->persist($currentProductInCart);
        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        return $cart;
    }

    /**
     * @param ProductInCart $productInCart
     * @param Cart $cart
     * @param Product $product
     * @return Cart
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function removeProductFromCart(ProductInCart $productInCart, Cart $cart, Product $product): Cart
    {
        $productInCart->decrementTotalProducts($productInCart);

        if ($productInCart->getProductCount() === 0) {

            $this->productInCartRepository->delete($productInCart);
        } else {
            $this->entityManager->persist($productInCart);
        }

        $cart->subtractProductPriceFromCart($cart, $product);
        $this->entityManager->persist($cart);
        $this->entityManager->flush();
        return $cart;
    }

    /**
     * @param Cart $cart
     * @return array
     */
    public function getCartsProducts(Cart $cart): array
    {
        $products = [];
        foreach ($cart->getProductsInCart() as $singleProductInCart) {

            $singleProductInCart->getProductCount();
            $singleProductInCart->getProduct();

            $product = $singleProductInCart->getProduct();
            $data = [
                'uuid' => $product->getUuid()->toString(),
                'title' => $product->getTitle(),
                'price' => $product->getPrice(),
                'productCount' => $singleProductInCart->getProductCount()
            ];

            $products [] = $data;
        }
        return $products;
    }
}