angular.module('dfaCustomControls', [])
/**** TREE VIEW DIRECTIVE****/
    .factory('ngTreeViewService', ['$q', '$http', function ($q, $http) {

        var treeViewService = {};

        treeViewService.getOnDemandResult = function (options, data) {

            var deferred = $q.defer();

            $http({
                url: options.url,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                data: $.param(data),
                method: 'POST',
                ignoreLoadingBar: true
            }).success(function (success) {
                angular.forEach(options.responseDataKey, function (value) {
                    if (success[value] != undefined) {
                        success = success[value];
                    }
                })
                deferred.resolve(success);
            })

            return deferred.promise;

        }

        return treeViewService;

    } ])
    .directive('ngTreeView', ['$compile', function ($compile) {
        function ensureDefault(obj, prop, value) {
            if (!obj.hasOwnProperty(prop))
                obj[prop] = value;
        }
        return {
            restrict: 'E',
            transclude: true,
            require: '^ngTreeView',
            scope: {
                treeListModel: '=',
                options: '=treeViewOptions',
                selectedNodes: "=ngModel",
                expandedNodes: "=?",
                searchText: "=?",
                searchCount: "=?",
                selectedParentNodes: "=?",
                paginationTo: "=?",
                paginationFrom: "=?"
            },
            controller: ['$scope', '$filter', 'ngTreeViewService', function ($scope, $filter, ngTreeViewService) {

                // contains all options for a tree view.
                $scope.options = $scope.options || {};

                setDefaults();

                // list of all the nodes that are selected
                $scope.selectedNodes = $scope.selectedNodes || [];

                // list of all the expanded nodes.
                $scope.expandedNodes = $scope.expandedNodes || [];

                $scope.selectedParentNodes = $scope.selectedParentNodes || [];

                $scope.expandNode = function (node) {

                    var currentNodeLevel = node.level || 0;

                    var expanding = ($scope.expandedNodes.indexOf(node.id) < 0);

                    // if node is to be expanded than add expandedNodes else remove from expandedNodes
                    if (expanding) {

                        if (node.isChildAvailable && $scope.options.onDemand && (!node.nodes || node.nodes.length <= 0)) {

                            node.loading = true;

                            ngTreeViewService.getOnDemandResult($scope.options.onDemandOptions, node[$scope.options.onDemandOptions.requestDataKey])
                            .then(function (data) {

                                node.nodes = data;

                                node.loading = false;

                                $scope.expandedNodes.push(node.id);

                            })
                        } else {

                            $scope.expandedNodes.push(node.id);

                        }

                    } else {
                        $scope.expandedNodes.splice($scope.expandedNodes.indexOf(node.id), 1);
                    }

                }

                $scope.nodeExpanded = function () {

                    var currentNode = this.node;

                    if (currentNode) {

                        if (($scope.expandedNodes.indexOf(currentNode.id) >= 0) && $scope.options.onDemand && currentNode.isChildAvailable && (currentNode.nodes == undefined || currentNode.nodes.length <= 0) && !currentNode.loading) {

                            currentNode.loading = true;

                            ngTreeViewService.getOnDemandResult($scope.options.onDemandOptions, currentNode[$scope.options.onDemandOptions.requestDataKey])
                            .then(function (data) {

                                currentNode.nodes = data;

                                currentNode.loading = false;

                            })
                        }

                    }

                    return ($scope.expandedNodes.indexOf(currentNode.id) >= 0);
                };

                $scope.selectNode = function (selectedNode, parentNode) {

                    var selected = false;

                    var isRootNode = !parentNode.id;

                    if (isRootNode && $scope.options.selectionOptions.disableRootLevelSelection) {
                        // if root level selection is disbaled.
                    } else if (selectedNode.isChildAvailable == true && $scope.options.selectionOptions.leafNodeOnly) {
                        // do nothing
                    } else {
                        var pos = getSelectedNodeIndex(selectedNode.id);

                        if (!$scope.options.selectionOptions.multiSelection) {
                            $scope.selectedNodes = [];
                        }

                        if (pos === -1) {
                            addToSelectedList(selectedNode);
                            selectedNode.selected = true;
                            updateSelectedParentNode(parentNode, true);
                        } else {
                            if ($scope.options.selectionOptions.multiSelection) {
                                $scope.selectedNodes.splice(pos, 1);
                            }
                            selectedNode.selected = false;
                            updateSelectedParentNode(parentNode, false);
                        }
                    }
                }

                $scope.nodeSelected = function (node) {

                    node.selected = false;

                    var pos = getSelectedNodeIndex(node.id, function (selectedNode) {

                        if (selectedNode.fields == undefined) {
                            selectedNode.fields = node.fields;
                        }
                        if (selectedNode.returnData == undefined) {
                            selectedNode.returnData = node.returnData;
                        }

                        selectedNode.selected = true;

                    });

                    return !(pos === -1);

                }

                $scope.parentSelected = function (parentId) {

                    var pos = -1;

                    pos = $scope.selectedParentNodes.indexOf(parentId);

                    return !(pos === -1);

                }

                if ($scope.searchCount == undefined) {
                    $scope.searchCount = 0;
                }

                $scope.setCount = function (count) {
                    $scope.searchCount = count;
                }

                $scope.selectOrClearAll = function (node) {

                    var data = {
                        nodes: '',
                        selectAll: false
                    };
                    if ($scope.options.selectionOptions.disableRootLevelSelection && !node.id) {
                        // if root level selection is disbaled.
                    } else if ($scope.options.selectionOptions.leafNodeOnly && node.isChildAvailable == true) {
                        // do nothing
                    } else {
                        data.nodes = node.nodes;
                        node.selectAll = !node.selectAll;
                        data.selectAll = node.selectAll;

                        if (data.selectAll) {
                            selectAllNodes(data.nodes);
                        } else {
                            clearAllNodes(data.nodes);
                        }
                    }

                }

                /***** Private Functions *****/
                function setDefaults() {
                    // set defaults
                    ensureDefault($scope.options, 'onDemand', false);
                    ensureDefault($scope.options, 'onDemandOptions', {});
                    ensureDefault($scope.options, 'selectionOptions', {});
                    ensureDefault($scope.options, 'nodeOptions', {});
                    ensureDefault($scope.options, 'labelOptions', {});
                    ensureDefault($scope.options, 'useICheck', false);

                    ensureDefault($scope.options, 'pagination', false);

                    ensureDefault($scope.options.selectionOptions, 'selectedClass', 'nodeSelected');
                    ensureDefault($scope.options.selectionOptions, 'multiSelection', false);
                    ensureDefault($scope.options.selectionOptions, 'showCheckBox', false);
                    ensureDefault($scope.options.selectionOptions, 'checkBoxClass', '');
                    ensureDefault($scope.options.selectionOptions, 'showRadioButton', false);
                    ensureDefault($scope.options.selectionOptions, 'radioButtonClass', '');
                    ensureDefault($scope.options.selectionOptions, 'selectedHTML', '');
                    ensureDefault($scope.options.selectionOptions, 'disableRootLevelSelection', false);
                    ensureDefault($scope.options.selectionOptions, 'parentSelectedCss', '');
                    ensureDefault($scope.options.selectionOptions, 'leafNodeOnly', false)

                    ensureDefault($scope.options.nodeOptions, 'showNodeOpenCloseClass', true);
                    ensureDefault($scope.options.nodeOptions, 'nodeOpenClass', 'fa fa-plus');
                    ensureDefault($scope.options.nodeOptions, 'nodeCloseClass', 'fa fa-minus');
                    ensureDefault($scope.options.nodeOptions, 'nodeLeafClass', '');
                    ensureDefault($scope.options.nodeOptions, 'showLoader', true);
                    ensureDefault($scope.options.nodeOptions, 'loaderClass', 'fa fa-spinner fa-spin');

                    ensureDefault($scope.options.labelOptions, 'fields', []);
                    ensureDefault($scope.options.labelOptions, 'prefix', '');
                    ensureDefault($scope.options.labelOptions, 'suffix', '');
                    ensureDefault($scope.options.labelOptions, 'class', '');

                    ensureDefault($scope.options, 'searchOptions', {});
                    ensureDefault($scope.options.searchOptions, 'fields', []);
                    ensureDefault($scope.options.searchOptions, 'caseSensitive', false);
                    ensureDefault($scope.options.searchOptions, 'ignoreUndefinedFields', true);
                    ensureDefault($scope.options.searchOptions, 'level', 0);

                    ensureDefault($scope.options, 'selectAll', {});
                    ensureDefault($scope.options.selectAll, 'text', 'Select All');
                    ensureDefault($scope.options.selectAll, 'enabled', false);
                    ensureDefault($scope.options.selectAll, 'rootLevel', true);
                    ensureDefault($scope.options.selectAll, 'childLevel', true);

                }

                /** HTML HELPERS **/
                function buildLabelHtml() {

                    var html = '';

                    html = (
                            '<span class="lblTxt ' + ($scope.options.labelOptions.class) + '" ' + parentSelectedHtml() + '>' +
                                selectedHtml() +
                                checkBoxHtml() +
                                radioButtonHtml() +
                                '<span class="nodeText">' +
                                    buildLabelString() +
                                '</span>' +
                            '</span>'
                    )
                    return html;

                }

                function parentSelectedHtml() {

                    var html = '';

                    if ($scope.options.selectionOptions.parentSelectedCss != '') {
                        html = 'ng-class="{\'' + $scope.options.selectionOptions.parentSelectedCss + '\': parentSelected(node.id)}"'
                    }

                    return html;

                }

                function buildLabelString() {

                    var html = '';

                    var fields = [];

                    if ($scope.options.labelOptions.fields.length > 0) {
                        fields = $scope.options.labelOptions.fields;
                        for (i = 0; i < fields.length; i++) {
                            html += '<span ng-if="node.fields.' + fields[i].id + '" class="' + (fields[i].css || '') + '">' +
                                '{{node.fields.' + fields[i].id + ' + "' + fields[i].seperator + '"}}' +
                                '</span>';
                        }
                    }

                    return html;
                }

                function hideIfRootNodeHtml() {

                    var html = '';

                    if ($scope.options.selectionOptions.disableRootLevelSelection || $scope.options.selectionOptions.leafNodeOnly) {
                        if ($scope.options.selectionOptions.leafNodeOnly)
                            html = 'ng-if="node.isChildAvailable == false"'
                        else if ($scope.options.selectionOptions.disableRootLevelSelection)
                            html = 'ng-if="$parent.node.id != undefined"'
                    }

                    return html;

                }

                function checkBoxHtml() {

                    var html = '';

                    var checkBoxHtml = ('<input type="checkbox" class="' + ($scope.options.selectionOptions.checkBoxClass) + '" ' + hideIfRootNodeHtml() + '  ng-checked="nodeSelected(node)">');

                    if ($scope.options.selectionOptions.showCheckBox) {
                        html = $scope.options.useICheck ? getICheckHtml() : checkBoxHtml;
                    }

                    return html;

                }

                function radioButtonHtml() {

                    var html = '';

                    var radioHtml = ('<input type="radio" class="' + ($scope.options.selectionOptions.radioButtonClass) + '" ' + hideIfRootNodeHtml() + ' ng-checked="nodeSelected(node)"');

                    if ($scope.options.selectionOptions.showRadioButton) {
                        html = $scope.options.useICheck ? getICheckHtml(true) : radioHtml;
                    }

                    return html;

                }

                function getICheckHtml(isRadio) {

                    var cssClass = 'icheckbox_minimal-grey icheck-input';

                    if (isRadio) {
                        cssClass = 'iradio_minimal-grey icheck-input';
                    }

                    return '<div ' + hideIfRootNodeHtml() + ' class="input icheck"><div class="' + cssClass + '" style="position: relative;" ng-class="{\'hover\' : node.hover, \'checked\': nodeSelected(node)}" ng-mouseenter="node.hover = true" ng-mouseleave="node.hover = false"></div></div>';
                }

                function selectedHtml() {

                    var html = '';
                    if ($scope.options.selectionOptions.selectedHTML != '') {
                        html = '<span ng-show="nodeSelected(node)">' + $scope.options.selectionOptions.selectedHTML + '</span>';
                    }

                    return html;

                }

                function nodeOpenCloseHtml() {

                    var html = '';
                    if ($scope.options.nodeOptions.showNodeOpenCloseClass) {
                        html = (
                            '<span class="control-box">' +
                                '<span>' +
                                    '<i class="' + $scope.options.nodeOptions.nodeOpenClass + '" ng-show="node.isChildAvailable && !nodeExpanded() && !node.loading" stop-event="click" ng-click="expandNode(node)" ></i>' +
                                    '<i class="' + $scope.options.nodeOptions.nodeCloseClass + '" ng-show="node.isChildAvailable && nodeExpanded() && !node.loading" stop-event="click" ng-click="expandNode(node)" ></i>' +
                                    '<i class="' + $scope.options.nodeOptions.nodeLeafClass + ' ng-show="!node.isChildAvailable && !node.loading"></i>' +
                                    (($scope.options.nodeOptions.showLoader && $scope.options.onDemand) ? '<i class="' + $scope.options.nodeOptions.loaderClass + '" ng-show="node.loading"></i></span>' : '') +
                                '</span>' +
                            '</span>'
                        )
                    }

                    return html;
                }

                function selectAllCancelAllHtml() {

                    var selectAllCancelAllHtml = '';

                    if ($scope.options.selectionOptions.multiSelection && $scope.options.selectAll.enabled) {
                        selectAllCancelAllHtml = (
                        '<li ng-if="((options.selectAll.rootLevel == true && $parent.node == undefined) || (options.selectAll.childLevel == true && $parent.node != undefined)) && (searchText == undefined || searchText == \'\')">' +
                            '<div class="list-container">' +
                                '<div class="list-header" ng-click="selectOrClearAll($parent.node)">' +
                                    ($scope.options.nodeOptions.showNodeOpenCloseClass ? '<span class="control-box"><span></span></span>' : '') +
                                    '<span class="lableText">' +
                                       '<span class="lblTxt">' +
                                            '<div class="input icheck">' +
                                                '<div class="icheckbox_minimal-grey icheck-input" ng-class="{\'checked\': $parent.node.selectAll}" style="position: relative;"></div>' +
                                            '</div>' +
                                            '<span class="nodeText">' +
                                               '<span style="font-style:italic;">' + $scope.options.selectAll.text + '</span>' +
                                            '</span>' +
                                        '</span>' +
                                    '</span>' +
                                '</div>' +
                            '</div>' +
                        '</li>'
                        );
                    }

                    return selectAllCancelAllHtml;

                }

                function clickEventHtml() {

                    var html = ''

                    var html = 'ng-click="selectNode(node, $parent.node)"';

                    return html;

                }

                function paginationHtml() {
                    var html = '';

                    if ($scope.options.pagination) {
                        html = '| treeViewPaginationFilter : paginationFrom : paginationTo : $parent.node'
                    }

                    return html;
                }
                /** Functions **/

                function selectAllNodes(nodes) {

                    angular.forEach(nodes, function (node) {

                        var pos = getSelectedNodeIndex(node.id);

                        node.selected = true;

                        if (pos === -1) {
                            addToSelectedList(node);
                        }

                    })

                }

                function clearAllNodes(nodes) {

                    angular.forEach(nodes, function (node) {

                        node.selected = false;

                        var pos = getSelectedNodeIndex(node.id, function (selectedNode, pos) {
                            $scope.selectedNodes.splice(pos, 1);
                        });

                    })

                }

                function getSelectedNodeIndex(id, ifFoundCallback) {

                    var pos = -1;

                    if ($scope.selectedNodes) {
                        for (var i = 0; i < $scope.selectedNodes.length; i++) {
                            if (id === $scope.selectedNodes[i].id) {
                                pos = i;
                                if (ifFoundCallback) {
                                    ifFoundCallback($scope.selectedNodes[i], pos);
                                }
                                break;
                            }
                        }
                    }


                    return pos;

                }

                function addToSelectedList(node) {
                    $scope.selectedNodes.push({
                        id: node.id,
                        returnData: node.returnData,
                        fields: node.fields
                    });
                }

                function updateSelectedParentNode(parentNode, isSelected) {

                    // if the selected node is the root do not add.
                    if (parentNode.id) {
                        // when a node is selected - if the Parent Node id is not present then add to list.
                        // else when a node is removed, check if any child node is selected. if not remove from list.
                        if (isSelected) {
                            if ($scope.selectedParentNodes.indexOf(parentNode.id) < 0) {
                                $scope.selectedParentNodes.push(parentNode.id)
                            }
                        } else {
                            var childSelected = false;

                            angular.forEach(parentNode.nodes, function (node) {
                                if (node.selected == true) {
                                    childSelected = true;
                                }
                            });

                            if (!childSelected) {
                                var pos = $scope.selectedParentNodes.indexOf(parentNode.id);
                                $scope.selectedParentNodes.splice(pos, 1);
                            }

                        }
                    }
                }

                /** Template **/
                var selectedClass = '';

                if ($scope.options.selectionOptions.selectedClass != '')
                    selectedClass = 'ng-class="{' + $scope.options.selectionOptions.selectedClass + ': nodeSelected(node)}"';

                var template = (
                        '<ul>' +
                            selectAllCancelAllHtml() +
                            '<li ng-repeat="node in node.nodes | searchTreeView : { searchText: searchText, searchOptions: options.searchOptions, setCount: setCount } : $parent.node ' + paginationHtml() + ' ">' +
                                '<div class="list-container">' +
                                    '<div class="list-header"' + clickEventHtml() + ' ' + selectedClass + '>' +
                                        nodeOpenCloseHtml() +
                                        '<span class="lableText">' +
                                            buildLabelHtml() +
                                        '</span>' +
                                    '</div>' +
                                    '<tree-item class="list-child" ng-if="nodeExpanded()">' +
                                    '</tree-item>' +
                                '</div>' +
                            '</li>' +
                        '</ul>'
                );

                this.template = $compile(template);

            } ],
            link: function link(scope, element, attrs, controller, transcludeFn) {
                // for first time intialization.
                scope.$watch('treeListModel', function (newValue) {
                    if (angular.isArray(newValue)) {
                        if (angular.isDefined(scope.node) && angular.equals(scope.node['nodes'], newValue))
                            return;
                        scope.node = {};
                        scope.synteticRoot = scope.node;
                        scope.node['nodes'] = newValue;
                    } else {
                        if (angular.equals(scope.node, newValue))
                            return;
                        scope.node = newValue;
                    }
                });

                controller.template(scope, function (clone) {
                    element.html('').append(clone);
                });
            }
        }
    } ])
    .directive('treeItem', function () {
        return {
            restrict: 'E',
            require: "^ngTreeView",
            link: function (scope, element, attrs, controller) {
                // Rendering template for the current node
                controller.template(scope, function (clone) {
                    element.html('').append(clone);
                });
            }
        }
    })
    .filter('searchTreeView', function ($filter) {
        return function (nodes, search, parentNode) {
            if (search.searchText == undefined || search.searchText == '') {
                if (search.setCount != undefined && (parentNode == undefined || parentNode.id == undefined)) {
                    if (nodes == undefined) {
                        search.setCount(0)
                    } else {
                        search.setCount(nodes.length);
                    }
                }
                return nodes;
            } else {
                var filteredNodes = [];

                if (search.searchOptions.fields.length > 0) {

                    angular.forEach(nodes, function (node) {

                        var searchStr = '';

                        angular.forEach(search.searchOptions.fields, function (field) {
                            searchStr += (node.fields[field] || '') + ' ';
                        })

                        if (searchStr.trim() == '' && search.searchOptions.ignoreUndefinedFields) {
                            filteredNodes.push(node);
                        } else if (searchStr.trim().toLowerCase().indexOf(search.searchText.trim().toLowerCase()) >= 0) {
                            filteredNodes.push(node);
                        }

                    });
                    if (search.setCount != undefined && (parentNode == undefined || parentNode.id == undefined)) {
                        search.setCount(filteredNodes.length);
                    }
                    return filteredNodes;
                } else {
                    filteredNodes = $filter('filter')(nodes, search.searchText);
                    if (search.setCount != undefined && (parentNode == undefined || parentNode.id == undefined))
                        search.setCount((filteredNodes ? filteredNodes.length : 0));

                    return filteredNodes;
                }
            }
        };
    })
    .filter('treeViewPaginationFilter', function ($filter) {
        // from 1 to 10
        return function (nodes, from, to, parentNode) {
            if ((parentNode == undefined || parentNode.id == undefined) && nodes) {
                return nodes.slice(from - 1, to);
            } else {
                return nodes;
            }
        };
    })

/**** TREE VIEW DROP DOWN DIRECTIVE ****/
    .directive('ngTreeViewDropdown', function () {
        return {
            restrict: 'E',
            templateUrl: 'js/app/shared/dfaCustomControls/views/treeViewDropdown.html',
            scope: {
                dropdownList: '=',
                treeViewOptions: '=',
                selectedList: '=ngModel',
                onAdd: '=',
                onConfirm: '=',
                properties: '=?dropdownProperties',
                disabled: '=?ngDisabled'
            },
            controller: function ($scope) {

                function ensureDefault(obj, prop, value) {
                    if (!obj.hasOwnProperty(prop))
                        obj[prop] = value;
                }

                $scope.disabled = $scope.disabled || false;

                $scope.properties = $scope.properties || {};

                ensureDefault($scope.properties, 'search', true);

                ensureDefault($scope.properties, 'showSelected', true);

                ensureDefault($scope.properties, 'autoSelect', false);

                ensureDefault($scope.properties, 'placeholder', '-- Select --');

                ensureDefault($scope.properties, 'showReset', true);

                ensureDefault($scope.properties, 'confirmText', 'OK');

                ensureDefault($scope.properties, 'resetText', 'Reset');

                ensureDefault($scope.properties, 'addText', 'Add');

                ensureDefault($scope.properties, 'searchPlaceholderText', 'Search');

                ensureDefault($scope.properties, 'selectedText', 'Selected');

                $scope.dropdown = {
                    isOpen: false,
                    searchText: '',
                    selectedList: angular.copy($scope.selectedList),
                    displayText: ''
                };

                $scope.toggleDropdown = function () {
                    if ($scope.disabled) {
                        $scope.dropdown.isOpen = false;
                    } else {
                        $scope.dropdown.isOpen = !$scope.dropdown.isOpen;
                    }
                };

                $scope.reset = function () {
                    $scope.dropdown.selectedList = [];
                };

                $scope.confirm = function () {

                    if ($scope.properties.autoSelect == false) {
                        $scope.selectedList = $scope.dropdown.selectedList;
                        setDisplayText();
                    }

                    $scope.dropdown.isOpen = false;

                    if ($scope.onConfirm) {
                        $scope.onConfirm();
                    }

                };

                $scope.add = function () {
                    if ($scope.onAdd != undefined)
                        $scope.onAdd();
                }

                if ($scope.properties.autoSelect) {
                    $scope.$watch('dropdown.selectedList', function (newValue) {

                        if (newValue) {

                            setDisplayText();

                            if ($scope.properties.autoSelect && $scope.treeViewOptions.selectionOptions.multiSelection == false) {
                                $scope.dropdown.isOpen = false;
                            }

                            $scope.selectedList = newValue;

                        }

                    }, true)
                }

                $scope.$watch('selectedList', function (newValue) {
                    if (newValue && newValue.length == 0) {
                        $scope.dropdown.selectedList = [];
                    } else if (newValue && newValue.length > 0) {
                        $scope.dropdown.selectedList = newValue;
                        setDisplayText();
                    }
                })

                function setDisplayText() {

                    var selectedList = $scope.dropdown.selectedList;

                    if (selectedList.length == 1) {

                        var selectedNode = selectedList[0];

                        if ($scope.treeViewOptions.labelOptions.fields.length > 0) {
                            var text = '';
                            angular.forEach($scope.treeViewOptions.labelOptions.fields, function (value) {
                                text += angular.isUndefined(selectedNode.fields[value.id]) ? '' : (selectedNode.fields[value.id] + value.seperator);
                            })
                            $scope.dropdown.displayText = text;
                        } else {
                            $scope.dropdown.displayText = 'Single Selection';
                        }
                    } else if (selectedList.length > 1) {
                        $scope.dropdown.displayText = 'Multiple Selection';
                    } else {
                        $scope.dropdown.displayText = '';
                    }
                };

            }
        }
    })

/**** TREE VIEW LIST WITH SEARCH ****/
    .directive('dfaTreeViewBox', function () {
        return {
            restrict: 'E',
            templateUrl: 'js/app/shared/dfaCustomControls/views/treeViewBox.html',
            scope: {
                dropdownList: '=',
                treeViewOptions: '=',
                selectedList: '=ngModel',
                displayOptions: '=?',
                selectedParentNodes: '=?'
            },
            controller: function ($scope) {
                $scope.selectedParentNodes = $scope.selectedParentNodes || [];
                $scope.displayOptions = $scope.displayOptions || {};
                $scope.displayOptions.showSelected = $scope.displayOptions.showSelected || true;
                $scope.displayOptions.showReset = $scope.displayOptions.showReset || true;
                $scope.displayOptions.showSearchCount = $scope.displayOptions.showSearchCount || true;
                $scope.displayOptions.showSearch = $scope.displayOptions.showSearch || true;
                $scope.displayOptions.resetText = $scope.displayOptions.resetText || 'Reset';
            }
        }
    })

/**** TREE VIEW WITH PAGINATION ****/
    .directive('dfaTreeViewPagination', function () {
        return {
            restrict: 'E',
            templateUrl: 'js/app/shared/dfaCustomControls/views/treeViewPagination.html',
            scope: {
                treeListModel: '=',
                treeViewOptions: '=',
                selectedList: '=ngModel',
                selectedParentNodes: '=?',
                searchText: "=?",
                searchCount: "=?",
                pagination: "=?paginationConfig",
                onPaginationChange: "&?",
                languageText: "=?",
                expandedNodes: '=?'
            },
            controller: function ($scope) {

                $scope.languageText = $scope.languageText || {};
                $scope.languageText.showing = $scope.languageText.showing || 'Showing';
                $scope.languageText.to = $scope.languageText.to || 'to';
                $scope.languageText.of = $scope.languageText.of || 'of';
                $scope.languageText.records = $scope.languageText.records || 'records';
                $scope.languageText.dislpay = $scope.languageText.dislpay || 'Display';

                $scope.expandedNodes = $scope.expandedNodes || [];

                $scope.selectedParentNodes = $scope.selectedParentNodes || [];

                $scope.searchText = $scope.searchText || '';

                $scope.searchCount = $scope.searchCount || '';

                $scope.treeViewOptions['pagination'] = true;

                $scope.pagination = ((!!$scope.pagination) ? angular.copy($scope.pagination) : {
                    pageSize: 10,
                    currentPage: 1,
                    from: 1,
                    to: 10,
                    totalCount: ''
                });

                $scope.$watch('searchCount', function (newValue, oldValue) {
                    $scope.pagination.totalCount = newValue;
                    $scope.paginationChanged();
                })

                $scope.paginationChanged = function () {
                    var startIndex = $scope.pagination.pageSize * ($scope.pagination.currentPage - 1);
                    var endIndex = startIndex + ($scope.pagination.pageSize - 1);
                    $scope.pagination.from = startIndex + 1;
                    $scope.pagination.to = endIndex + 1;
                    // for last page show the total count.
                    if ($scope.pagination.from < $scope.pagination.totalCount && $scope.pagination.totalCount <= $scope.pagination.to) {
                        $scope.pagination.to = $scope.pagination.totalCount;
                    }

                    if ($scope.onPaginationChange != undefined) {
                        $scope.onPaginationChange();
                    }
                }
            }
        }
    })

/**** Suggestion Box ****/
    .directive('dfaSuggestionBox', ['$compile', function ($compile) {
        return {
            restrict: 'E',
            require: '^dfaSuggestionBox',
            scope: {
                data: '=ngModel',
                suggestionList: '=',
                onSuggestionClick: '=',
                properties: '='
            },
            controller: ['$scope', function ($scope) {

                $scope.showSuggestionList = false;

                $scope.suggestionListSelected = function (obj) {
                    $scope.onSuggestionClick(obj);
                    $scope.showSuggestionList = false;
                }

                // set required property
                var required = '';

                if ($scope.properties.required == true) {
                    required = 'required'
                }

                // set filter object and set label option
                var filter = '';

                var label = '';

                if (angular.isUndefined($scope.properties.labelKey) || $scope.properties.labelKey == '') {
                    filter = 'data';
                    label = '{{suggestion}}';
                } else {
                    filter = '{' + $scope.properties.labelKey + ': data}';
                    label = '{{suggestion.' + $scope.properties.labelKey + '}}';
                }

                var style = '';

                if ($scope.properties.width != undefined && $scope.properties.width != '') {
                    style = 'width: ' + $scope.properties.width + '';
                }

                var template = (
                    '<div style="' + style + '" class="suggestionbox" dropdown auto-close="outsideClick" is-open="showSuggestionList">' +
                        '<input type="text" name="' + $scope.properties.name + '" autocomplete="off" ng-model="data" ng-focus="showSuggestionList = true;" ' + required + '>' +
                        '<ul class="dropdown-menu" role="menu" ng-show="filtered.length > 0">' +
                            '<li ng-repeat="suggestion in filtered = (suggestionList | filter: ' + filter + ')">' +
                                '<a href="javascript:void(0)" ng-click="suggestionListSelected(suggestion)">' + label + '</a>' +
                            '</li>' +
                        '</ul>' +
                    '</div>'
                );


                this.template = $compile(template);

            } ],
            link: function (scope, element, attrs, controller) {
                controller.template(scope, function (clone) {
                    element.html('').append(clone);
                });
            }
        }
    } ])

/**** File Input Control ****/
    .directive('dfaFileInput', ['$compile', function ($compile) {
        return {
            restrict: 'E',
            require: '^dfaFileInput',
            scope: {
                file: '=ngModel',
                fileAccept: '@?',
                descriptionText: '@?'
            },
            controller: ['$scope', function ($scope) {

                $scope.emptyFile = function () {
                    $scope.file = '';
                }

                var acceptFile = '';

                if ($scope.fileAccept) {
                    var acceptFile = 'ngf-pattern="' + $scope.fileAccept + '"';
                }

                var template = '<div class="fileinput input-group" data-provides="fileinput">' +
                                    '<div ngf-select ' + acceptFile + ' ng-model="file" class="form-control" data-trigger="fileinput" style="padding: 7px 5px;">' +
                                        '<i class="fa fa-file-o" ng-show="file"></i>' +
                                        '<span class="fileinput-filename" ng-show="file">{{file.name}}</span>' +
                                    '</div>' +
                                    '<a  ng-show="file" ng-click="emptyFile()" class="input-group-addon btn btn-default fileinput-exists btn-file-cancel" style="position: relative"><i class="fa fa-close"></i></a>' +
                                    '<span ngf-select ' + acceptFile + ' ng-model="file" class="input-group-addon btn btn-default btn-file">' +
                                        '<span><i class="fa fa-folder"></i></span>' +
                                    '</span>' +
                                    '<div class="file-input-text" ng-if="descriptionText">' +
                                        '<p>{{descriptionText}}</p>' +
                                    '</div>' +
                                '</div>';

                this.template = $compile(template);

            } ],

            link: function (scope, element, attrs, controller) {
                controller.template(scope, function (clone) {
                    element.html('').append(clone);
                });
            }
        }
    } ])

/**** Calendar Control ****/
    .directive('dfaCalendarInput', ['$compile', function ($compile) {
        return {
            restrict: 'E',
            scope: {
                model: '=ngModel',
                format: '@?',
                dateOptions: '@?'
            },
            controller: ['$scope', '$attrs', function ($scope, $attrs) {

                $scope.isCalendarOpen = false;

                $scope.openCalendar = function ($event) {
                    $event.preventDefault();
                    $event.stopPropagation();
                    $scope.isCalendarOpen = true;
                }

                var defaultDateOptions = {
                    formatYear: 'yyyy',
                    startingDay: 1
                };

                $scope.dateOptions = $scope.dateOptions || defaultDateOptions;

                $scope.format = $scope.format || 'dd-MM-yyyy';

                var template = (
                        '<input type="text" class="form-control" ng-model="model" datepicker-popup="{{format}}" is-open="isCalendarOpen" datepicker-options="dateOptions" ng-focus="openCalendar($event)" show-button-bar="false" />' +
                        '<span class="input-group-addon" ng-click="openCalendar($event)">' +
                            '<i class="glyphicon glyphicon-calendar"></i>' +
                        '</span>'
                );

                this.template = $compile(template);

            } ],

            link: function (scope, element, attrs, controller) {
                controller.template(scope, function (clone) {
                    element.html('').append(clone);
                });
            }
        }
    } ])