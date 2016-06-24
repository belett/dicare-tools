<?php

$queries = sparql::getQueries();
if (count($queries) >= 1) {
    echo '<h2>Requête'.((count($queries) >= 2) ? 's' : '').' SPARQL</h2>';
    $i = 0;
    foreach ($queries as $query) {
        echo '<h3>#'.(++$i).'</h3><pre>'.htmlentities(trim($query)).'</pre>';
    }
}

?>
</body>
</html>
