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
            <form>
                <div class="form-group">
                    <label for="long-text">Long Text</label>
                    <textarea class="form-control" data-ng-model="vm.source" rows="10" id="long-text"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" data-ng-click="vm.encodeLong()">Encode!</button> (Length: <span data-ng-bind="vm.source.length"></span>)
            </form>
        </div>
        <br>
        <div class="col-md-12">
            <h1>Result</h1>
            <pre data-ng-bind="vm.longResult | json"></pre>
        </div>
    </div>
</div>

<script src="bower_components/angular/angular.min.js"></script>
<script src="bower_components/moment/min/moment-with-locales.min.js"></script>
<script src="bower_components/javascript-md5/js/md5.min.js"></script>
<!--<script src="bower_components/lz-string/libs/lz-string.js"></script>-->
<script src="bower_components/lz-string/libs/lz-string.min.js"></script>
<script src="main.js"></script>
</body>
</html>

