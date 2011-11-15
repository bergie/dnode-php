<?php

include 'RemoteSessionServer.php';

$credentials = new \PHPCR\SimpleCredentials("admin", "password");
$params = array (
    'midgard2.configuration.file' => getenv('MIDGARD_ENV_GLOBAL_SHAREDIR') . "/midgard2.conf"
);
$repository = Midgard\PHPCR\RepositoryFactory::getRepository($params);

$server = new RemoteSessionServer($repository, $credentials);
$server->listen(6060);


