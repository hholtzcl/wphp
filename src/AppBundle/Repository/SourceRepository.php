<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Source;
use AppBundle\Entity\Title;
use Doctrine\ORM\EntityRepository;

/**
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SourceRepository extends EntityRepository {
    /**
     * Execute a name search for a typeahead widget.
     *
     * @param string $q
     *
     * @return mixed
     */
    public function typeaheadQuery($q) {
        $qb = $this->createQueryBuilder('e');
        $qb->andWhere('e.name LIKE :q');
        $qb->orderBy('e.name');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }

    /**
     * Count the titles in a source.
     *
     * @todo this code is out of date. Titles no longer have source2, source3 etc fields.
     *
     * @param Source $source
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return mixed
     */
    public function countTitles(Source $source) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('count(title.id)');
        $qb->where('title.source = :source');
        $qb->orWhere('title.source2 = :source');
        $qb->orWhere('title.source3 = :source');
        $qb->setParameter('source', $source);
        $qb->from(Title::class, 'title');

        return $qb->getQuery()->getSingleScalarResult();
    }
}
