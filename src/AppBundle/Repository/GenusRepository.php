<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Genus;
use Doctrine\ORM\EntityRepository;

class GenusRepository extends EntityRepository
{
    /**
     * @return Genus[]
     */
    public function findAllPublishedOrderedByRecentlyActive() // On the list page, it orders the genuses by which one had the most recent note add to it
    {
        return $this->createQueryBuilder('genus') // This refers to the entity class that this will query. In other words, which database table it will query.
            ->andWhere('genus.isPublished = :isPublished')
            ->setParameter('isPublished', true)
            ->leftJoin('genus.notes', 'genus_note') // Joining over to the GenusNote table so that we can order the Genuses by a column that exists on another database table (GenusNote)
                                                                // Added 'genus.notes' because this is the table that we want to join to. The '.notes' at the end is the property name in the Genus class, 'genus.' refers to the entity class we a querying
                                                                // The 'genus_note' part is the alias which we can use during the rest of the query to reference fields in the joined GenusNote database table
            ->orderBy('genus_note.createdAt', 'DESC') // This is what we are ordering by, using the GenusNote (database table) alias along with one of its properties(database column, then giving it the argument of how to order it
            ->getQuery()
            ->execute();
    }
}
