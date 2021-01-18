<?php declare(strict_types = 1);

namespace App\Controller;

use App\Entity\Author;
use App\Entity\Book;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MainController extends AbstractController
{
    private UrlGeneratorInterface $router;
    private ValidatorInterface $validator;

    public function __construct(UrlGeneratorInterface $router, ValidatorInterface $validator)
    {
        $this->router = $router;
        $this->validator = $validator;
    }

    public function index()
    {
        return $this->render('main/index.html.twig');
    }

    public function authorsPost(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(), true);

        if (null === $data) {
            $response = new JsonResponse(['message' => 'Invalid JSON'], 400);
            $response->headers->set('Content-Type', 'application/problem+json');

            return $response;
        }

        // check if author already exists

        $author = new Author();
        if (isset($data['author'])) {
            $previousAuthor = $this->getDoctrine()
                ->getRepository(Author::class)
                ->findOneBy(['name' => $data['author']]);
            // check author already exists
            if (isset($previousAuthor)) {
                $response = new JsonResponse([
                    'message' => 'Author '.$data['author'].' already registered id = '.$previousAuthor->getId()
                ]);

                return $response;
            }
            $author->setName($data["author"]);
        }

        $errors = $this->validator->validate($author);
        $errorMessage = "";
        foreach($errors as $error) {
            $errorMessage .= $error->getMessage();
        }

        if (0 < count($errors)) {
            // kinda RFC 7807
            $data = [
                'type' => 'validation_error',
                'title' => 'Validation error detected',
                'errors' => $errorMessage
            ];
            // HTTP 400 Bad Request
            $response = new JsonResponse($data, 400);
            $response->headers->set('Content-Type', 'application/problem+json');

            return $response;
        }

        $entityManager->persist($author);
        $entityManager->flush();

        $data = $this->serializeAuthor($author);

        // HTTP 201 Created and return the info back with the id assigned
        $response = new JsonResponse($data, 201);
        $authorPage = $this->router->generate('authors_get', [
            'id' => $author->getId(),
        ]);

        $response->headers->set('Content-Type', 'application/json');
        // Location of the resource
        $response->headers->set("Location ", $authorPage);

        return $response;
    }

    public function authorsGet(int $id)
    {
        $author = $this->getDoctrine()
            ->getRepository(Author::class)
            ->find($id);

        if (!$author) {
            return $this->notFound404();
        }

        $books = $this->getDoctrine()
            ->getRepository(Book::class)
            ->findBy(['author' => $author]);
        $serialAuthor = $this->serializeAuthor($author);
        $serialBooks = [];
        foreach ($books as $book) {
            $serialBooks[] = ["id" => $book->getId(), "name" => $book->getName()];
        }
        $data = ["author" => $serialAuthor, "books" => $serialBooks];
        // header is 'application/json'
        return new JsonResponse($data, 200);
    }

    public function authorsGetAll()
    {
        $authors = $this->getDoctrine()
            ->getRepository(Author::class)
            ->findAll();

        $data = ['authors' => []];
        foreach ($authors as $author) {
            $data['authors'][] = $this->serializeAuthor($author);
        }
        // header is 'application/json'
        return new JsonResponse($data, 200);
    }

    public function booksPost(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if (null === $data) {
            $response = new JsonResponse(['message' => 'Invalid JSON'], 400);
            $response->headers->set('Content-Type', 'application/problem+json');

            return $response;
        }
        if (isset($data['author']) && isset($data['name'])) {
            // check author exists
            $book = $this->getDoctrine()
                ->getRepository(Book::class)
                ->findOneBy([
                    'author' => $data['author'],
                    'name' => $data['name']
                ]);
            if ($book) {
                return new JsonResponse(['message', 'book already stored'], 200);
            }
        }

        $author = null;
        if (isset($data['author'])) {
            $author = $this->getDoctrine()
                ->getRepository(Author::class)
                ->findOneBy([
                    'id' => $data['author']
                ]);

            if (!$author) {
                return new JsonResponse(['message', 'author does not exist'], 200);
            }
        }

        $entityManager = $this->getDoctrine()->getManager();
        $book = new Book();
        $book->setAuthor($author);
        if (isset($data['name']))
            $book->setName($data['name']);

        $errors = $this->validator->validate($book);
        $errorMessage = "";
        foreach($errors as $error) {
            $errorMessage .= $error->getMessage(). "; ";
        }
        if (0 < count($errors)) {
            // kinda RFC 7807
            $data = [
                'type' => 'validation_error',
                'title' => 'Validation error detected',
                'errors' => $errorMessage
            ];
            // HTTP 400 Bad Request
            $response = new JsonResponse($data, 400);
            $response->headers->set('Content-Type', 'application/problem+json');

            return $response;
        }

        $entityManager->persist($book);
        $entityManager->flush();

        $data = $this->serializeBook($book);

        // best practice: 201 HTTP Created and return Location Header
        $response = new JsonResponse($data, 201);
        $bookPage = $this->router->generate('books_get', [
            'id' => $book->getId(),
        ]);

        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Location ', $bookPage);

        return $response;
    }

    public function booksGet(int $id)
    {
        $book = $this->getDoctrine()
            ->getRepository(Book::class)
            ->findOneBy(['id' => $id]);

        if (!$book) {
            return $this->notFound404();
        }

        $data = $this->serializeBook($book);
        return new JsonResponse($data, 200);
    }

    public function booksGetAll()
    {
        $authors = $this->getDoctrine()
            ->getRepository(Author::class)
            ->findAll();

        $bookRepo = $this->getDoctrine()
            ->getRepository(Book::class);

        $data = [];
        foreach ($authors as $author) {
            $books = $bookRepo->findBy(['author' => $author]);
            $b = [];
            foreach($books as $book) {
                $b[] = [
                    'id' => $book->getId(),
                    'name' => $book->getName(),
                ];
            }
            $data[] = [
                'id' => $author->getId(),
                'name' => $author->getName(),
                'books' => $b,
            ];
        }

        return new JsonResponse($data);
    }

    public function notFound404($message = 'Resource not found')
    {
        $data = [
            'type' => 'about:blank',
            'title' => 'Not found',
            'errors' => $message
        ];
        // HTTP 404 Not found
        $response = new JsonResponse($data, 404);
        $response->headers->set('Content-Type', 'application/problem+json');

        return $response;
    }

    private function serializeAuthor(Author $author)
    {
        return ['id' => $author->getId(), 'name' => $author->getName()];
    }

    private function serializeBook(Book $book)
    {
        return [
            'id' => $book->getId(),
            'author' => $book->getAuthor()->getName(),
            'name' => $book->getName()
        ];
    }
}