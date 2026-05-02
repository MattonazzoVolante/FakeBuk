<?php
ini_set('session.gc_maxlifetime', 30 * 86400); // Extend session lifetime to 30 days
session_set_cookie_params(30 * 86400);
session_start();
include("scripts/db_connection.php");
include("scripts/verScript.php");

$token = bin2hex(random_bytes(32));
$id_user;
$role;
$logged = verAccount($db, $id_user, $role);
if ($logged) {
    echo "<script>window.location.href = 'index.php';</script>";
}

$db->close();

/*
Questo script serve per loggare l'utente e creare una sessione per lui univoca.
Non solo, segna la sessione dell'utente nel database per una continua validazione
*/
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="structure/specific_sections.css">
    <link rel="icon" href="assets\browser_head_images\main_logo.png" type="image/png">
</head>

<body class="login">
    <?php include("structure/header.php") ?>
    <div>
        <div class="mainLoginSex">
                <div id="mainDiv">
                    <form action="" method="post">
                        <div class="formAction">
                            <i>
                                <h1>Username:</h1> <input type="text" name="username" id="">
                                <h1>Password: </h1> <input type="password" name="password" id="">
                            </i>
                            <i><input id="loginButton" type="submit" value="Accedi"></i>
                        </div>
                    </form>
                    <div class="lowerButtons">
                        <i><a href="signup.php">Nessun account? Creane uno qui!</a></i>
                        <i><a href="recoverPassword.php">Recupera password</a></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="app.js"></script>
    <script src="structure\darkmode.js"></script>
</body>

</html>

<?php


include("scripts/db_connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    /*CONTROLLA GLI INPUT, VULNERABILE ALLE SQLi*/

    if (!isset($_REQUEST["username"]) && !isset($_REQUEST["password"])) {
        echo "Errore nell'inserimento dei parametri";
        die();
    }

    if (empty($_REQUEST["username"]) && empty($_REQUEST["password"])) {
        echo "Errore nell'inserimento dei parametri";
        die();
    }


    if ($db->connect_error) {
        echo "errore nella connessione del db";
        die();
    }




    $password = $_REQUEST["password"];
    $username = $_REQUEST["username"];
    $id_user = 0;

    //La parte qui sotto convalida il nome utente e la password inserita
    $query = "SELECT Id_user,username,password FROM users WHERE username=?";
    $selIdUs = $db->prepare($query);
    $selIdUs->bind_param("s", $username);
    $selIdUs->execute();
    $selIdUs->store_result();
    $selIdUs->bind_result($id, $us_name, $passw);

    while ($selIdUs->fetch()) {
        if (password_verify($password, $passw) && $us_name == $username) {
            $logged = true;
            $id_user = $id;
            $_SESSION["username"] = $username;
        };
    }
    if (!$logged) die();

    /*QUESTO CODICE SI OCCUPA DI GESTIRE LE SESSIONI UNA VOLTA CHE L'UTENTE E' LOGGATO!
        Come funziona?
        Se esiste già un token sessione, quest'ultimo viene aggiornato.
        Se non esiste, viene creato*/
    if ($logged) {
        $date_start = date("Y-m-d h:i:s");
        $date_end = new DateTime;
        $date_end->modify('+1 month');
        $date_end = $date_end->format('Y-m-d H:i:s');
        $query = "SELECT Id_session,Id_user FROM sessions WHERE Id_session = ?;";
        $selSes = $db->prepare($query);
        $selSes->bind_param("s", $token);
        $selSes->execute();
        $selSes->store_result();
        $selSes->bind_result($id_ses, $id_us);

        //Lo script qua sotto crypta l'indirizzo Ip dell'utente per poi inviarlo al database, questo per motivi di sicurezza
        /*$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encryptedData = openssl_encrypt($_SERVER['REMOTE_ADDR'], "aes-256-cbc", getenv("ENCRYPTION_KEY"), 0, $iv);
        $prep = base64_encode($iv . $encryptedData);*/

        $prep = password_hash($_SERVER['REMOTE_ADDR'], PASSWORD_DEFAULT);

        /*
                Lo script qua sotto procede a rinnovare il token di sessione se 
                rileva che l'utente è loggato con un account
            */
        if ($selSes->num_rows >= 1) {
            while ($selSes->fetch()) {
                if ($id_us == $id_user) {
                    $old_token = $token;
                    $token = bin2hex(random_bytes(32));
                    $query = "UPDATE `sessions` SET `Id_session` =?,Ip_address = ? WHERE `sessions`.`Id_session` = ?;";
                    $updateToken = $db->prepare($query);
                    $updateToken->bind_param("sss", $token, $prep, $old_token);
                    $updateToken->execute();
                    $_SESSION["ses_token"] = $token;
                    session_regenerate_id(true);
                }
            }
        } else {
            //Se non l'utente non è già loggato il server procederà a creare una nuova sessione nel database per lui
            $query = "INSERT INTO sessions(Id_session,Id_user,data_start,data_end,Ip_address) VALUES (?,?,?,?,?)";
            $insSes = $db->prepare($query);
            $insSes->bind_param("sisss", $token, $id_user, $date_start, $date_end, $prep);
            $insSes->execute();
            $_SESSION["id_user"] = $id_user;
            $_SESSION["ses_token"] = $token;
            echo "<script>window.location.href = 'index.php';</script>";
        }
    }
}
$db->close();
?>