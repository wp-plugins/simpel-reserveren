angular.module('sr.services', [])
.factory('MapFactory', function ($resource) {
    return $resource('/company/:id/map', {
        create: { method: 'POST' },
        update: { method: 'PUT' },
        delete: { method: 'DELETE' }
    })
});