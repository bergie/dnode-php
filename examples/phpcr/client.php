<?php

include 'RemoteSessionClient.php';

$crSession = new RemoteSessionClient(6060);
$value = $crSession->getPropertyValue("/jcr:primaryType");
var_dump ($value);

?>
