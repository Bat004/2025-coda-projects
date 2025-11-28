    <?php

require_once __DIR__ . '/../inc/page.inc.php';

$doInstall = $_GET['doInstall'] ?? '0';
$content = null;

if ("1" === $doInstall) {
    require_once __DIR__ . '/database_init.php';

    $dbInitializer = new DatabaseInitializer();
    $results = $dbInitializer->initialize();

    $resultsHtml = '';
    foreach ($results as $step => $result) {
        $class = "bg-success-subtle text-success-emphasis";
        if (str_contains($result, "Erreur")) {
            $class = "bg-danger-subtle";
        }

        $resultsHtml .= "<tr><td class='{$class}'>{$step}</td><td class='{$class}'>{$result}</td></tr>";
    }

    $content = <<<HTML
    <div class="container mt-5">
        <h1>Installation de Lowify</h1>
        <p>Résultats de l'installation :</p>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Étape</th>
                    <th>Résultat</th>
                </tr>
            </thead>
            <tbody>
                {$resultsHtml}
            </tbody>
        </table>
    </div>
    HTML;
} else {
    $content = <<<HTML
    <div class="container mt-5">
        <h1>Installation de Lowify</h1>
        <p>Bienvenue dans l'assistant d'installation de Lowify. Cliquez sur le bouton ci-dessous pour commencer l'installation.</p>
        <a href="?doInstall=1" class="btn btn-primary">Démarrer l'installation</a>
    </div>
    HTML;
}


echo (new HTMLPage('Installation de Lowify'))
    ->setupBootstrap([
        "class" => "bg-dark text-white p-4",
        "data-bs-theme " => "dark"
    ])->setupNavigationTransition()->addContent($content)
    ->render();