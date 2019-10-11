<?php

namespace AppBundle\Repository;

/**
 * EnRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class OrlandoBiblioRepository extends \Doctrine\ORM\EntityRepository {
    /**
     * Do a name search on author and title.
     *
     * @param string $q
     *
     * @return mixed
     */
    public function searchQuery($q) {
        $qb = $this->createQueryBuilder('e');
        $qb->addSelect('MATCH (e.author, e.monographicStandardTitle) AGAINST(:q BOOLEAN) as HIDDEN score');
        $qb->where('MATCH (e.author, e.monographicStandardTitle) AGAINST(:q BOOLEAN) > 0');
        $qb->orderBy('score', 'DESC');
        $qb->setParameter('q', $q);

        return $qb->getQuery();
    }
}
