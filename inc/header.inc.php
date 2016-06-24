<?php

array_walk_recursive($_GET, function (&$val) { $val = trim($val); });
array_walk_recursive($_POST, function (&$val) { $val = trim($val); });

require '../conf/conf.inc.php';

function __autoload($className) {
	include '../inc/lib/'.$className.'.class.php';
}

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php if (!empty(PAGE_TITLE)): echo htmlentities(PAGE_TITLE); endif; ?></title>
    <link rel="stylesheet" type="text/css" href="static/style.css" />
    <script type="text/javascript" src="static/script.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>
<body>
<div id="menu">
    <p>
        <strong>
            <a href=".">Documentation</a>
            | <a href="nom-de-famille-suggestion.php">Suggestion</a>
            | <a href="nom-de-famille.php">Nom de famille</a>
            | <a href="homonymie.php">Homonymie</a>
            | <a href="departements.php">Statistiques</a>
        </strong>
    </p>
    <p class="license">Outils Dicare par <a href="https://www.wikidata.org/wiki/User:Envlh">User:Envlh</a> | <a href="http://www.dicare.org/">dicare.org</a></p>
</div>