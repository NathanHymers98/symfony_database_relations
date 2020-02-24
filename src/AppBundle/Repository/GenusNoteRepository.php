<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Genus;
use AppBundle\Entity\GenusNote;
use Doctrine\ORM\EntityRepository;

class GenusNoteRepository extends EntityRepository
{
    /**
     * @param Genus $genus
     * @return GenusNote[]
     */
    public function findAllRecentNotesForGenus(Genus $genus)// This queries the database to find genus notes that were created less than 3 months ago
    {
        return $this->createQueryBuilder('genus_note')
            ->andWhere('genus_note.genus = :genus') // Selects all from genus notes 'genus' column which is equal to the parameter genus
            ->setParameter('genus', $genus) // Setting the genus parameter to the $genus property inside the GenusNote entity class.
            ->andWhere('genus_note.createdAt > :recentDate') // where genus note createdAt column is greater than the parameter recentDate
            ->setParameter('recentDate', new \DateTime('-3 months')) // Setting the parameter recentDate to a new DateTime object of -3 months
            ->getQuery()
            ->execute();
    }
}
