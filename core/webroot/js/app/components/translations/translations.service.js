appConfig.getModule('translations')
.factory('translationsService', ['$http', '$q', '$filter', 'SERVICE_CALL', 'commonService',
function ($http, $q, $filter, SERVICE_CALL, commonService) {

    var translationsService = {};

    translationsService.getLanguageList = function (data) {

        var deferred = $q.defer();
        
        $http(commonService.createHttpRequestObject(SERVICE_CALL.translations.getLanguageList, data))
        .success(function (res) {

            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        })
        return deferred.promise;
    }

    translationsService.getLanguageKeys = function (data) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.translations.getLanguageKeys, data))
        .success(function (res) {

            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        })
        return deferred.promise;
    }

    translationsService.getTranslationDetails = function (data) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.translations.getTranslationDetails, data))
        .success(function (res) {

            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        })
        return deferred.promise;
    }

    translationsService.deleteLanguageKey = function (data) {

        var deferred = $q.defer();
        $http(commonService.createHttpRequestObject(SERVICE_CALL.translations.deleteLanguageKey, data))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.success);
            } else {
                deferred.reject(res.err);
            }
        })
        return deferred.promise;

    }

    translationsService.addTranslation = function (data) {

        var deferred = $q.defer();
        $http(commonService.createHttpRequestObject(SERVICE_CALL.translations.addTranslation, data))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.success);
            } else {
                deferred.reject(res.err);
            }
        })
        return deferred.promise;

    }

     translationsService.modifyTranslation = function (data) {

        var deferred = $q.defer();
        $http(commonService.createHttpRequestObject(SERVICE_CALL.translations.modifyTranslation, data))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.success);
            } else {
                deferred.reject(res.err);
            }
        })
        return deferred.promise;

    }

    translationsService.compileLanguage = function (data) {

        var deferred = $q.defer();
        $http(commonService.createHttpRequestObject(SERVICE_CALL.translations.compileLanguage, data))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.success);
            } else {
                deferred.reject(res.err);
            }
        })
        return deferred.promise;

    }

    translationsService.getExportUrl = function () {

        var deferred = $q.defer();
        $http(commonService.createHttpRequestObject(SERVICE_CALL.translations.getExportUrl))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }

        })
        return deferred.promise;

    }

    translationsService.getUserSelectedLanguage = function () {

        var currentTranslationObj = commonService.localStorage.getKeyValue('translation', false);

        return currentTranslationObj ? (currentTranslationObj.langCode || '') : '';

    }

    translationsService.setUserSelectedLanguage = function (langCode, langDir) {

        var currentTranslationObj = commonService.localStorage.getKeyValue('translation', false);

        if (currentTranslationObj) {
            currentTranslationObj['langCode'] = langCode;
            currentTranslationObj['langDir'] = langDir;
        } else {
            currentTranslationObj = {
                langCode: langCode,
                langDir: langDir
            }
        }

        commonService.localStorage.setKeyValue('translation', currentTranslationObj);

    }

    translationsService.getDefaultLanguage = function () {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.translations.getDefaultLanguage))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }

        })

        return deferred.promise;

    }

    translationsService.setTranslationStrings = function (langCode, langDir) {

        var deferred = $q.defer();

        var currentTranslationObj = commonService.localStorage.getKeyValue('translation', false);

        var translationVersion;

        if (currentTranslationObj) {
            if (langCode == currentTranslationObj.langCode) {
                translationVersion = currentTranslationObj.version || '';
            } else {
                translationVersion = '';
            }
        }

        $http(commonService.createHttpRequestObject(SERVICE_CALL.translations.getTranslationStrings, { langCode: langCode, version: translationVersion }))
        .success(function (res) {
            if (res.success) {
                // if is not recent update the value else resolved.
                if (!res.data.isRecent) {
                    var data = {
                        version: res.data.version,
                        langCode: langCode,
                        translationObj: res.data.readPublishedLang,
                        langDir: langDir
                    }
                    commonService.localStorage.setKeyValue('translation', data);
                }

                deferred.resolve();
            } else {
                deferred.reject(res.err);
            }

        })

        return deferred.promise;

    }

    translationsService.getTranslatedKey = function (key) {

        var value = key;

        var currentTranslationObj = commonService.localStorage.getKeyValue('translation', false);

        if (currentTranslationObj && currentTranslationObj.translationObj) {
            value = currentTranslationObj.translationObj[key] || value;
        }

        return value;

    }

    return translationsService;

} ])