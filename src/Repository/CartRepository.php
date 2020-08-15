<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\Product;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

/**
 * @method Cart|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cart|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cart[]    findAll()
 * @method Cart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CartRepository extends ServiceEntityRepository
{
    /**
     * {@inheritdoc}
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    /**
     * @param Request $request
     * @return Cart|null
     */
    public function getCartBasedOnCookie(Request $request): ?Cart
    {
        $cartUuid = $request->cookies->get('cartUuid');
        if (empty($cartUuid)){
            throw new BadRequestException('No cart');
        }

        return $this->findOneBy(['uuid' => Uuid::fromString($cartUuid)]);
    }
}