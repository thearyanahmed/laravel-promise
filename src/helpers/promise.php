<?php

if(!function_exists('res')) {
    function res($content = null,string $key = null) {
        $res = app( Prophecy\Promise::class);

        if($content) {
            $res = $res->content($content,$key);
        }
        return $res;
    }
}

if(! function_exists('success_response')) {
    function success_response(string $message = 'Request successful.',$content = null,int $code =
    200) {
        return res()->message($message)->status(true)->content($content)->code($code)->send();
    }
}

if(! function_exists('resource_created_response')) {
    function resource_created_response(
        string $message = 'Resource created successfully.',
        $content = null
    ) {
        return res()->message($message)->status(true)->content($content)->code(201)->send();
    }
}

if(! function_exists('error_response')) {
    function error_response(string $message = 'Request failed.',$content = null,int $code =
    422) {
        return res()->message($message)->status(false)->content($content)->code($code)->send();
    }
}

if(! function_exists('unauthorized_response')) {
    function unauthorized_response(string $message = 'Unauthorized.',$content = null) {
        return res()->message($message)->status(false)->content($content)->code(401)->send();
    }
}

if(! function_exists('not_found_response')) {
    function not_found_response(string $message = 'Data not found.',$content = null) {
        return res()->message($message)->status(false)->content($content)->code(404)->send();
    }
}
