<?php

define('PAGE_TITLE', 'Nom de famille');
require '../inc/header.inc.php';

echo '<h1>Ajout d\'un nom de famille</h1>
<p>Cet outil génère le code <a href="https://tools.wmflabs.org/wikidata-todo/quick_statements.php">QuickStatements</a> pour ajouter rapidement un nom de famille à des éléments Wikidata n\'ayant pas la propriété <a href="https://www.wikidata.org/wiki/Property:P734">P734</a> renseignée.</p>
<form method="post" action="nom-de-famille.php">
<p><label for="id">Identifiant Wikidata d\'un nom de famille</label> (exemple : Q23777432 pour <em>Binet</em>) :<br /><input type="text" id="id" name="id" value="'.htmlentities(page::getParameter('id')).'" /></p>
<p><label for="countries">Identifiants Wikidata d\'un ou plusieurs pays</label> (exemple : Q142 pour la France) :<br /><input type="text" id="countries" name="countries" value="'.htmlentities(page::getParameter('countries', 'Q142')).'" /></p>
<p><input type="submit" value="Lister" /></p>
</form>';

$id = null;
if (!empty($_POST['id']) && preg_match('/^Q[0-9]+$/', $_POST['id'])) {
    $id = $_POST['id'];
}

$countries = array();
if (!empty($_POST['countries'])) {
    preg_match_all('/Q[1-9][0-9]*/', $_POST['countries'], $matches);
    $countries = array_unique($matches[0]);
}
if (count($countries) === 0) {
    $countries = array('Q142');
}

if ($id != null) {
    
    // get lastname string
    
    $query = '
SELECT ?lastnameLabel WHERE {
    wd:'.$id.' rdfs:label ?lastnameLabel .
    FILTER (LANG(?lastnameLabel) = "fr")
}
';
    
    $items = sparql::query($query);
    
    if ((count($items->results->bindings) !== 1) || empty($items->results->bindings[0]->lastnameLabel->value)) {
        throw new Exception('Impossible de récupérer le libellé en français de '.$id.'.');
    }
    
    $lastname = $items->results->bindings[0]->lastnameLabel->value;
    if (!preg_match('/[a-zA-ZàâäéèêëîïôöûüÿÀÂÄÉÈÊËÎÏÔÖÛÜŸœŒ\']+/', $lastname)) {
        throw new Exception('Le libellé du nom de famille contient des caractères spéciaux non valides.');
    }
    
    // get persons
    
    $countriesConditions = array();
    foreach ($countries as $country) {
        $countriesConditions[] = '?nation = wd:'.$country;
    }

    $query = '
SELECT ?person
(GROUP_CONCAT(DISTINCT ?personLabel ; separator = ",") AS ?personLabel)
(GROUP_CONCAT(DISTINCT ?birthname ; separator = ",") AS ?birthname)
(GROUP_CONCAT(DISTINCT ?pseudo ; separator = ",") AS ?pseudo)
(GROUP_CONCAT(DISTINCT ?alias ; separator = ",") AS ?alias)
WHERE {
    ?person wdt:P31 wd:Q5 .
    ?person wdt:P27 ?nation .
    FILTER ('.implode(' || ', $countriesConditions).')
    FILTER NOT EXISTS { ?person wdt:P734 ?anything }
    ?person rdfs:label ?personLabel
    FILTER (LANG(?personLabel) = "fr" && STRENDS(?personLabel, " '.$lastname.'"))
    OPTIONAL { ?person wdt:P1477 ?birthname . }
    OPTIONAL { ?person wdt:P742 ?pseudo . }
    OPTIONAL { ?person wdt:P1449 ?alias . }
}
GROUP BY ?person
ORDER BY ?personLabel
# '.time().'
';

    $items = sparql::query($query);
    
    echo '<h2>Résultats [<a href="homonymie.php?id='.$id.'&amp;fallback='.urlencode(LANG_FALLBACK).'">Homonymie</a>, <a href="?id='.urlencode(page::getParameter('id')).'&amp;countries='.urlencode(page::getParameter('countries', 'Q142')).'">Permalien</a>]</h2>';
    if (count($items->results->bindings) === 0) {
        echo '<p>Aucun résultat.</p>';
    }
    else {
        echo '<table id="results"><tr><th>?</th><th>Identifiant</th><th>Libellé</th><th>Autres libellés</th></tr>';
        foreach ($items->results->bindings as $item) {
            $itemId = substr($item->person->value, 32);
            $aliases = array();
            if (!empty($item->birthname->value)) {
                $aliases = array_merge($aliases, explode(',', $item->birthname->value));
            }
            if (!empty($item->pseudo->value)) {
                $aliases = array_merge($aliases, explode(',', $item->pseudo->value));
            }
            if (!empty($item->alias->value)) {
                $aliases = array_merge($aliases, explode(',', $item->alias->value));
            }
            $aliases = array_unique($aliases);
            $aliases = array_diff($aliases, array($item->personLabel->value));
            sort($aliases);
            echo '<tr><td><input type="checkbox" checked="checked" id="person_'.$itemId.'" /></td><td><a href="'.$item->person->value.'">Q'.$itemId.'</a></td><td><label for="person_'.$itemId.'">'.htmlentities($item->personLabel->value).'</label></td><td>'.htmlentities(implode(', ', $aliases)).'</td></tr>';
        }
        echo '</table><p><input type="button" value="Générer" onclick="generate(\''.$id.'\');" /></p><div id="generated"></div>';
    }
    
}

require '../inc/footer.inc.php';

?>