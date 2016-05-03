appConfig.getModule()
.filter('paginationFilter', function ($filter) {
    // from 1 to 10
    return function (arr, from, to, callBack) {
        if (arr) {
            if (callBack) {
                callBack(arr);
            }
            return arr.slice(from - 1, to);
        } else {
            return arr;
        }
    };
});