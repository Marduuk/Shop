<?php
declare(strict_types=1);

namespace App\Tests;

use App\Entity\Product;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/**
 * Class ProductGetTest
 * @package App\Tests
 */
class ProductGetTest extends WebTestCase
{
    /**
     * @return array
     */
    public function getDataForProducts(): array
    {
        return [
            ['title' => 'Fallout', 'price' => 1.99],
            ['title' => 'Don\'t Starve', 'price' => 2.99],
            ['title' => 'Baldur\'s Gate', 'price' => 3.99],
            ['title' => 'Icewind Dale', 'price' => 4.99],
            ['title' => 'Bloodborne', 'price' => 5.99]
        ];
    }

    /**
     * @param EntityManager $em
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function fillDbWithProducts(EntityManager $em): void
    {
        foreach ($this->getDataForProducts() as $singleProductData) {

            $product = new Product();
            $product->setTitle($singleProductData['title']);
            $product->setPrice($singleProductData['price']);
            $product->setCurrency('USD');
            $em->persist($product);
        }
        $em->flush();
    }

    public function testProductGetPositive(): void
    {
        $client = static::createClient();
        /** @var EntityManager $em */
        $em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $query = $em->createQuery('DELETE App:Product');
        $query->execute();

        $this->fillDbWithProducts($em);

        $client->request('GET', 'http://127.0.0.1:4040/product/1');
        $resp = $client->getResponse();
        $jsonFirstPage = json_decode($resp->getContent(), true);

        $this->assertEquals(200, $resp->getStatusCode());

        $client->request('GET', 'http://127.0.0.1:4040/product/2');
        $resp = $client->getResponse();
        $jsonSecondPage = json_decode($resp->getContent(), true);

        $this->assertEquals(200, $resp->getStatusCode());

        foreach (array_merge($jsonFirstPage['products'], $jsonSecondPage['products']) as $product) {

            $isTitleFound = false;
            foreach ($this->getDataForProducts() as $singleProductData) {
                if ($singleProductData['title'] == $product['title']) {
                    $isTitleFound = true;
                }
            }
            $this->assertTrue($isTitleFound);
        }

        $em->createQuery('DELETE App:Product')->execute();
        $em->createQuery('DELETE App:Cart')->execute();
        $em->createQuery('DELETE App:ProductInCart')->execute();
    }
}