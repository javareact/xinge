<?php

namespace Javareact\Xinge\Entity;


class TagTokenPair
{
    public $tag;
    public $token;

    /**
     * TagTokenPair constructor.
     * @param $tag
     * @param $token
     */
    public function __construct($tag, $token)
    {
        $this->tag = strval($tag);
        $this->token = strval($token);
    }

    public function __destruct()
    {
    }


}