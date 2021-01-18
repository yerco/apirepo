<?php declare(strict_types = 1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
    private $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testPostGetOneAuthor()
    {
        // submits a raw JSON string in the request body
        $this->client->request(
            'POST',
            '/author',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"author":"Fabien"}'
        );
        $response = $this->client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals("Fabien", json_decode($response->getContent())->name);
        $id = preg_replace('/[^0-9]/', '', $response->getContent());
        $this->assertEquals('/author/'.$id, $response->headers->get('Location '));

        $this->client->request('GET', '/author/'.$id);
        $jsonResponse = $this->client->getResponse();
        $jsonData = json_decode($jsonResponse->getContent());
        $this->assertEquals('Fabien', $jsonData->author->name);
    }

    public function testInvalidPostToAuthorsEndpoint()
    {
        $this->client->request(
            'POST',
            '/author',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"some":"thing"}'
        );
        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
        $jsonResponse = json_decode($response->getContent());
        $this->assertEquals("author name is required", $jsonResponse->errors);
    }

    public function testGetAuthorNotFound()
    {
        $this->client->request('GET', '/author/99999999');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testPostGetOneBook()
    {
        $bookName = "Pinocchio el infame";
        $this->client->request(
            'POST',
            '/book',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"author": "1", "name": "'.$bookName.'"}'
        );
        $response = $this->client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($bookName, json_decode($response->getContent())->name);
        $id = preg_replace('/[^0-9]/', '', json_decode($response->getContent())->id);
        $this->assertEquals('/book/'.$id, $response->headers->get('Location '));

        $this->client->request('GET', '/book/'.$id);
        $jsonResponse = $this->client->getResponse();
        $jsonData = json_decode($jsonResponse->getContent());
        $this->assertEquals($bookName, $jsonData->name);
    }

    public function testGetBookNotFound()
    {
        $this->client->request('GET', '/book/99999999');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testInvalidPostToBooksEndpoint()
    {
        $this->client->request(
            'POST',
            '/book',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"some":"thing"}'
        );
        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
        $jsonResponse = json_decode($response->getContent());
        $this->assertStringContainsString("author id is required", $jsonResponse->errors);
        $this->assertStringContainsString("book name is required", $jsonResponse->errors);
    }

    public function testNotFound()
    {
        $response  = $this->client->request('GET', '/whatever');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
        dump($this->client->getResponse());
    }
}