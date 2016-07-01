<?php

define('PAGE_TITLE', 'Suggestion de noms de famille');
require '../inc/header.inc.php';

echo '<h1>Suggestion de noms de famille</h1>
<p>Cet outil suggère des noms de famille manquants dans Wikidata, en filtrant sur un lieu de naissance.</p>
<form method="post" action="nom-de-famille-suggestion.php">
<p><label for="id">Identifiant Wikidata d\'un lieu</label> (exemples : Q12193 pour Brest, Q3389 pour le Finistère ou Q142 pour la France) :<br /><input type="text" id="id" name="id" value="'.htmlentities(page::getParameter('id')).'" /></p>
<p><input type="submit" value="Lister" /></p>
</form>';

$id = null;
if (!empty($_POST['id']) && preg_match('/^Q[0-9]+$/', $_POST['id'])) {
    $id = $_POST['id'];
}

if ($id != null) {
    
    // get lastname string
    
    $query = '
SELECT ?person ?personLabel WHERE {
    ?person wdt:P31 wd:Q5 ; wdt:P19/wdt:P131* wd:'.$id.' .
    FILTER NOT EXISTS { ?person wdt:P734 ?anything . }
    ?person rdfs:label ?personLabel .
    FILTER (LANG(?personLabel) = "fr")
}
LIMIT 1000
';
    
    $items = sparql::query($query);
    
    echo '<h2>Résultats [<a href="?id='.htmlentities(page::getParameter('id')).'">Permalien</a>]</h2>';
    if (count($items->results->bindings) === 0) {
        echo '<p>Aucun résultat.</p>';
    }
    else {
        echo '<p><strong>'.count($items->results->bindings).'</strong> personnes sans nom de famille et ayant pour lieu de naissance <a href="http://www.wikidata.org/entity/'.$id.'">'.$id.'</a> :</p>';
        $names = array();
        $examples = array();
        foreach ($items->results->bindings as $item) {
            $name = end(explode(' ', $item->personLabel->value));
            $example = '<a href="'.$item->person->value.'">'.htmlentities($item->personLabel->value).'</a>';
            if (!isset($names[$name])) {
                $names[$name] = 1;
                $examples[$name] = array($example);
            } else {
                $names[$name] += 1;
                $examples[$name][] = $example;
            }
        }
        arsort($names);
        echo '<ul>';
        foreach ($names as $name => $count) {
            echo '<li><strong>'.htmlentities($name).'</strong> ('.$count.') : '.implode(', ', $examples[$name]).'</li>';
        }
        echo '</ul>';
    }
    
}

require '../inc/footer.inc.php';

?>