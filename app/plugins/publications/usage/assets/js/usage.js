String.prototype.nohtml = function () {
	return this + (this.indexOf('?') == -1 ? '?' : '&') + 'no_html=1';
};

$(document).ready(function () {
    var $ = jq,
        chart,
        month_short = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    function updateCharts(event, pos, item) {
        if (!item) {
            return;
        }

        var mm   = item.series.data[item.dataIndex][0].getMonth()+1; // January is 0!
        var yyyy = item.series.data[item.dataIndex][0].getFullYear();
        // Prepend 0s
        if (mm < 10) {
            mm = '0' + mm
        }

        if (chart_views) {
            chart_views.unhighlight();
            chart_views.highlight(0, item.dataIndex);
        }
        if (chart_downloads) {
            chart_downloads.unhighlight();
            chart_downloads.highlight(0, item.dataIndex);
        }
    };
    
    const url = $('#usage-form').attr('action') + '/' + $('#usage-form').attr('version')

    $.ajax({
        method: 'GET',
        url: url.nohtml(),
        dataType: 'json',
        success: function (response, status, xhr) {

            var views = $('#chart-views');
            views.bind("plotclick", function(event, pos, item) {
                return updateCharts(event, pos, item);
            });

            let views_array = response.views.splice(0, response.views.length)
            let views_split = []
            let dataset_views = [{
                color: response.dataset_views.color,
                data: views_split,
                label: response.dataset_views.label
            }]

            for (i = 0; i < views_array.length; i++) {
                views_split.push(eval(views_array[i]))
            }

            var chart_views = $.plot(views, dataset_views, {
                series: {
                    lines: {
                        show: true,
                        fill: false
                    },
                    points: { show: false },
                    shadowSize: 0
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.6)',
                    borderWidth: 1,
                    borderColor: 'transparent',
                    hoverable: true,
                    clickable: true
                },
                tooltip: true,
                    tooltipOpts: {
                    content: "%y %s in %x",
                    shifts: {
                        x: -60,
                        y: 25
                    },
                    defaultTheme: false
                },
                legend: {
                    show: false
                },
                xaxis: {
                    mode: "time",
                    tickDecimals: 0,
                    tickFormatter: function (val, axis) {
                        var d = new Date(val);
                        return (d.getUTCMonth() + 1) + "/" + d.getUTCFullYear().toString().substr(2,2);
                    }
                },
                yaxis: {
                    min: 0,
                    tickFormatter: function (val, axis) {
                        if (val > 1000) {
                            val = (val / 1000) + ' K';
                        }
                        return val;
                    }
                }
            });
            chart_views.highlight(0, response.chart_views - 1);


            var downloads = $('#chart-downloads');
            downloads.bind("plotclick", function(event, pos, item) {
                return updateCharts(event, pos, item);
            });

            let downloads_array = response.downloads.splice(0, response.downloads.length)
            let downloads_split = []
            let dataset_downloads = [{
                color: response.dataset_downloads.color,
                data: downloads_split,
                label: response.dataset_downloads.label
            }]
            
            for (i = 0; i < downloads_array.length; i++) {
                downloads_split.push(eval(downloads_array[i]))
            }

            var chart_downloads = $.plot(downloads, dataset_downloads, {
                series: {
                    lines: {
                        show: true,
                        fill: false
                    },
                    points: { show: false },
                    shadowSize: 0
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.6)',
                    borderWidth: 1,
                    borderColor: 'transparent',
                    hoverable: true,
                    clickable: true
                },
                tooltip: true,
                    tooltipOpts: {
                    content: "%y %s in %x",
                    shifts: {
                        x: -60,
                        y: 25
                    },
                    defaultTheme: false
                },
                legend: {
                    show: false
                },
                xaxis: {
                    mode: "time",
                    tickDecimals: 0,
                    tickFormatter: function (val, axis) {
                        var d = new Date(val);
                        return (d.getUTCMonth() + 1) + "/" + d.getUTCFullYear().toString().substr(2,2);
                    }
                },
                yaxis: {
                    min: 0,
                    tickFormatter: function (val, axis) {
                        if (val > 1000) {
                            val = (val / 1000) + ' K';
                        }
                        return val;
                    }
                }
            });
            chart_downloads.highlight(0, response.chart_downloads - 1);
        },
        error: function (xhr, status, error) {
            
        }
    }) 
})