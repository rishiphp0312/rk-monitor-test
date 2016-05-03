<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MUserAudit Entity.
 *
 * @property int $id
 * @property string $user_id
 * @property \App\Model\Entity\User $user
 * @property string $ip_address
 * @property \Cake\I18n\Time $login_date
 * @property \Cake\I18n\Time $logout_date
 * @property string $module_name
 * @property string $login_status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 */
class MUserAudit extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];
}
