// Init the application configuration module for AngularJS application
var appConfig = (function () {
    // Init module configuration options
    var appName = 'dfaMonitoring';

    var appDependencies = [
        'ui.router',
        'ui.bootstrap',
        'ui.grid',
        'ui.grid.autoResize',
        'ui.grid.treeView',
        'ui.grid.selection',
        'ui.grid.resizeColumns',
        'ui.grid.exporter',
        'ui.grid.moveColumns',
        'ui.grid.grouping',
        'ui.grid.edit',
        'ngAnimate',
        'ngTouch',
        'stopEvent',
        'ngFileUpload',
        'ngProgressBar',
        'ngFileUploader',
        'angular-loading-bar',
        'dfaCustomControls',
        'ngMask',
        'LocalStorageModule',
        'bgDirectives'
    ];

    // Add a new vertical module
    var registerModule = function (moduleName) {

        var completeModuleName = appName + '.' + moduleName;

        // Create angular module
        angular.module(completeModuleName, []);

        // Add the module to the AngularJS configuration file
        angular.module(appName).requires.push(completeModuleName);

    };

    var getModule = function (moduleName) {

        var completeModuleName = appName + (moduleName ? ('.' + moduleName) : '');

        return angular.module(completeModuleName);

    }

    var serviceCallUrl = 'api/queryService/';

    return {
        appName: appName,
        appDependencies: appDependencies,
        registerModule: registerModule,
        serviceCallUrl: serviceCallUrl,
        getModule: getModule
    };

})();