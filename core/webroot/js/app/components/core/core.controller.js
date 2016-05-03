appConfig.getModule()
.controller('appController', ['$scope', '$rootScope', '$stateParams', '$state', 'AUTH_EVENTS', 'authService', 'commonService', 'errorService', 'PAGINATION_DEFAULT', 'modalService', 'translationsService',
function ($scope, $rootScope, $stateParams, $state, AUTH_EVENTS, authService, commonService, errorService, PAGINATION_DEFAULT, modalService, translationsService) {

    $rootScope.appTitle = _APP_NAME;

    $rootScope.langDir = "ltr";

    $rootScope.passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[&@#$])[A-Za-z\d$@$!%*?&]{8,}/;

    // current logged in user details 
    $scope.currentUser = null;

    // checks if user is authorized.
    $scope.isAuthorized = authService.isAuthorized;

    // Listens to not authenticated event.
    $rootScope.$on(AUTH_EVENTS.notAuthenticated, function () {
        authService.destroySession();
        location.href = _WEBSITE_URL;
    })

    $rootScope.$on(AUTH_EVENTS.notAuthorized, function () {
        $state.go('dfaMonitoring.notAuthorized');
    })

    $scope.credentials = {
        username: '',
        password: ''
    }

    $scope.loginFailed = false;

    $scope.login = function () {

        var data = {
            username: $scope.credentials.username,
            password: commonService.encrypt($scope.credentials.password)
        }

        authService.login(data)
        .then(function () {
            $state.go('dfaMonitoring.home.planning');
        }, function (err) {
            $scope.loginFailedMsg = errorService.resolve(err);
            $scope.loginFailed = true;
            $scope.credentials = {
                email: '',
                password: ''
            }
        });

    }

    $scope.logout = function () {
        authService.logout()
        .then(function () {
            location.href = _WEBSITE_URL;
        });
    }

    $scope.closeLoginFailedAlert = function () {
        $scope.loginFailed = false;
    }

    $scope.websiteUrl = _WEBSITE_URL;

    $rootScope.onLanguageChange = function () {

        var langDir = 'ltr';

        angular.forEach($rootScope.languageOptions, function (lang) {
            if (lang.code == $rootScope.selectedLangCode) {
                langDir = lang.rtl == true ? 'rtl' : 'ltr';
            }
        })

        $rootScope.langDir = langDir;

        translationsService.setUserSelectedLanguage($rootScope.selectedLangCode, $rootScope.langDir);

        translationsService.setTranslationStrings($rootScope.selectedLangCode);
    }

    $rootScope.getTranslatedKey = function (key) {
        return translationsService.getTranslatedKey(key);
    }

    $rootScope.setTabView = function (value) {

        if (value == undefined) {
            value = false;
        }

        $rootScope.showTabView = value;
    }

} ])