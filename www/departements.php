<?php

define('PAGE_TITLE', 'Statistiques par département');
require '../inc/header.inc.php';

echo '<h1>Statistiques par département</h1>
<p>Le tableau ci-dessous donne, par département de naissance, le nombre de personnes ayant les propriétés <em>nom de famille</em> (<a href="https://www.wikidata.org/wiki/Property:P734">P734</a>) et <em>prénom</em> (<a href="https://www.wikidata.org/wiki/Property:P735">P735</a>) renseignées dans <a href="https://www.wikidata.org/">Wikidata</a>.</p>';

$departements = name::getStats();

echo '<p>Données du '.date('d/m/Y à H:i:s', sparql::getQueryTime(sparql::getQueries()[0])).'.</p>';

echo '<table class="stats"><tr><th class="label">Département</th><th>Total de personnes</th><th>Avec nom de famille</th><th>Taux (%)</th><th class="ratio"></th><th>Avec prénom</th><th>Taux (%)</th></tr>';
$total_total = 0;
$total_lastname = 0;
$total_firstname = 0;
foreach ($departements as $insee => &$value) {
    $total_total += $value['total'];
    $total_lastname += $value['lastname'];
    $total_firstname += $value['firstname'];
    $ratio_lastname = ($value['total'] >= 1) ? 100 / $value['total'] * $value['lastname'] : null;
    $ratio_firstname = ($value['total'] >= 1) ? 100 / $value['total'] * $value['firstname'] : null;
    echo '<tr><td class="label"><strong>'.htmlentities($insee).'</strong> <a href="suggestions.php?id='.htmlentities($value['qid']).'">'.htmlentities($value['label']).'</a></td><td>'.htmlentities(display::formatInt($value['total'])).'</td><td>'.htmlentities(display::formatInt($value['lastname'])).'</td><td>'.(($value['total'] >= 1) ? number_format($ratio_lastname, 1, ',', ' ') : '').'</td><td class="ratio"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFBQIAX8jx0gAAAABJRU5ErkJggg==" style="height: 12px; width:'.round($ratio_lastname * 2).'px;" /></td><td>'.htmlentities(display::formatInt($value['firstname'])).'</td><td>'.(($value['total'] >= 1) ? number_format($ratio_firstname, 1, ',', ' ') : '').'</td></tr>'."\n";
}
echo '<tr><td class="label"><strong>Total</strong></td><td><strong>'.display::formatInt($total_total).'</strong></td><td><strong>'.display::formatInt($total_lastname).'</strong></td><td><strong>'.number_format(100 / $total_total * $total_lastname, 1, ',', ' ').'</strong></td><td></td><td><strong>'.display::formatInt($total_firstname).'</strong></td><td><strong>'.number_format(100 / $total_total * $total_firstname, 1, ',', ' ').'</strong></td></tr></table>';

require '../inc/footer.inc.php';

?>