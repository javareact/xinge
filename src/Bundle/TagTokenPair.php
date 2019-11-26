<?php


namespace Javareact\Xinge\Bundle;


class TagTokenPair
{
    public $tag;
    public $token;

    public function __construct($tag, $token)
    {
        $this->tag   = strval($tag);
        $this->token = strval($token);
    }

    public function __destruct()
    {
    }


}