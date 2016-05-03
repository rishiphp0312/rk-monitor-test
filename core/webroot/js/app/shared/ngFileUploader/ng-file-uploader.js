angular.module('ngFileUploader', [])
.factory('ngFileUploaderService', ['Upload', '$q', '$http',
function (Upload, $q, $http) {

    var ngFileUploaderService = {};

    ngFileUploaderService.uploadFile = function (file, fileData, progressCallBack) {

        var deffered = $q.defer();

        fileData['file'] = file;

        Upload.upload({
            url: fileData.url,
            data: fileData,
            sendFieldsAs: fileData.sendFieldAs //'form'
        }).progress(function (evt) {
            var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
            progressCallBack(progressPercentage);
        }).success(function (res) {
            if (res.success) {
                deffered.resolve(res);
            } else {
                deffered.reject(res);
            }
        })

        return deffered.promise;

    }

    return ngFileUploaderService;


} ])

.directive('ngFileUploader', function () {
    return {
        restrict: 'E',
        scope: {
            acceptExt: '=',
            fileData: '=',
            onFileSuccess: '=',
            onFileFail: '=',
            loadingMsg: '=?',
            buttonText: '=?',
            onUploadStart: '=?',
            placeholderText: '=?'
        },
        controller: ['$scope', 'ngFileUploaderService', function ($scope, ngFileUploaderService) {
            $scope.isLoading = false;
            $scope.file = '';
            $scope.progressPercent = 0;
            $scope.enableSelect = true;
            $scope.buttonText = $scope.buttonText || 'Upload';
            $scope.placeholderText = $scope.placeholderText || 'Upload file';

            $scope.uploadFile = function () {

                $scope.enableSelect = false;

                $scope.isLoading = true;

                if ($scope.onUploadStart) {
                    $scope.onUploadStart();
                }

                ngFileUploaderService
                .uploadFile($scope.file, $scope.fileData, function (progressPercent) {
                    $scope.progressPercent = progressPercent;
                })
                .then(function (success) {
                    $scope.onFileSuccess(success, $scope.fileData);
                    $scope.isLoading = false;
                    $scope.enableSelect = true;
                    $scope.progressPercent = 0;
                }, function (err) {
                    $scope.onFileFail(err, $scope.fileData);
                    $scope.isLoading = false;
                    $scope.enableSelect = true;
                    $scope.progressPercent = 0;
                })
            }
        } ],
        template: ('<div class="upload">' +

                    '<div ng-progress-bar class="upload-box" ngf-select ngf-pattern="{{acceptExt}}" ng-model="file">' + // 

                        '<div>' +

                             '<span ng-show="!file">' +
                                    '{{placeholderText}}' +
                            '</span>' +

                             '<span>' +
                                    '{{file.name}}' +
                             '</span>' +

                        '</div>' +

                    '</div>' +

                    '<a class="btn btn-default" ng-click="enableSelect && file && uploadFile()"><i class="kd-upload"></i> {{buttonText}} </a>' +

                     '<div class="upload-status">' +

                        '<span class="loading small-text" ng-if="loadingMsg && isLoading">' +

                            '<i class="fa fa-spinner fa-spin"></i> {{loadingMsg}}' +

                        '</span>' +

                    '</div>' +


                  '</div>')
    }

})

