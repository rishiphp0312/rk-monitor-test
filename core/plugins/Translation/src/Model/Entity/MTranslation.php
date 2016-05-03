<?php
namespace Translation\Model\Entity;

use Cake\ORM\Entity;

/**
 * MTranslation Entity.
 */
class MTranslation extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'code' => true,
        'en' => true,
        'fr' => true,
        'modified_user_id' => true,
        'created_user_id' => true,
        'modified_user' => true,
        'created_user' => true,
    ];
}
