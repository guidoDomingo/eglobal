var graphs = {
    lines: function(title, data){
        var chart = AmCharts.makeChart("chartdiv", {
            "type": "serial",
            "categoryField": "category",
            "startDuration": 1,
            "categoryAxis": {
                "gridPosition": "start"
            },
            "trendLines": [],
            "graphs": [
                {
                    "balloonText": "[[title]] of [[category]]:[[value]]",
                    "bullet": "round",
                    "id": "AmGraph-1",
                    "lineColor": "#00a553",
                    "title": "Exitosas",
                    "valueField": "Exitosas"
                },
                {
                    "balloonText": "[[title]] of [[category]]:[[value]]",
                    "bullet": "round",
                    "id": "AmGraph-2",
                    "lineColor": "#daa751",
                    "title": "Canceladas",
                    "valueField": "Canceladas"
                },
                {
                    "balloonText": "[[title]] of [[category]]:[[value]]",
                    "bullet": "round",
                    "id": "AmGraph-3",
                    "lineColor": "#dd4b39",
                    "title": "Error",
                    "valueField": "Error"
                },
                {
                    "balloonText": "[[title]] of [[category]]:[[value]]",
                    "bullet": "round",
                    "id": "AmGraph-4",
                    "lineColor": "#000000",
                    "title": "Iniciadas",
                    "valueField": "Iniciadas"
                }

            ],
            "guides": [],
            "valueAxes": [
                {
                    "id": "ValueAxis-1",
                    "title": ""
                }
            ],
            "allLabels": [],
            "balloon": {},
            "legend": {
                "enabled": true,
                "useGraphSettings": true
            },
            "titles": [
                {
                    "id": "Title-1",
                    "size": 15,
                    "text": ""
                }
            ],
            "dataProvider": data
        });
    }
}