<?php declare(strict_types=1);

namespace App\Domain\Entities\File;

use App\Domain\AbstractEntity;
use App\Domain\Entities\Catalog\Product as CatalogProduct;
use App\Domain\Entities\FileRelation;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CatalogProductFileRelation extends FileRelation
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Domain\Entities\Catalog\Product", inversedBy="files")
     * @ORM\JoinColumn(name="entity_uuid", referencedColumnName="uuid", nullable=true)
     */
    protected CatalogProduct $catalog_product;

    public function setEntity(AbstractEntity $entity)
    {
        if (is_object($entity) && is_a($entity, CatalogProduct::class)) {
            $this->entity_uuid = $entity->getUuid();
            $this->catalog_product = $entity;
        }

        return $this;
    }
}
