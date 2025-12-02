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
    $artistes = $db->executeQuery("SELECT id, name, cover FROM artist ORDER BY name ASC");
} catch (PDOException $e) {
    echo "Erreur requête : " . htmlspecialchars($e->getMessage());
    exit;
}

$artistesHtml = '';
if ($artistes) {
    $artistesHtml .= '<div class="artistes-gallery">';
    foreach ($artistes as $artiste) {
        $id = (int)$artiste['id'];
        $nom = htmlspecialchars($artiste['name']);
        $cover = htmlspecialchars($artiste['cover'] ?? '');

        $artistesHtml .= <<<HTML
<div class="artiste-item">
    <a href="artist.php?id={$id}">
        <div class="artiste-img" style="background-image:url('{$cover}')"></div>
        <div class="artiste-nom">{$nom}</div>
    </a>
</div>
HTML;
    }
    $artistesHtml .= '</div>';
} else {
    $artistesHtml = '<p>Aucun artiste disponible.</p>';
}

$contenuPage = <<<HTML
<style>
@import url('https://fonts.googleapis.com/css2?family=Fredoka+One&display=swap');

body {
    font-family: 'Fredoka One', cursive;
    margin:0; padding:0;
    background: #0a0f1a;
    color:#eee;
    overflow-x:hidden;
}

a { text-decoration:none; color:inherit; }

.container {
    max-width:1400px;
    margin:auto;
    padding:50px 20px;
    text-align:center;
}

a.retour {
    display:inline-block;
    margin-bottom:30px;
    padding:10px 20px;
    background: #66d0ff;
    color: #0a0f1a;
    font-weight:bold;
    border-radius:10px;
    transition: background 0.3s, transform 0.3s;
}
a.retour:hover {
    background: #4aa0dd;
    transform: translateY(-3px);
}

h1 {
    font-size:3em;
    margin-bottom:50px;
    color:#66d0ff;
    text-shadow: 0 2px 15px rgba(0,0,0,0.5);
}

.artistes-gallery {
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    gap:40px;
}

.artiste-item {
    position:relative;
    width:160px;
    text-align:center;
    transition: transform 0.4s, filter 0.4s;
}

.artiste-item:hover {
    transform: translateY(-8px) scale(1.05) rotate(-2deg);
    filter: drop-shadow(0 6px 10px rgba(102,208,255,0.5));
    z-index:10;
}

.artiste-img {
    width:160px;
    height:160px;
    border-radius:50%;
    background-size:cover;
    background-position:center center;
    border:3px solid rgba(102,208,255,0.3);
    transition: transform 0.4s, box-shadow 0.4s;
    margin-bottom:12px;
}

.artiste-item:hover .artiste-img {
    transform: scale(1.05) rotate(3deg);
    box-shadow: 0 0 15px rgba(102,208,255,0.6);
}

.artiste-nom {
    font-size:1.2em;
    font-weight:bold;
    color:#66d0ff;
    text-align:center;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.6);
}
</style>

<div class="container">
    <a href="index.php" class="retour">&lt; Retour à l'accueil</a>
    <h1>Artistes</h1>
    {$artistesHtml}
</div>
HTML;

$page = new HTMLPage("Lowify - Artistes");
$page->addContent($contenuPage);
echo $page->render();
