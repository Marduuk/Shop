<?php
declare(strict_types=1);

namespace App\Tests;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class RemoveFromCartTest
 * @package App\Tests
 */
class RemoveFromCartTest extends WebTestCase
{
    public function testRemoveFromCartPositive(): void
    {
        $client = static::createClient();
        $em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $product = new Product();
        $product->setTitle('Title one');
        $product->setPrice(10);
        $product->setCurrency('USD');
        $em->persist($product);

        $p2 = new Product();
        $p2->setTitle('Some more random title');
        $p2->setPrice(5);
        $p2->setCurrency('USD');
        $em->persist($p2);
        $em->flush();

        $client->request('POST', 'http://127.0.0.1:4040/cart');

        $client->request('PATCH', 'http://127.0.0.1:4040/cart/' . $product->getUuid()->toString());
        $client->request('PATCH', 'http://127.0.0.1:4040/cart/' . $product->getUuid()->toString());
        $client->request('PATCH', 'http://127.0.0.1:4040/cart/' . $p2->getUuid()->toString());

        $client->request('DELETE', 'http://127.0.0.1:4040/cart/' . $product->getUuid()->toString());
        $client->request('GET', 'http://127.0.0.1:4040/cart');


        $resp = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(15 , $resp['totalCartPrice']);

        $client->request('DELETE', 'http://127.0.0.1:4040/cart/' . $product->getUuid()->toString());
        $client->request('GET', 'http://127.0.0.1:4040/cart');

        $resp = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(5 , $resp['totalCartPrice']);

        $em->createQuery('DELETE App:Product')->execute();
        $em->createQuery('DELETE App:Cart')->execute();
        $em->createQuery('DELETE App:ProductInCart')->execute();
    }
}