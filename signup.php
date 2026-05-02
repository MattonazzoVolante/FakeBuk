<?php
session_start();

include("scripts/db_connection.php");
include("scripts/verScript.php");
include("scripts/post_functions.php");

$token = bin2hex(random_bytes(32));
$id_user;
$role;
$logged = verAccount($db, $id_user, $role);
if ($logged) {
    header("Location: index.php");
}

$db->close();

/*
Pagina che permette di registrarsi in caso di utente nuovo


*/

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets\browser_head_images\main_logo.png" type="image/png">
    <title>Registrati</title>
    <link rel="stylesheet" href="structure/specific_sections.css">
</head>

<body>
    <?php include("structure/header.php"); ?>

    <main>
        <div class="register" style="display: grid; grid-template-columns: 50% 50%">
            <div class="signUp">
                <div>
                    <h1>Registrati!</h1>
                    <form action="" method="post">
                        <h1>Nome</h1><input type="text" name="name">
                        <h1>Cognome</h1><input type="text" name="surname">
                        <h1>Nickname</h1><input type="text" name="username">
                        <h1>Email</h1><input type="email" name="email" id="">
                        <h1>Password</h1><input type="password" name="password1" id="">
                        <h1>Rimetti password</h1><input type="password" name="password2" id="">
                        <input id="loginButton" type="submit" value="Registrati">
                    </form>
                </div>
            </div>
            <div class='privacy_pol'>
                <?php
                    include("assets/privacy_policy.html");
                ?>
            </div>
        </div>
        <script src="structure\darkmode.js"></script>
    </main>
</body>

</html>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //Parte di verifica degli input
    echo "<div class='output'>";
    if (
        !isset($_REQUEST["name"]) &&
        !isset($_REQUEST["surname"]) &&
        !isset($_REQUEST["username"]) &&
        !isset($_REQUEST["email"]) &&
        !isset($_REQUEST["password1"]) &&
        !isset($_REQUEST["password2"])
    ) {
        echo "si è verificato un errore";
        die();
    }

    $name = $_REQUEST["name"];
    $surname = $_REQUEST["surname"];
    $username = $_REQUEST["username"];
    $email = $_REQUEST["email"];

    if (
        empty($name) ||
        empty($surname) ||
        empty($username) ||
        empty($email) ||
        empty($_REQUEST["password1"]) ||
        empty($_REQUEST["password2"])
    ) {
        echo "Riempi tutti i campi!";
        die();
    }

    if(strlen($username)> 12 ||  str_contains($username," ")){exit("Il nome utente non può essere più lungo di 12 caratteri, o contentere spazi!");}

    if ($_REQUEST["password1"] != $_REQUEST["password2"]) {
        echo "Ricontrolla la password!";
        die();
    }

    if (strlen($_REQUEST["password1"]) < 8) {
        echo "la password deve essere di almeno 8 caratteri!";
        die();
    }

    if(badWordsList($name)){exit("NOME NON VALIDO");}
    if(badWordsList($surname)){exit("COGNOMW NON VALIDO");}
    if(badWordsList($username)){ exit("USERNAME NON VALIDO");}
    if(badWordsList($email)){ exit("EMAIL NON VALIDA");}


    $password = password_hash($_REQUEST["password1"], PASSWORD_DEFAULT);


    session_reset();


    //Parte effettiva dell'inserimento nel database
    include("scripts/db_connection.php");

    // Sanitizzazione degli input (evita SQL injection)
    sanitize($db, $name);
    sanitize($db, $surname);
    sanitize($db, $username);
    sanitize($db, $email);
    sanitize($db, $password);

    $query = "INSERT INTO users(name, surname, username, password, email) VALUES (?, ?, ?, ?, ?);";
    $prep = $db->prepare($query);
    $prep->bind_param("sssss", $name, $surname, $username, $password, $email);

    $res = false;
    try {
        $res = $prep->execute();
    } catch (Exception $e) {
        echo "Prova con un altro username o email! ";
        die();
    }
    $queryIdUs = "SELECT Id_user FROM users WHERE username = ?;";
    $prepIdUs = $db->prepare($queryIdUs);
    $prepIdUs->bind_param("s", $username);
    $prepIdUs->bind_result($id_user);
    $prepIdUs->execute();
    $id_user = 0;
    while ($prepIdUs->fetch()) {}
    if (!$res) {
        echo "errore nella query";
        die();
    }

    echo "DATI INSERITI CON SUCCESSO!!";
    echo "</div>";
    $db->close();
    
    echo "<script>window.location.href = 'login.php';</script>";
}
?>