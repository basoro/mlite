<?php
$headers = array('Accept', 'Accept-CH', 'Accept-Charset', 'Accept-Datetime', 'Accept-Encoding', 'Accept-Ext', 'Accept-Features', 'Accept-Language', 'Accept-Params', 'Accept-Ranges',
    'Access-Control-Allow-Credentials', 'Access-Control-Allow-Headers', 'Access-Control-Allow-Methods', 'Access-Control-Allow-Origin', 'Access-Control-Expose-Headers',
    'Access-Control-Max-Age', 'Access-Control-Request-Headers', 'Access-Control-Request-Method', 'Age', 'Allow', 'Alternates', 'Authentication-Info', 'Authorization', 'C-Ext',
    'C-Man', 'C-Opt', 'C-PEP', 'C-PEP-Info', 'CONNECT', 'Cache-Control', 'Compliance', 'Connection', 'Content-Base', 'Content-Disposition', 'Content-Encoding', 'Content-ID',
    'Content-Language', 'Content-Length', 'Content-Location', 'Content-MD5', 'Content-Range', 'Content-Script-Type', 'Content-Security-Policy', 'Content-Style-Type',
    'Content-Transfer-Encoding', 'Content-Type', 'Content-Version', 'Cookie', 'Cost', 'DAV', 'DELETE', 'DNT', 'DPR', 'Date', 'Default-Style', 'Delta-Base', 'Depth', 'Derived-From',
    'Destination', 'Differential-ID', 'Digest', 'ETag', 'Expect', 'Expires', 'Ext', 'From', 'GET', 'GetProfile', 'HEAD', 'HTTP-date', 'Host', 'IM', 'If', 'If-Match',
    'If-Modified-Since', 'If-None-Match', 'If-Range', 'If-Unmodified-Since', 'Keep-Alive', 'Label', 'Last-Event-ID', 'Last-Modified', 'Link', 'Location', 'Lock-Token',
    'MIME-Version', 'Man', 'Max-Forwards', 'Media-Range', 'Message-ID', 'Meter', 'Negotiate', 'Non-Compliance', 'OPTION', 'OPTIONS', 'OWS', 'Opt', 'Optional', 'Ordering-Type',
    'Origin', 'Overwrite', 'P3P', 'PEP', 'PICS-Label', 'POST', 'PUT', 'Pep-Info', 'Permanent', 'Position', 'Pragma', 'ProfileObject', 'Protocol', 'Protocol-Query', 'Protocol-Request',
    'Proxy-Authenticate', 'Proxy-Authentication-Info', 'Proxy-Authorization', 'Proxy-Features', 'Proxy-Instruction', 'Public', 'RWS', 'Range', 'Referer', 'Refresh', 'Resolution-Hint',
    'Resolver-Location', 'Retry-After', 'Safe', 'Sec-Websocket-Extensions', 'Sec-Websocket-Key', 'Sec-Websocket-Origin', 'Sec-Websocket-Protocol', 'Sec-Websocket-Version',
    'Security-Scheme', 'Server', 'Set-Cookie', 'Set-Cookie2', 'SetProfile', 'SoapAction', 'Status', 'Status-URI', 'Strict-Transport-Security', 'SubOK', 'Subst', 'Surrogate-Capability',
    'Surrogate-Control', 'TCN', 'TE', 'TRACE', 'Timeout', 'Title', 'Trailer', 'Transfer-Encoding', 'UA-Color', 'UA-Media', 'UA-Pixels', 'UA-Resolution', 'UA-Windowpixels', 'URI',
    'Upgrade', 'User-Agent', 'Variant-Vary', 'Vary', 'Version', 'Via', 'Viewport-Width', 'WWW-Authenticate', 'Want-Digest', 'Warning', 'Width', 'X-Content-Duration',
    'X-Content-Security-Policy', 'X-Content-Type-Options', 'X-CustomHeader', 'X-DNSPrefetch-Control', 'X-Forwarded-For', 'X-Forwarded-Port', 'X-Forwarded-Proto', 'X-Frame-Options',
    'X-Modified', 'X-OTHER', 'X-PING', 'X-PINGOTHER', 'X-Powered-By', 'X-Requested-With', 'X-Token');

header('Time-Zone: ' . @date_default_timezone_get());
header('Access-Control-Allow-Origin: *');
header('Access-Control-Expose-Headers: ' . implode(',', $headers));
header('Access-Control-Allow-Methods: CONNECT, CUSTOMREQUEST, DEBUG, DELETE, DONE, GET, HEAD, HTTP, HTTP/0.9, HTTP/1.0, HTTP/1.1, HTTP/2, OPTIONS, ORIGIN, ORIGINS, PATCH, POST, PUT, QUIC, REST, REQUEST, SESSION, SHOULD, SPDY, TRACE, TRACK');
header('Access-Control-Allow-Headers: ' . implode(',', $headers));

class PHP_Webserver_Router
{

    private $request_uri = "";
    private $physical_file = "";
    private $extension = "";
    private $eTag = "";
    private $eTagHeader = "";
    private $last_modified = "";
    private $if_modified_since = "";
    private $file_length = "";


    var $log_enable = TRUE;

    var $indexPath = "index.php";
    var $mvc_enabled = TRUE;
    var $rules = array();

    var $http_status = 200;

    var $php_warning = 0;
    var $php_notice = 0;

    var $script_filename = "";

    var $accepted_extensions_as_root = array("php", "html", "htm");

    function __construct()
    {

        if (!function_exists('console_output')) {

            function console_output()
            {

                call_user_func_array(array(new PHP_Webserver_Router(), 'console'), func_get_args());

            }

        }

    }

    /**
     * Prepare variables
     */
    private function init()
    {

        set_error_handler(function ($error_type) {

            switch ($error_type) {
                case 2 || E_WARNING || 8 || E_NOTICE:
                    $this->php_warning++;
                    break;
            }

        }, E_ALL);

        /**
         * Fixed cross-os include path
         */
        set_include_path(get_include_path() . (DIRECTORY_SEPARATOR == '/' ? ':' : ';') . $_SERVER['DOCUMENT_ROOT']);

        if (ini_get('auto_prepend_file') && !in_array(realpath(ini_get('auto_prepend_file')), get_included_files(), true)) {

            include(ini_get('auto_prepend_file'));

        }

        $this->refresh_paths();
        $this->script_filename = $_SERVER['SCRIPT_FILENAME'];

        $this->request_uri = \filter_input(\INPUT_SERVER, 'REQUEST_URI', \FILTER_SANITIZE_ENCODED);
        $this->request_uri = $this->format_unix(urldecode($this->request_uri));

        $this->physical_file = $this->format_unix($_SERVER['SCRIPT_FILENAME']);
        $this->extension = strrev(strstr(strrev($this->physical_file), '.', TRUE));

        $this->last_modified = time();
        $this->eTag = md5($this->last_modified);
        $this->file_length = 0;

        if (file_exists($this->physical_file)) {

            $this->last_modified = filemtime($this->physical_file);
            $this->eTag = md5_file($this->physical_file);
            $this->file_length = filesize($this->physical_file);

        }

        $this->if_modified_since = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false);
        $this->eTagHeader = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

    }

    /**
     * Format paths
     */
    function refresh_paths()
    {

        $formatVarsToUnix = array('DOCUMENT_ROOT', 'SCRIPT_FILENAME', 'SCRIPT_NAME', 'PHP_SELF');
        foreach ($formatVarsToUnix as $var) {
            if (isset($_SERVER[$var])) {
                $_SERVER[$var] = preg_replace('([/\\\]+)', '/', $_SERVER[$var]);
            }
        }

    }

    /**
     * This will add a favicon.ico to your page
     * Browsers will do a request for favicon.ico by default so if you don't have one you will see a 404 request
     * To prevent that the router will serve you a default icon to make peace with /favicon.ico request
     */
    function favicon()
    {

        $favicons = array(
            "php_7" => array(
                "logo" => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTM4IDc5LjE1OTgyNCwgMjAxNi8wOS8xNC0wMTowOTowMSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo2ZDc0YmRlZi1jNDhjLTc2NDQtYjA1My02MTQwOTk0NDcyOTMiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RENGOEI5NjAxQzVDMTFFN0FDRTJFMzYxOUJBOEREMzMiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6RENGOEI5NUYxQzVDMTFFN0FDRTJFMzYxOUJBOEREMzMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTcgKFdpbmRvd3MpIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6ZDVjNmU2N2MtYmQ3NS1jOTRlLThmOWQtYjRmMzE1ZjQwMzIyIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjZkNzRiZGVmLWM0OGMtNzY0NC1iMDUzLTYxNDA5OTQ0NzI5MyIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/Pjb6wE0AAAThSURBVHjatFdLbxtVFD7zdGI7aXBqA2loEtokVSmoqAUBQTxKERu6qVjACjbs+BdsEEsW/AuEUBe8iiKVQlQCiEJBeVCorKTxK3bi13jGnhnOuZ65Hk9mxjaIG51Yvr4z9zvfPec75wq2bcNBtZV+/6Nvt1p6Z0oUBfi/h26Y8N7bT1194kzmU9mZq6Mdok2N+jILHQB7uLWCIKDxr2365wLQ0PbR5kYFMJOZgFhMBtsKQOEhUxRFZFqD8oHmTpW8APjEKIO2nEjGIBlXwLSiaZAlEZqaAXZ3WcdxuA9Azv+QaVrdB4RwAO22CUZbBCsMAHIuYVyZYEG7Y7mzNbTKQADTD8QZ8ijf0tMJiI8r3VgIOAHTtOGw1mLvIIccXw4cEMEA3Lh6543zMDc7GRlkEgKkwApaQpvdL9Ths6+3QLC7YJxB9OuhAOiFBqZKraEzBoaK8LCU05F89FwURIwTy11Y5MHpWZvvSy8808ph6z/nvKa3u4yiWciA4NvLnwU85GRZhF838pg+AHbEETyYToKqSHyT+ROToCg9v2p1g72RHOpmitDHthdAGa2JlqAvKr5k424J7mwWItNweeE4JBMKoIrC6flpOHXymMd7E7b+LrNj7HQoo7gne0EASAmrLgBaSw8GxQC9iLxh/jgMxeMqrFyYBa+Ur9/ew2PUYHxMRvm1GAuOEgYy0HBYeDhSfHCzsZgCCdyQIGQwDekITs+nIDU1xtdVGwYUyw1cp7DoZ4BtcAEUggBYrjpFDQOF5+xiGt5960n2QkUWvfrOB6nj1deW4No3f8JuvuqIGjFACdlTXdn3XH4QAKKY0pM2jlznoCLPXUHyMX0kDQPVMOjFVYrsYUsvBidVQcZAf6wFMpAbhoFG00B12wRVleH5i4/ARELlv+/m67Bf0br6j95TdhDojsnrQMVh4d8BIGbJm08+34BFTLtLz87z35paB7668Rcy1GJA6U/GdJYJTE+EiuBR7pFjwM2EBBagN6+chZgq8fmf7uSg3tQxS+QjemH2GOgTFn8MFIYBQLQ+ff4ELC6k+FyxrMEf20UMTikQsAdALgoARWdkhJGYTCRVeP2Vpb75W7/sYr03j6Sk4BEu57e9KAC8Tod6j1Xy0nMLkE6N87m72UO4t3MQnJq4qwvAXweCAPBOJWiQns9kknB5ZaFvbv32LkQ108Sap2PKRwEwotSQup4rl5eZtrvjt80Sk1wpom8wHQCCrxcIAnBkgdfT2YeOweNn0nxuJ1eHmz9mWS2ISluL1QHGgO5VwaA0DNUC6g9KlQZ88PF3cGouBY9hPVj7eYd1umOx8fCm1JFhpxBVnTgbHYB7loX9BtzP1+DmepbRvvzodOglhOKCBKnTK0QVf5AHAcgP6u/dHsEOuPXQJwGlqtlEya6TYWkWWDZAhVR5EIDsKD0feUhGTayGAtVodDclsXK7IAJENyNVlbIeQQoF8AXaM2gvor2MdhHteFgnTI1rEY+FCpTRtlimCKxTIkYEHa9tv5+cmbxxbimziqx8f+361kAAlIq3HPvQ2fwC2ktoL6CdQ5vk2l2qe4/AkgRhG7+uoa0ikjW8M25T3Kyu3WO3YgmD2cTPKAD+Qd3Ll47RIBVaQXuVWEL66Ub9A9p1tilKAwkmvwIj5UZV5ykp+LT6HwEGAL2WVjwbkFaFAAAAAElFTkSuQmCC",
                "errors" => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTM4IDc5LjE1OTgyNCwgMjAxNi8wOS8xNC0wMTowOTowMSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo2ZDc0YmRlZi1jNDhjLTc2NDQtYjA1My02MTQwOTk0NDcyOTMiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NDg2QTc4OEIxQzZFMTFFNzg4RTBCQkVEQzU0ODcxOEYiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NDg2QTc4OEExQzZFMTFFNzg4RTBCQkVEQzU0ODcxOEYiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTcgKFdpbmRvd3MpIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6NGFkZGUzMjUtODgxMS0wZDRjLWI5NjYtYjYyNTFiMDg3ZjFmIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjZkNzRiZGVmLWM0OGMtNzY0NC1iMDUzLTYxNDA5OTQ0NzI5MyIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/Pu7ooIQAAAVBSURBVHjatFdLbBtVFD1vZvyJYztuSRMllCRVS1yJBClKES1UhJZSJP4IqbBBqlQgC9iwZAdiU5C6YVFWqELsEEHlJ6gUEaho0kZ81A8gkkVDEKnjOLHj+DMzng/3jcfj33iSFPGkmzjPL++ee+69573HTNPE4lJ217sfXJpjjMUYw/8+irKGl54bfnnsYP+Hkj2XI1sni213M4MCgLm1tRQgagJc4T8qAIpkq2T92wXQ2xVBICDBNFxQ1LApCAIy2SLWMsXKVLIWAB+p7TrnLiPhAMIhH3TDmwZJFFAoqjDLyzQ74DoAicZ/0kWKzKMo+F6q6Icq+WC0AkAexZIKHQZKmlEhZYMsvSmAznQCoq65p5iVEXRTKkNB0RUAowW6FECmp48+Mei6UfkqY4NwB2BFTXZy4jT6luYADxZEvQRm1ue7NvpEfATn3z4H3m267oDk9CstATBarPiC2AjHIJGDLReEy5AjMeg+P5giU50YlVhWnOKsWbtc116CiHR013/v+egOmLQX7xJDd6hyfDV2gbNC0lVc23+I2DAoJULLsHvMAvzMsHLMR9/VS5Bkp9Ww0dljpZDXSE2nJNwArJEVyNr5H36q3D/2juL64CFP1uP9MasN5WA74j98gYGfv3e+V8IdmD/8OCRVgUbOeR3YKbjlBoArYbYCgG8uaSXLmhwTI7oolukihgwSmXAqgQc+PgNB05x1Pz0/jtX+ONoKWSiUf2MTBvI2Cz2etUbO25Q8wvl1GBRON1uBLxpGfOo8Yks3nXU5oj65bxjta0nooZBFPxchm4GkGwCjok5eQ/UFcM/8LF755B2rXTlDvN8rElcZofQKnn3rJL5+8yz+Hh2jFizYKbCaNuUGoKkT3IZAlG+Ed5Bj1XudXk6FTiqJeg2oMN3Uhq5q2LQx5TJLALY6ZFrLQXMVNOtrzZWBLQDQkW+L4vNjpxBQizj8yzcI59LVDfaPIEWFJ1BqDMlvCRHTdWhVGU7bLNweAF71mujDxGPjGFy4iqOXP6uKTsdOXHjjDDK9AxBK5dqQ5AJpimalgFXvAeZt10DlrGin1nrxq/fhV2Vn/tdnTiHbvRvBbLppfc1BlKxjtGHv5JbyGmjD/dcmse+v687c6kAcN46fgK9YcDuTagEkvADw6vQsb35GRHIZPDn1Ud38lRdehxqKgFGNNJ3aZlmGG1XQDYBzTreM3t+GR2Y+RedadZ+FA0dw876j8MkFt4ugA8A+ZjwZcG4qbqNEVX1ncgHHpiecOc0fxOyJ16pcu7HGT8KqDC97AVBbqiGPhGT4qe/OIag4XYTfKe/Ld98LSZFbsqbbABrvAm4AmhY4kVLr3XVrHsN/XnbmlroGcPHpV+s6wQW35dwss6PUqqBbG7bUAn4zSu3sxenxs9i7eANDc7OYHjmOXKgDdxglGB51wzXAPoiydp1tH4Cl54KE5c7d+Kd7D34cfYJUUUNcocKj+4DbI0Rg/D3ALBW0D6J0Y5G7AVj2uvhV7whc58ymVw+zbz9qSUehoCLHLa9a84QhTb+0zQAsbucWyiPkpqo6ioqGfL7sVKbPmlaOnAPiLyO/X1ysEaSWAL4lO0g2RnaE7AB/IrR6GqTXZays5pEnp2rJsN6KfJ4JnBGm0LPtt77e6MWhwa4pYmX6y8m5TQHwVrxi23u281Gyh8keIhsiizrancrVpsAQGZunP2fIpgjJDN2G55MEcGpmAQqxJEqC9ULyAtA4+O3lgm187CF7kOxRzhLRz1/Us2STllOAHxBOX5aIcjWrOC3JGh45/wowAJiJcJomgiWdAAAAAElFTkSuQmCC",
            ),
            "php_5" => array(
                "logo" => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTM4IDc5LjE1OTgyNCwgMjAxNi8wOS8xNC0wMTowOTowMSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo2ZDc0YmRlZi1jNDhjLTc2NDQtYjA1My02MTQwOTk0NDcyOTMiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RDdFRjkwRTIxQzVDMTFFNzk1OTFEOUVGQzMzQUNGNDUiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6RDdFRjkwRTExQzVDMTFFNzk1OTFEOUVGQzMzQUNGNDUiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTcgKFdpbmRvd3MpIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6ZDVjNmU2N2MtYmQ3NS1jOTRlLThmOWQtYjRmMzE1ZjQwMzIyIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjZkNzRiZGVmLWM0OGMtNzY0NC1iMDUzLTYxNDA5OTQ0NzI5MyIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PqrPoewAAAUtSURBVHjatFfbbhtVFN1nxpc4FzdubkoKQSHFBCrxUp6KQKAKeAAJ8cATH4D4Aj4AId7hH3gCCakqlajUqNAKCYS4qJBQwqWlbWzXjmOPb2PPhb3OnBnPTMZOQsREu4rjM2evfVlr7wrXdWm/2Vt8/6Ovf+uZ1qymCfo/H3bH5rrvvXvhmdWVU7dS6u8GW5Nt9rgXOo575LNCBMF12Pbxiw+gy1ZjWz2Oc1z46EqeUrqGqGJfRj9qfLZW71DDMPGxEQeA5+GxUikvJcrPZCmT1g8CiD06H24YPVkCxlJXWYgAKMdfsmyHXJlikQjA4Uv7A1vVNhmA4B9d994fWI5/VRWvHwpgZXGGshmd3BEZ0DmU5YVpSqdGZICd2RwEN7mM3LFdP5SKfyQMYDf87sCy6a3XNmhjfeFEXY+6f3ZlmxzXIXvYsKUkAJEMAG3T6J+Ydt2eRZZybDuqBO5RAPA7zZZ5YgAm94irAICynv9kANV4/Yx2PwTIpUtXb8t6aqqpQK0l7gGPhkk0Jao3epRKaWT2bXVGQignAdhTepDzj7VCAODs5+0K7dypM+00rwmZBU+uz1OWaeiMYIGuaZTm84g+dOZhEoCGUsOcLzJhAHgKpyY4WuF1vQKA6HU2bQQAX9rBBjCFadlXoncAQIsNArHkR9zuDiKXLc1PSRDZbCqg4fRUZqwQmaYlz0JTyJMUw1fBOAA73AeoX4cBADkixPPmqxv0+sUiCXEwwlHP5Wu/U7na4Xtc5V86N5IARARC8MWgUM+0aWrSA4Bmgh3n6Q8cCRiB+NKAPwcBxM6Xwxno9S0pIP+Zgtz5Jt+Bu1ACkTBz4hkohbveYu3+YnOH8tPZkdNwbjYnS4QegNKtrxZorpBT0ds0GDgy8ShBkt7EAezGnXz51Z9hCZVdH8wCLtMG0zCT8ZoQw2aenQcAOAMyciHCJSiNA1CO1s+mN14u0lNnvXlQb3bpk89v8aWu7CYAgHOfBQA3NZkJ3kcJAR6YZRDicACR+uAlpPiJtdMBpT7NbFG/M5Aliq9aKAWmZwAAFOQvXFfIOSA8BBEA8SZEh1rhmbvX6A4Ps4Mca0CX6YkGs5W6IfXQ/FRKRAB0ewNvDPM/odWtMi4DdcXRgs8EozVUwzRT8J23z9PWTpVu/1Wjf3abEtCZpRlaXpymR5bzlJtIDwF0rWAIyUEkZEKq4wAYSpILgRx3onK8ws5gF59bozZ/hzOTuXQiS1ACoUqpMtBWQY4sQU8NpUDlavvdkWMZDTfO+T4voLjDmwOReTMyA5FGRMrvlwz64OMbtHomT8XH56i4NidTHldERIjR+6BiyHcqtTZLuSXP2R3IsBxEeyoLYwGU42JjcKp/2qrQD7+UaSKr08LcFAvOLJ0rLsrhdPdBk3YrLbkrgLp4BxRF9MLfhLxBUFUSMhZAKWml1lV3I5UldoYGvPn9PbkPgP+gJRxCE3zgmFNgDhTVTWDAkTKQtOUgrTpM7QNDp55jhyPGDGizXqCJsVdoHgUq8RU/CcDd4wwcP9WQXfAeDsEOTFE0n68B6KeJbPpvNOdhAC6xXWB7ke0ltvNspxNWfm+R5LW7x2Mbu0Pfsv3/+ciIORvGdC7949nHCptPFxc2q7XOd1eu/yHBjAMAzn2j7EO1IT2rwDzPdg4M9Dsf/TCsuTAZ2TZ/vMF2jX//lqfgPTTpnfsNng0274c6hTfY1BGyjJ64rAxPUQF5he0FTj+E4CbbVbbrbL+q7UpmCaxAPwQ9wj9hGvwrwADNQplMB/Y+jgAAAABJRU5ErkJggg==",
                "errors" => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTM4IDc5LjE1OTgyNCwgMjAxNi8wOS8xNC0wMTowOTowMSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo2ZDc0YmRlZi1jNDhjLTc2NDQtYjA1My02MTQwOTk0NDcyOTMiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MzEzMTQ3OTUxQzZFMTFFNzhDQTdEOEJDM0NDRTMwQUUiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MzEzMTQ3OTQxQzZFMTFFNzhDQTdEOEJDM0NDRTMwQUUiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTcgKFdpbmRvd3MpIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6NGFkZGUzMjUtODgxMS0wZDRjLWI5NjYtYjYyNTFiMDg3ZjFmIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjZkNzRiZGVmLWM0OGMtNzY0NC1iMDUzLTYxNDA5OTQ0NzI5MyIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/Pt3XMmYAAAW5SURBVHjatFfbbxRVHP7OzOxO99LthZZbgWqLtLYEI4iJCgY18YlofDC+mOib0fjiqy8+GeI/IIkxJr6oD/JEUCNEQiIhQKKgiNKIlNaWtrTd7V7nPv7OmbOXmZ0tbYiz+SUzs2fO+c53fr/vO4f5vo+Z+eLWT05evMUY62UM//tlWq7//luHDxwY33pDk+9KFEWK3s125nn+htvSBCEnWKUo8Js6gBrFCsWezQzOO9y9MwdNVcCZDP8ZflSo7Uq+irWyyR/XogD4dX8zg/uiUyDXrSOZUNsBRC6VGq+VDMhmeclCCMBi9CNHS8BnSkcAHnVqaTr10hkA8z2otiXubcerE7PMP18fAFG2c3Eaum3A7wCAz2qH24MEBxDXil65iSQKQ48QQAbPbbRZqt+0ArjXuoC2lsTr33+K8X9+eaiMXxkew6kTX4vpupSwMgkXGrkRz4BP9Koodvc/dMnVevrhJHVRLa7n1bNzIY6B0BJ4BLWYfXgAZqYbvqrx6beWbCyA5ejHpUxTFjgjp198G4XcABTPlaUFbBvMyjKMS0AX+aFRaLYJkwbnbeQSLMYBWJV6kAo+BsrpnmYdE32/jT2Dv4f3I0kd1pNwbHQAekKD53txNQDVsZFwLTF7jxCwSMm3AliTapiql085nQuldF/xPjTXQcKxGgA0KjEVLpQOZahQez6qS0vAS5XEy5Ki1wagLAVim/iQAFRCAOiP5VkBQjdrEgCQXXGQ1OKWIHhhZnvg6zocAiBeMSH7hTgAbmseMOqxmuqGqyagurZ499rZz3H8/JchlVUUtq5cnvnwJBYnDxEDlbpSFKT3tAEICQRfgpqegaGnkKkGADSinscmzAJWKiMmw5egLg0UVpwOhLKTf8QH9zpI8cZKMCeCUQLzJWAxnhNloKlQxIBD9H937E3kyvkWe/NbJ4gtvWmoKhM54JJ3jF4+hy13b4n/+eztVJp7NjHgx+pNFEBTjmVW/XjkDbiKJgH4ogoglZ9XwfjoYOCG9LOJsYGZqSYAEiEn2RVdgoX1AITQWQkdr577Ao/fDvwgTyL01SsfCGbAXY4AJKtdEgB1ZtSQWW2kEQyqAG5G3A25D4A9GEBofVyS0C2FJTw2fT1YUz2Nb613aWY61XdYeLhtq5SgernYAqAXvhJYNfcBFvGBuCTkGeq0vljt2RoSlZRZRa0rCzOZEgA9CrsrLTRfMw3olSaAWq6ffEANVNBrt+I4BvKyRvvqclzKNv2AK+A733yEP0cOYurRJzC7axypIsPQzSns+OMqdv1+mZ5XQ05Y3zd6gRX7Uc+JAihJSQ4AUPtyRA13Lt4R8dKlU6hkesBohumWQVsvI9cnSHebDFTkJDsugSFNqUH5Su92suW+2AEylbXOg3f3orBjWDhn4AMhv+nIQCgROeVz20bw8XufYc/8FPbduU5xDUNL022KyHMhv2sE8xOHMTf5NJb27keVlkAj3+AawH+UhKuShXUBLEY3lSWy5evjz+HXiaPoog4HV+cxOnsDk1NX0JdSMfPkEdwbPyhmbKWzQvl4RSiOA0alKnZCgREth5SsA4CFti010ahaNVluDAuDuzG7fRQXDx/H2MgAtBSVpcUHtEkXyo0zAzcqhSzTod2wH1MBG2KgfZdDakgD8Y2Gqihit5P07YY0M3rn0YxNy0GFTKxctVCuWOJgQnqwxCJnvzgAM5s5npANiJlalouaEQxYoTDMIPn4LohXQIL2DF16YpoDexCA0xTPUhyjeIHiEEV/+2ZLbiTpuGUYDqo1G5bjNvZ9ijgHslI2lbi2d7jv/MS+wfPLK9WrP1y4/UAA/PB2ScYJuUN6SoI5SjHJK7AuMAtLLWvOmEnI/qLHnyl+ovsrVAH/0ukbd+fWYBBLiaQq2FoPQFxOnJHBr30SyMsUzxP95Ey4SHGW4gLFTbm7EixZtos67SJHIjnwnwADADQtpLEI+Gh8AAAAAElFTkSuQmCC",
            )
        );

        $last_segment = explode('/', $this->request_uri);
        $last_segment = $last_segment[count($last_segment) - 1];

        if (($found_match = strstr($last_segment, '?', true)) !== FALSE) {

            $last_segment = $found_match;

        }

        $favicon_urls = array(
            "favicon.ico",
            "favicon.png",
            "apple-touch-icon-120x120-precomposed.png",
            "apple-touch-icon-precomposed.png",
            "apple-touch-icon.png"
        );

        if (in_array(trim(strtolower($last_segment), '/'), $favicon_urls)) {

            $boom = explode('.', phpversion());
            $version = $boom[0];
            $errors = $this->php_notice + $this->php_warning;

            $icon = explode(',', $favicons['php_' . $version][$errors ? 'errors' : 'logo']);
            $icon = base64_decode($icon[1]);

            header('Content-Type: image/png');
            header('Pragma: no-cache');
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
            header('Content-Length: ' . strlen($icon));
            echo $icon;
            exit;

        }

    }

    /**
     * Output info to Terminal
     * This won't work if it's being loaded through NODE.JS
     */
    function log_output()
    {

        if ($this->log_enable) {

            $host_port = $_SERVER["REMOTE_ADDR"] . ":" . $_SERVER["REMOTE_PORT"];

            $date = new DateTime();

            $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ? "[XHR]" : "";
            $method = isset($_SERVER['REQUEST_METHOD']) ? "[" . strtoupper($_SERVER['REQUEST_METHOD']) . "]" : "";

            $this->console(sprintf("[%s] %s %s%s: %s", $date->format(DateTime::RFC2822), $host_port, $method, $is_ajax, urldecode($this->request_uri)));

        }

    }

    /**
     * Retrieve the mime type of a file
     * @param string $filename
     * @return mixed|string
     */
    private function get_mime_type($filename = "")
    {

        $mime_type_db = $this->retrieve_mime_types();

        if (strlen($filename) == 0) {

            $mime_type = isset($mime_type_db[$this->extension]) ? $mime_type_db[$this->extension] : mime_content_type($this->physical_file);

        } else {

            $extension = strrev(strstr(strrev($filename), '.', TRUE));
            $mime_type = isset($mime_type_db[$extension]) ? $mime_type_db[$extension] : mime_content_type($filename);

        }


        return $mime_type;

    }

    /**
     * Serve CACHED | RAW Files
     */
    function process_request()
    {

        $uri_path = $this->URI_no_query();

        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . urldecode(substr($uri_path, 1)))) {

            $this->favicon();

            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            $this->http_status = 404;

        } else {

            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $this->last_modified) . ' GMT');
            header('ETag: ' . $this->eTag);
            header('Cache-Control: public');

            /**
             * Always set Content-Type and Content-Length
             * Pipes and Proxies will need them
             */
            $mime_type = $this->get_mime_type();
            header('Content-Type: ' . $mime_type);
            header('Content-Length: ' . $this->file_length);

            if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $this->last_modified || $this->eTagHeader == $this->eTag) {

                header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
                $this->http_status = 304;

            } else {

                @readfile($this->physical_file);

                exit;

            }

        }

        exit;

    }

    /**
     * Get Extension
     * @param string $str
     * @return string
     */
    private function getExt($str = "")
    {

        $str = strtolower(trim($str));
        if (($no_q = strstr($str, '?', true)) !== FALSE) {
            $str = $no_q;
        }

        return strstr($str, '.') === FALSE ? "" : strrev(strstr(strrev($str), '.', true));


    }

    /**
     * Format to UNIX path
     * @param string $str
     * @return mixed
     */
    private function format_unix($str = "")
    {
        return preg_replace('([/\\\]+)', '/', $str);
    }

    private function format_path_dir($str = "")
    {

        $str = $this->format_unix(trim($str));

        if (trim($str) == '/' || strlen($str) == 0) {
            return '/';
        }

        if (!strlen($this->getExt($str))) {

            /**
             * A path without extension or with / must be checked if it is a valid directory
             */
            if (is_dir($this->format_unix($_SERVER['DOCUMENT_ROOT'] . '/' . $str . '/')) || is_dir($this->format_unix($str))) {

                $str = $str . '/';

            } else {

                $str = dirname($str) . '/';

            }

        } else {

            $str = dirname($str) . '/';

        }

        $str = $this->format_unix('/' . $str);
        $drf = $this->format_unix(strtolower($_SERVER['DOCUMENT_ROOT']));

        if (DIRECTORY_SEPARATOR != '/' && substr(strtolower($str), 1, strlen($drf)) == $drf) {

            $str = ltrim($str, '/');

        }

        return $str;

    }

    /**
     * Serve your application
     */
    function bootstrap()
    {

        chdir($_SERVER['DOCUMENT_ROOT']);

        $uri_path = $this->URI_no_query();
        $uri_filepath = $_SERVER['DOCUMENT_ROOT'] . '/' . urldecode(substr($uri_path, 1));

        $load_index = $_SERVER['DOCUMENT_ROOT'] . "/" . $this->indexPath;
        $load_index = $this->format_unix(trim($load_index));


        $_SERVER['SCRIPT_NAME'] = $this->format_unix(DIRECTORY_SEPARATOR . $this->indexPath);
        $_SERVER['PHP_SELF'] = $this->format_unix($uri_path);

        $_SERVER['SCRIPT_FILENAME'] = $this->format_unix($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $this->indexPath);

        if (!file_exists($load_index)) {

            $not_found_message = "Your script file doesn't exist at " . $load_index;

            $this->console($not_found_message);
            exit($not_found_message);

        } else {

            if (file_exists($uri_filepath) && !is_dir($uri_filepath)) {

                $this->process_request();

                exit();

            } else {

                $this->favicon();

                if (in_array($this->getExt($this->script_filename), array("", "php"))) {

                    return include($_SERVER['DOCUMENT_ROOT'] . '/' . $this->indexPath);

                } else {

                    return include($this->script_filename);

                }

            }

        }

    }

    /**
     * Remove query from REQUEST_URI if it has one
     * @return string
     */
    private function URI_no_query()
    {
        $filename = $this->request_uri;

        if (($found = strstr($this->request_uri, "?", TRUE)) != FALSE) {

            $filename = $found;

        }

        return $filename;

    }

    /**
     * Retrieve the first encounter of a filename in REQUEST_URI
     * e.g something/edit.php/id?and=query   =   something/edit.php
     */
    private function URI_Filename()
    {

        $uri_split = explode('/', substr($this->URI_no_query(), 1));

        if ($total = count($uri_split)) {

            foreach ($uri_split as $current_key => $segment) {

                if (strstr($segment, '.', TRUE) !== FALSE) {

                    for ($i = $current_key + 1; $i < $total; $i++) {

                        unset($uri_split[$i]);

                    }

                    return implode('/', $uri_split);

                }

            }

        }

        return FALSE;

    }

    /**
     * Check if the requested URI is a PHP script
     * @return bool
     */
    private function URIhasPHP()
    {

        return strrev(strstr(strrev(strtolower($this->URI_Filename())), '.', TRUE)) == 'php' ? TRUE : FALSE;

    }

    public function is_root_script()
    {

        if (
            $this->format_unix($_SERVER['SCRIPT_FILENAME']) === $this->format_unix($_SERVER['DOCUMENT_ROOT'] . '/' . $this->indexPath)
            && $this->getExt($this->indexPath) == 'php'
        ) {

            return TRUE;

        }

        return FALSE;

    }

    private function __is_static_file()
    {

        return $this->getExt(strtolower($_SERVER['SCRIPT_FILENAME'])) != 'php';

    }

    private function __url_add_trailing_slash()
    {
        $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] . '/';
    }

    private function __im_not_a_method_trust_me()
    {
        return substr($_SERVER['REQUEST_URI'], -1, 1) !== '/' && !strlen($this->getExt(trim(urldecode($_SERVER['REQUEST_URI']))));
    }

    private function fix_url_rewrite()
    {

        if ($this->__im_not_a_method_trust_me()) {

            if (
                !isset($_SERVER['PHP_INFO'])
                ||
                $this->__is_static_file()
            ) {

                $this->__url_add_trailing_slash();

                /**
                 * Force redirect on HTML, HTM files
                 */
                if ($this->__is_static_file()) {

                    header("Location: " . $_SERVER['REQUEST_URI']);
                    exit;

                }

            }

        } else {

            /**
             * Make sure we have a Content-Length
             * for static files after redirect.
             */
            if ($this->__is_static_file()) {

                header("Content-Length: " . $this->file_length);

            }

        }

    }

    /**
     *  Adjust some $_SERVER variables
     */
    function fix_path_info()
    {

        $url = $_SERVER['REQUEST_URI'];

        if (($url_no_q = strstr($url, '?', true)) !== FALSE) {
            $url = $url_no_q;
        }

        $path_info = isset($_SERVER['PHP_INFO']) ? $_SERVER['PHP_INFO'] : '/';

        if (($dot = strstr($url, '.')) !== FALSE) {

            if (($ext = strstr($dot, '/', TRUE)) !== FALSE) {

                $explode = explode('/', $dot);
                $path_info = '/' . $explode[1];

            }

        } else {

            if (substr($path_info, -1, 1) != '/') {
                $path_info = $path_info . '/';
            }

        }

        /**
         * Correct HTTP_CACHE_CONTROL
         * Problem:
         * Encountered during development containing a value of "max-age"
         * It seems to be a malformed version of HTTP_CACHE_CONTROL , HTTP_............L
         * It would appear and disappear on random requests switching with HTTP_CACHE_CONTROL,
         * yet both would contain "max-age"
         * Logic: Since both HTTP_CACHE_CONTROL and HTTP_L switch places on random request and HTTP_L is an invalid header
         * we can detect if HTTP_CACHE_CONTROL was about to be created by checking for HTTP_L
         * Solution: Check for HTTP_L and assign the value to HTTP_CACHE_CONTROL
         */
        if (isset($_SERVER['HTTP_L'])) {
            $_SERVER['HTTP_CACHE_CONTROL'] = $_SERVER['HTTP_L'];
            unset($_SERVER['HTTP_L']);
        }


        /**
         * Correct URL's by adding a trailing slash for independent folders
         * These folders don't require $_SERVER['DOCUMENT_ROOT'] .'/'. $this->indexPath
         * and may have their own index file.
         * e.g. : /my-custom-folder -> /my-custom-folder/
         *
         * Problems:
         *      -   incorrect output of relative links
         *      -   incorrect process of certain pages
         * Logic: Check if the processed file is different from $_SERVER['DOCUMENT_ROOT'] .'/'. $this->indexPath to determine if your app is loading or something else.
         * Solution: Append a trailing slash to $_SERVER['REQUEST_URI'] and pass it to the web-server
         */
        if (!$this->is_root_script()) {

            $this->fix_url_rewrite();

            return FALSE;

        }

        /**
         * Keep original PHP_SELF
         * ORIG_PHP_SELF -
         */
        if (!isset($_SERVER['ORIG_PHP_SELF'])) {
            $_SERVER['ORIG_PHP_SELF'] = $_SERVER['PHP_SELF'];
        }
        /**
         * Create ORIG_PATH_INFO variable
         */
        if (!isset($_SERVER['ORIG_PHP_SELF'])) {
            $_SERVER['ORIG_PATH_INFO'] = "";
        }
        $_SERVER['ORIG_PATH_INFO'] = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : "";

        /**
         * Drupal 8 - NPAS:
         *      -   upload files - ok
         *      -   update.php - ok
         *      -   install themes - ok
         * Codeigniter - NPAS - ok
         * Wordpress NPAS - install | custom links | upload | page not found - ok
         */
        if (isset($_SERVER['PHP_INFO'])) {

            $_SERVER['PATH_INFO'] = $path_info;

        } else {

            /**
             * Drupal 7 - default:
             *      -   /user must show /user/ page - fix
             * Slim 3 - will enable if DRUPAL_ROOT is not found in your index.php
             */
            $readFile = fopen($_SERVER['DOCUMENT_ROOT'] . '/' . $this->indexPath, "r");
            $isDrupal = false;
            if ($readFile) {
                while (!feof($readFile) && $isDrupal == false) {
                    $buffer = fread($readFile, 4096);
                    if (strstr($buffer, 'DRUPAL_ROOT') !== FALSE) {
                        $isDrupal = true;
                    }
                }
                fclose($readFile);
            }

            if ($isDrupal) {
                $this->fix_url_rewrite();
            }

        }

        $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'] . (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : "");

        if (substr($_SERVER['PHP_SELF'], -1, 1) == '/') {
            $_SERVER['PHP_SELF'] = substr($_SERVER['PHP_SELF'], 0, -1);
        }

    }


    /**
     * Autodetect index
     */
    function autoDetectIndex()
    {

        $indexRoot = $_SERVER["DOCUMENT_ROOT"] . '/' . $this->indexPath;

        if (!file_exists($indexRoot)) {

            $viableFilesForIndex = array('index.php', 'index.phtml', 'index.html', 'index.htm', 'index.html5', 'index.php5');

            $scanRoot = scandir($_SERVER["DOCUMENT_ROOT"]);
            $scanRoot = array_filter($scanRoot, function ($k) use ($viableFilesForIndex) {
                return in_array(strtolower($k), $viableFilesForIndex);
            });
            $scanRoot = array_values($scanRoot);

            if (count($scanRoot)) {

                $this->indexPath = $scanRoot[0];

            }

        }


    }


    /**
     * Listen for requests
     * @return bool|mixed
     */
    function listen()
    {

        $this->fix_path_info();
        $this->init();
        $this->log_output();

        $falsy_ext = $this->getExt($this->URI_no_query());

        $this->autoDetectIndex();

        if (in_array($falsy_ext, array("", "php"))) {

            /**
             * Drupal file uploads
             */

            if ($this->URIhasPHP()) {

                return FALSE;

            } else {

                /**
                 * Wordpress wp-admin
                 */

                if ($this->getExt($this->URI_no_query()) == "") {

                    return FALSE;

                }

            }

        } else {

            if (strlen(trim($falsy_ext))) {

                if (($e = strstr($falsy_ext, '/', TRUE)) !== FALSE) {
                    $falsy_ext = $e;
                }

                if ($falsy_ext == 'php') {

                    return FALSE;

                }

            }

        }

        return $this->bootstrap();

    }

    /**
     * This is for development purpose
     * You can output to console anything you want
     */
    function console()
    {

        $args = func_get_args();

        if (count($args) > 0) {

            foreach ($args as $arg) {

                ob_start();
                print_r($arg);
                $output = ob_get_contents();
                ob_end_clean();
                file_put_contents("php://stdout", $output . PHP_EOL);

            }

        }

    }

    /**
     * Load mime types
     * @return array|mixed|object
     */
    private function retrieve_mime_types()
    {

        $mimes_file = dirname(__FILE__) . '/mimes.json';

        if (!file_exists($mimes_file)) {

            $this->create_mime_file();

        }

        return json_decode(file_get_contents($mimes_file), true);

    }

    /**
     * Download and create a mimes.json if you don't have one
     */
    private function create_mime_file()
    {

        $s = array();
        foreach (@explode("\n", @file_get_contents("http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types")) as $x) {

            if (isset($x[0]) && $x[0] !== '#' && preg_match_all('#([^\s]+)#', $x, $out) && isset($out[1]) && ($c = count($out[1])) > 1) {
                for ($i = 1; $i < $c; $i++) {
                    $s[] = '&nbsp;&nbsp;&nbsp;\'' . $out[1][$i] . '\' => \'' . $out[1][0] . '\'';
                }
            }
        }

        $tmp_arr = array();

        foreach ($s as $k => $v) {

            $uri_path = explode('=>', $v);
            $new_key = trim(preg_replace('/\s+/', '', str_replace(array("   '", "'", " ", "	", "   ", '&nbsp;'), "", $uri_path[0])));
            $new_val = trim(str_replace(array("   '", "'"), "", $uri_path[1]));

            $tmp_arr[$new_key] = $new_val;

        }
        ksort($tmp_arr);

        fwrite(fopen(dirname(__FILE__) . '/mimes.json', 'w+'), json_encode($tmp_arr));

    }

}
