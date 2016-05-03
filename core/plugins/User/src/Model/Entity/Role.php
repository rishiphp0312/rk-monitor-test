<?php
namespace User\Model\Entity;
use Cake\ORM\Behavior\Translate\TranslateTrait;

use Cake\ORM\Entity;

/**
 * Role Entity.
 */
class Role extends Entity
{
    use TranslateTrait;
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
     protected $_accessible = ['*' => true];
	
   
	
}
