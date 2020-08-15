<?php
declare(strict_types=1);

namespace App\Tests;

use App\Entity\Cart;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class CreateCartTest
 * @package App\Tests
 */
class CreateCartTest extends WebTestCase
{
    public function testCreateCartPositive(): void
    {
        $client = static::createClient();
        $em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $client->request('POST', 'http://127.0.0.1:4040/cart');

        $cartRepository = $em->getRepository(Cart::class);
        $cart = $cartRepository->findOneBy(['uuid' => $client->getCookieJar()->get('cartUuid')->getValue()]);

        $this->assertNotNull($cart);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $em->remove($cart);
        $em->flush();
    }
}