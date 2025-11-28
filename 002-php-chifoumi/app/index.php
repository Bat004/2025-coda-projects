<?php

$userChoice = $_GET["choice"] ?? "pas choisi";

$possibleChoices = ["pierre", "feuille", "ciseaux"];

$phpChoice = "pas choisi";

if ($userChoice !== "pas choisi") {
    $phpChoice = $possibleChoices[array_rand($possibleChoices)];
}
if ($userChoice === "pas choisi") {
    $result = "Faites un choix pour commencer la partie.";
} elseif ($userChoice === $phpChoice) {
    $result = "Égalité.";
} elseif (
    ($userChoice === 'pierre' && $phpChoice === 'ciseaux') ||
    ($userChoice === 'feuille' && $phpChoice === 'pierre') ||
    ($userChoice === 'ciseaux' && $phpChoice === 'feuille')
) {
    $result = 'Vous avez gagné !';
} else {
    $result = "PHP a gagné.";
}


$page = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Chifumi</title>
</head>

<body>

<h1>Jeu Pierre - Feuille - Ciseaux</h1>

<p><strong>Votre choix :</strong> $userChoice</p>
<p><strong>Choix du bot :</strong> $phpChoice</p>

<h2>Résultat : $result</h2>

<hr>

<p>Faites un choix :</p>
<a href="?choice=pierre">Pierre</a><br>
<a href="?choice=feuille">Feuille</a><br>
<a href="?choice=ciseaux">Ciseaux</a><br><br>

<a href="?">Réinitialiser</a>

</body>
</html>
HTML;

echo $page;
