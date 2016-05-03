<?php
namespace Module\Model\Entity;

use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\Entity;

/**
 * Module Entity.
 */
class Module extends Entity
{
	use TranslateTrait;

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
     protected $_accessible = [
        '*' => true,
       
    ];

	
   
	
}
