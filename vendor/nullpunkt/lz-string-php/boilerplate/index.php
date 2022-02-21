<!doctype html>
<html class="no-js" lang="" data-ng-app="lzapp" ng-strict-di>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>lz-string-php boilerplate</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <style>
        body {
            padding-top: 50px;
            padding-bottom: 20px;
        }
    </style>
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap-theme.min.css">
</head>
<body>

<div class="container" data-ng-controller="LZStringCtrl as vm">
    <div class="row">
        <div class="col-md-12">
            <form class="form-inline">
                <div class="form-group">
                    <div class="input-group">
                        <input type="text" class="form-control" data-ng-model="vm.source">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" data-ng-click="vm.encode()">Encode!</button>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <span data-ng-if="vm.activeTab == 'compress64'">
                    <input id="display-compressed" type="checkbox" class="form-control" data-ng-model="vm.displayCompressed">
                    <label for="display-compressed">Display Compressed Bytes</label>
                </span>
            </form>
        </div>
        <br>
        <br>
        <br>
        <ul class="nav nav-tabs">
            <li data-ng-class="{active: (vm.activeTab == 'compress64')}"><a href data-ng-click="vm.activeTab = 'compress64'">Compressed64</a></li>
            <li data-ng-class="{active: (vm.activeTab == 'utf16')}"><a href data-ng-click="vm.activeTab = 'utf16'">UTF-16</a></li>
        </ul>
        <br>
        <div data-ng-if="vm.activeTab == 'compress64'">
            <div class="col-md-12">
                <table class="table table-condensed">
                    <thead>
                    <tr>
                        <th>Source</th>
                        <th data-ng-show="vm.displayCompressed">Compressed</th>
                        <th data-ng-show="vm.displayCompressed">Compressed [Bytes]</th>
                        <th>Compressed64</th>
                        <th>Decompressed</th>
                        <th>Decompressed64</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr data-ng-repeat="row in vm.results | orderBy:'$index':true">
                        <td data-ng-bind="row.input"></td>
                        <td data-ng-show="vm.displayCompressed" ng-class="{warning: row.compressed!=row.php.compressed}">
                            <div data-ng-bind="row.compressed"></div>
                            <div data-ng-bind="row.php.compressed"></div>
                        </td>
                        <td data-ng-show="vm.displayCompressed" ng-class="{warning: row.compressedBytesString!=row.php.compressedBytesString}">
                            <div data-ng-bind="row.compressedBytesString"></div>
                            <div data-ng-bind="row.php.compressedBytesString"></div>
                        </td>
                        <td ng-class="{warning: row.compressed64!=row.php.compressed64}">
                            <div data-ng-bind="row.compressed64"></div>
                            <div data-ng-bind="row.php.compressed64"></div>
                        </td>
                        <td ng-class="{warning: row.decompressed!=row.php.decompressed}">
                            <div data-ng-bind="row.decompressed"></div>
                            <div data-ng-bind="row.php.decompressed"></div>
                        </td>
                        <td ng-class="{warning: row.decompressed64!=row.php.decompressed64}">
                            <div data-ng-bind="row.decompressed64"></div>
                            <div data-ng-bind="row.php.decompressed64"></div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div data-ng-if="vm.activeTab == 'utf16'">
            <div class="col-md-12">
                <table class="table table-condensed">
                    <thead>
                    <tr>
                        <th>Source</th>
                        <th>Compressed [Bytes]</th>
                        <th>Decompressed</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr data-ng-repeat="row in vm.results16 | orderBy:'$index':true">
                        <td data-ng-bind="row.input"></td>
                        <td ng-class="{warning: row.compressedBytesString!=row.php.compressedBytesString}">
                            <div data-ng-bind="row.compressedBytesString"></div>
                            <div data-ng-bind="row.php.compressedBytesString"></div>
                        </td>
                        <td ng-class="{warning: row.decompressed!=row.php.decompressed}">
                            <div data-ng-bind="row.decompressed"></div>
                            <div data-ng-bind="row.php.decompressed"></div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="bower_components/angular/angular.min.js"></script>
<script src="bower_components/lz-string/libs/lz-string.js"></script>
<!--<script src="bower_components/lz-string/libs/lz-string.min.js"></script>-->
<script src="main.js"></script>
</body>
</html>

