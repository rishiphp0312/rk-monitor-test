appConfig.getModule()
.service('session', ['$rootScope', 'commonService',
function ($rootScope, commonService) {
    var self = this;
    // creates the cookie for current user and also stores user information in root scope.
    self.create = function (user) {
        self.user = user;
        $rootScope.currentUser = user;
        commonService.localStorage.setKeyValue('currentUser', user);
    };

    self.destroy = function () {
        self.id = undefined;
        self.user = undefined;
        $rootScope.currentUser = undefined;
        commonService.localStorage.setKeyValue('currentUser', '');
    };

    self.getSession = function () {
        return commonService.localStorage.getKeyValue('currentUser') || undefined;
    }

} ])

.factory('authService', ['$http', '$q', 'session', '$rootScope', 'commonService', 'SERVICE_CALL',
function ($http, $q, session, $rootScope, commonService, SERVICE_CALL) {

    var authService = {};

    // login
    authService.login = function (credentials) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.system.login, credentials))
        .success(function (res) {
            if (res.success) {
                var data = res.data;
                session.create(data.user);
                deferred.resolve(true);
            } else {
                deferred.reject(res.err);
            }
        });

        return deferred.promise;

    }

    // logout for user.
    authService.logout = function () {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.system.logout))
        .then(function (res) {
            authService.destroySession();
            deferred.resolve(res);
        });

        return deferred.promise;

    }

    // destroy the current session and local storage.
    authService.destroySession = function () {
        session.destroy();
        commonService.localStorage.clearAll();
    }

    // check if user is logged in
    authService.isAuthenticated = function () {

        var deferred = $q.defer();

        var user = session.getSession();

        // if user id found in cookie than request is authetnicated else check via service call.
        if (angular.isUndefined(user) || angular.isUndefined(user.userName)) {
            $http(commonService.createHttpRequestObject(SERVICE_CALL.system.checkSessionDetails))
            .success(function (res) {
                if (res.success) {
                    var data = res.data;
                    session.create(data.user);
                    deferred.resolve(true);
                } else {
                    authService.destroySession();
                    deferred.resolve(false);
                }
            });
        } else {
            $rootScope.currentUser = user;
            deferred.resolve(true);
        }

        return deferred.promise;

    };

    /* 
    * check if user is Authorized -- incase of super admin always authorized.
    * input param: authorizedRoles- required role to authorize.
    */
    authService.isAuthorized = function (authorizedRoles, dbId) {

        var deferred = $q.defer();

        var isAuthorized = false;

        // super admin or state avaiable to all
        // else
        // check for dbid roles.
        if (authService.isSuperAdmin() || authorizedRoles.indexOf('*') >= 0) {
            isAuthorized = true;
            deferred.resolve(isAuthorized);
        } else {
            // convert to array.
            if (!angular.isArray(authorizedRoles)) {
                authorizedRoles = [authorizedRoles];
            }

            // if currentUser has dbRole.
            if ($rootScope.currentUser.dbRole != undefined && $rootScope.currentUser.dbRole.length > 0) {

                angular.forEach($rootScope.currentUser.dbRole, function (value) {
                    if (authorizedRoles != undefined && authorizedRoles.indexOf(value) >= 0) {
                        isAuthorized = true;
                    }
                })

                deferred.resolve(isAuthorized);

            } else {

                commonService.getUserDbRoles({ dbId: dbId })
                .then(function (res) {
                    //set db roles
                    authService.setDBRole(res.data.usrDbRoles);

                    angular.forEach($rootScope.currentUser.dbRole, function (value) {
                        if (authorizedRoles != undefined && authorizedRoles.indexOf(value) >= 0) {
                            isAuthorized = true;
                        }
                    })

                    deferred.resolve(isAuthorized);
                }, function (fail) {
                    isAuthorized = false;
                    deferred.resolve(isAuthorized);
                })
            }

        }

        return deferred.promise;
    };

    return authService;

} ])

.factory('commonService', ['$http', '$q', '$rootScope', '$httpParamSerializerJQLike', 'SERVICE_CALL', 'localStorageService',
function ($http, $q, $rootScope, $httpParamSerializerJQLike, SERVICE_CALL, localStorageService) {

    var commonService = {};

    commonService.createServiceCallUrl = function (serviceCall) {
        return appConfig.serviceCallUrl + serviceCall;
    }

    //creates HTTP request Object as per params Passed.
    commonService.createHttpRequestObject = function (serviceCall, data, url, method, headers) {

        var req = {};

        req['method'] = method || 'POST';

        if (serviceCall) {
            req['url'] = commonService.createServiceCallUrl(serviceCall);
        } else if (url) {
            req['url'] = url;
        }

        if (data) {

            var token = commonService.getToken();

            if (token != '') {
                data['_token'] = token;
            }

            req['data'] = $httpParamSerializerJQLike(data);
        }

        req['headers'] = headers || { 'Content-Type': 'application/x-www-form-urlencoded' };

        return req;
    }

    //gets list of roles.
    commonService.getUserRolesList = function () {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.system.getUserRolesList))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data.roleDetails);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;

    }

    // gets all users List
    commonService.getAllUsersList = function () {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.system.getAllUsersList))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data.usersList);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;

    }

    // get current database roles for a user
    commonService.getUserDbRoles = function (data) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.system.getUserDbRoles, data))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;
    }

    // sets a default value for a property if property does not exist in an object
    commonService.ensureDefault = function (obj, prop, value) {
        if (!obj.hasOwnProperty(prop))
            obj[prop] = value;
    }

    // gets the list of IUS for a dbId
    commonService.getIUSList = function (dbId, idVal) {

        var deferred = $q.defer();

        var dataObj = {
            dbId: dbId,
            type: 'iu',
            onDemand: true
        }

        if (angular.isDefined(idVal)) {
            dataObj['idVal'] = idVal;
        }


        $http(commonService.createHttpRequestObject(SERVICE_CALL.commonService.getIUSList, dataObj))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;
    }

    //get the list of subgroup list for dbid 
    commonService.getSubgroupList = function (dbId, idVal) {

        var deferred = $q.defer();

        var dataObj = {
            dbId: dbId,
            type: 'subgroupVal',
            onDemand: false
        }
        if (angular.isDefined(idVal)) {
            dataObj['idVal'] = idVal;
        }

        $http(commonService.createHttpRequestObject(SERVICE_CALL.commonService.getSubgroupList, dataObj))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        });

        return deferred.promise;
    }

    // get areaList.
    commonService.getAreaList = function (dbId, idVal) {

        var deferred = $q.defer();
        var dataObj = {
            dbId: dbId,
            type: 'Area',
            onDemand: true
        }
        if (angular.isDefined(idVal)) {
            dataObj['idVal'] = idVal;
        }
        $http(commonService.createHttpRequestObject(SERVICE_CALL.commonService.getAreaList, dataObj))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        });

        return deferred.promise;

    }

    // gets the timeperiod List for a dbid
    commonService.getTPList = function (dbId) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.commonService.getTPList, {
            dbId: dbId,
            type: 'tp',
            onDemand: false
        }))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;
    }

    // gets the source list for a dbid
    commonService.getSourceList = function (dbId, idVal) {

        var deferred = $q.defer();
        var dataObj = {
            dbId: dbId,
            type: 'source',
            onDemand: false
        };

        if (angular.isDefined(idVal)) {
            dataObj['idVal'] = idVal;
        }

        $http(commonService.createHttpRequestObject(SERVICE_CALL.commonService.getSourceList, dataObj))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;
    }

    // get IC List.
    commonService.getICList = function (dbId, idVal, icType) {

        var deferred = $q.defer();

        var dataObj = {
            dbId: dbId,
            type: 'ic',
            onDemand: true
        };

        if (angular.isDefined(idVal)) {
            dataObj['idVal'] = idVal;
        };

        if (angular.isDefined(icType)) {
            dataObj['icType'] = icType;
        }

        $http(commonService.createHttpRequestObject(SERVICE_CALL.commonService.getICList, dataObj))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;

    }

    //  get the list of IC indicator for dbid 
    commonService.getICINDList = function (data, idVal) {

        var deferred = $q.defer();

        if (angular.isDefined(idVal)) {
            data['idVal'] = idVal;
        };

        $http(commonService.createHttpRequestObject(SERVICE_CALL.commonService.getICList, data))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;

    }

    //  get the list of IC Indicator Unit  List for dbid 
    commonService.getICIUList = function (data, idVal) {

        var deferred = $q.defer();

        if (angular.isDefined(idVal)) {
            data['idVal'] = idVal;
        };

        $http(commonService.createHttpRequestObject(SERVICE_CALL.commonService.getICList, data))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;

    }

    // get the list of indicators for dbid 
    commonService.getIndicatorList = function (dbId, idVal) {

        var deferred = $q.defer();

        var dataObj = {
            dbId: dbId,
            type: 'ind',
            onDemand: true
        }

        if (angular.isDefined(idVal)) {
            dataObj['idVal'] = idVal;
        }

        $http(commonService.createHttpRequestObject(SERVICE_CALL.commonService.getIndicatorList, dataObj))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;

    }

    //get the list of units for dbid 
    commonService.getUnitList = function (dbId, idVal) {

        var deferred = $q.defer();
        var dataObj = {
            dbId: dbId,
            type: 'unit'
        };
        if (angular.isDefined(idVal)) {
            dataObj['idVal'] = idVal;
        }


        $http(commonService.createHttpRequestObject(SERVICE_CALL.commonService.getUnitList, dataObj))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        })
        return deferred.promise;
    }

    //get indicator, IUS, source, geographycount
    commonService.getAllCounts = function (data) {

        var deferred = $q.defer();
        $http(commonService.createHttpRequestObject(SERVICE_CALL.commonService.getAllCounts, data))
        .success(function (res) {
            if (res.success) {

                deferred.resolve(res.data);
            } else {
                deferred.reject(res.err);
            }
        })
        return deferred.promise;
    }

    // gets on demand url for area
    commonService.getAreaOnDemandURL = function () {
        return commonService.createServiceCallUrl(SERVICE_CALL.commonService.getAreaList);
    }

    // gets on demand url for IUS
    commonService.getIUSOnDemandUrl = function () {
        return commonService.createServiceCallUrl(SERVICE_CALL.commonService.getIUSList);
    }

    // gets on demand url for area
    commonService.getICOnDemandURL = function () {
        return commonService.createServiceCallUrl(SERVICE_CALL.commonService.getICList);
    }

    // gets on demand url for IC IND
    commonService.getICINDOnDemandURL = function () {
        return commonService.createServiceCallUrl(SERVICE_CALL.commonService.getICINDList);
    }

    /** IUS List **/
    commonService.getICTypes = function () {

        var icTypes = [];
        var iusList = {};

        angular.forEach($rootScope.systemConfig.IND_CLASSIFICATIONS_SETTINGS, function (icType) {
            if (icType.visible) {

                icTypes.push({
                    id: icType.type,
                    title: $rootScope.getTranslatedKey(icType.type)//icType.name
                })

                iusList[icType.type] = [];

            }
        })

        return {
            icTypes: icTypes,
            iusList: iusList
        };

    }

    /** User Preferences **/
    commonService.getCurrentUserPref = function () {

        var userPref;

        var userPref = commonService.localStorage.getKeyValue('userPref', true)[$rootScope.currentUser.username];

        return userPref;

    }

    commonService.setCurrentUserLastDbId = function (dbId) {

        var userPref = commonService.localStorage.getKeyValue('userPref', true);

        if ($rootScope.currentUser && $rootScope.currentUser.username) {
            userPref[$rootScope.currentUser.username] = userPref[$rootScope.currentUser.username] || {};

            userPref[$rootScope.currentUser.username]['lastDbId'] = dbId;

            commonService.localStorage.setKeyValue('userPref', userPref);
        }

    }

    /*** Local Storage ***/
    commonService.localStorage = {};

    commonService.localStorage.checkSupport = function () {
        return localStorageService.isSupported;
    }

    commonService.localStorage.setDbIfDoesntExist = function (key) {
        if (commonService.localStorage.getDb(key) == null) {
            return localStorageService.set(key, {});
        }
        return false;
    }

    commonService.localStorage.setDb = function (key, value) {
        return localStorageService.set(key, value);
    }

    commonService.localStorage.getDb = function (key, createIfDoesntExist) {
        var value = localStorageService.get(key);
        if (value == null && createIfDoesntExist) {
            value = {};
            commonService.localStorage.setDb(key, value);
        }
        return value;
    }

    commonService.localStorage.getUserPreference = function (dbId, moduleType, key) {

        var userPreference = undefined;

        var dbPreference = commonService.localStorage.getDb(dbId);

        if (dbPreference != null) {
            if (dbPreference[moduleType]) {
                userPreference = dbPreference[moduleType][key];
            }
        }

        return userPreference;
    }

    commonService.localStorage.setUserPreference = function (dbId, moduleType, key, value) {

        var dbPreference = commonService.localStorage.getDb(dbId, true);

        if (dbPreference[moduleType]) {
            dbPreference[moduleType][key] = value;
        } else {
            dbPreference[moduleType] = {};
            dbPreference[moduleType][key] = value;
        }

        return commonService.localStorage.setDb(dbId, dbPreference);

    }

    commonService.localStorage.clearAll = function () {
        return localStorageService.clearAll(/^((?!(translation|userPref)).)*$/);
    }

    commonService.localStorage.setKeyValue = function (key, value) {
        return localStorageService.set(key, value);
    }

    commonService.localStorage.getKeyValue = function (key, createIfDoesntExist) {

        var value = localStorageService.get(key);

        if (value == null && createIfDoesntExist) {
            value = {};
            commonService.localStorage.setDb(key, value);
        }

        return value;
    }

    commonService.getVersionInfoFromFile = function () {
        var deferred = $q.defer();
        var fileurl = 'appfiles/version.json';
        $http.get(fileurl).success(function (res) {
            if (res) {
                deferred.resolve(res);
            }
        })

        return deferred.promise;

    }

    /** ENCRYPTION **/

    commonService.encrypt = function (value) {
        var encryptedVal = '';

        var encrypt = new JSEncrypt();

        var PUK = "-----BEGIN PUBLIC KEY-----\
                    MIGeMA0GCSqGSIb3DQEBAQUAA4GMADCBiAKBgGizHUqM0IWLNH+/AZdCDSrQoRFL\
                    tUqhR7ismjSV9AwtK7TkKukrf3kQQfV2NGwOVsSE4zwe4fp6vVHGwfIcmHxjCK8H\
                    12uPN6tFcuJgsZWgQBp76PGILanAKsZOaYdHeKBrgcPpv8vS7m/ExvN2lBTK9Tmv\
                    xkgwx4mT+whVVv+9AgMBAAE=\
                    -----END PUBLIC KEY-----"

        encrypt.setKey(PUK);

        encryptedVal = encrypt.encrypt(value);

        return encryptedVal;
    }

    commonService.getToken = function () {

        var token = decodeURIComponent(getCookie('XSRF-TOKEN'));

        if (token && token != '') {
            return commonService.encrypt(token);
        }

        return '';
    }

    function getCookie(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1);
            if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
        }
        return "";
    }

    return commonService;

} ])

.factory('authInterceptor', function ($rootScope, $q, AUTH_EVENTS) {
    return {
        responseError: function (response) {
            switch (response.status) {
                case 401:
                    $rootScope.$emit(AUTH_EVENTS.notAuthenticated);
                    return $q.reject(response);
                    break;
                case 403:
                    $rootScope.$emit(AUTH_EVENTS.notAuthorized);
                    return $q.reject(response);
                    break;
                case 500:
                    alert('Soemthing went wrong: Internal Server Error');
                    return $q.reject(response);
                    break;
                default:
                    return $q.reject(response);
            }
        }
    };
})

.factory('errorService', ['ERROR_CODE', 'modalService',
function (ERROR_CODE, modalService) {

    var errorService = {};

    errorService.resolve = function (errObj) {

        var errorMessage = 'An error occurred during the process.';

        if (errObj != undefined) {

            if (errObj.code != undefined && errObj.code != '') {
                errorMessage = ERROR_CODE[errObj.code];
            } else if (angular.isDefined(errObj.msg)) {
                errorMessage = errObj.msg;
            }

        }

        return errorMessage;

    }

    errorService.show = function (errObj) {
        modalService.show({}, {
            actionButtonText: 'OK',
            headerText: 'Error',
            bodyText: errorService.resolve(errObj),
            showCloseButton: false
        })
    }

    return errorService;

} ])

.factory('onSuccessDialogService', ['modalService',
function (modalService) {

    var onSuccessDialogService = {};

    onSuccessDialogService.show = function (msg, callBack) {
        modalService.show({ backdrop: false }, {
            actionButtonText: 'OK',
            headerText: 'Success',
            bodyText: msg,
            showCloseButton: false
        }).then(function (result) {
            if (callBack != undefined) {
                callBack();
            }
        })
    }

    return onSuccessDialogService;

} ])

.service('modalService', ['$uibModal',
function ($uibModal) {

    var modalOptions = {
        closeButtonText: 'Close',
        actionButtonText: 'OK',
        headerText: 'Confirmation',
        bodyText: 'Are you sure you want to perform this action?',
        showCloseButton: true
    };

    var modalDefaults = {
        backdrop: true,
        keyboard: true,
        templateUrl: 'js/app/components/core/views/modal.html'
    };

    this.show = function (customModalDefaults, customModalOptions) {
        //Create temp objects to work with since we're in a singleton service
        var tempModalDefaults = {};
        var tempModalOptions = {};

        //Map angular-ui modal custom defaults to modal defaults defined in service
        angular.extend(tempModalDefaults, modalDefaults, customModalDefaults);

        //Map modal.html $scope custom properties to defaults defined in service
        angular.extend(tempModalOptions, modalOptions, customModalOptions);

        if (!tempModalDefaults.controller) {
            tempModalDefaults.controller = function ($scope, $uibModalInstance) {
                $scope.modalOptions = tempModalOptions;
                $scope.confirm = function (result) {
                    $uibModalInstance.close(result);
                };
                $scope.close = function (result) {
                    $uibModalInstance.dismiss('cancel');
                };
            }
        }

        return $uibModal.open(tempModalDefaults).result;
    };

} ])

.service('treeViewModalService', ['$uibModal', '$http', '$q',
function ($uibModal, $http, $q) {

    var modalDefaults = {
        backdrop: true,
        keyboard: true,
        templateUrl: 'js/app/components/core/views/treeViewModal.html'
    };
    var modalOptions = {
        closeButtonText: 'Close',
        actionButtonText: 'OK',
        headerText: 'Confirmation',
        bodyText: 'Are you sure you want to perform this action?',
        showCloseButton: true
    };

    this.show = function (options) {
        modalDefaults.controller = function ($scope, $uibModalInstance) {
            $scope.headerText = options.header;
            $scope.selectedList = options.selectedList;
            $scope.treeViewList = options.treeViewList;
            $scope.treeViewOptions = options.treeViewOptions;
            $scope.confirm = function () {
                $uibModalInstance.close($scope.selectedList);
            };
            $scope.close = function () {
                $uibModalInstance.dismiss('cancel');
            };
            $scope.reset = function () {
                $scope.selectedList = [];
            }
        }
        return $uibModal.open(modalDefaults).result;
    }

} ])