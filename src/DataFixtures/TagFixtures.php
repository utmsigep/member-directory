<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TagFixtures extends Fixture
{
    public const TAG_1901_CLUB = '1901 Club';

    public function load(ObjectManager $manager): void
    {
        $tag = new Tag();
        $tag->setTagName('1901 Club');
        $manager->persist($tag);
        $manager->flush();

        $this->addReference(self::TAG_1901_CLUB, $tag);
    }
}
