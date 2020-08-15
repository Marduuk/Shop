<?php
declare(strict_types=1);

namespace App\Tests;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ProductPostTest
 * @package App\Tests
 */
class ProductPostTest extends WebTestCase
{
    public function testProductPostPositive(): void
    {
        $client = static::createClient();
        $em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $client->request('POST', 'http://127.0.0.1:4040/product',
            [
            'title' => 'Diabolo', 'price' => '20.00', 'currency' => 'USD'
            ]
        );

        $productRepository = $em->getRepository(Product::class);
        $product = $productRepository->findOneBy(['title' => 'Diabolo']);

        $this->assertNotNull($product);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $em->remove($product);
        $em->flush();
    }
}