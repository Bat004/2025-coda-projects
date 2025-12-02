<?php
require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

function formatAuditeurs(int $nb): string {
    if ($nb >= 1000000) return round($nb / 1000000, 1) . 'M';
    if ($nb >= 1000) return round($nb / 1000, 1) . 'k';
    return (string)$nb;
}

function formatTemps(int $secondes): string {
    $minutes = intdiv($secondes, 60);
    $secondesRestantes = $secondes % 60;
    return sprintf('%02d:%02d', $minutes, $secondesRestantes);
}

try {
    $db = new DatabaseManager(
        dsn: 'mysql:host=mysql;dbname=lowify;charset=utf8mb4',
        username: 'lowify',
        password: 'lowifypassword'
    );
} catch (PDOException $e) {
    echo "Erreur DB : " . htmlspecialchars($e->getMessage());
    exit;
}

$idArtiste = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($idArtiste <=0) { echo "Artiste introuvable"; exit; }

try {
    $infosArtiste = $db->executeQuery("SELECT * FROM artist WHERE id=$idArtiste LIMIT 1")[0] ?? null;
    if (!$infosArtiste) exit("Artiste introuvable");

    $meilleursTitres = $db->executeQuery("
        SELECT s.id, s.name AS nomTitre, s.duration, s.note,
               a.cover AS coverAlbum, a.name AS nomAlbum
        FROM song s
        LEFT JOIN album a ON s.album_id = a.id
        WHERE s.artist_id=$idArtiste
        ORDER BY s.note DESC
        LIMIT 5
    ");

    $albums = $db->executeQuery("SELECT * FROM album WHERE artist_id=$idArtiste ORDER BY release_date DESC");

} catch (PDOException $e) {
    echo "Erreur requête : " . htmlspecialchars($e->getMessage());
    exit;
}

$nomArtiste = htmlspecialchars($infosArtiste['name']);
$coverArtiste = htmlspecialchars($infosArtiste['cover'] ?? '');
$auditeurs = formatAuditeurs((int)$infosArtiste['monthly_listeners']);
$bio = nl2br(htmlspecialchars($infosArtiste['biography'] ?? ''));

$topTitresHtml = '';
foreach ($meilleursTitres as $titre) {
    $nomTitre = htmlspecialchars($titre['nomTitre']);
    $duree = formatTemps((int)$titre['duration']);
    $note = htmlspecialchars((string)$titre['note']);
    $cover = htmlspecialchars($titre['coverAlbum'] ?? '');
    $nomAlbum = htmlspecialchars($titre['nomAlbum'] ?? '');

    $topTitresHtml .= <<<HTML
<div class="titre-item">
    <div class="titre-img" style="background-image:url('{$cover}')"></div>
    <div class="titre-info">
        <div class="titre-nom">{$nomTitre}</div>
        <div class="titre-details">Album: {$nomAlbum} • Durée: {$duree}</div>
    </div>
    <div class="titre-note">{$note}</div>
</div>
HTML;
}

$albumsHtml = '';
foreach ($albums as $album) {
    $idAlbum = $album['id'];
    $nom = htmlspecialchars($album['name']);
    $cover = htmlspecialchars($album['cover'] ?? '');
    $annee = $album['release_date'] ? date('Y', strtotime($album['release_date'])) : '';
    $albumsHtml .= <<<HTML
<div class="album-card">
    <a href="album.php?id={$idAlbum}">
        <div class="album-img" style="background-image:url('{$cover}')"></div>
        <div class="album-nom">{$nom}</div>
        <div class="album-annee">{$annee}</div>
    </a>
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

.artiste-header {
    display:flex;
    gap:25px;
    margin-bottom:50px;
    align-items:center;
    flex-wrap:wrap;
}

.artiste-header img {
    width:180px;
    height:180px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid rgba(102,208,255,0.3);
    transition: transform 0.3s, box-shadow 0.3s;
}

.artiste-header img:hover {
    transform: scale(1.05) rotate(2deg);
    box-shadow: 0 0 20px rgba(102,208,255,0.5);
}

.artiste-header h1 {
    margin:0;
    color:#66d0ff;
    text-shadow: 1px 1px 5px rgba(0,0,0,0.5);
}

.artiste-header .auditeurs {
    color:#a0cfff;
    margin:6px 0 12px 0;
}

.top-titres .titre-item {
    display:flex;
    align-items:center;
    margin-bottom:12px;
    background: linear-gradient(120deg, #0a0f1a, #112233);
    padding:10px;
    border-radius:10px;
    transition: transform 0.3s, box-shadow 0.3s;
}

.titre-item:hover {
    transform: translateY(-5px) scale(1.03);
    box-shadow: 0 0 15px rgba(102,208,255,0.5);
}

.titre-img {
    width:60px;
    height:60px;
    border-radius:8px;
    background-size:cover;
    background-position:center;
    margin-right:12px;
}

.titre-info { flex-grow:1; }

.titre-nom { font-weight:bold; color:#66d0ff; }

.titre-details { font-size:0.9em; color:#a0cfff; }

.titre-note { font-weight:bold; margin-left:12px; color:#00ffff; }

.albums-grid {
    display:flex;
    flex-wrap:wrap;
    gap:20px;
    justify-content:flex-start;
}

.album-card {
    width:160px;
    text-align:center;
    transition: transform 0.3s, box-shadow 0.3s;
}

.album-card:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 0 15px rgba(102,208,255,0.5);
}

.album-img {
    width:160px;
    height:160px;
    border-radius:12px;
    background-size:cover;
    background-position:center;
    margin-bottom:8px;
}

.album-nom {
    font-weight:bold;
    color:#66d0ff;
}

.album-annee {
    font-size:0.9em;
    color:#a0cfff;
}

.album-card a:hover .album-nom { text-decoration:underline; }

</style>

<div class="container">
<a href="artists.php" class="retour">&lt; Retour aux artistes</a>

<div class="artiste-header">
    <img src="{$coverArtiste}" alt="{$nomArtiste}">
    <div>
        <h1>{$nomArtiste}</h1>
        <div class="auditeurs">{$auditeurs} auditeurs mensuels</div>
        <div>{$bio}</div>
    </div>
</div>

<section style="margin-bottom:30px;">
<h2>Top titres</h2>
<div class="top-titres">{$topTitresHtml}</div>
</section>

<section>
<h2>Albums</h2>
<div class="albums-grid">{$albumsHtml}</div>
</section>
</div>
HTML;

$page = new HTMLPage("$nomArtiste - Lowify");
$page->addContent($contenuPage);
echo $page->render();
