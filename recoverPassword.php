<?php
    ini_set('session.gc_maxlifetime', 3600); // Extend session lifetime to 30 days
    session_set_cookie_params(3600);
    session_start();
    include("scripts/db_connection.php");
    include("scripts/verScript.php");
    //print_r($_REQUEST);
    //print_r($_COOKIE);

    /*
    SCRIPT ANCORA IN FASE SPERIMENTALE
    Non potendo usare il servizio smtp... l'unico modo che ho per poter dare 
    l'opportunità agli utenti di recuperare la propria password è di usare
    delle domande con una risposta... 
    Che agiscono come se fossero una seconda password con indizio visto che
    basta azzeccare la risposta per riavere l'account.
    LO SO, NON E' SICURO QUANTO AVERE UNA MAIL, però come ho detto il servizio 
    smtp su altervista non c'è, e tanto è un sito che rimarrà online al massimo 2 settimane
    */

    /*Lo script è composto da 3 fasi:
        1) Verifica del nome utente: Controlla se il nome utente inserito è valido

        2) Presa della domanda di sicurezza
            Verifica della risposta con il database

        3) Inserimento nuova password

    Il sito capisce a che fase si è arrivati attraverso Cookie che durano qualche secondo
    Si possono boicottare i cookie? Si. basta crearne uno con javascript che abbia lo stesso nome e si possono
    saltare fasi della verifica, DA SISTEMARE
    */      
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="structure/specific_sections.css">
</head>
<body>
    <?php include("structure/header.php") ?>
    <div class="recoverPassword">
        <h1>Questa è la sezione dove puoi recuperare la tua password</h1>
        <h2>A condizione che sia stata impostata una domanda di sicurezza con una risposta</h2>
    <?php
    if(isset($_SESSION["setNewPass"])){ ?>
        <form action="" method="post">
            <input type="password" name="pass1" id="" placeholder="password nuova">
            <input type="password" name="pass2" id="" placeholder="ripeti password">
            <input type="submit" name="changePsw" id="">
        </form>
    <?php } ?>

    <?php if(!isset($_REQUEST["inserUsName"]) && !isset($_SESSION["nomeUsName"]) ){ ?>
        <form action="" method="post">
            <input type="text" name="nomeUsName" id="" placeholder="Inserisci il nome utente">
            <input type="submit" name="inserUsName">
        </form>
    <?php }else if((isset($_SESSION["nomeUsName"]) && !empty($_SESSION["nomeUsName"])) && !isset($_SESSION["setNewPass"])){
        include("scripts/db_connection.php");
        $usName =  $_SESSION["nomeUsName"];
        
        echo $usName;
        $recover_question = "";
        $recover_answer = "";
        $id = 0;
        if(!takeRecQaAFromUsName($db,$usName,$id,$recover_question,$recover_answer)) echo "utente inesistewnte"/*exit("Utente inesistente!")*/;
        if(is_null($recover_question) || is_null($recover_answer) || empty($recover_question) || empty($recover_answer)){
            $_SESSION = [];
            exit("Utente inesistente o non ha impostato una domanda di sicurezza");
        }
        if(isset($_REQUEST["recover"])){
            echo "arriva2";
            if(password_verify($_REQUEST["recoverAnsw"],$recover_answer)){
                echo "interesse verificato";
                $_SESSION["id_stor"] = $id;
                $_SESSION["setNewPass"] = true;
            }
        }
    ?>
        <form action="" method="post">
            <h1><?php echo $recover_question ?></h1>
            <input type="text" name="recoverAnsw" id="" placeholder="Risposta">
            <input type="submit" name="recover">
        </form>
    <?php } 

        if(isset($_SESSION["nomeUsName"])){
    ?>
        <form action="" method="post">
            <input type="submit" name="anoth" value="Riavvia procedura">
        </form>
    <?php } ?>
    </div>
    <script src="structure\darkmode.js"></script>
</body>
</html>

<?php
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        
        if(isset($_REQUEST["anoth"])){$_SESSION = []; }

        if(isset($_REQUEST["inserUsName"])){ECHO "yooooo";$_SESSION["nomeUsName"]=$_REQUEST["nomeUsName"];}
        
        if(isset($_REQUEST["changePsw"])){
            $passw1 = $_REQUEST["pass1"];
            $passw2 = $_REQUEST["pass2"];
            $id_user = $_SESSION["id_stor"];
            if(strcmp($passw1,$passw2) != 0) exit("ricontrolla le password");
            $query = "UPDATE users
            SET password= ?
            WHERE Id_user=?;";

            $prep = $db->prepare($query);
            $passC = password_hash($passw1,PASSWORD_DEFAULT);
            $prep->bind_param("ss",$passC,$id_user);
            $prep->execute();
            $db->query("DELETE FROM sessions WHERE Id_user = $id_user");
            $_SESSION = [];
            exit("<a href='login.php'>Password changed, click here to login</a>");
        }
        echo "<script>window.location.href = 'recoverPassword.php';</script>";
    }
    
    

    
?>