<?php

require_once 'inc/page.inc.php';

$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : "Une erreur est survenue.";

$contenuPage = <<<HTML
<style>
@import url('https://fonts.googleapis.com/css2?family=Fredoka+One&display=swap');

body {
    font-family: 'Fredoka One', cursive;
    background: #0a0f1a;
    color: #eee;
    margin:0; padding:0;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.container {
    text-align:center;
    background: linear-gradient(135deg, #112233, #223344);
    padding:40px 50px;
    border-radius:20px;
    box-shadow: 0 0 30px rgba(102,208,255,0.5);
    max-width:500px;
}

h1 {
    margin-bottom:20px;
    color:#66d0ff;
    text-shadow: 1px 1px 5px rgba(0,0,0,0.7);
}

p { margin-bottom:15px; color:#a0cfff; }

a {
    color:#00ffff;
    text-decoration:none;
    font-weight:bold;
    transition: color 0.3s;
}

a:hover { color:#66d0ff; text-decoration:underline; }
</style>

<div class="container">
    <h1>Erreur</h1>
    <p>{$message}</p>
    <p><a href="artists.php">&lt; Retour aux artistes</a></p>
</div>
HTML;

$page = new HTMLPage("Erreur - Lowify");
$page->addContent($contenuPage);
echo $page->render();
