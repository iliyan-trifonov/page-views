(function (angular) {
    "use strict";

    angular.module("PageViews.filters", [])
        .filter("getByDomainName", function () {
            return function (domains, domain) {
                var i = 0, len = domains.length;
                for (; i < len; i++) {
                    if (domains[i].name === domain) {
                        return domains[i];
                    }
                }
                return null;
            }
        });
})(angular);
