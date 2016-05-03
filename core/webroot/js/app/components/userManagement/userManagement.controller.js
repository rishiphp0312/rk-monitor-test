appConfig.getModule('userManagement')
.controller('confirmPasswordCtrl', ['$scope', '$stateParams', '$state', 'userManagementService', 'onSuccessDialogService', 'errorService', 'commonService',
function ($scope, $stateParams, $state, userManagementService, onSuccessDialogService, errorService, commonService) {

    $scope.key = $stateParams.key;

    $scope.password = '';

    $scope.confirmPassword = '';

    $scope.savePassword = function (password) {

        if (password !== $scope.confirmPassword) {
            return false;
        } else {
            userManagementService.confirmPassword({ password: $scope.password, key: $scope.key })
            .then(function (res) {
                onSuccessDialogService.show($scope.getTranslatedKey('ACTIVATION_SUCCESS'), function () {
                    $state.go('dfaMonitoring');
                });
            }, function (err) {
                errorService.show(err);
            });
        }

    }
} ])

.controller('forgotPasswordCtrl', ['$scope', '$stateParams', '$state','modalService', 'userManagementService', 'onSuccessDialogService', 'errorService', 'commonService',
function ($scope, $stateParams, $state, modalService,userManagementService, onSuccessDialogService, errorService, commonService) {

    $scope.userName = '';

    $scope.forgotPassword = function () {

        userManagementService.forgotPassword({ userName: $scope.userName })
        .then(function (res) {
            onSuccessDialogService.show($scope.getTranslatedKey('FORGOT_PASSWORD_SUCCESS'), function () {
                $state.go('dfaMonitoring');
            });
        }, function (err) {
            errorService.show(err);
        });

    }
} ])