<?php


namespace Bayard\DirectusSdk;


use Cache\Adapter\PHPArray\ArrayCachePool;
use Curl\Curl;

class CurlRemote
{
    private  Curl $curl;

    /**
     * CurlRemote constructor.
     */
    public function __construct()
    {
        $this->curl = new Curl();
    }

    function get(string $url, array $query = [])
    {


        $this->curl->get($url, $query);

        if ($this->curl->error) {
            throw new \InvalidArgumentException ('Error: ' . $this->curl->errorCode . ': ' . $this->curl->getErrorMessage() . "\n");
        } else {
            echo 'Response:' . "\n";
            return $this->curl->response;
        }
    }
    function delete(string $url, array $query = [])
    {


        $this->curl->delete($url, $query);

        if ($this->curl->error) {
            throw new \InvalidArgumentException ('Error: ' . $this->curl->errorCode . ': ' . $this->curl->getErrorMessage() . "\n");
        } else {
            echo 'Response:' . "\n";
            return $this->curl->response;
        }
    }




    public function post(string $url, $data = "")
    {
            $this->curl->post($url, $data);

        if ($this->curl->error) {
             echo new \InvalidArgumentException ('Error: ' . $this->curl->errorCode . ': ' . $this->curl->getErrorMessage() . "\n");
        } else {

            return $this->curl->response;
        }
    }

    /**
     * @return Curl
     */
    public function getCurl(): Curl
    {
        return $this->curl;
    }



}


