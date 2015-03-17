(function (angular) {
    "use strict";

    angular.module("PageViews.controllers", [
        "PageViews.vars",
        "PageViews.filters",
        "PageViews.services"
    ])
    .controller("IndexCtrl", [
        "$scope", "$interval", "$filter", "Api", "series", "interval",
        function ($scope, $interval, $filter, Api, series, interval) {

            var avg = {};

            $scope.domains = [];

            $scope.seconds = interval;

            $scope.sign = "+";

            Highcharts.setOptions({
                global: {
                    useUTC: false
                }
            });

            var chart = new Highcharts.Chart({
                title: { text: 'Page Views History' },
                chart: { renderTo: 'container' },
                xAxis: { type: 'datetime' },
                yAxis: { title: { text: 'Pages Number' } },
                series: series
            });

            var pointAdded = false;

            function countdown () {
                $scope.seconds--;
                if ($scope.seconds < 1) {
                    $scope.seconds = interval;
                    refresh();
                }
            }

            function getAverage (domain, last) {
                if (!angular.isDefined(avg[domain])) {
                    avg[domain] = {
                        "sum": 0,
                        "count": 0
                    };
                }
                avg[domain].sum += last;
                avg[domain].count ++;
                return Math.round(
                    avg[domain].sum / avg[domain].count
                );
            }

            function addChartPoint (domain, last) {
                for (var i = 0; i < chart.series.length; i++) {
                    if (chart.series[i].name === domain) {
                        chart.series[i].addPoint([new Date().getTime(), last], false);
                        pointAdded = true;
                        break;
                    }
                }
            }

            function updateChart () {
                if (pointAdded) {
                    chart.redraw();
                    pointAdded = false;
                }
            }

            function refresh () {
                Api.domains.list()
                    .success(function (data) {
                        var domains = [];
                        angular.forEach(data, function (pageviews, domain) {
                            var last = 0, average = 0;
                            var found = $filter('getByDomainName')($scope.domains, domain);
                            if (found) {
                                last = pageviews - found.pageviews;
                                if (last < 0) {
                                    last *= -1;
                                }
                                average = getAverage(domain, last);
                                addChartPoint(domain, last);
                            } else if (!$filter('getByDomainName')(series, domain)) {
                                chart.addSeries({
                                    "name": domain,
                                    "data": [pageviews]
                                });
                            }
                            domains.push({
                                "name": domain,
                                "pageviews": pageviews,
                                "last": last,
                                "average": average
                            });
                        });
                        $scope.domains = domains;
                        updateChart();
                    });
            }

            refresh();

            $interval(countdown, 1E3);

        }
    ]);
})(angular);
