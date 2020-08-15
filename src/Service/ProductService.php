<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductInCartRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductService
{
    /** @var ProductInCartRepository  */
    private $productInCartRepository;

    /** @var EntityManagerInterface  */
    private $entityManager;

    /**
     * CartController constructor.
     * @param ProductInCartRepository $productInCartRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ProductInCartRepository $productInCartRepository, EntityManagerInterface $entityManager)
    {
        $this->productInCartRepository = $productInCartRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param Product $product
     * @param float $oldPrice
     * @param float $newPrice
     */
    public function updateCartsWhenPriceChanges(Product $product, float $oldPrice, float $newPrice): void
    {
        $productsInCart = $this->productInCartRepository->findBy(['product' => $product]);
        $priceDifference = $oldPrice - $newPrice;

        foreach ($productsInCart as $productInCart){

            $cart = $productInCart->getCart();
            $newCartPrice = $cart->getTotalPrice() - $productInCart->getProductCount() * $priceDifference;
            $cart->setTotalPrice($newCartPrice);

            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }
    }

    /**
     * @param Product $product
     */
    public function updateCartsWithRemovedProduct(Product $product): void
    {
        $productsInCart = $this->productInCartRepository->findBy(['product' => $product]);

        foreach ($productsInCart as $productInCart){
            $cart = $productInCart->getCart();

            $newCartPrice = $cart->getTotalPrice() - $product->getPrice() * $productInCart->getProductCount();
            $cart->setTotalPrice($newCartPrice);

            $this->entityManager->remove($productInCart);
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }
    }

    /**
     * @param Product[] $products
     * @return array
     */
    public function getProductsForOutput($products): array
    {
        $productsOutput = [];
        foreach ($products as $product) {
            $productsOutput [] = $this->getProductForOutput($product);
        }
        return $productsOutput;
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getProductForOutput(Product $product): array
    {
        return [
            'uuid' => $product->getUuid()->toString(),
            'title' => $product->getTitle(),
            'price' => $product->getPrice(),
            'currency' => $product->getCurrency()
        ];
    }
}