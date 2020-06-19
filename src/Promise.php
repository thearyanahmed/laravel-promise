<?php

namespace Prophecy\Promise;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

trait Promise
{
    /**
     * @var string for all api responses
     * depending the $success being true or false,
     * the response will contain 'status' => 'success/error'
     */
    private static $SUCCESS = 'SUCCESS';
    private static $ERROR   = 'ERROR';

    /**
     * @var string $DEFAULT_KEY is the default value for
     * collection items or model instance/s
     * If none is provided, the this value will be set as key
     * response example:
     * 'data' => [...array]
     */
    private static $DEFAULT_KEY = 'data';

    /**
     * @var int Response HTTP status code
     */
    private $httpStatusCode = 200;

    /**
     * @var array Optional headers for the response
     */
    private $headers = [];

    /**
     * @var int $options
     */
    private $options = 0;

    /**
     * @var string $message
     */
    private $message;

    /**
     * @var string $details an optional value
     * The purpose of this value is to tell the client end
     * a bit more about the description.
     */
	private $details;

    /**
     * @var string $key value will be sent as the key of the 'content'
     */
    private $key;

    /**
     * @var bool $success determines if the 'status' will be is "SUCCESS" or "ERROR" of the response
     */
	private $success = true;

    /**
     * @var Collection|LengthAwarePaginator|Model|array|string is the main response,the meat
     */
    private $content;


    /**
     * Sets headers
     *
     * @param string|array $first Can be both Array or String. It the $first key is array, the
     * second key will be ignored. Else the second key will be used to build an associative array.
     * @param string|null $second
     * @param bool $reset will be used to remove the previously set headers.Which were set on
     * runtime. The default value of reset is false, in this mode, the coming headers will be
     * appended to the existing headers
     * @return $this (builder) instance
     */
    public function headers($first,string $second = null,bool $reset = false)
    {
        $headers = $first;

        if (!is_array($headers)) {
            $headers = [$first,$second];
        }

        if($reset) {
            $this->headers = $headers;
        }
        else {
            $this->headers[] = $headers;
        }

        return $this;
    }

    /**
     * @param string $message
     * @return $this (builder) instance
     */
    public function message(string $message)
	{
		$this->message = $message;

		return $this;
	}

    /**
     * @param string $details
     * @return $this (builder) instance
     */
	public function details(string $details = null)
	{
		$this->details = $details;
		return $this;
	}

    /**
     * @param int $code
     * @return $this (builder) instance
     */
	public function code(int $code = null)
    {
        $this->httpStatusCode = $code;
        return $this;
    }

    /**
     * @param int $options
     * @return $this (builder) instance
     */
    public function options(int $options)
    {
        $this->options = $options;
        return $this;
    }

	public function send()
    {
        $content = $this->build();
        return response()->json($content,$this->httpStatusCode,$this->headers,$this->options);
    }

    public function content($content = null,string $key = null)
    {
        $this->content = $content;
        if ( $key) {
            $this->key = $key;
        }
        return $this;
    }

    /**
     * @param null $content
     * @param string|null $key
     * @return Promise
     */
    public function response($content = null,string $key = null)
    {
        return $this->content($content,$key);
    }


    public function key(string $key)
    {
        $this->key = $key;
        return $this;
    }

    public function status($status)
    {
        $this->success = $status;
        return $this;
    }

    private function build()
    {
        $content = $this->content;

        $meta = [
            'code'    => $this->httpStatusCode,
            'status'  => $this->success ? self::$SUCCESS : self::$ERROR
        ];

        if ($this->message) {
            $meta['message'] = $this->message;
        }

        if ($this->details) {
            $meta['details'] = $this->details;
        }

        $key = $this->key ?: self::$DEFAULT_KEY;

        if ( ! $content) {
            return $meta;
        }

        if(is_array($content)) {
            $meta[$key] = $content;
            return $meta;
        }

        if ($content instanceof Model) {
            if( ! $this->key) {
                $key = $this->getModelClassName($content);
            }
            $meta[$key] = $content;
            return $meta;
        }


        $collection = collect($meta);

        if($content instanceof LengthAwarePaginator) {
            $collection = $collection->merge($content);
            return $collection;
        }

        if( $content instanceof Collection) {
            if(! $this->key) {
                $key = $this->resolveClassName($content);
            }
            $collection = $collection->put($key, $content);
            return $collection;
        }

        $meta[$key] = $this->content;

        return $meta;
    }

    private function getModelClassName($content)
    {
        $contentClassName = explode("\\",get_class($content));
        return strtolower(preg_replace('/\B([A-Z])/', '_$1', end($contentClassName)));
    }

    private function resolveClassName($content)
    {
        if(isset($content[0])) {
            return Str::plural( $this->getModelClassName($content[0]));
        }
        return $this->key ?: self::$DEFAULT_KEY;
    }
}
