appConfig.getModule()
.run(['$rootScope', '$state', '$urlRouter', '$templateCache', 'AUTH_EVENTS', 'authService',
function ($rootScope, $state, $urlRouter, $templateCache, AUTH_EVENTS, authService) {
    $rootScope.$on('$stateChangeStart', function (event, toState, toParams, fromState, fromParams) {
        // stop all transition before request is authenticated
        event.preventDefault();

        // check for authentication
        authService.isAuthenticated()
        .then(function (isAuthenticated) {
            if (toState.url == '/' && isAuthenticated) {
                $state.go('dfaMonitoring.home');
            } else if (toState.data != undefined && !toState.data.authenticationRequired) {
                $state.go(toState.name, toParams, { notify: false }).then(function () {
                    $rootScope.$broadcast('$stateChangeSuccess', toState, toParams, fromState, fromParams);
                });
            } else if (toState.data != undefined && toState.data.authenticationRequired) {
                if (isAuthenticated) {
                    //        // get Authorized Roles for the next state.
                    //        var authorizedRoles = toState.data.authorizedRoles;

                    //        // check for authorization
                    //        authService.isAuthorized(authorizedRoles, toParams.dbId)
                    //        .then(function (isAuth) {
                    //            if (!isAuth) {
                    //                $state.go('dataAdmin.notAuthorized');
                    //            } else {
                    //                $state.go(toState.name, toParams, { notify: false }).then(function () {
                    //                    $rootScope.$broadcast('$stateChangeSuccess', toState, toParams, fromState, fromParams);
                    //                });
                    //            }
                    //        })
                    $state.go(toState.name, toParams, { notify: false }).then(function () {
                        $rootScope.$broadcast('$stateChangeSuccess', toState, toParams, fromState, fromParams);
                    });
                } else {
                    $state.go('dfaMonitoring');
                }
            } else {
                $state.go(toState.name, toParams, { notify: false }).then(function () {
                    $rootScope.$broadcast('$stateChangeSuccess', toState, toParams, fromState, fromParams);
                });
            }



        });
    });
} ])

.config(['$stateProvider', '$urlRouterProvider', '$httpProvider', 'localStorageServiceProvider', 'cfpLoadingBarProvider',
function ($stateProvider, $urlRouterProvider, $httpProvider, localStorageServiceProvider, cfpLoadingBarProvider) {

    localStorageServiceProvider
        .setPrefix(appConfig.appName);

    $httpProvider.interceptors.push('authInterceptor');

    cfpLoadingBarProvider.spinnerTemplate = '<div class="load-page"><div class="loader-text fade-in fast"><div class="loader lt-ie9"></div><p>' + 'Loading...' + '</p></div></div>';

    cfpLoadingBarProvider.includeBar = false;

    $urlRouterProvider.otherwise('/');

    $stateProvider
        .state(registerState(), {
            url: '/',
            views: {
                'header': {
                },
                'content': {
                    templateUrl: 'js/app/components/core/views/login.html'
                },
                'footer': {
                    templateUrl: 'js/app/components/core/views/footer.html'
                }
            },
            data: {
                authenticationRequired: false
            },
            resolve: {
                languageOptions: function ($q, $timeout, $rootScope, translationsService) {

                    var deferred = $q.defer();

                    translationsService.getLanguageList({ defaultLang: false })
                    .then(function (data) {

                        $rootScope.languageOptions = data.languagesList;

                        var userSelectedLanguage = translationsService.getUserSelectedLanguage();

                        if (userSelectedLanguage != '') {

                            $rootScope.selectedLangCode = userSelectedLanguage;

                            angular.forEach($rootScope.languageOptions, function (lang) {
                                if (lang.code == $rootScope.selectedLangCode) {
                                    $rootScope.langDir = lang.rtl == true ? 'rtl' : 'ltr';
                                }
                            })

                        }

                        if ($rootScope.selectedLangCode == undefined || $rootScope.selectedLangCode == '') {

                            angular.forEach($rootScope.languageOptions, function (lang) {
                                if (lang.isDefault == true) {
                                    $rootScope.selectedLangCode = lang.code;
                                    $rootScope.langDir = lang.rtl == true ? 'rtl' : 'ltr';
                                    // get language string.
                                    translationsService.setTranslationStrings($rootScope.selectedLangCode)
                                }
                            })

                        } else {
                            // get language string.
                            translationsService.setTranslationStrings($rootScope.selectedLangCode)
                        }

                    })

                    deferred.resolve();

                    return deferred.promise;

                }
            }
        })

        .state(registerState('notAuthorized'), {
            url: 'NotAuthorized',
            views: {
                'header@': {
                    templateUrl: 'js/app/components/core/views/header.html'
                },
                'content@': {
                    templateUrl: 'js/app/components/core/views/notAuthorized.html'
                }
            }
        })

        .state(registerState('confirmPassword'), {
            url: 'UserActivation/:key',
            views: {
                'header@': {
                    templateUrl: 'js/app/components/core/views/header.html'
                }, 'content@': {
                    templateUrl: 'js/app/components/userManagement/views/confirmPassword.html',
                    controller: 'confirmPasswordCtrl'
                }
            },
            data: {
                authenticationRequired: false
            }
        })

        .state(registerState('forgotPassword'), {
            url: 'forgotPassword',
            views: {
                'header@': {
                    templateUrl: 'js/app/components/core/views/header.html'
                }, 'content@': {
                    templateUrl: 'js/app/components/userManagement/views/forgotPassword.html',
                    controller: 'forgotPasswordCtrl'
                }
            },
            data: {
                authenticationRequired: false
            }
        })

    /** HOME STATE **/
        .state(registerState('home'), {
            url: 'Home',
            views: {
                'header@': {
                    templateUrl: 'js/app/components/core/views/header.html'
                },
                'content@': {
                    templateUrl: 'js/app/components/home/views/index.html',
                    controller: 'homeCtrl'
                }
            },
            data: {
                authenticationRequired: true
            }
        })

    /** PLANNING MODULE **/
        .state(registerState('planning', ['home']), {
            url: '^/Planning',
            views: {
                'moduleView@dfaMonitoring.home': {
                    templateUrl: 'js/app/components/planning/views/index.html',
                    controller: 'planningCtrl'
                }
            },
            data: {
                authenticationRequired: true
            }
        })

        .state(registerState('add', ['home', 'planning']), {
            url: '/Add',
            views: {
                'tabView@dfaMonitoring.home.planning': {
                    templateUrl: 'js/app/components/planning/views/addModify.html',
                    controller: 'addModifyPlanningCtrl'
                }
            },
            data: {
                authenticationRequired: true
            }
        })

        .state(registerState('modify', ['home', 'planning']), {
            url: '/Modify/:planningId',
            views: {
                'tabView@dfaMonitoring.home.planning': {
                    templateUrl: 'js/app/components/planning/views/addModify.html',
                    controller: 'addModifyPlanningCtrl'
                }
            },
            data: {
                authenticationRequired: true
            }
        })

    /** TRANSLATION MODULE**/
       .state(registerState('translation', ['home']), {
           url: '^/Translation',
           views: {
               'moduleView@dfaMonitoring.home': {
                   templateUrl: 'js/app/components/translations/views/index.html',
                   controller: 'translationsCtrl'
               }
           },
           data: {
               authenticationRequired: true
           }
       })

            .state(registerState('add', ['home', 'translation']), {
                url: '/Add',
                views: {
                    'moduleView@dfaMonitoring.home': {
                        templateUrl: 'js/app/components/translations/views/addModify.html',
                        controller: 'addModifyCtrl'
                    }
                },
                data: {
                    authenticationRequired: true
                }
            })

            .state(registerState('modify', ['home', 'translation']), {
                url: '/Modify/:keyCode',
                views: {
                    'moduleView@dfaMonitoring.home': {
                        templateUrl: 'js/app/components/translations/views/addModify.html',
                        controller: 'addModifyCtrl'
                    }
                },
                data: {
                    authenticationRequired: true
                }
            })

             .state(registerState('importExport', ['home', 'translation']), {
                 url: '/Import-Export',
                 views: {
                     'moduleView@dfaMonitoring.home': {
                         templateUrl: 'js/app/components/translations/views/importExport.html',
                         controller: 'importExportCtrl'
                     }
                 },
                 data: {
                     authenticationRequired: true
                 }
             })


    /* 
    ** REGISTERING A STATE **
    newState - name of the new state (string)
    parentStates - parent states for the new state (array or a string)
    */
    function registerState(newState, parentStates) {

        var state = appConfig.appName;

        if (parentStates) {
            if (!angular.isArray(parentStates)) {
                parentStates = [parentStates];
            }

            state += '.' + parentStates.join('.');

        }

        if (newState) {
            state += '.' + newState;
        }

        return state;
    }

} ])