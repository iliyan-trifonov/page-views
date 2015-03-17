(function (angular) {
    "use strict";

    angular.module("PageViews.services", [])
        .service("Api", [
            "$http",
            function ($http) {
                return {
                    "domains": {
                        "list": function () {
                            return $http({
                                url: "/?ajax=1",
                                method: "GET",
                                cache: false
                            });
                        }
                    }
                };
            }
        ]);
})(angular);
