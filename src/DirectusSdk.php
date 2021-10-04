<?php
namespace Bayard\DirectusSdk;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Curl\Curl;
use JetBrains\PhpStorm\Pure;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;


class DirectusSdk{

    /**
     * @var string
     */
    private string $baseUrl;
    const AUTH_AUTHENTICATE_ENDPOINT = '/auth/login';
    const AUTH_REFRESH_ENDPOINT = '/auth/refresh';
    const ITEMS_MANY_ENDPOINT = '/items/:collection';
    const ITEMS_ONE_ENDPOINT = '/items/:collection/:id';
    private string $email;
    private string $password;
    private CurlRemote $remote;

    /**
     * DirectusSdk constructor.
     * @param string $baseUrl
     * @param string $email
     * @param string $password
     */
    public function __construct(string $baseUrl ,string $email ,string $password )
    {
        $this->email=$email;
        $this->password=$password;
        $this->baseUrl=$baseUrl;


    }

   public function get (string $collection , array $query=[])
{
    $token=$this->getToken();
    $url= sprintf('%s/%s', $this->getBaseUrl() . '/items' ,$collection);
    $remote=new CurlRemote();
    $curl=$remote->getCurl();
    $curl->setOpts('UTF-8');
    if ($query)
    {
        $query['access_token']=$token;
        $query=$this->buildquery($query);

    }
    else
    $query['access_token']=$token;

    $curl->get($url,$query);

    if ($curl->error) {
        echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
    } 
    else {
        echo 'Response:' . "\n";
        return $curl->response;
    }
}
 private function buildquery(array $query): array
{   $_query=[];
    foreach ($query as $key =>$value)
       switch ($key)
       {
           case 'ofsset':
           case 'meta':
           case 'limit':
           case 'access_token':
           $_query[$key]=$value;
               break;
           default:
               $ch=html_entity_decode('filter[' . $key . '][_eq]');
               $_query[$ch]=$value;
               break;

       }
       return $_query;
}

        private function getBaseUrl(): string
        {
            return $this->baseUrl;
        }

    public function post (string $collection ,  $data="")
    {
        $token=$this->getToken();
        $url= sprintf('%s/%s', $this->getBaseUrl() . '/items' ,$collection);
        $remote = new CurlRemote();
        $curl=$remote->getCurl();
        $curl->setHeaders(array('Content-Type: application/json','Authorization: Bearer ' . $token));
        $curl->post($url ,$data);
        return $curl->response;

    }

   private function getToken ()
    {
        $url= $this->getBaseUrl() . self::AUTH_AUTHENTICATE_ENDPOINT;

        $pool = new ArrayCachePool();

            if ($pool->hasItem("CMS_TOKEN")) {
                return $pool->get("CMS_TOKEN");
            }


        $remote= new CurlRemote();
        $curl=$remote->getCurl();

        $data=[	"email"=> $this->email, "password"=> $this->password];
    
        $curl->setHeaders(array('Content-Type: application/json'));
        $curl->post($url,$data);

        if ($curl->error) {

           echo new \InvalidArgumentException ('Error: ' . $curl->errorCode . ': ' .  $curl->getRawResponse() . "\n");
        }
        else {
            $result= $curl->response;
            try {
                $pool->set("CMS_TOKEN", $result->data->access_token, $result->data->expires);

                return $pool->getItem("CMS_TOKEN")->get() ;

            } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
                echo $e->getMessage();
            }

        }
    }
    public function getOneById( string $collection,string $id )
    {
        $uri= sprintf('%s/%s',$collection  ,$id);

        return $this->get($uri);
    }
    public function deleteItem( string $collection,string $id )
    {
        $token=$this->getToken();
        $url= sprintf('%s/%s',$this->getBaseUrl() . '/items/' . $collection  ,$id);
        $remote= new CurlRemote();
        $curl=$remote->getCurl();
        $curl->setHeaders(array('Content-Type: application/json','Authorization: Bearer ' . $token));
        return  $curl->delete($url);
    }

    public function deleteItems( string $collection ,$data )
    {   $token=$this->getToken();
        $url= sprintf('%s/%s', $this->getBaseUrl() . '/items/' ,$collection);
        $remote= new CurlRemote();
        $curl=$remote->getCurl();

        $curl->setHeaders(array('Content-Type: application/json','Authorization: Bearer ' . $token));

        return $curl->delete($url ,$data);
    }

}

