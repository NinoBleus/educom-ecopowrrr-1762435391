<?php

namespace App\Repository;

use App\Entity\BuyPricePeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BuyPricePeriod>
 */
class BuyPricePeriodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BuyPricePeriod::class);
    }

    public function saveBuyPricePeriod(array $params): ?BuyPricePeriod
    {
        $buyPricePeriod = null;

        if (!empty($params['id'])) {
            $buyPricePeriod = $this->find($params['id']);

            if ($buyPricePeriod === null) {
                return null; // service layer can detect not found
            }
        }

        if ($buyPricePeriod === null) {
            $buyPricePeriod = new BuyPricePeriod();
        }

        if (array_key_exists('pricePerKwh', $params)) {
            $buyPricePeriod->setPricePerKwh((string) $params['pricePerKwh']);
        }

        if (array_key_exists('validFrom', $params)) {
            $buyPricePeriod->setValidFrom($params['validFrom']);
        }

        if (array_key_exists('validTo', $params)) {
            $buyPricePeriod->setValidTo($params['validTo'] ?: null);
        }

        $this->getEntityManager()->persist($buyPricePeriod);
        $this->getEntityManager->flush();

        return $buyPricePeriod;
    }
    
    public function readBuyPricePeriod($buyPricePeriodId) {
        return($this->find($buyPricePeriodId));       
    }

    public function deleteBuyPricePeriod($id) {

    $buyPricePeriod = $this->find($id);
    if($buyPricePeriod) {
        $this->getEntityManager()->remove($buyPricePeriod);
        $this->getEntityManager()->flush();
        return(true);
    }

    return(false);
    }
}
