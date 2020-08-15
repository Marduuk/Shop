<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\ExceptionService;
use App\Service\ProductService;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as FOSRest;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Exception;

/**
 * Class ProductController
 * @package App\Controller
 */
class ProductController extends AbstractFOSRestController
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var PaginatorInterface */
    private $paginator;

    /** @var ProductRepository */
    private $repository;

    /** @var ExceptionService */
    private $exceptionService;

    /** @var ProductService */
    private $productService;

    /**
     * ProductController constructor.
     * @param FormFactoryInterface $formFactory
     * @param EntityManagerInterface $entityManager
     * @param PaginatorInterface $paginator
     * @param ProductRepository $repository
     * @param ProductService $productService
     * @param ExceptionService $exceptionService
     */
    public function __construct(FormFactoryInterface $formFactory,
                                EntityManagerInterface $entityManager,
                                PaginatorInterface $paginator,
                                ProductRepository $repository,
                                ProductService $productService,
                                ExceptionService $exceptionService)
    {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->paginator = $paginator;
        $this->repository = $repository;
        $this->productService = $productService;
        $this->exceptionService = $exceptionService;
    }

    /**
     * @FOSRest\Get("/product/{page}")
     * @param int $page
     * @return JsonResponse
     */
    public function getProducts(int $page): JsonResponse
    {
        $products = $this->repository->findAll();
        /** @var Product[] $products */
        $products = $this->paginator->paginate($products, $page, 3);

        return new JsonResponse(
            [
                'message' => 'Successfully fetched products',
                'success' => true,
                'products' => $this->productService->getProductsForOutput($products)
            ],
            200);
    }

    /**
     * @FOSRest\Post("/product")
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $product = new Product();
        $form = $this->formFactory->createNamed('product', ProductType::class, $product);
        $form->submit($request->request->all());

        if ($form->isValid()) {
            $this->entityManager->persist($product);
            $this->entityManager->flush();

            $response = new JsonResponse(
                [
                    'message' => 'Successfully added new product',
                    'success' => true,
                    'product' => $this->productService->getProductForOutput($product)
                ],
                200);

        } else {
            $errors = $this->exceptionService->getFormErrors($form);
            $response = new JsonResponse(
                [
                    'message' => $errors,
                    'success' => false
                ],
                400);
        }
        return $response;
    }

    /**
     * @FOSRest\Delete("/product/{uuid}")
     * @ParamConverter("product", options={"mapping": {"uuid": "uuid"}})
     * @param Product $product
     * @return JsonResponse
     * @throws ConnectionException
     * @throws Exception
     */
    public function delete(Product $product): JsonResponse
    {
        $this->entityManager->getConnection()->beginTransaction();
        try {
            $this->productService->updateCartsWithRemovedProduct($product);

            $this->entityManager->remove($product);
            $this->entityManager->flush();

            $this->entityManager->getConnection()->commit();

            $response = new JsonResponse(
                [
                    'message' => 'Successfully removed product from cart.',
                    'success' => true
                ], 200
            );

        } catch (Exception $e) {
            $this->entityManager->getConnection()->rollBack();
            throw $e;
        }
        return $response;
    }

    /**
     * @FOSRest\Patch("/product/{uuid}")
     * @ParamConverter("product", options={"mapping": {"uuid": "uuid"}})
     * @param Request $request
     * @param Product $product
     * @return JsonResponse
     * @throws ConnectionException
     * @throws Exception
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $currentProductPrice = $product->getPrice();
        $form = $this->formFactory->createNamed('product', ProductType::class, $product, ['method' => 'patch']);
        $form->submit($request->request->all(), false);

        if ($form->isValid()) {

            $this->entityManager->getConnection()->beginTransaction();
            try {

                if ($request->request->get('price') !== null) {
                    $this->productService->updateCartsWhenPriceChanges($product, $currentProductPrice, $request->request->get('price'));
                }

                $this->entityManager->persist($product);
                $this->entityManager->flush();
                $this->entityManager->getConnection()->commit();

                $response = new JsonResponse(
                    [
                        'message' => 'Successfully updated product',
                        'success' => true,
                        'product' => $this->productService->getProductForOutput($product)
                    ], 200);

            } catch (Exception $e) {
                $this->entityManager->getConnection()->rollBack();
                throw $e;
            }
        } else {
            $errors = $this->exceptionService->getFormErrors($form);
            $response = new JsonResponse(
                [
                    'message' => $errors,
                    'success' => false
                ], 400);
        }
        return $response;
    }
}
