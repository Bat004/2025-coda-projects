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

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
if (!$query) {
    header("Location: index.php");
    exit;
}
$likeQuery = '%' . $query . '%';

try {
    $artistes = $db->executeQuery(
        "SELECT id, name, cover, monthly_listeners 
         FROM artist 
         WHERE name LIKE :search
         ORDER BY monthly_listeners DESC",
        ['search' => $likeQuery]
    );

    $albums = $db->executeQuery(
        "SELECT a.id, a.name, a.cover, a.release_date, ar.id AS artist_id, ar.name AS artist_name
         FROM album a
         LEFT JOIN artist ar ON a.artist_id = ar.id
         WHERE a.name LIKE :search
         ORDER BY a.release_date DESC",
        ['search' => $likeQuery]
    );

    $songs = $db->executeQuery(
        "SELECT s.id, s.name, s.duration, s.note, a.id AS album_id, a.name AS album_name,
                ar.id AS artist_id, ar.name AS artist_name
         FROM song s
         LEFT JOIN album a ON s.album_id = a.id
         LEFT JOIN artist ar ON a.artist_id = ar.id
         WHERE s.name LIKE :search
         ORDER BY s.note DESC",
        ['search' => $likeQuery]
    );
} catch (PDOException $e) {
    echo "Erreur requête : " . htmlspecialchars($e->getMessage());
    exit;
}

$artistesHtml = '';
if ($artistes) {
    $artistesHtml .= '<div class="cards-grid">';
    foreach ($artistes as $artiste) {
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
    $artistesHtml .= '</div>';
} else {
    $artistesHtml = '<p>Aucun artiste trouvé.</p>';
}

$albumsHtml = '';
if ($albums) {
    $albumsHtml .= '<div class="cards-grid">';
    foreach ($albums as $album) {
        $id = (int)$album['id'];
        $nom = htmlspecialchars($album['name']);
        $cover = htmlspecialchars($album['cover'] ?? '');
        $annee = $album['release_date'] ? date('Y', strtotime($album['release_date'])) : '';
        $artistNom = htmlspecialchars($album['artist_name'] ?? 'Inconnu');
        $artistId = (int)$album['artist_id'];
        $albumsHtml .= <<<HTML
<div class="card">
    <a href="album.php?id={$id}">
        <div class="card-img" style="background-image:url('{$cover}')"></div>
        <div class="card-title">{$nom}</div>
        <div class="card-sub">{$artistNom} • {$annee}</div>
    </a>
</div>
HTML;
    }
    $albumsHtml .= '</div>';
} else {
    $albumsHtml = '<p>Aucun album trouvé.</p>';
}

$songsHtml = '';
if ($songs) {
    $songsHtml .= '<div class="songs-list">';
    foreach ($songs as $song) {
        $nom = htmlspecialchars($song['name']);
        $duree = formatTemps((int)$song['duration']);
        $note = htmlspecialchars((string)$song['note']);
        $albumNom = htmlspecialchars($song['album_name'] ?? 'Inconnu');
        $albumId = (int)$song['album_id'];
        $artistNom = htmlspecialchars($song['artist_name'] ?? 'Inconnu');
        $artistId = (int)$song['artist_id'];
        $songsHtml .= <<<HTML
<div class="song-item">
    <a href="album.php?id={$albumId}">
        <div><strong>{$nom}</strong> — {$duree} — Note: {$note}</div>
        <div>{$albumNom} • {$artistNom}</div>
    </a>
</div>
HTML;
    }
    $songsHtml .= '</div>';
} else {
    $songsHtml = '<p>Aucune chanson trouvée.</p>';
}
$contenuPage = <<<HTML
<style>
@import url('https://fonts.googleapis.com/css2?family=Fredoka+One&display=swap');

body { font-family:'Fredoka One', cursive; background:#0a0f1a; color:#eee; margin:0; padding:0; }
a { text-decoration:none; color:inherit; }

.container { max-width:1200px; margin:auto; padding:40px 20px; text-align:center; }

.header-top { display:flex; justify-content: space-between; align-items: center; margin-bottom:40px; }
.header-top h1 { color:#66d0ff; font-size:2.8em; margin:0; text-shadow: 0 2px 15px rgba(0,0,0,0.5); }

form.search-bar { display:flex; justify-content:center; margin-bottom:50px; }
form.search-bar input[type=text] { padding:12px 15px; font-size:1em; border:none; border-radius:8px 0 0 8px; width:300px; }
form.search-bar button { padding:12px 20px; font-size:1em; border:none; border-radius:0 8px 8px 0; background:#66d0ff; color:#0a0f1a; cursor:pointer; font-weight:bold; }
form.search-bar button:hover { background:#4aa0dd; }

.section { margin-bottom:50px; display:flex; flex-direction:column; align-items:center; text-align:left; }

h2 { color:#66d0ff; margin-bottom:20px; font-size:1.8em; }

.cards-grid { display:flex; flex-wrap:wrap; justify-content:center; gap:25px; }
.card { width:180px; text-align:center; transition: transform 0.3s, box-shadow 0.3s; cursor:pointer; }
.card:hover { transform: translateY(-5px) scale(1.05); box-shadow: 0 0 20px rgba(102,208,255,0.5); }
.card-img { width:180px; height:180px; border-radius:15px; background-size:cover; background-position:center; margin-bottom:10px; }
.card-title { font-weight:bold; color:#66d0ff; font-size:1.1em; }
.card-sub { font-size:0.9em; color:#a0cfff; }

.songs-list { display:flex; flex-direction:column; gap:12px; }
.song-item { padding:10px; border-radius:10px; background:linear-gradient(120deg, #0a0f1a, #112233); }
.song-item:hover { transform: translateY(-3px); box-shadow: 0 0 10px rgba(102,208,255,0.5); }
.song-item div { margin:2px 0; }
</style>

<div class="container">
<div class="header-top">
    <h1>Résultats pour "{$query}"</h1>
    <a href="index.php" class="artistes-btn">Accueil</a>
</div>

<section class="section">
<h2>Artistes</h2>
{$artistesHtml}
</section>

<section class="section">
<h2>Albums</h2>
{$albumsHtml}
</section>

<section class="section">
<h2>Chansons</h2>
{$songsHtml}
</section>
</div>
HTML;

$page = new HTMLPage("Lowify - Recherche");
$page->addContent($contenuPage);
echo $page->render();
