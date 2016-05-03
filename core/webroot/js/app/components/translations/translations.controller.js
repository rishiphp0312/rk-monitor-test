appConfig.getModule('translations')
.controller('translationsCtrl', ['$scope', '$state', '$rootScope', 'translationsService', '$stateParams', 'commonService', 'PAGINATION_DEFAULT', 'errorService', 'modalService', '$filter', 'MODULES',
function ($scope, $state, $rootScope, translationsService, $stateParams, commonService, PAGINATION_DEFAULT, errorService, modalService, $filter, MODULES) {

    $scope.pagination = angular.copy(PAGINATION_DEFAULT);

    $scope.showDeleteInfo = false;

    $scope.showCompileMsg = false;

    var localStorageObj = translationsService.getUserSelectedLanguage();

    // default lagnauge set to false because we want entire list of languages.
    translationsService.getLanguageList({ defaultLang: false })
    .then(function (data) {

        $scope.languageList = data.languagesList;

        angular.forEach($scope.languageList, function (lang, index) {
            if (localStorageObj != '' && localStorageObj != undefined) {
                if (localStorageObj == lang.code) {
                    $scope.selectedLang = lang;
                }
            }
            if (lang.isDefault == true) {

                $scope.defaultLang = {
                    code: lang.code,
                    name: lang.name
                };

                if (localStorageObj == '' || localStorageObj == undefined) {
                    $scope.selectedLang = lang;
                }
            }
        });
        getTranslationList();
    }, function (err) {
        errorService.show(err);
    })

    $scope.langListChange = function () {

        getTranslationList();
        commonService.localStorage.setUserPreference('lang', MODULES.translation, 'translations', $scope.selectedLang)
    }

    function getTranslationList() {
        translationsService.getLanguageKeys({ langCode: $scope.selectedLang.code })
            .then(function (data) {
                $scope.langStringsList = data.Translations;

                $scope.paginationChanged();
            }, function (err) {
                errorService.show(err);
            });
    }

    $scope.deleteLanguageKey = function (id) {

        modalService.show({}, {
            closeButtonText: $rootScope.getTranslatedKey('CANCEL'),
            actionButtonText: $rootScope.getTranslatedKey('DELETE'),
            headerText: $rootScope.getTranslatedKey('TRANSLATIONS'),
            bodyText: $rootScope.getTranslatedKey('DELETE_CONFIRMATION')
        }).then(function (result) {
            translationsService.deleteLanguageKey({ id: id })
            .then(function (res) {
                $scope.langStringsList = $filter('filter')($scope.langStringsList, { id: ('!' + id) });
                $scope.paginationChanged();
                $scope.showDeleteInfo = true;
            }, function (err) {
                errorService.show(err);
            });
        });
    }

    $scope.sort = function (header) {
        var sortOrderType = {
            'asc': true,
            'desc': false,
            'none': ''
        }

        if ($scope.sortHeader != header) {
            $scope.sortHeader = header;
            $scope.sortOrder = sortOrderType.none;
        }

        if ($scope.sortOrder == sortOrderType.none)
            $scope.sortOrder = sortOrderType.asc;
        else
            $scope.sortOrder = !$scope.sortOrder;
    }

    $scope.setTotalCount = function (arr) {
        if (arr)
            $scope.pagination.totalCount = arr.length;
    }

    $scope.paginationChanged = function () {
        var startIndex = $scope.pagination.pageSize * ($scope.pagination.currentPage - 1);
        var endIndex = startIndex + ($scope.pagination.pageSize - 1);
        $scope.pagination.from = startIndex + 1;
        $scope.pagination.to = endIndex + 1;
        // for last page.
        if ($scope.pagination.from < $scope.pagination.totalCount && $scope.pagination.totalCount <= $scope.pagination.to) {
            $scope.pagination.to = $scope.pagination.totalCount;
        }
        //$state.transitionTo($state.current, { dbId: $rootScope.currentDatabase.id, page: $scope.pagination.currentPage }, { notify: false });
    }
    // end pagination

    $scope.compileLanguage = function () {

        translationsService.compileLanguage({ langCode: $scope.selectedLang.code })
            .then(function (res) {
                if (res == true) {
                    $scope.showCompileMsg = true;
                }
            }, function (err) {
                errorService.show(err);
            });
    }

} ])

.controller('addModifyCtrl', ['$scope', '$state', '$rootScope', 'translationsService', '$stateParams', 'commonService', 'errorService',
function ($scope, $state, $rootScope, translationsService, $stateParams, commonService, errorService) {

    $scope.showSucessMsg = false;

    $scope.createAnother = true;

    $scope.isModify = $stateParams.keyCode ? true : false;

    translationsService.getLanguageList({ defaultLang: false }).then(function (data) {

        $scope.languageList = data.languagesList;

    }, function (err) {
        errorService.show(err);
    })

    if ($scope.isModify) {

        translationsService.getTranslationDetails({ langCode: $stateParams.keyCode }).then(function (data) {

            $scope.translation = data.Translations;

        }, function (err) {
            errorService.show(err);
        })
    }

    $scope.saveTranslation = function () {
        if (!$scope.isModify) {
            translationsService.addTranslation($scope.translation)
        .then(function (data) {
            $state.go('dfaMonitoring.home.translation');
        })
        }
        else {
            translationsService.modifyTranslation($scope.translation)
                .then(function (data) {
                    $state.go('dfaMonitoring.home.translation');
                })
        }
    }, function (err) {
        errorService.show(err);
    }
} ])

.controller('importExportCtrl', ['$scope', '$state', '$rootScope', 'translationsService', '$stateParams', 'commonService', 'errorService', 'SERVICE_CALL',
function ($scope, $state, $rootScope, translationsService, $stateParams, commonService, errorService, SERVICE_CALL) {

    $scope.submitExportDetails = function () {

        if ($scope.showExportLoader == true) {
            return;
        }

        $scope.exportUrl = '';
        $scope.showExportLoader = true;

        translationsService.getExportUrl()
            .then(function (res) {
                $scope.showExportLoader = false;
                if (res.exportTranslations) {
                    $scope.exportUrl = res.exportTranslations;
                }
                else {
                    $scope.exportUrl = '';
                }
            }, function (err) {
                $scope.showExportLoader = false;
                errorService.show(err);
            })
    }

    $scope.generateFileData = {
        url: commonService.createServiceCallUrl(SERVICE_CALL.translations.importFile),
        fields: { type: 'lang' },
        sendFieldsAs: 'form'
    };

    $scope.hideDataLog = function () {
        $scope.showDataImportLog = false;
    }
    
    $scope.onFileSuccess = function (successObj, fileData) {

        $scope.showDataImportLog = true;

    }

    $scope.onFileFail = function (response, fileData) {
        errorService.show(response.err);
    }

} ])