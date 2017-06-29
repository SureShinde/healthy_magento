'use strict';

/**
 * @ngdoc overview
 * @name publicHtmlApp
 * @description
 * # publicHtmlApp
 *
 * Main module of the application.
 */
angular
    .module('recipesApp', [
        'ngAnimate',
        'ngMaterial',
        'ngMdIcons'
    ])
    .filter('unsafe', function ($sce) {
        return $sce.trustAsHtml;
    })
    .filter('filterTitles', function () {
        return function (input, search) {
            if (!input) return input;
            if (!search || search.length <= 0) return input;

            var result = [];
            angular.forEach(input, function (value, key) {
                if(value.title.toLowerCase().indexOf(search.toLowerCase()) > -1){
                    result.push(value);
                }
            });
            return result;
        }
    })
    .filter('filterPrepTimes', function () {
        return function (input, search) {
            if (!input) return input;
            if (!search || search.length <= 0) return input;

            var result = [];
            angular.forEach(input, function (value, key) {
                if (value.prep_time.toLowerCase().indexOf(search.toLowerCase()) > -1) {
                    result.push(value);
                }
            });
            return result;
        }
    })
    .filter('filterTypes', function () {
        return function (input, search) {
            if (!input) return input;
            if (!search || search.length <= 0) return input;

            var result = [];
            angular.forEach(input, function (value, key) {
                if (value.dishtype.replace('&reg;', '®').toLowerCase().indexOf(search.toLowerCase()) > -1) {
                    result.push(value);
                }
            });
            return result;
        }
    })

    .config(function ($mdThemingProvider) {
        $mdThemingProvider.definePalette('pink', {
            '50': '#ff8cbe',
            '100': '#ff4092',
            '200': '#ff0872',
            '300': '#bf0052',
            '400': '#a10045',
            '500': '#820038',
            '600': '#63002b',
            '700': '#45001e',
            '800': '#260010',
            '900': '#080003',
            'A100': '#ff8cbe',
            'A200': '#ff4092',
            'A400': '#a10045',
            'A700': '#45001e',
            'contrastDefaultColor': 'light',
            'contrastDarkColors': '50 100 A100 A200'
        });

        $mdThemingProvider.definePalette('grey', {
            '50': '#ffffff',
            '100': '#ffffff',
            '200': '#ffffff',
            '300': '#fdfdfd',
            '400': '#ededed',
            '500': '#dedede',
            '600': '#cfcfcf',
            '700': '#bfbfbf',
            '800': '#b0b0b0',
            '900': '#a1a1a1',
            'A100': '#ffffff',
            'A200': '#ededed', //was FFF
            'A400': '#ededed',
            'A700': '#bfbfbf',
            'contrastDefaultColor': 'light',
            'contrastDarkColors': '50 100 200 300 400 500 600 700 800 900 A100 A200 A400 A700'
        });

        $mdThemingProvider.theme('HMR')
            .primaryPalette('pink')
            .accentPalette('grey');
    });

