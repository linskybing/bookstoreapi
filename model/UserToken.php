<?php

namespace Model;

class UserToken
{

    public $table = 'UserToken';

    public $Id;
    public $Account;
    public $Token;
    public $LastAccessAt;
    public $CreatedAt;
    public $UpdatedAt;
    public $ExpiredAt;
}
