<?php
namespace SSO\Model\Entity;

use Cake\ORM\Entity;
use Cake\Auth\DefaultPasswordHasher;

/**
 * Area Entity.
 */
class User extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
     protected $_accessible = ['*' => true];
     protected $_hidden = ['password'];
	
    protected function _setPassword($password)
    {
        return (new DefaultPasswordHasher)->hash($password);
    }
	
}
