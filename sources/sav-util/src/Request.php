<?php
namespace SavUtil;

class Request
{
    public function __construct()
    {
        $curl = curl_version();
        $this->version = $curl['version_number'];
        $this->handle = curl_init();
        $this->stream_handle = null;
        $this->done_headers = false;
        curl_setopt($this->handle, CURLOPT_HEADER, false);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, 1);
        if ($this->version >= 0x070A05) {
            curl_setopt($this->handle, CURLOPT_ENCODING, '');
        }
        $protocols = CURLPROTO_HTTP | CURLPROTO_HTTPS;
        curl_setopt($this->handle, CURLOPT_PROTOCOLS, $protocols);
        curl_setopt($this->handle, CURLOPT_REDIR_PROTOCOLS, $protocols);
    }
    public function __destruct()
    {
        if (is_resource($this->handle)) {
            curl_close($this->handle);
        }
    }
    public static function fetch($opts)
    {
        $req = new static();
        return $req->request($opts);
    }
    public static function fetchAll($requests, $options = array())
    {
        $req = new static();
        return $req->requests($requests, $options);
    }
    public function request($options = array())
    {
        $this->setupHandle($options);
        curl_exec($this->handle);
        if (curl_errno($this->handle) === 23 || curl_errno($this->handle) === 61) {
            curl_setopt($this->handle, CURLOPT_ENCODING, 'none');
            $this->response_data = '';
            curl_exec($this->handle);
        }
        curl_setopt($this->handle, CURLOPT_HEADERFUNCTION, null);
        curl_setopt($this->handle, CURLOPT_WRITEFUNCTION, null);
        return $this->processResponse();
    }
    public function requests($requests, $opts = array())
    {
        if (empty($requests)) {
            return array();
        }
        $multihandle = curl_multi_init();
        $subrequests = array();
        $subhandles = array();
        $class = get_class($this);
        foreach ($requests as $id => $request) {
            $options = &$request['options'];
            is_array($options) || ($options = array());
            $options = array_merge($opts, $options);
            $subrequests[$id] = new $class();
            $subhandles[$id] = $subrequests[$id]->setupHandle($request);
            curl_multi_add_handle($multihandle, $subhandles[$id]);
        }
        $completed = 0;
        $responses = array();
        do {
            $active = false;
            do {
                $status = curl_multi_exec($multihandle, $active);
            } while ($status === CURLM_CALL_MULTI_PERFORM);
            $to_process = array();
            while ($done = curl_multi_info_read($multihandle)) {
                $key = array_search($done['handle'], $subhandles, true);
                if (!isset($to_process[$key])) {
                    $to_process[$key] = $done;
                }
            }
            foreach ($to_process as $key => $done) {
                if (CURLE_OK !== $done['result']) {
                    $error = sprintf(
                        'cURL error %s: %s',
                        curl_errno($done['result']),
                        curl_error($done['handle'])
                    );
                    $responses[$key] = new \Exception($error);
                } else {
                    $responses[$key] = $subrequests[$key]->processResponse();
                }
                curl_multi_remove_handle($multihandle, $done['handle']);
                curl_close($done['handle']);
                $completed++;
            }
        } while ($active || $completed < count($subrequests));
        curl_multi_close($multihandle);
        return $responses;
    }
    protected function setupHandle($opts)
    {
        if (is_string($opts)) {
            $opts = array('url' => $opts);
        }
        $url = $opts['url'];
        $headers = &$opts['headers'];
        $data = &$opts['data'];
        $options = &$opts['options'];
        is_array($headers) || ($headers = array());
        is_array($options) || ($options = array());
        $options = $this->options = array_merge(self::getDefaultOptions(), $options);
        $headers = self::flatten($headers);
        if (!isset($headers['connection'])) {
            $headers['connection'] = 'close';
        }
        $type = $options['type'];
        $buildUrl = in_array($type, array('HEAD', 'GET', 'DELETE'));
        if (!empty($data)) {
            if ($buildUrl) {
                $url = self::buildUri($url, $data);
                $data = '';
            } elseif (!is_string($data)) {
                $data = http_build_query($data, null, '&');
            }
        }
        switch ($type) {
            case 'POST':
                curl_setopt($this->handle, CURLOPT_POST, true);
                curl_setopt($this->handle, CURLOPT_POSTFIELDS, $data);
                break;
            case 'HEAD':
                curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $options['type']);
                curl_setopt($this->handle, CURLOPT_NOBODY, true);
                break;
            case 'TRACE':
                curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $options['type']);
                break;
            case 'PATCH':
            case 'PUT':
            case 'DELETE':
            case 'OPTIONS':
            default:
                curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $options['type']);
                if (!empty($data)) {
                    curl_setopt($this->handle, CURLOPT_POSTFIELDS, $data);
                }
        }
        $timeout = max($options['timeout'], 1);
        if (is_int($timeout) || $this->version < 0x071002) {
            curl_setopt($this->handle, CURLOPT_TIMEOUT, ceil($timeout));
        } else {
            curl_setopt($this->handle, CURLOPT_TIMEOUT_MS, round($timeout * 1000));
        }
        if (is_int($options['connect_timeout']) || $this->version < 0x071002) {
            curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT, ceil($options['connect_timeout']));
        } else {
            curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT_MS, round($options['connect_timeout'] * 1000));
        }
        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_REFERER, $url);
        curl_setopt($this->handle, CURLOPT_USERAGENT, $options['useragent']);
        if (!empty($headers)) {
            curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($this->handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($this->handle, CURLOPT_HEADERFUNCTION, array(&$this, 'streamHeaders'));
        curl_setopt($this->handle, CURLOPT_WRITEFUNCTION, array(&$this, 'streamBody'));
        curl_setopt($this->handle, CURLOPT_BUFFERSIZE, 1160);
        if (isset($options['verify'])) {
            if ($options['verify'] === false) {
                curl_setopt($this->handle, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, 0);
            } elseif (is_string($options['verify'])) {
                curl_setopt($this->handle, CURLOPT_CAINFO, $options['verify']);
            }
        }
        if (isset($options['verifyname']) && $options['verifyname'] === false) {
            curl_setopt($this->handle, CURLOPT_SSL_VERIFYHOST, 0);
        }
        if ($options['filename']) {
            $this->stream_handle = fopen($options['filename'], 'wb');
        }
        $this->response_data = '';
        $this->response_headers = '';
        return $this->handle;
    }
    public function processResponse()
    {
        if ($this->options['filename']) {
            fclose($this->stream_handle);
            $this->stream_handle = null;
        }
        if (curl_errno($this->handle)) {
            $error = sprintf(
                'cURL error %s: %s',
                curl_errno($this->handle),
                curl_error($this->handle)
            );
            throw new \Exception($error);
        }
        $headers = str_replace("\r\n", "\n", trim($this->response_headers));
        $headers = preg_replace('/\n[ \t]/', ' ', $headers);
        $headers = explode("\n", $headers);
        preg_match('#^HTTP/(1\.\d)[ \t]+(\d+)#i', array_shift($headers), $matches);
        if (empty($matches)) {
            throw new Exception('Response could not be parsed: '. curl_getopt($this->handle, CURLOPT_URL));
        }
        $ret = new \stdClass();
        $ret->status = (int) $matches[2];
        $ret->headers = array();
        foreach ($headers as $header) {
            list($key, $value) = explode(':', $header, 2);
            $value = trim($value);
            preg_replace('#(\s+)#i', ' ', $value);
            $ret->headers[$key] = $value;
        }
        $ret->response = $this->response_data;
        if ($this->options['dataType'] === 'json') {
            $ret->response = json_decode(
                trim($this->response_data),
                $this->options['assoc']
            );
        }
        return $ret;
    }
    public function streamHeaders($handle, $headers)
    {
        if ($this->done_headers) {
            $this->response_headers = '';
            $this->done_headers = false;
        }
        $this->response_headers .= $headers;
        if ($headers === "\r\n") {
            $this->done_headers = true;
        }
        return strlen($headers);
    }
    public function streamBody($handle, $data)
    {
        $data_length = strlen($data);
        if ($this->stream_handle) {
            fwrite($this->stream_handle, $data);
        } else {
            $this->response_data .= $data;
        }
        return $data_length;
    }
    public static function buildUri($url, $data)
    {
        if (!empty($data)) {
            $url_parts = parse_url($url);
            if (empty($url_parts['query'])) {
                $query = $url_parts['query'] = '';
            } else {
                $query = $url_parts['query'];
            }
            $query .= '&' . http_build_query($data, null, '&');
            $query = trim($query, '&');

            if (empty($url_parts['query'])) {
                $url .= '?' . $query;
            } else {
                $url = str_replace($url_parts['query'], $query, $url);
            }
        }
        return $url;
    }
    public static function flatten($array)
    {
        $return = array();
        foreach ($array as $key => $value) {
            $return[] = sprintf('%s: %s', $key, $value);
        }
        return $return;
    }
    public static function test($ssl = false)
    {
        if (!function_exists('curl_init') || !function_exists('curl_exec')) {
            return false;
        }
        if ($ssl) {
            $version = curl_version();
            if (!(CURL_VERSION_SSL & $version['features'])) {
                return false;
            }
        }
        return true;
    }
    public static function getDefaultOptions()
    {
        return array(
            'timeout' => 10,
            'connect_timeout' => 10,
            'useragent' => 'curl',
            'type' => 'GET',
            'dataType' => '',
            'assoc' => true,
            'filename' => false,
            'verify' => false,
            'verifyname' => false,
        );
    }
}
