<?php

namespace Translation\Controller\Component;
use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;
use AppServiceUtil\Utility\AppServiceUtil;
use Cake\Core\Configure;

/**
 * Translation  Component used to perform language translation related activities
 */
class TranslationComponent extends Component {

    public $TransTblObj = NULL;
    public $controller = '';
    public $components = ['AppServiceUtil.UtilCommon', 'Auth'];

    /*
     * Initialize function to setup some initial valrs and call method to do intial function
     * @access public
     * @params $config array to populate initialize time config vars to user component
     */

    public function initialize(array $config) {
        parent::initialize($config);
        $this->TransTblObj = TableRegistry::get('Translation.MTranslations');
        $this->LanguagesObj = TableRegistry::get('Translation.MLanguages');
    }

    /**
     * method to add  translations 
     */
    public function addTranslation() {
        $postedData = $this->request->data;
        $postedData['created_user_id'] = $postedData['modified_user_id'] = $this->Auth->User('id');
        $defaultLangDt = $this->LanguagesObj->getFirst(['code'], ['isDefault' => LANGUAGES_DEFAULT_VALUE]); //get default lang code  
        $defaultLangDtCode = '';
        if (!empty($defaultLangDt)) {
            $defaultLangDtCode = $defaultLangDt['code']; //default language code 
            Configure::write('DFLT_LANG_CODE', $defaultLangDtCode);
        }
        if (isset($postedData['id']))
            unset($postedData['id']);
        // save translations
        return $result = $this->TransTblObj->insertTranslation($postedData);
    }

    /**
     * method to return list of translations for specific language 
     * @return type
     * @langCode is the language code (en or fr )
     */
    public function listTranslation() {
        $transList = [];
        $langCode = (isset($this->request->data['langCode'])) ? $this->request->data['langCode'] : '';
        $alllangCodeList = $this->LanguagesObj->getList([ 'id', 'code'], []);
        if (!empty($langCode)) {

            $langCodeList = $this->LanguagesObj->getList([ 'id', 'code'], ['isDefault' => LANGUAGES_DEFAULT_VALUE]); //get default lang list 
            $fields = array_merge(['id', 'code'], $langCodeList);

            if (in_array($langCode, $alllangCodeList) == true)
                $fields = array_merge($fields, [$langCode]);
            else
                return AppServiceUtil::errResponse('INVALID_REQUEST');

            $transList['Translations'] = $this->TransTblObj->getRecords($fields, [], 'all');
        }
        return $transList;
    }

    /**
     * get specifc translation data
     * @langCode language code 
     * @return type array 
     */
    public function getTranslationDetail() {
        $data = [];
        $code = isset($this->request->data['langCode']) ? $this->request->data['langCode'] : null; //translation code which will be unique 
       
        if (!empty($code)) {
            $langCodeList = $this->LanguagesObj->getList(['id', 'code'], []);
            $fields = array_merge(['id', 'code'], $langCodeList);

            $data['Translations'] = $this->TransTblObj->getFirst($fields, ['code' => $code]);
            if (empty($data))
                return AppServiceUtil::errResponse('INVALID_REQUEST');
        }
        return $data;
    }

    /**
     * method to delete translation
     * @id is the translation id 
     * @return type 
     */
    public function deleteTranslation() {

        $id = isset($this->request->data['id']) ? $this->request->data['id'] : null;
        if (empty($id)) {
            return AppServiceUtil::errResponse('MISSING_PARAMETERS');
        } else {
            $conditions = ['id' => $id];
            $result = $this->TransTblObj->deleteAll($conditions);
            if ($result == 0) {
                return AppServiceUtil::errResponse('INVALID_REQUEST');
            }
        }
    }

    /**
     * method to compile translations
     * langCode code for which the language is compiled
     */
    public function compileTranslation() {
        $code = isset($this->request->data['langCode']) ? $this->request->data['langCode'] : null;
        if (empty($code)) {
            return AppServiceUtil::errResponse('MISSING_PARAMETERS');
        } else {
            
            return $result = $this->publishLanguage($code);
        }
    }

    /**
     * 
     * @param type $code
     * @param type $createFiles boolean if true will create file default is false
     * @return type
     */
    public function publishLanguage($code = null, $createFiles = false) {

        $langExists = $this->LanguagesObj->getFirst([], ['code' => $code]);
        if (!empty($langExists)) {
            $translationsList = $this->TransTblObj->getList([ 'code', $code], []);

            if ($createFiles == true) {
                // Write JSON File
                /* $fh = fopen(_PUBLISH_PATH . '/' . $code . '.json', 'w');
                  fwrite($fh, json_encode($translationsList));
                  fclose($fh); */
            } else {
                // Update Published translations
                $langExists['translation_object'] = json_encode($translationsList);
            }
            $this->poGenerateFromExcel($code,$translationsList);// add strings to po file 
            // Update Version (Increment)
            $langExists['version'] ++;
            $this->LanguagesObj->updateLanguage($langExists);
        } else {

            return AppServiceUtil::errResponse('INVALID_REQUEST');
        }
    }

    /**
     * method to update  translations 
     */
    public function modifyTranslation() {
        $postedData = $this->request->data;
        $postedData['modified_user_id'] = $this->Auth->User('id');
        $defaultLangDt = $this->LanguagesObj->getFirst(['code'], ['isDefault' => LANGUAGES_DEFAULT_VALUE]); //get default lang code  
        $defaultLangDtCode = '';
        if (!empty($defaultLangDt)) {
            $defaultLangDtCode = $defaultLangDt['code'];
            Configure::write('DFLT_LANG_CODE', $defaultLangDtCode);
        }
        // save translations
        return $result = $this->TransTblObj->updateTranslation($postedData);
    }

    /**
     * Method to export  translations 
     */
    public function exportTranslation() {
        set_time_limit(0);
        //------ Write File
        //Get PHPExcel vendor
        require_once(ROOT . DS . 'vendor' . DS . 'PHPExcel' . DS . 'PHPExcel' . DS . 'IOFactory.php');
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        // Title Row
        $titleRow = ['Code'];
        $langList = $this->LanguagesObj->getList(['id', 'code'], []); // get list of language codes
        $translations = $this->TransTblObj->getRecords([], [], 'all'); // // get all records  of translations  
        $titleRow = array_merge($titleRow, $langList); // merge id ,code with all available languages 
        //Prepare Title row Cells
        $character = 'A';
        $row = 1;
        foreach ($titleRow as $titleColumns) {
            $objPHPExcel->getActiveSheet()->SetCellValue($character . $row, $titleColumns);
            $character++;
        }

        //Prepare Data row Cells
        foreach ($translations as $translation) {
            $row++;
            $character = 'A';
            $transCode =(!empty($translation['code']))?$translation['code']:'';    
            $objPHPExcel->getActiveSheet()->SetCellValue($character . $row, $transCode);
            $character++; //Increment Column
            
            foreach ($langList as $lang) {
                $transLang =(isset($translation[$lang]) && !empty($translation[$lang]))?$translation[$lang]:'';    
                $objPHPExcel->getActiveSheet()->SetCellValue($character . $row, $transLang);
                $character++; //Increment Column
            }
        }

        // Write Title and Data to Excel
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $returnFilename = 'TRANSLATIONS' . '_' . date('Y-m-d-h-i-s') . '.xls';
        $returnFilePath = TRANSLATIONS_PATH_WEBROOT . DS . $returnFilename;
        $objWriter->save($returnFilePath);
        return ['exportTranslations'=>WEBSITE_URL.DS.$returnFilePath];

    }

    /**
     * IMPORT Translations of Language
     * 
     * @param 
     * @return boolean true/false
     */
    public function importTranslation() {

        $uploadedPath = '';
        $titleRow = $dataArray = $updateArray = $insertArray = [];
        $dataKeys = ['0' => 'code'];
        //$filepath = WWW_ROOT . DS . "TRANSLATIONS.xls";       // for testing purpose       
        $allowedMimeTypes = ['xls' => 'application/vnd.ms-excel', 'xls2' => 'application/vnd.ms-office',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

        $validateUploadProcess = AppServiceUtil::processFileUpload($_FILES, $allowedMimeTypes); //validate uploaded  file 

        if (isset($validateUploadProcess['error'])) {
              return  AppServiceUtil::errResponse($validateUploadProcess['error']); // returns error code if any error exists 
        } else {
            $uploadedPath = $validateUploadProcess[0];
        }
        $objPHPExcel = AppServiceUtil::readXlsOrCsv($uploadedPath, false);       // return  object after reading xls file

        $langList = $this->LanguagesObj->getList(['id', 'code'], []);        // get list of language codes
        $translationsCodes = $this->TransTblObj->getList(['id', 'code'], []); // get list of translation  codes
        $langCodeList = array_merge($dataKeys, $langList);
        $user = $this->Auth->user();

        // Iterate through Worksheets
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {

            $highestRow = $worksheet->getHighestRow(); // e.g. 10
            $highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
            $startRows = 1;

            // get prepared data for all rows
            for ($row = $startRows; $row <= $highestRow; ++$row) {
                for ($col = 0; $col < $highestColumnIndex; ++$col) {
                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                    $val = $cell->getValue();

                    if ($row == 1) {
                        $titleRow[] = strtolower($val);
                    } else if ($row > $startRows) {
                        $dataArray[$row][] = $val;
                    } else {
                        continue;
                    }
                }
                // validate format of sheet at row 1 
                if ($row == 1) {
                    $errorFormat = $this->validateSheetFormat($titleRow, $langCodeList);
                    if (isset($errorFormat['error'])) {
                        return AppServiceUtil::errResponse($errorFormat['error']);
                    }
                }

                // code is mandatory title in sheet  
                if ($row > $startRows) {
                    // Attach column names to their values
                    $dataArray[$row] = array_combine(array_replace($titleRow, $dataKeys), $dataArray[$row]);
                    // Only pick existing language columns in DB and neglect others                   
                    $dataArray[$row] = array_intersect_key($dataArray[$row], array_flip($langCodeList));
                    $dataArray[$row]['modified_user_id'] = $user['id'];

                    if (!empty($dataArray[$row]['code'])) {
                        if (in_array($dataArray[$row]['code'], $translationsCodes)) {  // MODIFY/UPDATE case 
                            $dataArray[$row]['id'] = array_search($dataArray[$row]['code'], $translationsCodes);
                            $this->TransTblObj->updateTranslation($dataArray[$row]);
                        } else {                                                   // INSERT  case 
                            $dataArray[$row]['created_user_id'] = $user['id'];
                            $this->TransTblObj->insertTranslation($dataArray[$row]);
                        }
                    }
                }
            }
        }


        return true;
    }

    /**
     * validate sheet format of translations 
     * @param type array $titleRow
     * @param type array $langCodeList
     */
    public function validateSheetFormat($titleRow = [], $langCodeList = []) {
        if (empty($titleRow)) {
            return ['error' => 'INVALID_FORMAT'];  // 1 row should be title row with columns 
        }
        if (in_array('code', $titleRow) == true) {
            $cnt = 0;
            if (!empty($langCodeList)) {
                $defaultLangDt = $this->LanguagesObj->getFirst(['code'], ['isDefault' => LANGUAGES_DEFAULT_VALUE]); //get default lang list 
                $defaultLangDtCode = '';
                if (!empty($defaultLangDt)) {
                    $defaultLangDtCode = $defaultLangDt['code']; //default lang code 
                    Configure::write('DFLT_LANG_CODE', $defaultLangDtCode);
                }
                $cnt = 0;
                foreach ($titleRow as $value) {
                    $value = strtolower(trim($value));
                    if (strtolower($defaultLangDtCode) == $value) {
                        $cnt = 1;
                    }
                }
                if ($cnt == 0)
                    return ['error' => 'DEFAULT_LANG_MISSING'];  // sheet must have default language        
            }else {
                return ['error' => 'INVALID_REQUEST'];
            }
        } else {
            return ['error' => 'INVALID_FORMAT']; // code column must be present in excel sheet title 
        }
    }
    
    
    /**
     * method to get all language list
     * @defaultLang if this variable is true will return default language 
     */
    public function getLanguageList(){
         $conditions=[];
         $defaultLang = (isset($this->request->data['defaultLang']))?$this->request->data['defaultLang']:false;
         return AppServiceUtil:: getAllLanguages($defaultLang);
         
    }
    
    /**
     * 
     * @param type $lang
     * @return string
     */
    public function getPoHeader($lang) {
        $str = '';
        $str.='msgid ""' . PHP_EOL;
        $str.='msgstr ""' . PHP_EOL;
        $str.='"Plural-Forms: nplurals=2; plural=(n != 1);"' . PHP_EOL;
        $str.='"Project-Id-Version: DFA-Monitor"' . PHP_EOL;
        $str.='"POT-Creation-Date: "' . PHP_EOL;
        $str.='"PO-Revision-Date: "' . PHP_EOL;
        $str.='"Last-Translator: "' . PHP_EOL;
        $str.='"Language-Team: DFA-Monitor <equist@dataforall.org>"' . PHP_EOL;
        $str.='"MIME-Version: 1.0"' . PHP_EOL;
        $str.='"Content-Type: text/plain; charset=UTF-8"' . PHP_EOL;
        $str.='"Content-Transfer-Encoding: 8bit"' . PHP_EOL;
        $str.='"Language: ' . $lang . '"' . PHP_EOL;
        $str.='"X-Generator: Poedit 1.8.5"' . PHP_EOL;
        $str.='"X-Poedit-SourceCharset: UTF-8"' . PHP_EOL;
        return $str;
    }
    
    /**
     * method to update po file for specific language 
     * @param type $langCode
     */
    public function poGenerateFromExcel($langCode='',$transData=[]) {
        $keyCol = 0;
        $logMsg = '';
        $poFile = 'default.po';
    
        $website_base_url='';
        $website_base_url = $_SERVER['SCRIPT_FILENAME'] . "/";
        $website_base_url = str_replace('webroot/', '', $website_base_url);
        $website_base_url = str_replace('index.php/', '', $website_base_url);
        $langPOFilePath = $website_base_url.DS.'src'.DS.'Locale' . DS . $langCode  ;
        $langPOFile = $langPOFilePath .DS.$poFile; 
            // Goto to src
      
                 // Get Header for PO file
        $header = $this->getPoHeader($langCode);
        // Create a backup of file and store to tmp
        $backupDir = PO_BCKP_PATH ;
        if (!file_exists($backupDir)) {
            mkdir($backupDir);
        }

        $backupFile = $backupDir . DS . time() . '_' . $langCode . '_' . $poFile;
        if (copy($langPOFile, $backupFile)) {
            $handle = fopen($langPOFile, 'w');
            $str = '';
            $str.=$header;
            foreach ($transData as $code=> $langValue){
                $row1 = 'msgid "' . $code . '"' . PHP_EOL;
                $row2 = 'msgstr "' . $langValue . '"' . PHP_EOL;

                $str.=$row1;
                $str.=$row2;
                $str.=PHP_EOL;
            }
            
            // Write to file
            fwrite($handle, $str);
            fclose($handle);
            // Backup done.Do changes
          
            
           
        } 

      
        
       
    }
    
    
    /**
     * READ PUBLISHED/COMPILED Language
     * $version version no
     * $readFiles by default false else if true will create file 
     * @param string $code Language Code
     * @return boolean true/false
     */
    public function readPublishedLanguage() {
        $data = $this->request->data();
        $code = (isset($data['langCode']))?$data['langCode']:'';
        $version = (isset($data['version']))?$data['version']:'';
        $readFiles = (isset($data['readFiles']))?$data['readFiles']:false;
        if(empty($code)){
            return AppServiceUtil::errResponse('MISSING_LANG_CODE');
        }
        
        $langDetails = $this->LanguagesObj->getFirst(['version','translation_object'], ['code' => $code]); 
        if(!empty($langDetails)) {
            if(!empty($version)) {
                if($version == $langDetails['version']) {
                    return  ['isRecent'=>true];
                }
            }

            if($readFiles == true) { 
                // language files are no more available. Now language compiled object is being stored in database
                // else condition will be executed everytime
                
            } else {
                $return = ['version' => $langDetails['version'], 'readPublishedLang' => json_decode($langDetails['translation_object'], true),    'langCode'=> $code];
            }
        } else {
            return AppServiceUtil::errResponse('INVALID_REQUEST');
        }
        if(isset($return['version'])){
           return array_merge($return,['isRecent'=>false]);
        }
        
    }


}
