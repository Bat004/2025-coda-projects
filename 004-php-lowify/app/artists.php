<?php

// -- importation des librairies à l'aide de require_once
require_once 'inc/page.inc.php';
require_once 'inc/database.inc.php';

// -- initialisation de la connexion à la base de données

// c'est une opération dangereuse, donc on utilise try/catch
// et on affiche le message d'erreur si une erreur survient
try {
    $db = new DatabaseManager(
        dsn: 'mysql:host=mysql;dbname=lowify;charset=utf8mb4',
        username: 'lowify',
        password: 'lowifypassword'
    );
} catch (PDOException $ex) {
    echo "Erreur lors de la connexion à la base de données : " . $ex->getMessage();
    exit;
}

// -- on récupère les infors de tout les artistes depuis la base de données
$allArtists = [];

// c'est une opération dangereuse, donc on utilise try/catch
// et on affiche le message d'erreur si une erreur survient
try {
    // version en une ligne
    $allArtists = $db->executeQuery("SELECT id, name, cover FROM artist");

    // version multi-ligne
    $allArtists = $db->executeQuery(<<<SQL
    SELECT 
        id,
        name,
        cover
    FROM artist
SQL);

} catch (PDOException $ex) {
    echo "Erreur lors de la requête en base de données : " . $ex->getMessage();
    exit;
}

// -- on crée une variable pour contenir le HTML qui rerpésentera la liste des artistes
$artistsAsHTML = "";

$iterator = 0;

// -- pour chaque artiste récupéré depuis la base de donnée
foreach ($allArtists as $artist) {
    // on pré-réserve des variables pour injecter le nom, l'id et la cover de l'artiste dans le HTML
    $artistName = $artist['name'];
    $artistId = $artist['id'];
    $artistCover = $artist['cover'];

    // juste pour l'affichage, pas obligé
    if ($iterator % 4 == 0) {
        $artistsAsHTML .= '<div class="row mb-4">';
    }

    // -- on ajoute une carte HTML représentant l'artiste courant
    $artistsAsHTML .= <<<HTML
            <div class="col-lg-3 col-md-6 mb-4">
                <a href="artist.php?id=$artistId" class="text-decoration-none text-white">
                    <div class="card h-100 bg-dark text-white border-dark shadow">
                        <img src="$artistCover" class="card-img-top rounded-circle" alt="Image 1">
                        <div class="card-body bg-secondary-subtle  text-white">
                            <h5 class="card-title">$artistName</h5>
                        </div>
                    </div>
                </a>
            </div>
HTML;

    // juste pour l'affichage, pas obligé
    if ($iterator % 4 == 3) {
        $artistsAsHTML .= '</div>';
    }

    $iterator++;
}

// -- on crée la structure HTML de notre page
// en injectant le HTML correspondant à la liste des artistes
$html = <<<HTML
<div class="container bg-dark text-white p-4">
        <a href="index.php" class="link text-white"> < Retour à l'accueil</a>

    <h1 class="mb-4">Artistes</h1>
    
    <div>
    {$artistsAsHTML}
    </div>
</div>
HTML;

// -- on génère et on affiche la page
// je vous conseille de faire comme ça
$page = new HTMLPage("Artistes - Lowify");
$page->addContent($html);
echo $page->render();

// -- version compacte avec bootstrap et navigation transition
// /!\ A SUPPRIMER SI VOUS N'UTILISEZ PAS BOOTSTRAP
echo (new HTMLPage(title: "Artistes - Lowify"))
    ->setupBootstrap([
        "class" => "bg-dark text-white p-4",
        "data-bs-theme " => "dark"
    ])
    ->setupNavigationTransition()
    ->addContent($html)
    ->render();