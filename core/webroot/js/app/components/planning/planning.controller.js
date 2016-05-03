appConfig.getModule('planning')
.controller('planningCtrl', ['$scope', '$state', '$filter', 'planningService',
function ($scope, $state, $filter, planningService) {

    $scope.setTabView(false);

    $scope.addModifyPlanning = function (isEdit) {

        var row = $scope.gridApi.selection.getSelectedRows();

        if (isEdit) {
            if (row.length > 0) {
                $scope.setTabView(true);
                $state.go('dfaMonitoring.home.planning.modify', { planningId: row[0].id });
            } else {
                alert('No row selected to modify');
            }

        } else {
            $scope.setTabView(true);
            $state.go('dfaMonitoring.home.planning.add');
        }

    }

    $scope.filterOptions = {};

    $scope.gridOptions = {
        enableColumnResizing: true,
        enableGridMenu: true,
        enableFiltering: true,
        multiSelect: false,
        useExternalFiltering: true,
        onRegisterApi: function (gridApi) {
            $scope.gridApi = gridApi;
            $scope.gridApi.core.on.filterChanged($scope, function () {

                console.log('filter');

                var grid = this.grid;

                var filterObj = {};

                angular.forEach(grid.columns, function (column) {
                    var field = column.field;
                    angular.forEach(column.filters, function (filter) {
                        if (filter.term && filter.term.length > 0) {
                            filterObj[field] = filter.term;
                        }
                    })
                })

                if (filterObj == undefined || Object.keys(filterObj).length === 0) {
                    setResultMatrixGridData(angular.copy(planningService.getResultMatrix()));
                } else {
                    var unfilteredData = angular.copy(planningService.getResultMatrix());

                    var filteredData = [];

                    angular.forEach(unfilteredData, function (data) {

                        var show = true;

                        angular.forEach(filterObj, function (value, key) {
                            if (value.indexOf(data[key]) >= 0 && show) {
                                show = true;
                            } else {
                                show = false;
                            }
                        })

                        if (show) {
                            filteredData.push(data);
                        }

                    })

                    $scope.gridOptions.data = filteredData;
                }

            })
        }
    }

    $scope.addData = function () {

        var row = $scope.gridApi.selection.getSelectedRows();

        if (row.length > 0) {
            row = row[0];
        } else {
            row = undefined;
        }

        if (row && row.levelName == 'Indicator') {
            alert('cannot add data');
        } else {
            if (row) {

                var data = {
                    id: $scope.gridOptions.data.length + 1,
                    level: row.level + 1,
                    levelName: getNextLevel(row.levelName),
                    name: 'New ' + getNextLevel(row.levelName),
                    parentId: row.id,
                    $$treeLevel: row.$$treeLevel + 1
                }

                var insertIndex = '';

                angular.forEach($scope.gridOptions.data, function (dataObj, index) {
                    if (dataObj.id == row.id) {
                        insertIndex = index + 1;
                    }
                })

                $scope.gridOptions.data.splice(insertIndex, 0, data);

            } else {
                $scope.gridOptions.data.push({
                    id: $scope.gridOptions.data.length + 1,
                    level: 1,
                    levelName: 'Result Area',
                    name: 'New Result Area',
                    parentId: '',
                    $$treeLevel: 0
                })
            }
        }

    }

    setResultMatrixGridData(angular.copy(planningService.getResultMatrix()), true);

    function setResultMatrixGridData(dataList, setColumns) {

        if (setColumns) {

            var columns = [];

            var columnDefs = [];

            angular.forEach(dataList, function (dataObj) {
                angular.forEach(dataObj, function (value, key) {

                    // set columns definition
                    if (columns.indexOf(key) < 0 && (key != 'level' && key != 'parentId')) {

                        columns.push(key);

                        columnDefs.push({
                            field: key,
                            displayName: key,
                            cellClass: cellClass,
                            filterHeaderTemplate: '<div class="ui-grid-filter-container" ng-repeat="colFilter in col.filters"><div my-custom-dropdown></div></div>',
                            filter: {}
                        })

                    }

                    // set filter options
                    if ((key != 'level' && key != 'parentId')) {

                        $scope.filterOptions[key] = $scope.filterOptions[key] || [];

                        if ($scope.filterOptions[key].indexOf(value) < 0)
                            $scope.filterOptions[key].push(value);

                    }

                })
            })

            angular.forEach(columnDefs, function (column) {
                if ($scope.filterOptions[column.field] && $scope.filterOptions[column.field].length > 0) {
                    column.filter.options = $scope.filterOptions[column.field];
                }
            })

            $scope.gridOptions.columnDefs = columnDefs;

        }

        $scope.resultMatrixData = [];

        getDataByParentId(dataList);

        $scope.gridOptions.data = $scope.resultMatrixData;

    }

    function cellClass(grid, row, col, rowRenderIndex, colRenderIndex) {
        return getRowCssClass(row);
    }

    function getDataByParentId(dataList, parentId) {

        var parentId = parentId || '';

        angular.forEach(dataList, function (dataObj) {
            if (dataObj.parentId == parentId) {
                dataObj.$$treeLevel = dataObj.level - 1;
                $scope.resultMatrixData.push(dataObj);
                getDataByParentId(dataList, dataObj.id);
            }
        })

        return;

    }

    function getRowCssClass(row) {

        var cssClass = '';

        if (row.entity.levelName == 'Result Area') {
            cssClass = 'darkBlue';
        } else if (row.entity.levelName == 'Outcome') {
            cssClass = 'lightBlue';
        } else if (row.entity.levelName == 'Output') {
            cssClass = 'lightGray'
        } else if (row.entity.levelName == 'Indicator') {
            cssClass = 'white';
        } else {
            cssClass = 'white';
        }

        return cssClass;
    }

    function getNextLevel(levelName) {

        var nextLevel = 'Result Area';

        if (levelName) {
            switch (levelName) {
                case 'Result Area':
                    nextLevel = 'Outcome';
                    break;
                case 'Outcome':
                    nextLevel = 'Output';
                    break;
                case 'Output':
                    nextLevel = 'Indicator';
                    break;
            }
        }

        return nextLevel;

    }

    function sortGridData(text) {
        $scope.gridOptions.data = [];
    }

} ])

.directive('myCustomDropdown', function () {
    return {
        template: '<button ng-click="showFilters()">Filter <i stlyle="float:right;" class="fa fa-filter"></i></button>', //,
        controller: function ($scope, modalService) {
            $scope.showFilters = function () {
                modalService.show({
                    templateUrl: 'js/app/components/planning/views/test.html',
                    controller: 'myCustomCtrl',
                    resolve: {
                        filterOptions: function () {
                            return $scope.colFilter.options;
                        },
                        selectedFilter: function () {
                            return $scope.colFilter.term || [];
                        }
                    }
                }).then(function (result) {
                    $scope.colFilter.term = result;
                });
            }
        }
    };
})

.controller('myCustomCtrl', function ($scope, $uibModalInstance, filterOptions, selectedFilter) {

    $scope.filterOptions = filterOptions;

    $scope.selectedFilter = angular.copy(selectedFilter);

    $scope.filterSelcted = function (option) {

        var index = $scope.selectedFilter.indexOf(option);

        if (index < 0) {
            $scope.selectedFilter.push(option);
        } else {
            $scope.selectedFilter.splice(index, 1);
        }

    }

    $scope.confirm = function () {
        $uibModalInstance.close($scope.selectedFilter);
    }

    $scope.close = function () {
        $uibModalInstance.dismiss();
    }

})

.controller('addModifyPlanningCtrl', ['$scope', '$state', '$stateParams', 'planningService',
function ($scope, $state, $stateParams, planningService) {

    $scope.setTabView(true);

    $scope.isModify = $stateParams.planningId ? true : false;

    $scope.planningId = $stateParams.planningId || '';

    if ($scope.isModify) {
        $scope.planningObj = planningService.getPlanningData($scope.planningId);
        if ($scope.planningObj.levelName == 'Indicator') {
            $scope.indicatorData = planningService.getIndicatoData();
        }
        $scope.gridOptions = {
            enableColumnResizing: true,
            enableGridMenu: true,
            enableFiltering: true,
            treeRowHeaderAlwaysVisible: false,
            columnDefs: [{
                displayName: 'Indicator',
                field: 'indicator'
            }, {
                displayName: 'Unit',
                field: 'unit'
            }, {
                displayName: 'Subgroup',
                field: 'subgroup'
            }, {
                displayName: 'Area ID',
                field: 'areaId'
            }, {
                displayName: 'Area Name',
                field: 'areaName'
            }, {
                displayName: 'Time Period',
                field: 'timePeriod'
            }, {
                displayName: 'Source',
                field: 'source'
            }, {
                displayName: 'Actual Value',
                field: 'value.actual',
                enableCellEdit: true
            }, {
                displayName: 'Planned Value',
                field: 'value.planned',
                enableCellEdit: true
            }],
            data: $scope.indicatorData,
            onRegisterApi: function (gridApi) {
                $scope.gridApi = gridApi;
            }
        }
    }

    $scope.close = function () {

        $scope.setTabView(false);

        $state.go('dfaMonitoring.home.planning');

    }

} ])

//$scope.gridOptions.columnDefs = [{
//    displayName: 'Level',
//    field: 'levelName',
//    cellClass: function (grid, row, col, rowRenderIndex, colRenderIndex) {
//        return getRowCssClass(row);
//    },
//    filterHeaderTemplate: '<div class="ui-grid-filter-container" ng-repeat="colFilter in col.filters"><div my-custom-dropdown></div></div>',
//    filter: {},
//    menuItems: [{
//        title: 'Result Area',
//        icon: 'fa fa-square-o',
//        action: function () {
//            sortGridData();
//        },
//        active: function () {
//            if (this.icon == 'fa fa-square-o') {
//                return false;
//            } else {
//                return true;
//            }
//        },
//        context: 'ResultArea'
//    }, {
//        title: 'Outcome',
//        action: function () {
//        }
//    }, {
//        title: 'Output',
//        action: function () {

//        }
//    }, {
//        title: 'Indicator',
//        action: function () {

//        }
//    }]
//}, {
//    displayName: 'ID',
//    field: 'id',
//    cellClass: function (grid, row, col, rowRenderIndex, colRenderIndex) {
//        return getRowCssClass(row);
//    }
//}, {
//    displayName: 'Label',
//    field: 'name',
//    cellClass: function (grid, row, col, rowRenderIndex, colRenderIndex) {
//        return getRowCssClass(row);
//    }
//}]