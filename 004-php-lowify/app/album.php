<?php

require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

function formatDuree(int $secondes): string {
    $minutes = intdiv($secondes, 60);
    $restant = $secondes % 60;
    return sprintf('%02d:%02d', $minutes, $restant);
}

try {
    $db = new DatabaseManager(
        dsn: 'mysql:host=mysql;dbname=lowify;charset=utf8mb4',
        username: 'lowify',
        password: 'lowifypassword'
    );
} catch (PDOException $e) {
    header("Location: error.php?message=" . urlencode("Erreur de connexion à la base de données."));
    exit;
}

$idAlbum = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($idAlbum <= 0) {
    header("Location: error.php?message=" . urlencode("Album introuvable."));
    exit;
}

try {
    $album = $db->executeQuery("SELECT * FROM album WHERE id = $idAlbum LIMIT 1")[0] ?? null;
    if (!$album) {
        header("Location: error.php?message=" . urlencode("Album introuvable."));
        exit;
    }

    $artiste = $db->executeQuery("SELECT id, name FROM artist WHERE id = {$album['artist_id']} LIMIT 1")[0] ?? null;
    $idArtiste = $artiste ? (int)$artiste['id'] : 0;
    $nomArtiste = $artiste ? htmlspecialchars($artiste['name']) : "Inconnu";

    $titres = $db->executeQuery("SELECT id, name, duration, note FROM song WHERE album_id = $idAlbum ORDER BY id ASC");

} catch (PDOException $e) {
    header("Location: error.php?message=" . urlencode("Erreur lors de la récupération des données."));
    exit;
}

$titresHtml = '';
foreach ($titres as $titre) {
    $nomTitre = htmlspecialchars($titre['name']);
    $duree = formatDuree((int)$titre['duration']);
    $note = htmlspecialchars((string)$titre['note']);
    $titresHtml .= <<<HTML
<div class="titre-item">
    <div class="titre-nom">{$nomTitre}</div>
    <div class="titre-details">Durée: {$duree} • Note: {$note}</div>
</div>
HTML;
}

$contenuPage = <<<HTML
<style>
@import url('https://fonts.googleapis.com/css2?family=Fredoka+One&display=swap');

body {
    font-family: 'Fredoka One', cursive;
    background: #0a0f1a;
    color:#eee;
    margin:0; padding:0;
}

a { text-decoration:none; color:inherit; }

.container {
    max-width:1200px;
    margin:auto;
    padding:40px 20px;
}

a.retour {
    display:inline-block;
    margin-bottom:25px;
    color:#66d0ff;
    font-weight:bold;
}
a.retour:hover { text-decoration:underline; }

.album-header {
    display:flex;
    gap:25px;
    margin-bottom:50px;
    align-items:center;
    flex-wrap:wrap;
}

.album-header img {
    width:180px;
    height:180px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid rgba(102,208,255,0.3);
    transition: transform 0.3s, box-shadow 0.3s;
}

.album-header img:hover {
    transform: scale(1.05) rotate(2deg);
    box-shadow: 0 0 20px rgba(102,208,255,0.5);
}

.album-header h1 {
    margin:0;
    color:#66d0ff;
    text-shadow: 1px 1px 5px rgba(0,0,0,0.5);
}

.album-header .artiste {
    color:#a0cfff;
    margin:6px 0 12px 0;
}

.top-titres .titre-item {
    display:flex;
    flex-direction:column;
    margin-bottom:12px;
    background: linear-gradient(120deg, #0a0f1a, #112233);
    padding:10px;
    border-radius:10px;
    transition: transform 0.3s, box-shadow 0.3s;
}

.top-titres .titre-item:hover {
    transform: translateY(-5px) scale(1.03);
    box-shadow: 0 0 15px rgba(102,208,255,0.5);
}

.titre-nom { font-weight:bold; color:#66d0ff; font-size:1.1em; }
.titre-details { font-size:0.9em; color:#a0cfff; margin-top:4px; }

</style>

<div class="container">
<a href="artist.php?id={$idArtiste}" class="retour">&lt; Retour à l'artiste</a>

<div class="album-header">
    <img src="{$album['cover']}" alt="{$album['name']}">
    <div>
        <h1>{$album['name']}</h1>
        <div class="artiste">Artiste: <a href="artist.php?id={$idArtiste}">{$nomArtiste}</a></div>
        <div>Date de sortie: {$album['release_date']}</div>
    </div>
</div>

<section>
<h2>Titres</h2>
<div class="top-titres">{$titresHtml}</div>
</section>
</div>
HTML;

$page = new HTMLPage(htmlspecialchars($album['name']) . " - Lowify");
$page->addContent($contenuPage);
echo $page->render();
