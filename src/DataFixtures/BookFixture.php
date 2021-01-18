<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BookFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $author = new Author();
        $author->setName("Pinocchio");
        $manager->persist($author);
        $book = new Book();
        $book->setAuthor($author);
        $book->setName("La historia secreta de Geppetto");
        $manager->persist($book);

        $manager->flush();
    }
}
