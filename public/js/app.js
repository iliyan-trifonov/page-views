(function (angular) {
    "use strict";

    angular.module("PageViews", [
        "PageViews.controllers"
    ])
        .value("interval", 60);//sec
})(angular);
