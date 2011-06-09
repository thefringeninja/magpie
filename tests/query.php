<?php
require '../controllers/components/raven.php';
$query = new RavenQueryOperation("http://localhost:8080", "dynamic/Albums");
$query->where('Genre.Name:Classical')->skip(25)->take(25);

$result = $query->to_array();
print_r($result);
?>