const firstApp = angular.module('helloWorldApp', []);
firstApp.controller('MyController', function($scope) {
    $scope.first = 5;
    $scope.second = 10;
    $scope.heading = 'Sum is : ';
    $scope.total = 15;
    $scope.updateSum = function() {
        $scope.total = parseInt($scope.first) + parseInt($scope.second);
    };
});