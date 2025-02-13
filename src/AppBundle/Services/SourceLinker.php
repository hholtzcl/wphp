<?php

namespace AppBundle\Services;

use AppBundle\Entity\En;
use AppBundle\Entity\EstcMarc;
use AppBundle\Entity\Jackson;
use AppBundle\Entity\OrlandoBiblio;
use AppBundle\Entity\OsborneMarc;
use AppBundle\Entity\Source;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Construct links to various sources.
 */
class SourceLinker {
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UrlGeneratorInterface
     */
    private $generator;

    /**
     * @var RoleChecker
     */
    private $checker;

    /**
     * SourceLinker constructor.
     *
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $generator
     * @param RoleChecker $checker
     */
    public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $generator, RoleChecker $checker) {
        $this->em = $em;
        $this->generator = $generator;
        $this->checker = $checker;
    }

    /**
     * Set the role checker.
     *
     * @param RoleChecker $checker
     */
    public function setRoleChecker(RoleChecker $checker) {
        $this->checker = $checker;
    }

    /**
     * Generate an ESTC link. The link will be internal if the user is logged in or to the ESTC website
     * otherwise.
     *
     * @param string $data
     *
     * @return null|string
     */
    public function estc($data) {
        if ( ! $this->checker->hasRole('ROLE_USER')) {
            return 'http://estc.bl.uk/' . $data;
        }
        $repo = $this->em->getRepository(EstcMarc::class);
        $record = $repo->findOneBy(array(
            'fieldData' => $data,
            'field' => '001',
        ));

        if ($record) {
            return $this->generator->generate('resource_estc_show', array(
                'id' => $record->getTitleId(),
            ));
        }
    }

    /**
     * Generate an Orlando link. The link will be internal if the user is logged in or to the Orlando website
     * otherwise.
     *
     * @param string $data
     *
     * @return null|string
     */
    public function orlando($data) {
        if ( ! $this->checker->hasRole('ROLE_USER')) {
            return;
        }
        $repo = $this->em->getRepository(OrlandoBiblio::class);
        $record = $repo->findOneBy(array(
            'orlandoId' => $data,
        ));
        if ($record) {
            return $this->generator->generate('resource_orlando_biblio_show', array(
                'id' => $record->getId(),
            ));
        }
    }

    /**
     * Generate a Jackson link. The link will be internal if the user is logged in or null otherwise.
     *
     * @param string $data
     *
     * @return null|string
     */
    public function jackson($data) {
        if ($this->checker->hasRole('ROLE_USER')) {
            $repo = $this->em->getRepository(Jackson::class);
            $record = $repo->findOneBy(array('jbid' => $data));
            if ($record) {
                return $this->generator->generate('resource_jackson_show', array('id' => $record->getId()));
            }
        }

        return "https://jacksonbibliography.library.utoronto.ca/search/details/{$data}";
    }

    /**
     * Generate a English Novel link. The link will be internal if the user is logged in or null otherwise.
     *
     * @param string $data
     *
     * @return null|string
     */
    public function en($data) {
        if ( ! $this->checker->hasRole('ROLE_USER')) {
            return;
        }
        $repo = $this->em->getRepository(En::class);
        $record = $repo->findOneBy(array(
            'enId' => $data,
        ));
        if ($record) {
            return $this->generator->generate('resource_en_show', array(
                'id' => $record->getId(),
            ));
        }
    }

    /**
     * Generate an Osborne link. The link will be internal if the user is logged in or null otherwise.
     *
     * @param string $data
     *
     * @return null|string
     */
    public function osborne($data) {
        if ( ! $this->checker->hasRole('ROLE_USER')) {
            return;
        }
        $repo = $this->em->getRepository(OsborneMarc::class);
        $record = $repo->findOneBy(array(
            'fieldData' => $data,
            'field' => '001',
        ));
        if ($record) {
            return $this->generator->generate('resource_osborne_show', array(
                'id' => $record->getTitleId(),
            ));
        }
    }

    /**
     * Generate a link to the ECCO website for all users.
     *
     * @param string $data
     *
     * @return string
     */
    public function ecco($data) {
        // No role checking for this one.
        $id = substr_replace($data, '0', 2, 0);

        return "http://link.galegroup.com/apps/doc/{$id}/ECCO?sid=WomenPrintHistProject";
    }

    /**
     * If the data matches https? it will be returned as is. Otherwise generate a URL based on the source.
     *
     * @param Source $source
     * @param string $data
     *
     * @return null|string
     */
    public function url(Source $source, $data) {
        if (preg_match('/https?:/', $data)) {
            return $data;
        }
        switch ($source->getName()) {
            case 'ESTC':
                return $this->estc($data);
            case 'Orlando':
                return $this->orlando($data);
            case 'Jackson Bibliography':
                return $this->jackson($data);
            case 'The English Novel 1770-1829':
            case 'The English Novel 1830-1836':
                return $this->en($data);
            case "Osborne Collection of Early Children's Books":
                return $this->osborne($data);
            case 'ECCO':
                return $this->ecco($data);
            default:
                return;
        }
    }
}
