<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $blogPost = new BlogPost();
        $blogPost->setTitle('Yess');
        $blogPost->setAuthor('Iva Iva');
        $blogPost->setContent('Eligendi non quis exercitationem culpa nesciunt nihil aut nostrum explicabo.');
        $blogPost->setCreatedAt(new \DateTime());
        $blogPost->setSlug('lo-lo');
        $manager->persist($blogPost);

        $blogPost = new BlogPost();
        $blogPost->setTitle('Post forman');
        $blogPost->setAuthor('Popila');
        $blogPost->setContent('Lorem ipsum dolor sit amet, consectetur adipisicing elit.');
        $blogPost->setCreatedAt(new \DateTime());
        $blogPost->setSlug('ko-ko');
        $manager->persist($blogPost);

        $manager->flush();
    }
}
