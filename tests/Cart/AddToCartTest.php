<?php
declare(strict_types=1);

namespace App\Tests;

use App\Entity\Cart;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class AddToCartTest
 * @package App\Tests
 */
class AddToCartTest extends WebTestCase
{
    public function testAddToCartPositive(): void
    {
        $client = static::createClient();
        $em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $product = new Product();
        $product->setTitle('Some random title');
        $product->setPrice(3.99);
        $product->setCurrency('USD');
        $em->persist($product);

        $p2 = new Product();
        $p2->setTitle('Some more random title');
        $p2->setPrice(12.99);
        $p2->setCurrency('USD');
        $em->persist($p2);

        $em->flush();

        $client->request('POST', 'http://127.0.0.1:4040/cart');

        $client->request('PATCH', 'http://127.0.0.1:4040/cart/' . $product->getUuid()->toString());
        $client->request('PATCH', 'http://127.0.0.1:4040/cart/' . $product->getUuid()->toString());
        $client->request('PATCH', 'http://127.0.0.1:4040/cart/' . $p2->getUuid()->toString());

        $resp = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(3.99 + 3.99 + 12.99, $resp['cart']['totalCartPrice']);

        $cartRepository = $em->getRepository(Cart::class);
        $cart = $cartRepository->findOneBy(['uuid' => $client->getCookieJar()->get('cartUuid')->getValue()]);

        $this->assertNotNull($cart);

        $em->createQuery('DELETE App:Product')->execute();
        $em->createQuery('DELETE App:Cart')->execute();
        $em->createQuery('DELETE App:ProductInCart')->execute();
    }
}