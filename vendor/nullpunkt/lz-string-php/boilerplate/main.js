angular.module('lzapp', []);

angular.module('lzapp').controller('LZStringCtrl', LZStringCtrl);

LZStringCtrl.$inject = ['$http'];

function LZStringCtrl($http) {
    var vm = this,
        // def = 'متن شگفت انگیز در اینجا'
        def = ' «sauvegardes», «'
        ;

    vm.source = def;
    vm.displayCompressed = false;
    vm.results = [];
    vm.results16 = [];
    vm.activeTab = 'utf16';

    vm.encode = encode;
    vm.encodeLong = encodeLong;

    vm.longResult = null;

    function encode() {
        if(vm.activeTab == 'utf16') {
            vm.results16.push(generate16(vm.source));
        } else {
            vm.results.push(generate(vm.source));
        }
        vm.source = def;
    }

    function encodeLong() {
        var start = moment();
        var compressed = LZString.compressToBase64(vm.source);
        vm.longResult = {
            initialTextLength: vm.source.length,
            compressedTextLength: compressed.length,
            timeToCompressJs: moment.duration(moment().diff(start)).humanize(),
            decompressedMd5: md5(vm.source),
            server: null
        };

        $http.post('service_long.php', {str: compressed}).then(function(res) {
            vm.longResult.server = res.data;
            vm.longResult.serverResponseTime = moment.duration(moment().diff(start)).humanize();
        });

    }

    function generate(str) {
        var com = LZString.compress(str), com64 = LZString.compressToBase64(str), compressedBytes = bytes(com);
        var result = {
            input: str,
            compressed: com,
            compressedBytes: compressedBytes,
            compressedBytesString: bytesToHexStr(compressedBytes),
            compressed64: com64,
            decompressed: LZString.decompress(com),
            decompressed64: LZString.decompressFromBase64(com64),
            php: {

            }
        };

        $http.post('service.php', {str: str, com64: com64}).then(function(res) {
            result.php = res.data;
            if(angular.isDefined(result.php.compressedBytes)) {
                result.php.compressedBytesString = bytesToHexStr(result.php.compressedBytes)
            }
        });

        return result;
    }

    function generate16(str) {
        var com = LZString.compressToUTF16(str), compressedBytes = bytes(com);
        var result = {
            input: str,
            compressed: com,
            compressedBytes: compressedBytes,
            compressedBytesString: bytesToHexStr(compressedBytes),
            decompressed: LZString.decompressFromUTF16(com),
            php: {

            }
        };

        $http.post('service16.php', {str: str}).then(function(res) {
            result.php = res.data;
            if(angular.isDefined(result.php.compressedBytes)) {
                // result.php.compressedBytesString = result.php.compressedBytes;
                result.php.compressedBytesString = bytesToHexStr(result.php.compressedBytes)
            }
        });

        return result;
    }

    function bytes(str) {
        var bytes = [];
        for (var i = 0; i < str.length; ++i) {
            var val = str.charCodeAt(i);
            bytes.push(val & 255);
            bytes.push((val>>8) & 255);
        }
        return bytes;
    }

    function bytesToHexStr(bytes) {
        var strings = [];
        angular.forEach(bytes, function(byte) {
            if(byte<0) {
                byte += 256;
            }
            var tmp = byte.toString(16);
            if(tmp.length<2) {
                tmp = '0'+tmp;
            }
            strings.push(tmp.toUpperCase());
        });
        return strings.join(' ');
    }

    //function convert_string_to_hex(s) {
    //    var byte_arr = [];
    //    for (var i = 0 ; i<s.length ; i++) {
    //        var value = s.charCodeAt(i);
    //        byte_arr.push(value & 255);
    //        byte_arr.push((value>>8) & 255);
    //    }
    //    return convert_to_formated_hex(byte_arr);
    //}
    //
    //function convert_to_formated_hex(byte_arr) {
    //    var hex_str = "",
    //        i,
    //        len,
    //        tmp_hex;
    //
    //    if (!is_array(byte_arr)) {
    //        return false;
    //    }
    //
    //    len = byte_arr.length;
    //
    //    for (i = 0; i < len; ++i) {
    //        if (byte_arr[i] < 0) {
    //            byte_arr[i] = byte_arr[i] + 256;
    //        }
    //        if (byte_arr[i] === undefined) {
    //            alert("Boom " + i);
    //            byte_arr[i] = 0;
    //        }
    //        tmp_hex = byte_arr[i].toString(16);
    //
    //        // Add leading zero.
    //        if (tmp_hex.length == 1) tmp_hex = "0" + tmp_hex;
    //
    //        if ((i + 1) % 16 === 0) {
    //            tmp_hex += "\n";
    //        } else {
    //            tmp_hex += " ";
    //        }
    //
    //        hex_str += tmp_hex;
    //    }
    //
    //    return hex_str.trim();
    //}
}