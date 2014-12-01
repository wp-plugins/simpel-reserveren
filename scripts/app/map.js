app.controller('MapController', ['$scope', '$http', 'bookgroups', 'company', 'leafletBoundsHelpers', 'leafletData', 
    function($scope, $http, bookgroups, company, leafletBoundsHelpers, leafletData) {
        $scope.baseURL = 'http://api.sr4.dev/map';
        $scope.viewport = $('body').width();
        $scope.company = company;
        $.cookie.json = true;

        console.log('load');

        $scope.items = [];
        var map, icon;
        var mapMinZoom = 3; 
        var mapMaxZoom = 6;

        // https://www.mapbox.com/maki/
        angular.extend($scope, { 
            defaults: {
                crs: L.CRS.Simple,
                tileLayer: '//static.simpelreserveren.nl/images/maps/'+$scope.company.id+'/{z}/{x}/{y}.png',
                tileLayerOptions: {
                    attribution: ''
                },
                minZoom: mapMinZoom,
                maxZoom: mapMaxZoom,
            },
            center: {
                lat: 500,
                lng: 500,
                zoom: 6
            },
            events: {}
        });

        leafletData.getMap().then(function(map) {
            var mapBounds = new L.LatLngBounds(
                map.unproject([0, company.map_bound_y], mapMaxZoom),
                map.unproject([company.map_bound_x, 0], mapMaxZoom));
                
            map.fitBounds(mapBounds);
        });

        $scope.markers = new Array();

        //$http.get($scope.baseURL).success($scope.display);


        $scope.display = function(company) {
            console.log(company);

            $scope.company = company;
            $scope.selected = {};

            


            var color = $("#color").spectrum({ 
                preferredFormat: 'hex', 
                showInitial: true,
                showPalette: true,
                showSelectionPalette: true,
                maxPaletteSize: 10 
            });

            $scope.place_markers();            
        }
    }
]);
