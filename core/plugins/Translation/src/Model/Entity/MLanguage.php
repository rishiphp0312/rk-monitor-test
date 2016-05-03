<?php
namespace Translation\Model\Entity;

use Cake\ORM\Entity;

/**
 * MLanguage Entity.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property bool $rtl
 * @property bool $isDefault
 * @property int $version
 * @property string $translation_object
 * @property int $modified_user_id
 * @property \App\Model\Entity\ModifiedUser $modified_user
 * @property \Cake\I18n\Time $modified
 * @property int $created_user_id
 * @property \App\Model\Entity\CreatedUser $created_user
 * @property \Cake\I18n\Time $created
 */
class MLanguage extends Entity
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
