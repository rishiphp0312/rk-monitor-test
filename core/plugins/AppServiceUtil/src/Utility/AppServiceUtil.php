<?php

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @since         2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace AppServiceUtil\Utility;

use Cake\ORM\TableRegistry;
use Cake\Utility\ArrayAccess;
use Cake\Utility\Hash;
use Cake\Network\Email\Email;
use Cake\Core\Configure;

/**
 * Library for manipulation of custom array and json data
 */
class AppServiceUtil {
    /* Function is used for flatten validation errors 
     *  @params $errors array
     *  @return flat array 
     */

    public static function flattenEntityValidationError($errors = []) {
        $data = [];
        if (!empty($errors) && is_array($errors)) {
            $flaterrors = Hash::flatten($errors);
            if (!empty($flaterrors)) {
                $data = array_values($flaterrors);
            }
        }
        return $data;
    }

    /* Function is used to return error reponse data for api call response
     *  @params $errors array,$err_key = errCode/errMsg key to write error response,$flatten_arr = true/false to convert arr as flat      *  or not
     *  @return flat array 
     */

    public static function errResponse($errors = [], $errKey = 'errCode', $flatten_arr = true) {
        $errCode = NULL;
        $errMsg = NULL;
        if ($flatten_arr && is_array($errors)) {
            $errors = self :: flattenEntityValidationError($errors);
        }
        if ($errKey == 'errCode') {
            $errCode = $errors;
        } else {
            $errMsg = $errors;
        }
        return ['hasError' => TRUE, 'err' => ['errCode' => $errCode, 'errMsg' => $errMsg]];
    }

    /**
     * method to get client ip address 
     * @return type
     */
    public static function getIpAddress() {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))  //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else
            $ip = $_SERVER['REMOTE_ADDR'];

        return $ip;
    }

    /**
     *  sendEmail method  to  send email
     * @param type $toEmail  recievers email
     * @param type $fromEmail senders email
     * @param type $subject subject of email
     * @param type $message message of email
     * @param type $type type method used for smtp
     */
    public static function sendEmail($toEmail, $fromEmail, $subject = null, $message = null, $type = 'smtp') {
        $return = false;
        try {
            if (!empty($toEmail) && !empty($fromEmail)) {
                ($type == 'smtp') ? $type = 'defaultsmtp' : $type = 'default';
                $emailClass = new Email($type);
                $result = $emailClass->emailFormat('html')->from([$fromEmail => $subject])->to($toEmail)->subject($subject)->send($message);
                if ($result) {
                    $return = true;
                }
            }
        } catch (Exception $e) {
            $return = $e;
        }

        return $return;
    }

    /**
     * Function to map backend model fileds with front end and vice versa
     * @param $msg
     * @return
     * $skipColArr define those cols which needs to be unset  
     * $serviceReq boolean true or false  
     */
    public static function backendFrontendFieldsMap($model, $requestKeys, $serviceReq, $skipColArr) {
        $mapping = [
            'Users' => ['workspaceId' => 'workspace_id', 'firstName' => 'first_name', 'lastName' => 'last_name', 'contactNumber' => 'contact_number', 'lastLoginDate' => 'last_login_date',
                'lastLoginIp' => 'last_login_ip', 'isActive' => 'is_active', 'isDeleted' => 'is_deleted', 'unAgency' => 'un_agency', 'createdUserId' => 'created_user_id', 'modifiedUserId' => 'modified_user_id'],
            'Roles' => ['id' => 'id', 'workspaceId' => 'workspace_id', 'role_gid' => 'roleGid',
                'roleName' => 'role_name', 'roleDescription' => 'role_description',
                'isSystemRole' => 'is_system_role', 'isActive' => 'is_active', 'isDeleted' => 'is_deleted'],
            'UserRoles' => ['roleId' => 'role_id', 'userId' => 'user_id'],
            'RoleModulePermission' => ['roleId' => 'role_id']
        ];
        if (!empty($mapping[$model])) {
            $mapKeys = $mapping[$model];
            // Start loop model key appears
            foreach ($mapKeys as $keyM => $actual) {
                if (array_search($keyM, $skipColArr) !== false) {

                    unset($requestKeys[$keyM]);
                }
                // If key found in request data ,Replace it
                if (isset($requestKeys[$keyM])) {

                    $requestKeys[$actual] = $requestKeys[$keyM];
                    if ($keyM != $actual)
                        unset($requestKeys[$keyM]);
                }
            }
        }
        return $requestKeys;
    }

    /**
     * method to encrypt data
     * @param type $plaintext
     * @return type
     */
    public static function encryptData($plaintext = '') {
        $publicKey = '-----BEGIN PUBLIC KEY-----
MIGeMA0GCSqGSIb3DQEBAQUAA4GMADCBiAKBgGizHUqM0IWLNH+/AZdCDSrQoRFL
tUqhR7ismjSV9AwtK7TkKukrf3kQQfV2NGwOVsSE4zwe4fp6vVHGwfIcmHxjCK8H
12uPN6tFcuJgsZWgQBp76PGILanAKsZOaYdHeKBrgcPpv8vS7m/ExvN2lBTK9Tmv
xkgwx4mT+whVVv+9AgMBAAE=
-----END PUBLIC KEY-----';


        openssl_public_encrypt($plaintext, $encrypted, $publicKey);
        $data = base64_encode($encrypted);
        return $data;   //encrypted string
    }

    /**
     * method to decrypt data
     * @param type $encryptedData is data in  encrypted format 
     * @return type plain text after decryption 
     */
    public static function decryptData($encryptedData = '') {


        $privateKey = '-----BEGIN RSA PRIVATE KEY-----
MIICWgIBAAKBgGizHUqM0IWLNH+/AZdCDSrQoRFLtUqhR7ismjSV9AwtK7TkKukr
f3kQQfV2NGwOVsSE4zwe4fp6vVHGwfIcmHxjCK8H12uPN6tFcuJgsZWgQBp76PGI
LanAKsZOaYdHeKBrgcPpv8vS7m/ExvN2lBTK9Tmvxkgwx4mT+whVVv+9AgMBAAEC
gYBZEul8n0hYFRJZDFuCIAOrxUsCt/JIx+WIy+91hY3XPibNAsEvFn6gtKApAKOg
uqI/Fv9sCqoeu4WNqRcfsAxZz61pLwcQHrXhU9ikxQHIIhlc2TkWD8EeUkVuZHhH
ThF3Wn3ltBn3fR4NTvUPbwT+wrP+B7tDLxC4QQ7MPR61AQJBAK1/5BE3QP1bmCWm
l7HSR3VZEg7hF3iXwXBkW5aqV9ijS+IKaTYZqezU3p68/xcW3KKrrb8/nlXDwO0n
8KbM860CQQCafDVV8VQRdKOB+OGDaEDfEEAeB9+afUmwhuMBLdu2DVbVOBoQRNUn
Eh4jQTl26/5Jz3wN8ScsMRhegTGYoj5RAkBWNrTn8SL0Qu2J2AyNKkakA0y75BI7
tH1FEjmI1sCsQAjXHAFIBtyveN/e1V+U46FjnBfMbxqI16sora4h1LpJAkAc4D6v
8838/UpazwSIJYKKr2TsuBgJroWJo4zm+YVqABBNBpGInPUiunY7rMNrAS2k6k2L
5Zmm3v5pG8kDeMAhAkAGUwSTlLZcPLuH4835HisBAGKhrZIzv2GlMmHCHqWB4e7h
h9qiv/VPmZ0aR7JeUFr5gLNoe88v801djYgb09/Y
-----END RSA PRIVATE KEY-----';



        if (!$privateKeyRes = openssl_pkey_get_private($privateKey))
            die('Loading Private Key failed');
        //Decrypt
        if (!openssl_private_decrypt(base64_decode($encryptedData), $decryptText, $privateKeyRes))
            die('Failed to decrypt data');

        //Free key
        openssl_free_key($privateKeyRes);

        return $decryptText;
    }

    /**
     * returns mime type of file 
     * @param type $filename tmp_name of file 
     * @return type
     */
    public static function getMimeContentType($filename) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $filename);
        finfo_close($finfo);

        return $mimetype;
    }

    /**
     * 
     * @return JSON/boolean
     * @throws NotFoundException When the view file could not be found
     * 	or MissingViewException in debug mode.
     */
    public static function readXlsOrCsv($filename = null, $unlinkFile = true, $sheetnames = null, $inputFileType = null) {

        require_once(ROOT . DS . 'vendor' . DS . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');
        /**  Identify the type of $inputFileName  * */
        if (empty($inputFileType))
            $inputFileType = \PHPExcel_IOFactory::identify($filename);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);

        if (!empty($sheetnames)) {
            //$objReader->setReadDataOnly(true);
            $objReader->setLoadSheetsOnly($sheetnames);
        }
        $objPHPExcel = $objReader->load($filename);

        //$objPHPExcel = \PHPExcel_IOFactory::load($filename);
        if ($unlinkFile == true)
            $this->unlinkFiles($filename); // Delete The uploaded file
        return $objPHPExcel;
    }

    /**
     * Process File uploads
     * 
     * @param array $files POST $_FILES Variable
     * @param array $allowedMimetypes Valid mime types allowed 
     * @param allowedFilesize can be passed inside extra array 
     * @return uploaded filename
     */
    public static function processFileUpload($files = null, $allowedMimetypes = [], $extra = []) {

        // Check Blank Calls
        if (!empty($files)) {

            foreach ($files as $fieldName => $fileDetails):
                // Check if file was uploaded via HTTP POST
                if (!is_uploaded_file($fileDetails['tmp_name'])) :
                    return ['error' => 'FILE_NOT_UPLOADED_HTTP_POST'];
                endif;


                $dest = XLS_PATH . DS . $fileDetails['name'];



                $mimeType = self ::getMimeContentType($fileDetails['tmp_name']);
                if (!in_array($mimeType, $allowedMimetypes)) {
                    return ['error' => 'INVALID_FILE'];
                }/**/

                if (isset($extra['allowedFilesize']) && is_numeric($extra['allowedFilesize'])) {
                    if ($fileDetails['size'] > $extra['allowedFilesize']) { // IN bytes - 1048576 bytes is 1 MB
                        return ['error' => 'INVALID_FILESIZE'];
                    }
                }

                // Upload File
                if (move_uploaded_file($fileDetails['tmp_name'], $dest)) :
                    $filePaths[] = $dest;   // Upload Successful

                else:
                    return ['error' => 'UPLOAD_FAILED'];   // Upload Failed
                endif;

            endforeach;

            return $filePaths;
        }
        return ['error' => 'INVALID_REQUEST'];
    }

    /**
     * Function to set app current language
     *
     * @return siteLanguage string
     */
    public static function setAppCurrentLanguage() {
        
    }

    /**
     * method to get all language list
     * @defaultLang if this variable is true will return default language 
     */
    public static function getAllLanguages($defaultLang = false) {
        $langTblObj = TableRegistry::get('Translation.MLanguages');
        $conditions = [];
        if ($defaultLang === true || $defaultLang == 'true') {
            $conditions = ['is_default' => LANGUAGES_DEFAULT_VALUE];
        }
        $data['languagesList'] = $langTblObj->getRecords(['id', 'code', 'name', 'rtl', 'isDefault' => 'is_default'], $conditions, 'all');
        return $data;
    }

    /**
      method to check is global admin or not
      return if not global admin return will be 0 else more than 0
     */
    public static function checkGlobalAdminRole($userId = '') {
        $UserRoleTblObj = TableRegistry::get('User.UserRoles');
        //$data =  $this->UserRoleTblObj->getRecords(['MRoles.id'], ['user_id' => $userId],'all',['contain'=>['MRoles']]);
        return $cnt = $UserRoleTblObj->getCount(['user_id' => $userId, 'MRoles.role_gid' => GLOBAL_ADMIN_GID_ID, 'MRoles.is_system_role' => SYSTEM_ROLE_YES], ['contain' => ['MRoles'], 'debug' => false]);
    }
    
    /**
     * method to get logged in user workspace Id 
     * @param type $userId
     */
    public static function getUserWorkspaceId($userId=''){
        $UserWorkspaceTblObj = TableRegistry::get('User.UserWorkspace');
        $data =  $UserWorkspaceTblObj->getFirst(['UserWorkspace.workspace_id'],['user_id' => $userId]);
        return (!empty($data))?$data['workspace_id']:'';
           
    }

}
