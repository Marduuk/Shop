<?php
declare(strict_types=1);

namespace App\Tests;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class GetCartTest
 * @package App\Tests
 */
class GetCartTest extends WebTestCase
{
    public function testGetCartPositive(): void
    {
        $client = static::createClient();
        $em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $product = new Product();
        $product->setTitle('Title one');
        $product->setPrice(5.99);
        $product->setCurrency('USD');
        $em->persist($product);

        $p2 = new Product();
        $p2->setTitle('Title two');
        $p2->setPrice(42.42);
        $p2->setCurrency('USD');
        $em->persist($p2);

        $em->flush();

        $client->request('POST', 'http://127.0.0.1:4040/cart');

        $client->request('PATCH', 'http://127.0.0.1:4040/cart/' . $product->getUuid()->toString());
        $client->request('PATCH', 'http://127.0.0.1:4040/cart/' . $product->getUuid()->toString());
        $client->request('PATCH', 'http://127.0.0.1:4040/cart/' . $p2->getUuid()->toString());

        $client->request('GET', 'http://127.0.0.1:4040/cart');

        $resp = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals('Title one', $resp['products'][0]['title']);
        $this->assertEquals(2, $resp['products'][0]['productCount']);

        $this->assertEquals('Title two', $resp['products'][1]['title']);
        $this->assertEquals(1, $resp['products'][1]['productCount']);

        $this->assertEquals(5.99 + 5.99 + 42.42, $resp['totalCartPrice']);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $em->createQuery('DELETE App:Product')->execute();
        $em->createQuery('DELETE App:Cart')->execute();
        $em->createQuery('DELETE App:ProductInCart')->execute();
    }
}