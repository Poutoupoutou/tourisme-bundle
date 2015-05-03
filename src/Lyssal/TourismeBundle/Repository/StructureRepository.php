<?php
namespace Lyssal\TourismeBundle\Repository;

use Lyssal\StructureBundle\Repository\EntityRepository;
use Lyssal\TourismeBundle\Entity\StructureType;

class StructureRepository extends EntityRepository
{
    /**
     * Retourne les structures par type aux alentours de coordonnées géographiques.
     *
     * @param \Lyssal\TourismeBundle\Entity\StructureType $structureType Type de structure
     * @param array<float, float> $coordonnees Coordonnées latitude + longitude
     * @param integer $distanceMaximale Distance maximale en mètres
     * @return array<\Lyssal\TourismeBundle\Entity\Structure, float> Structures avec leur distance
     */
    public function findByTypeAndCoordonnees(StructureType $structureType, array $coordonnees, $distanceMaximale)
    {
        $requete = $this->createQueryBuilder('structure');
        
        $requeteExpressionDistance = $requete->expr()->sqrt('Power(ville.latitude - :latitude, 2) + Power(ville.longitude - :longitude, 2)');
        
        $requete
            ->addSelect($requeteExpressionDistance)
            ->innerJoin('structure.ville', 'ville')
            ->addSelect('ville')
            ->innerJoin('structure.types', 'structureType')
            ->addSelect('structureType')
            ->where($requete->expr()->orX('structureType = :structureType', 'structureType.parent = :structureType'))
            ->setParameter('structureType', $structureType)
            ->andWhere($requete->expr()->lt($requeteExpressionDistance, $distanceMaximale * 0.00000899))
            ->setParameter('latitude', $coordonnees[0])
            ->setParameter('longitude', $coordonnees[1])
            ->orderBy($requeteExpressionDistance, 'ASC')
        ;
        
        $resultats = $requete->getQuery()->execute();
        
        foreach ($resultats as $i => $resultat)
        {
            $resultats[$i][1] = (floatval($resultat[1]) / 0.00000899); // Distance en mètres
        }

        return $resultats;
    }
}
