<?php

class name {
    
    public static function getStats($cache = 86400) {
        
        $departements = array();
        
        $query = 'SELECT ?departement ?insee ?label
WHERE {
    ?departement wdt:P2586 ?insee .
    ?departement rdfs:label ?label .
    FILTER (LANG(?label) = "fr")}
ORDER BY ?insee';
        $items = sparql::query($query, $cache);
        foreach ($items->results->bindings as $item) {
            $departements[$item->insee->value]['qid'] = substr($item->departement->value, 31);
            $departements[$item->insee->value]['label'] = $item->label->value;
        }
        
        foreach ($departements as $insee => &$value) {
            
            // total
            $query = 'SELECT (COUNT(*) AS ?count)
WHERE {
    ?item wdt:P31 wd:Q5 .
    ?item wdt:P19/wdt:P131* wd:'.$value['qid'].' .
}';
            $items = sparql::query($query, $cache);
            $value['total'] = $items->results->bindings[0]->count->value;
            
            // lastname
            $query = 'SELECT (COUNT(*) AS ?count)
WHERE {
    ?item wdt:P31 wd:Q5 .
    ?item wdt:P734 ?anything .
    ?item wdt:P19/wdt:P131* wd:'.$value['qid'].' .
}';
            $items = sparql::query($query, $cache);
            $value['lastname'] = $items->results->bindings[0]->count->value;
            
            // firstname
            $query = 'SELECT (COUNT(*) AS ?count)
WHERE {
    ?item wdt:P31 wd:Q5 .
    ?item wdt:P735 ?anything .
    ?item wdt:P19/wdt:P131* wd:'.$value['qid'].' .
}';
            $items = sparql::query($query, $cache);
            $value['firstname'] = $items->results->bindings[0]->count->value;
            
        }
        
        return $departements;
        
    }
    
}

?>