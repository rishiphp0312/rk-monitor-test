appConfig.getModule('userManagement')
.factory('userManagementService', ['$http', '$q', '$filter', 'SERVICE_CALL', 'commonService', function ($http, $q, $filter, SERVICE_CALL, commonService) {

    var userManagementService = {};

     userManagementService.confirmPassword = function (data) {

        var deferred = $q.defer();

        $http(commonService.createHttpRequestObject(SERVICE_CALL.userManagement.confirmPassword, data))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.success);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;

    }

    userManagementService.forgotPassword = function (data) {

        var deferred = $q.defer();
        
        $http(commonService.createHttpRequestObject(SERVICE_CALL.userManagement.forgotPassword, data))
        .success(function (res) {
            if (res.success) {
                deferred.resolve(res.success);
            } else {
                deferred.reject(res.err);
            }
        })

        return deferred.promise;

    }

    return userManagementService;
}])









