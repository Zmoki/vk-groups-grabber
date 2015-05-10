<?php

class Vkapi{

    const API_URL = 'http://api.vk.com/method';

    const API_VER = '3.0';

    protected $api_secret;

    protected $app_id;

    public function __construct($app_id = null, $api_secret = null){
        $this->app_id = $app_id;
        $this->api_secret = $api_secret;
    }

    protected function getSignature(array $params){
        $sig = '';
        foreach($params as $k => $v){
            $sig .= $k . '=' . $v;
        }
        $sig .= $this->api_secret;

        return md5($sig);
    }

    protected function query($method, array $params){
        $query = static::API_URL . '/' . $method . '?' . http_build_query($params);
        $res = file_get_contents($query);

        return json_decode($res, true);
    }

    protected function populateParams(array &$params){
//        $params['api_id'] = $this->app_id;
//        $params['v'] = !empty($params['v']) ? $params['v'] : static::API_VER;
//        $params['format'] = 'json';

        ksort($params);
//        $params['sig'] = $this->getSignature($params);
    }

    public function api($method, array $params){
        $this->populateParams($params);

        return $this->query($method, $params);
    }
}