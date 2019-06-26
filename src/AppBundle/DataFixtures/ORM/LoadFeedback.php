<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Feedback;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Load some test feedback data.
 */
class LoadFeedback extends Fixture implements FixtureGroupInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        for($i = 0; $i < 4; $i++) {
            $fixture = new Feedback();
            $fixture->setName('Name ' . $i);
            $fixture->setEmail('Email ' . $i);
            $fixture->setContent('Content ' . $i);
            
            $manager->persist($fixture);
            $this->setReference('feedback.' . $i, $fixture);
        }
        
        $manager->flush();
        
    }

    /**
     * {@inheritdoc}
     */
    public static function getGroups(): array {
        return array('test');
    }
}
