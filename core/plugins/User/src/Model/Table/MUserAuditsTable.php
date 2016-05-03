<?php
namespace User\Model\Table;
use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use User\Model\Entity\MUserAudit;
use Cake\Network\Session;
use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;
use AppServiceUtil\Utility\AppServiceUtil;
use Cake\Network\Exception\BadRequestException;
use Cake\DataSource\ConnectionManager;
use AppServiceUtil\Model\Table\UtilTableTrait;
use Cake\I18n\Time;


/**
 * MUserAudits Model
 *
 * 
 */
class MUserAuditsTable extends Table
{
   use UtilTableTrait;
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        

        $this->table('m_user_audits');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        
    }
    
    
    /**
     * 
     * @return string
     */
    public static function defaultConnectionName() {
        return 'default'; //Set whatever required
    }
    
    /**
     * method to insert user activity at every login and logout
     * @param type $fieldsArray
     * @return type  integer (id if success else error code )
     */
    public function insertUserActivity($fieldsArray = []) {
        //Create New Entity
        $UserAudit = $this->newEntity();
        //Patch New Entity Object with request data
        $UserAudit = $this->patchEntity($UserAudit, $fieldsArray);
            //Create new row and Save the Data
        $result = $this->save($UserAudit);
            if ($result) {
                return $result->id;
            } else {
                return AppServiceUtil::errResponse('SERVER_ERROR');
            }
        
    }
    
    
    
    /**
     * method to update user activity ex logout
     * @param type $fieldsArray
     * @return type  integer (id if success else error code )
     */
    public function updateUserActivity($fieldsArray = []) {
        if (empty($fieldsArray['id'])) {
            return AppServiceUtil::errResponse('AUDIT_ID_MISSING');
        }
        $uid = $fieldsArray['id'];
        
        //Create New Entity        
        $UserAudit = $this->get($uid);
        if ($UserAudit) {
            //Update New Entity Object with data
            $UserAudit = $this->patchEntity($UserAudit, $fieldsArray);
                $result = $this->save($UserAudit);
                if ($result) {
                     return $result->id;
                } else {
                    return AppServiceUtil::errResponse('SERVER_ERROR');
                }
            
        } else {
            return AppServiceUtil::errResponse('INVALID_REQUEST');
        }
    }

    

   
}
