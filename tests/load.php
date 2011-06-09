<?php
require '../controllers/components/raven.php';

$docs = new RavenDocumentOperation('http://localhost:8080', 'albums');
print_r($docs->load('626')->to_array());
?>

