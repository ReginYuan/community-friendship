<?php

namespace app\middleware;

class Cross
{
    public function handle($request, \Closure $next)
    {
        header("Access-Control-Allow-Origin:*");
        header('Access-Control-Max-Age: 3600');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
        header('Access-Control-Allow-Headers: Token, Authorization, Content-Type,Content-Length, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With,Origin,accept-language,accept-encoding,referer,content-type,user-agent,accept,content-length,connection,host,Content-Disposition');
        $response =  $next($request);
        return $response;
    }
}
