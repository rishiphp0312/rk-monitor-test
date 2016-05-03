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
class MUserLoginHistoryTable extends Table
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
        $this->table('m_user_login_history');
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
     * method to add/update User Login History
     * @param $params
     * @return type  integer (id if success else null )
     */
    public function accessUserLoginHistory($params = []) {
      $insertedId = null;
      $clientIp = AppServiceUtil::getIpAddress();
      $dbDate = date('Y-m-m H:i:s');
      $userLoginHistory = $this->newEntity();
      if (!empty($params["UserLoginId"])) {
         $userLoginHistory->id = $params["UserLoginId"];
         $userLoginHistory->logout_date = $dbDate;
      } else {
         $userLoginHistory->created = $dbDate;
      }
      $userLoginHistory->ip_address = $clientIp;
      if (!empty($params["user_id"])) {
         $userLoginHistory->user_id = $params["user_id"];
         $result = $this->save($userLoginHistory);
      }
      if (!empty($result))
         $insertedId = $result->id;
      return $insertedId;   
    }

    

   
}
