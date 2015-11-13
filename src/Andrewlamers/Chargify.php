<?php
namespace Andrewlamers\Chargify;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Stream\Stream;
use Illuminate\Support\Collection;
use Illuminate\Support\Pluralizer;
use Illuminate\Support\Str;

class Chargify
{
    private $http;

    protected $_password = 'x';
    protected $_apiKey;
    protected $_sharedKey;
    protected $_hostname;
    protected $_config = [];
    protected $_call_url;
    protected $_format = 'json';
    protected $_params = [];
    protected $_api_name = false;
    protected $_collection_name;
    protected $_class;
    protected $_segments = [];

    public function __construct($config)
    {
        $this->_config = $config;
        $this->_hostname = $config['hostname'];
        $this->_apiKey = $config['api_key'];
        $this->_sharedKey = $config['shared_key'];

        $this->http = new Client([
            'base_url' => 'https://' . $this->_hostname,
            'defaults' => [
                'timeout'         => 10,
                'allow_redirects' => FALSE,
                'auth'            => [$this->_apiKey, $this->_password],
                'headers'         => [
                    'Content-Type' => 'application/json'
                ]
            ]
        ]);
    }

    public function __call($name, $arguments)
    {
        $this->setApiName($name);

        $this->_segments[] = $name;

        if(isset($arguments[0]))
            $this->_segments[] = $arguments[0];

        return $this;
    }

    public function __get($name)
    {

        $this->setApiName($name);

        $this->_segments[] = $name;

        return $this;
    }

    public function get()
    {
        return $this->request('GET');
    }

    public function create($data = null)
    {
        return $this->request('POST', [], $data);
    }

    public function update($data)
    {
        return $this->request('PUT', [], $data);
    }

    public function delete()
    {
        return $this->request('DELETE');
    }

    public function reset()
    {
        $this->_call_url = '';
    }

    public function setParam($name, $value)
    {
        $this->_params[$name] = $value;

        return $this;
    }

    public function getParam($name)
    {
        return $this->_params[$name];
    }

    public function unsetParam($name)
    {
        unset($this->_params[$name]);

        return $this;
    }

    public function setApiName($name)
    {
        $this->_api_name = Pluralizer::singular($name);
        $this->_collection_name = Str::studly(Str::singular(Str::slug($this->_api_name, " ")));
        eval("namespace Andrewlamers\\Chargify; class ".$this->_collection_name." extends \\Illuminate\\Support\\Fluent {}");
        $this->_collection_name = "Andrewlamers\\Chargify\\".$this->_collection_name;
    }

    protected function parseResponse($response)
    {
        $return = [];

        $body = trim((string)$response->getBody());

        try {
            $body = json_decode($body);
        }
        catch(\Exception $e)
        {
            $body = false;
        }

        if(isset($body->errors))
        {
            return $body;
        }
        else if(isset($body->{$this->_api_name}))
        {
            return new $this->_collection_name((object)$body->{$this->_api_name});
        }
        else if(is_array($body))
        {
            foreach($body as $key => $item)
            {
                if($item->{$this->_api_name})
                {
                    $return[] = new $this->_collection_name((object)$item->{$this->_api_name});
                }
                else
                {
                    $return[] = new $this->_collection_name((object)$item);
                }
            }
        }
        else
        {
            return ['errors' => [$response->getReasonPhrase()],
                    'code' => $response->getStatusCode(),
                    'request_url' => $response->getEffectiveUrl()];
        }

        return new Collection($return);
    }

    protected function request($method, $params = [], $body = null)
    {
        $path = implode("/", $this->_segments). "." . $this->_format;
        $params = array_merge($params, $this->_params);

        $request = $this->http->createRequest($method, $path);

        if ($body)
        {
            $request->setBody($this->prepareBody($body));
        }

        if (count($params) > 0)
            $request->setQuery($params);

        try
        {
            $response = $this->http->send($request);
        } catch (RequestException $e)
        {
            if($e->hasResponse())
            {
                $response = $e->getResponse();
            }
            else
            {
                $response = false;
            }
        }

        $this->reset();

        return $this->parseResponse($response);
    }

    protected function prepareBody($body)
    {
        $return = [];
        if(!isset($body->{$this->_api_name}))
        {
            $return[$this->_api_name] = $body;
        }

        return Stream::factory(json_encode($return));
    }

    public function validateWebhook()
    {
        try
        {
            return $_SERVER['HTTP_X-Chargify-Webhook-Signature-Hmac-Sha-256'] === hash_hmac('sha256', file_get_contents('php://input'), $this->_sharedKey);
        }
        catch (\Exception $e)
        {
            return FALSE;
        }
    }
}