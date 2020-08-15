<?php
declare(strict_types=1);

namespace App\Tests;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ProductPatchTest
 * @package App\Tests
 */
class ProductPatchTest extends WebTestCase
{
    public function testProductPatchPositive(): void
    {
        $client = static::createClient();
        $em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $product = new Product();
        $product->setTitle('Starcraft');
        $product->setPrice(3.99);
        $product->setCurrency('USD');
        $em->persist($product);
        $em->flush();

        $newGameTitle = 'Witcher 4';
        $client->request(
            'PATCH',
            'http://127.0.0.1:4040/product/' . $product->getUuid()->toString(),
            [
                'title' => $newGameTitle
            ]
        );

        $productRepository = $em->getRepository(Product::class);
        $product = $productRepository->findOneBy(['title' => $newGameTitle]);

        $this->assertNotNull($product);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $em->remove($product);
        $em->flush();
    }

    public function testPriceUpdateInCartsPositive(): void
    {
        $client = static::createClient();
        $em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $product = new Product();
        $product->setTitle('Lul 2');
        $product->setPrice(40);
        $product->setCurrency('USD');
        $em->persist($product);

        $p2 = new Product();
        $p2->setTitle('Lul 1');
        $p2->setPrice(10);
        $p2->setCurrency('USD');
        $em->persist($p2);
        $em->flush();

        $client->request('POST', 'http://127.0.0.1:4040/cart');

        $client->request('PATCH', 'http://127.0.0.1:4040/cart/' . $product->getUuid()->toString());
        $client->request('PATCH', 'http://127.0.0.1:4040/cart/' . $p2->getUuid()->toString());

        $client->request('GET', 'http://127.0.0.1:4040/cart');
        $resp = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(50, $resp['totalCartPrice']);

        $client->request('PATCH', 'http://127.0.0.1:4040/product/' . $product->getUuid()->toString(),
            ['price' => 32]
        );
        $client->request('PATCH', 'http://127.0.0.1:4040/product/' . $p2->getUuid()->toString(),
            ['price' => 15]
        );

        $client->request('GET', 'http://127.0.0.1:4040/cart');

        $resp = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(47, $resp['totalCartPrice']);

        $em->createQuery('DELETE App:Product')->execute();
        $em->createQuery('DELETE App:Cart')->execute();
        $em->createQuery('DELETE App:ProductInCart')->execute();
    }
}