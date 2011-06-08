<?php
class RavenComponent {
    var $server = "http://localhost:8080";

    function  __construct() {
        
    }

    function query($index = 'dynamic') {
        return new RavenQueryOperation($this->server, $index);
    }

    function docs($entity) {
        return new RavenDocumentLoadOperation($this->server, $entity);
    }
}

abstract class RavenOperation {
    function to_array() {
        return json_decode($this->to_json(), true);
    }
    
    function to_json() {
        $handle = curl_init();
        curl_setopt_array($handle, $this->get_curl_options());
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($handle);
        $result = $this->transform_result($result);
        return $result;
    }
    
    abstract protected function get_curl_options();

    protected function transform_result($result) {
        return $result;
    }
}

class RavenDocumentOperation extends RavenOperation {
    private $url;
    private $id;
    private $etag;

    function  __construct($server, $entity) {
        $this->url = $server . '/docs/' . $entity;
    }

    function load($id, $etag = null) {
        $this->id = $id;
        $this->etag = $etag;
        return $this;
    }

    function get_curl_options() {
        $headers = array();
        if (isset($this->etag)) {
            $headers['If-None-Match: ' . $this->etag];
        }
        return array(
                CURLOPT_URL => $this->url . '/' . $this->id,
                CURLOPT_HEADER => $headers
            );
    }
}

class RavenQueryOperation extends RavenOperation {
    private $query = array('query' => '', 'start' => 0, 'pageSize' => 25);
    private $url;
    function  __construct($server, $index = 'dynamic', $returns = 'json') {
        $this->url = $server . '/indexes/' . $index;
        $this->returns = $returns;
    }

    function where($lucene_query) {
        $this->query['query'] = $lucene_query;
        return $this;
    }

    function order_by($sort) {
        $this->query['sort'] = $sort;
        return $this;
    }
    function take($pageSize) {
        $this->query['pageSize'] = $pageSize;
        return $this;
    }
    function skip($start) {
        $this->query['start'] = $start;
        return $this;
    }

    function  get_curl_options() {
        return array(
                CURLOPT_URL => $this->url
                    . '?'
                    . http_build_query($this->query)
            );
    }

    function  transform_result($result) {
        $result = substr($result, 3); // not sure what these 3 garbage characters are. figure it out later
        $result = str_replace('":NaN', '":null', $result); // is newtonsoft.json doing this or raven? figure it out later
        return $result;
    }
}
?>
