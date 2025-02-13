<?php

namespace AppBundle\Command;

use AppBundle\Entity\EstcMarc;
use AppBundle\Entity\Source;
use AppBundle\Entity\TitleSource;
use AppBundle\Services\MarcManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Check each title and update the ESTC source ID attribute if applicable.
 */
class UpdateEstcCommand extends ContainerAwareCommand {
    const BATCH_SIZE = 100;

    const ESTC_ID = 2;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var MarcManager
     */
    private $manager;

    /**
     * UpdateEstcCommand constructor.
     *
     * @param EntityManagerInterface $em
     * @param MarcManager $manager
     */
    public function __construct(EntityManagerInterface $em, MarcManager $manager) {
        parent::__construct();
        $this->em = $em;
        $this->manager = $manager;
    }

    /**
     * Configure the command.
     */
    protected function configure() {
        $this->setName('wphp:update:estc');
        $this->setDescription('Change the ESTC Identifiers from 009 to 001');
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     *                              Command input, as defined in the configure() method.
     * @param OutputInterface $output
     *                                Output destination.
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $qb = $this->em->createQueryBuilder();
        $source = $this->em->find(Source::class, self::ESTC_ID);
        $qb->select('ts')->from(TitleSource::class, 'ts')->where('ts.source = :source')->setParameter('source', $source);
        $iterator = $qb->getQuery()->iterate();
        while ($row = $iterator->next()) {
            /** @var TitleSource $titleSource */
            $titleSource = $row[0];
            $estcMarc = $this->em->getRepository(EstcMarc::class)->findOneBy(array(
                'field' => '009',
                'fieldData' => $titleSource->getIdentifier() . '\\',
            ));
            if ( ! $estcMarc) {
                continue;
            }
            $newId = $this->manager->getFieldValues($estcMarc, '001');

            $titleSource->setIdentifier($newId[0]);
            if (0 === $iterator->key() % self::BATCH_SIZE) {
                $this->em->flush();
                $this->em->clear();
                $output->write("\r" . $iterator->key());
            }
        }
        $this->em->flush();
        $this->em->clear();
        $output->writeln("\rfinished.");
    }
}
