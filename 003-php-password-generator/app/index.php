<?php

$password = "";
$length = $_POST['length'] ?? 10;
$useUpper = $_POST['uppercase'] ?? 0;
$useLower = $_POST['lowercase'] ?? 0;
$useNumbers = $_POST['numbers'] ?? 0;
$useSymbols = $_POST['symbols'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $characters = "";

    if ($useUpper)   $characters .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    if ($useLower)   $characters .= "abcdefghijklmnopqrstuvwxyz";
    if ($useNumbers) $characters .= "0123456789";
    if ($useSymbols) $characters .= "!@#$%^&*()-_+=[]{}<>?/";

    if ($characters !== "") {
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
    } else {
        $password = "Sélectionner au moins un type de caractère.";
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Générateur de mot de passe</title>

    <style>
        body {
            background: #f4f4f9;
            font-family: Arial, Helvetica, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 350px;
            text-align: center;
        }

        h1 {
            margin-bottom: 15px;
            color: #333;
        }

        label {
            display: block;
            margin: 8px 0;
            cursor: pointer;
        }

        select {
            padding: 6px;
            width: 100%;
            margin-top: 5px;
            border-radius: 5px;
        }

        button {
            background: #4a90e2;
            border: none;
            padding: 12px;
            color: white;
            width: 100%;
            margin-top: 15px;
            cursor: pointer;
            border-radius: 6px;
            font-size: 16px;
            transition: 0.2s;
        }

        button:hover {
            background: #357bc8;
        }

        .password-box {
            background: #eee;
            padding: 10px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 18px;
            word-break: break-all;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Générateur</h1>

    <form method="POST">
        <label>Longueur du mot de passe :
            <select name="length">
                <?php for ($i = 4; $i <= 30; $i++): ?>
                    <option value="<?= $i ?>" <?= $i == $length ? "selected" : "" ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </label>

        <label><input type="checkbox" name="uppercase" value="1" <?= $useUpper ? "checked" : "" ?>> Majuscules</label>
        <label><input type="checkbox" name="lowercase" value="1" <?= $useLower ? "checked" : "" ?>> Minuscules</label>
        <label><input type="checkbox" name="numbers" value="1" <?= $useNumbers ? "checked" : "" ?>> Chiffres</label>
        <label><input type="checkbox" name="symbols" value="1" <?= $useSymbols ? "checked" : "" ?>> Symboles</label>

        <button type="submit">Générer</button>
    </form>

    <div class="password-box">
        <?= $password ? htmlspecialchars($password) : "—" ?>
    </div>
</div>

</body>
</html>
