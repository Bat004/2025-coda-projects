<?php
require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

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

try {
    $topArtistes = $db->executeQuery("SELECT id, name, cover, monthly_listeners FROM artist ORDER BY monthly_listeners DESC LIMIT 5");
    $topSorties = $db->executeQuery("SELECT id, name, cover, release_date FROM album ORDER BY release_date DESC LIMIT 5");
    $topAlbums = $db->executeQuery("
        SELECT a.id, a.name, a.cover, a.release_date, AVG(s.note) AS note_moyenne
        FROM album a
        LEFT JOIN song s ON s.album_id = a.id
        GROUP BY a.id
        ORDER BY note_moyenne DESC
        LIMIT 5
    ");
} catch (PDOException $e) {
    echo "Erreur requête : " . htmlspecialchars($e->getMessage());
    exit;
}

function formatAuditeurs(int $nb): string {
    if ($nb >= 1000000) return round($nb / 1000000, 1) . 'M';
    if ($nb >= 1000) return round($nb / 1000, 1) . 'k';
    return (string)$nb;
}

$artistesHtml = '';
foreach ($topArtistes as $artiste) {
    $id = (int)$artiste['id'];
    $nom = htmlspecialchars($artiste['name']);
    $cover = htmlspecialchars($artiste['cover'] ?? '');
    $auditeurs = formatAuditeurs((int)$artiste['monthly_listeners']);
    $artistesHtml .= <<<HTML
<div class="card">
    <a href="artist.php?id={$id}">
        <div class="card-img" style="background-image:url('{$cover}')"></div>
        <div class="card-title">{$nom}</div>
        <div class="card-sub">{$auditeurs} auditeurs</div>
    </a>
</div>
HTML;
}

$sortiesHtml = '';
foreach ($topSorties as $album) {
    $id = (int)$album['id'];
    $nom = htmlspecialchars($album['name']);
    $cover = htmlspecialchars($album['cover'] ?? '');
    $annee = $album['release_date'] ? date('Y', strtotime($album['release_date'])) : '';
    $sortiesHtml .= <<<HTML
<div class="card">
    <a href="album.php?id={$id}">
        <div class="card-img" style="background-image:url('{$cover}')"></div>
        <div class="card-title">{$nom}</div>
        <div class="card-sub">{$annee}</div>
    </a>
</div>
HTML;
}

$albumsHtml = '';
foreach ($topAlbums as $album) {
    $id = (int)$album['id'];
    $nom = htmlspecialchars($album['name']);
    $cover = htmlspecialchars($album['cover'] ?? '');
    $annee = $album['release_date'] ? date('Y', strtotime($album['release_date'])) : '';
    $note = round((float)$album['note_moyenne'], 1);
    $albumsHtml .= <<<HTML
<div class="card">
    <a href="album.php?id={$id}">
        <div class="card-img" style="background-image:url('{$cover}')"></div>
        <div class="card-title">{$nom}</div>
        <div class="card-sub">Note: {$note} • {$annee}</div>
    </a>
</div>
HTML;
}

$contenuPage = <<<HTML
<style>
@import url('https://fonts.googleapis.com/css2?family=Fredoka+One&display=swap');

body {
    font-family:'Fredoka One', cursive;
    background:#0a0f1a;
    color:#eee;
    margin:0;
    padding:0;
}
a { text-decoration:none; color:inherit; }

.container { max-width:1200px; margin:auto; padding:40px 20px; text-align:center; }

.header-top {
    display:flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom:40px;
}

.header-top h1 { color:#66d0ff; font-size:2.8em; margin:0; text-shadow: 0 2px 15px rgba(0,0,0,0.5); }

.header-top a.artistes-btn {
    padding:10px 20px;
    background:#66d0ff;
    color:#0a0f1a;
    font-weight:bold;
    border-radius:10px;
    transition: background 0.3s, transform 0.3s;
}
.header-top a.artistes-btn:hover {
    background:#4aa0dd;
    transform: translateY(-3px);
}

form.search-bar { display:flex; justify-content:center; margin-bottom:50px; }
form.search-bar input[type=text] { padding:12px 15px; font-size:1em; border:none; border-radius:8px 0 0 8px; width:300px; }
form.search-bar button { padding:12px 20px; font-size:1em; border:none; border-radius:0 8px 8px 0; background:#66d0ff; color:#0a0f1a; cursor:pointer; font-weight:bold; }
form.search-bar button:hover { background:#4aa0dd; }

.section { margin-bottom:50px; display:flex; flex-direction:column; align-items:center; }

.cards-grid {
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    gap:25px;
}

.card { width:180px; text-align:center; transition: transform 0.3s, box-shadow 0.3s; cursor:pointer; }
.card:hover { transform: translateY(-5px) scale(1.05); box-shadow: 0 0 20px rgba(102,208,255,0.5); }
.card-img { width:180px; height:180px; border-radius:15px; background-size:cover; background-position:center; margin-bottom:10px; }
.card-title { font-weight:bold; color:#66d0ff; font-size:1.1em; }
.card-sub { font-size:0.9em; color:#a0cfff; }
</style>

<div class="container">

<div class="header-top">
    <h1>Lowify</h1>
    <a href="artists.php" class="artistes-btn">Artistes</a>
</div>

<form class="search-bar" action="search.php" method="get">
    <input type="text" name="query" placeholder="Rechercher un artiste, album ou titre...">
    <button type="submit">Rechercher</button>
</form>

<section class="section">
<h2>Top trending</h2>
<div class="cards-grid">{$artistesHtml}</div>
</section>

<section class="section">
<h2>Top sorties</h2>
<div class="cards-grid">{$sortiesHtml}</div>
</section>

<section class="section">
<h2>Top albums</h2>
<div class="cards-grid">{$albumsHtml}</div>
</section>
</div>
HTML;

$page = new HTMLPage("Lowify - Accueil");
$page->addContent($contenuPage);
echo $page->render();
