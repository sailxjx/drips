<?php

$config['socket'] = array(
    'address' => '127.0.0.1',
    'port' => '5677',
    'protocol' => SOL_TCP,
    'domain' => AF_INET,
    'type' => SOCK_STREAM
);

$config['dict'] = array(
    './dict/multipinyin.dic'
);

$config['logPath'] = './log/';

$config['memcache'] = array(
    'host' => '127.0.0.1',
    'port' => 11211,
    'persistent' => true
);