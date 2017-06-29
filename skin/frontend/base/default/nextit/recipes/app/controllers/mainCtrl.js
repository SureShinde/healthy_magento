'use strict';

/**
 * @ngdoc function
 * @name publicHtmlApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the publicHtmlApp
 */
angular.module('recipesApp')
    .controller('MainCtrl', function ($scope, $http, $timeout, $mdSidenav)
    {
        $scope.init = function ()
        {
            $scope.filter = {};
            $scope.loading = 0;
            $scope.title = "HMR Recipes";
            $scope.recipes = [];
            $scope.prepTimes = [];
            $scope.types = [];
            $scope.loadRecipes();
        };

        $scope.loadRecipes = function()
        {
            $scope.loading++;
            $http.get('load').then(function(data)
            {
                $scope.recipes = data.data;
                $scope.loading--;
                $scope.loadPrepTimes();
                $scope.loadTypes();
            });
        };

        $scope.loadPrepTimes = function(){
            angular.forEach($scope.recipes, function(recipe){
                $scope.prepTimes.push(recipe.prep_time);
            });
            $scope.prepTimes = $scope.uniqueArray($scope.prepTimes);
        };
        $scope.loadTypes = function () {
            angular.forEach($scope.recipes, function (recipe) {
                $scope.types.push(recipe.dishtype.replace('&reg;', '®'));
            });
            $scope.types = $scope.uniqueArray($scope.types);
        };
        $scope.uniqueArray = function(a) {
            var temp = {};
            for (var i = 0; i < a.length; i++)
                temp[a[i]] = true;
            var r = [];
            for (var k in temp)
                r.push(k);
            return r;
        };

        $scope.init();

        $scope.TypeOfMeal = buildToggler('typeOfMeal');
        $scope.Ingredients = buildToggler('ingredients');

        //$scope.isOpenRight = function () {
        //    return $mdSidenav('typeOfMeal').isOpen();
        //};

        /**
         * Build handler to open/close a SideNav; when animation finishes
         * report completion in console
         */

        function buildToggler(navID) {
            return function () {
                $mdSidenav(navID)
                    .toggle()
            }
        }
    })


    .controller('IngredientsController', function ($scope, $timeout, $mdSidenav) {
        $scope.close = function () {
            $mdSidenav('ingredients').close();
        };
    })

    .controller('TypeOfMealController', function ($scope, $timeout, $mdSidenav) {
        $scope.close = function () {
            $mdSidenav('typeOfMeal').close();
        };
    });
