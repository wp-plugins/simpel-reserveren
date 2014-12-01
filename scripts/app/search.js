app.controller('SearchController', ['$scope', '$http', 'bookgroups', function($scope, $http, bookgroups) {
    $ = jQuery;
    //$scope.baseURL = 'http://api.simpelreserveren.nl/search';
    $scope.baseURL = 'http://api.sr4.dev/search';
    $scope.localURL = '/wp-admin/admin-ajax.php?action=sr_entities';
    $scope.viewport = $('body').width();
    $.cookie.json = true;

    if($.cookie('sr')) {
        $scope.cookie = $.cookie('sr');
    } else {
        var next = new Date();
        next.setDate(next.getDate()+7);
        $scope.cookie = {
            'start': $.format.date(new Date(), 'dd-MM-yyyy'),
            'end': $.format.date(next, 'dd-MM-yyyy'),
            'type': '',
            'bookgroups': bookgroups
        }
    }
    $scope.form = $scope.cookie;

    $('#widgetCalendar').DatePicker({
        flat: true,
        format: 'd-m-Y',
        date: [$scope.form.start, $scope.form.end],
        current: $scope.form.start,
        calendars: ($scope.viewport > 400 ? 3 : 1),
        mode: 'range',
        starts: 1,
        onChange: function(formated) {
            $('#start').val(formated[0]);
            $('#end').val(formated[1]);
            if(formated[0] !== formated[1]) {
                $('#widgetCalendar').slideUp({duration: 200});
            }
        }
    }).hide();



    $scope.search = function() {
        $scope.setCookie();
        var url = $scope.baseURL + '?' + $.param( $scope.form );
        $http.get(url).success($scope.display);
    };

    $scope.setCookie = function(){
        $scope.cookie = $scope.form;
        $.cookie('sr', $scope.cookie, {expires: 100, 'path': '/'});
    }

    $scope.setBookgroups = function() {
        $('#bookgroup-panel').slideUp({duration: 200}); 
        var result = [];
        for(i in $scope.form.bookgroups) {
            var bookgroup = $scope.form.bookgroups[i];
            if(bookgroup.nr > 0) {
                result.push(bookgroup.nr + ' ' + bookgroup.title);
            }
        }
        $('#bookgroup').val(result.join(', '));
    }

    $scope.display = function(response){
        $scope.foundEntities = response.results;
        $scope.facilities = response.facilities;
        $scope.connectLocalEntities();
        $('.entities').addClass('show');        
    }

    $scope.toggleFacility = function() {
        var facility = this.facility;
        facility.selected = !facility.selected;
    }

    $scope.test = function(){
        console.log('test');
    }

    $scope.getLocalEntities = function(){
        $.get($scope.localURL).success(function(r){
            $scope.localEntities = JSON.parse(r);
        });
    }

    $scope.connectLocalEntities = function() {
        for(i in $scope.foundEntities) {
            $scope.foundEntities[i].local = $scope.findLocalEntity($scope.foundEntities[i].id);
        }
    }

    $scope.findLocalEntity = function(entityId) {
        for(i in $scope.localEntities) {
            if($scope.localEntities[i].entity_id == entityId) {
                return $scope.localEntities[i];
            }
        }
    }

    $scope.viewEntitiy = function() {
        document.location = this.item.local.permalink;
    }

    // do search
    $scope.getLocalEntities();
    $scope.search();
    $scope.setBookgroups();
}]);
