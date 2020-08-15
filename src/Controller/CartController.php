<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
use App\Repository\ProductInCartRepository;
use App\Repository\CartRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Cookie;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Class CartController
 * @package App\Controller
 */
class CartController extends AbstractFOSRestController
{

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var CartRepository */
    private $cartRepository;

    /** @var ProductInCartRepository */
    private $productInCartRepository;

    /** @var CartService */
    private $cartService;

    /**
     * CartController constructor.
     * @param EntityManagerInterface $entityManager
     * @param CartRepository $cartRepository
     * @param ProductInCartRepository $productInCartRepository
     * @param CartService $cartService
     */
    public function __construct(EntityManagerInterface $entityManager,
                                CartRepository $cartRepository,
                                ProductInCartRepository $productInCartRepository,
                                CartService $cartService
    )
    {
        $this->entityManager = $entityManager;
        $this->cartRepository = $cartRepository;
        $this->productInCartRepository = $productInCartRepository;
        $this->cartService = $cartService;
    }

    /**
     * @FOSRest\Post("/cart")
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        $cart = new Cart();

        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        $response = new JsonResponse(
            [
                'message' => 'Successfully added new cart',
                'success' => true,
                'cart' => [
                    'uuid' => $cart->getUuid()->toString(),
                    'totalCartPrice' => $cart->getTotalPrice()
                ]
            ],
            200);

        $response->headers->setCookie(Cookie::create('cartUuid', $cart->getUuid()->toString()));
        return $response;
    }

    /**
     * @FOSRest\Patch("/cart/{productUuid}")
     * @ParamConverter("product", options={"mapping": {"productUuid": "uuid"}})
     * @param Request $request
     * @param Product $product
     * @return JsonResponse
     */
    public function addToCart(Request $request, Product $product): JsonResponse
    {
        $cart = $this->cartRepository->getCartBasedOnCookie($request);
        $cart = $this->cartService->addProductToCart($cart, $product);

        return new JsonResponse(
            [
                'message' => 'Successfully added new product to cart',
                'success' => true,
                'cart' => [
                    'uuid' => $cart->getUuid()->toString(),
                    'totalCartPrice' => $cart->getTotalPrice()]
            ],
            200);
    }

    /**
     * @FOSRest\Delete("/cart/{productUuid}")
     * @ParamConverter("product", options={"mapping": {"productUuid": "uuid"}})
     * @param Request $request
     * @param Product $product
     * @return JsonResponse
     * @throws Exception
     */
    public function removeProductFromCart(Request $request, Product $product): JsonResponse
    {
        $cart = $this->cartRepository->getCartBasedOnCookie($request);

        $currentProductInCart = $this->productInCartRepository->getCurrentProductInCart($cart, $product);
        if ($currentProductInCart === null) {
            throw new BadRequestException('No given product in the cart.');
        }

        $this->entityManager->getConnection()->beginTransaction();
        try {
            $cart = $this->cartService->removeProductFromCart($currentProductInCart, $cart, $product);
            $this->entityManager->getConnection()->commit();

            $response = new JsonResponse(
                [
                    'message' => 'Successfully removed product from the cart.',
                    'success' => true,
                    'cart' => [
                        'uuid' => $cart->getUuid()->toString(),
                        'totalPrice' => $cart->getTotalPrice()
                    ]
                ],
                200);

        } catch (Exception $e) {
            $this->entityManager->getConnection()->rollBack();
            throw $e;
        }

        return $response;
    }

    /**
     * @FOSRest\Get("/cart")
     * @param Request $request
     * @return JsonResponse
     */
    public function getCart(Request $request): JsonResponse
    {
        $cart = $this->cartRepository->getCartBasedOnCookie($request);

        $products = $this->cartService->getCartsProducts($cart);

        return new JsonResponse(
            [
                'message' => 'Successfully fetched whole cart',
                'success' => true,
                'totalCartPrice' => $cart->getTotalPrice(),
                'products' => $products
            ],
            200);
    }
}
