$(function () {
    var interval = 60;//seconds
    var secondsLeft = interval;
    var avgSites = {};
    var chart = null;

    $('#status').text('refresh in: ' + secondsLeft + ' seconds');

    function countdown() {
        secondsLeft--;
        $('#status').text('refresh in: ' + secondsLeft + ' seconds');
        if (secondsLeft == 0) {
            secondsLeft = interval;
            refresh();
        }
    }

    function formatNumber(number) {
        //number = number.toFixed(2) + '';
        number = number + '';
        x = number.split('.');
        x1 = x[0];
        x2 = x.length > 1 ? '.' + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + ',' + '$2');
        }
        return x1 + x2;
    }

    function refresh() {
        $.ajax({
            url: '?ajax=1',
            dataType: 'json'
        }).done(function (result) {
            $('#status').text('refresh in: ' + secondsLeft + ' seconds');
            var i = 0, tr = null, siteName = '', pageviewsTD = null, pageviewsDiffTD = null, pageviewsAvgTD = 0,
                currentPagesNum = 0, pageviewsDiff = 0, lastPagesNumDiff = 0, avg = 0;
            $.each(result, function (index, value) {
                tr = $('#tr_' + index);
                siteName = $(tr.children()[0]).text();
                pageviewsTD = $(tr.children()[1]);
                pageviewsDiffTD = $(tr.children()[2]);
                pageviewsAvgTD = $(tr.children()[3]);
                currentPagesNum = parseInt(pageviewsTD.text().replace(/,/, ''));
                pageviewsDiff = parseInt(value) - currentPagesNum;
                pageviewsTD.text(formatNumber(parseInt(value)));
                for (i = 0; i < chart.series.length; i++) {
                    if (chart.series[i].name == siteName) {
                        chart.series[i].addPoint([new Date().getTime(), pageviewsDiff], false);
                        break;
                    }
                }
                if (pageviewsDiff > 0) {
                    if (typeof avgSites[siteName] == 'undefined') {
                        avgSites[siteName] = {};
                        avgSites[siteName].sum = 0;
                        avgSites[siteName].count = 0;
                    }
                    avgSites[siteName].sum += pageviewsDiff;
                    avgSites[siteName].count++;
                    avg = Math.round(avgSites[siteName].sum / avgSites[siteName].count);
                    pageviewsAvgTD.text(avg);
                    pageviewsDiffTD.text('+' + pageviewsDiff);
                    animateNewPageViews(tr);
                } else {
                    pageviewsDiffTD.text(0);
                    tr.css('font-weight', 'normal');
                }
            });
            chart.redraw();
        });
    }

    function animateNewPageViews(tr) {
        tr.css('font-weight', 'bold')
            .animate({backgroundColor: 'yellow'}, 750)
            .animate({backgroundColor: 'white'}, 750);
    }

    Highcharts.setOptions({
        global: {
            useUTC: false
        }
    });
    chart = new Highcharts.Chart({
        title: { text: 'Page Views History' },
        chart: { renderTo: 'container' },
        xAxis: { type: 'datetime' },
        yAxis: { title: { text: 'Pages Number' } },
        series: series
    });

    setInterval(countdown, 1E3);
});
