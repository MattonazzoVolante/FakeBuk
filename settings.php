<?php
session_start();
include("scripts/verScript.php");
include("scripts/db_connection.php");
$logged = verAccount($db, $id_user);
if (!$logged) {
    header("Location: index.php");
}
include("scripts/post_functions.php");

/* Impostazioni Utente */
/* 
    Questo è la pagina che permette all'utente di modificare le proprie informazioni contenute nel sito
    e anche altre cose di sicurezza
*/

$getUserInfo = "SELECT users.Id_user,name,surname,username,email,pic_path,description FROM users WHERE users.Id_user = ?;";
$prepGetUsInfo = $db->prepare($getUserInfo);
$prepGetUsInfo->bind_param("i", $id_user);
$prepGetUsInfo->execute();
$prepGetUsInfo->bind_result($id_user, $name, $surname, $username, $email, $pic_path, $description);
while ($prepGetUsInfo->fetch()) {
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets\browser_head_images\settings_logo.png" type="image/png">
    <title>Document</title>
    <link rel="stylesheet" href="structure/specific_sections.css">
</head>

<body>
    <main>
        <?php include("structure/header.php"); ?>
        <div class='settings'>
            <div class='lateralSettings'>
                <button onclick="cambiaSez(1)" id='b1'>Generale</button>
                <button onclick="cambiaSez(2)" id='b2'>Sicurezza</button>
                <button onclick="cambiaSez(3)" id='b3'>Aspetto</button>
                <button onclick="cambiaSez(4)" id='b4'>Altro</button>
            </div>
            <div class='settingSection'>
                <div class="general" id="SettSez1">
                    <h1>Informazioni personali:</h1>
                    <form action="" method="post" id="updInfo" >
                        <p>nome:</p><input type="text" name="newName" value='<?php echo $name; ?>' id="">
                        <p>cognome:</p><input type="text" name="newSurname" value="<?php echo $surname; ?>" id="">
                        <p>username:</p><input type="text" name="newUsername" value="<?php echo $username; ?>" id="">
                        <p>email:</p><input type="email" name="newEmail" value="<?php echo $email; ?>" id="">
                        <i></i><input type="submit" name="cambiaValori" value="Cambia Valori">
                    </form>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <label for="pic">Immagine profilo: </label><input type="file" name="pic" id="pic">
                        <img style="max-width: 200px; max-height: 200px;" id="previewI" src="" alt="">
                        <input type="text" name="desc" value="<?php echo $description; ?>">
                        <i></i><input type="submit" name="loadPic">
                    </form>
                </div>
                <div style="display:none;" class="security" id="SettSez2">
                    <form action="" method="post">
                        <h1>Vecchia password: </h1>
                        <input type="password" name="oldPassword" placeholder="Vecchia password">
                        <h1>Nuova pass</h1>
                        <input type="password" name="password1" placeholder="Nuova password">
                        <input type="password" name="password2" id="" placeholder="Reinserisci password">
                        <input type="submit" name="changePassword" value="Cambia la password">
                    </form>
                    <form action="" method="post">
                        <h1>Domanda di sicurezza </h1>
                        <input type="text" name="question" placeholder="Domanda...">
                        <h1>risposta: </h1>
                        <input type="text" name="response" placeholder="risposta">
                        <input type="password" name="password" placeholder="password" id="">
                        <input type="submit" name="changeSecQuestions" value="Imposta domanda di sicurezza">
                    </form>
                </div>
                <div style="display:none;" class="appearance" id="SettSez3">
                    <h1>
                        Dark mode:
                    </h1>
                    <button onclick="toggleDarkMode()" id="darkModeBut">Abilita DarkMode</button>
                    <h2>NOTA: la darkmode è stata appena inserita, è possibile che tu possa riscontrare in alcuni bug</h2>
                </div>
                <div style="display:none;" class="other" id="SettSez4">
                    <button onclick="logOut()">Logout</button>
                    <button onclick="showInserPassForDel()">DELETE PERMANTLY THE ACCOUNT</button>
                    <div style="display:none;" id="hiddenDiv" class="centerPosDiv">
                        <form action="" method="post">
                            <input type="password" name="password" placeholder="password">
                            <h1>Cancellando l'account non c'è modo di recuperarlo</h1><br>
                            <h1>Tutti i file caricati verranno cancellati in modo permanente</h1>
                            <input type="submit" name="deleteAccount" value="CANCELLA">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src='app.js'></script>
    <script>
        

        document.getElementById('pic').addEventListener('change', function(event) {
            isVideo = false;
            let prevI = document.getElementById('previewI');
            prevI.src = "";
            prevI.style.display = "none";
            document.getElementById('previewI').src = "";
            const file = event.target.files[0];

            if (!file) {
                console.log("No file selected.");
                return;
            }

            // Tipi di file accettati
            const allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            // Controllo dei tipi
            if (!allowedMimeTypes.includes(file.type)) {
                alert("Invalid file type. Only images are allowed.");
                
                event.target.value = ""; 
            } else {
                console.log("Valid image file.");
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                str = "previewI"; prevI.style.display = "none";
                const img = document.getElementById(str);
                img.src = e.target.result;
                img.style.display = "block";
            };

            reader.readAsDataURL(file);
        });

        function cambiaSez(a) {
            const sez1 = document.getElementById("SettSez1");
            const sez2 = document.getElementById("SettSez2");
            const sez3 = document.getElementById("SettSez3");
            const sez4 = document.getElementById("SettSez4");


            const b1 = document.getElementById("b1");
            const b2 = document.getElementById("b2");
            const b3 = document.getElementById("b3");
            const b4 = document.getElementById("b4");


            const selectedButtonColor = "white";


            s1 = (a == 1);
            s2 = (a == 2);
            s3 = (a == 3);
            s4 = (a == 4);

            let bArray = [b1, b2, b3,b4];
            let selB = 0;
            bArray.forEach(button => {
                button.style.border = '1px black solid';
            });
            if (s1) {
                sez1.style.display = "";
                selB = 1;
            } else {
                sez1.style.display = "none";
            }
            if (s2) {
                sez2.style.display = "";
                selB = 2;
            } else {
                sez2.style.display = "none";
            }
            if (s3) {
                sez3.style.display = "";
                selB = 3;
            } else {
                sez3.style.display = "none";
            }
            if (s4) {
                sez4.style.display = "";
                selB = 4;
            } else {
                sez4.style.display = "none";
            }

            for (let i = 1; i <= 4; i++) {
                if (i != selB) {
                    bArray[i - 1].style.borderTop = '3px black solid';
                    bArray[i - 1].style.backgroundColor = "#8e8e8e";
                } else {
                    bArray[i - 1].style.border = '3px black solid';
                    bArray[i - 1].style.borderTop = "0px black solid";
                    bArray[i - 1].style.backgroundColor = selectedButtonColor;
                }
                if (i == selB + 1) {
                    bArray[i - 1].style.borderLeft = '3px black solid';
                }
            }
        }

        document.onload(cambiaSez(1));

        

        function showInserPassForDel() {
            let div = document.getElementById("hiddenDiv");
            div.style.display = "block";
        }
    </script>
    <script src="structure\darkmode.js"></script>
</body>

</html>

<?php
/*
Questo ammasso orripilante di codice è quello che gestisce la verifica
dell'inserimento dato, fatto lato server per evitare modifiche lato client
*/

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    /*
    Questa è la sezione che fa la verifica, da un errore e interrompe
    le operazioni successive
    */
    echo "<div class='errori'>";
    if (isset($_REQUEST["cambiaValori"])) {
        if (
            !isset($_REQUEST["newName"]) &&
            !isset($_REQUEST["newSurname"]) &&
            !isset($_REQUEST["newUsername"]) &&
            !isset($_REQUEST["newEmail"])
        ) {
            echo "Controlla i campi!";
            die();
        }

        $name = $_REQUEST["newName"];
        $surname = $_REQUEST["newSurname"];
        $username = $_REQUEST["newUsername"];
        $email = $_REQUEST["newEmail"];
        if(strlen($username)> 12 ||  str_contains($username," ")){exit("Il nome utente non può essere più lungo di 12 caratteri, o contentere spazi!");}
        sanitize($db, $name);
        sanitize($db,$surname);
        sanitize($db,$username);
        sanitize($db,$email);

        

        if (
            empty($name) &&
            empty($surname) &&
            empty($username) &&
            empty($email)
        ) {
            echo "Controlla i campi!";
            die();
        }

        if(badWordsList($name)){$db->close(); exit("NOME NON VALIDO");}
        if(badWordsList($surname)){$db->close(); exit("COGNOMW NON VALIDO");}
        if(badWordsList($username)){$db->close(); exit("USERNAME NON VALIDO");}
        if(badWordsList($email)){$db->close(); exit("EMAIL NON VALIDA");}

        $query = "UPDATE users
            SET name = ?, surname = ?, username = ?,email= ?
            WHERE Id_user=?;";

        $prep = $db->prepare($query);
        $prep->bind_param("ssssi", $name, $surname, $username, $email, $id_user);
        $prep->execute();
        echo "<script>window.location.href = 'settings.php';</script>";
    }

    if (isset($_REQUEST["changePassword"])) {
        /*
        Codice backend per il cambio password

        Quello che fa è verificare se la vecchia password corrisponde a 
        quella attuale, e se le 2 password nuove sono uguali la cambia

        */
        if (
            !isset($_REQUEST["password1"]) &&
            !isset($_REQUEST["password2"]) &&
            !isset($_REQUEST["oldPassword"])
        ) {
            exit("Controlla i campi! - PASS");
        }

        $password1 = $_REQUEST["password1"];
        $password2 = $_REQUEST["password2"];
        $oldPassword = $_REQUEST["oldPassword"];

        if (
            empty($password1) ||
            empty($password2) ||
            empty($oldPassword)
        ) {
            exit("Controlla i campi! - PASS");
        }

        $takeOldPass = $db->query("SELECT Id_user,password FROM users WHERE Id_user = $id_user");
        while ($row = $takeOldPass->fetch_assoc()) {
            if (password_verify($row["password"], $oldPassword)) exit("Vecchia password sbagliata!");
        }

        if (strcmp($password1, $password2) != 0) {
            exit("Controlla che le 2 password siano uguali!");
        }

        $query = "UPDATE users
            SET password= ?
            WHERE Id_user=?;";

        $prep = $db->prepare($query);
        $passC = password_hash($password1, PASSWORD_DEFAULT);
        echo $passC;
        $prep->bind_param("ss", $passC, $id_user);
        $prep->execute();
        $db->query("DELETE FROM sessions WHERE Id_user = $id_user");
        //Quando viene cambiata la password, tutte le sessioni attive vengono cancellate
        //Bisogna relloggare con ogni dispositivo
        echo "SUCCESSO!";
        session_abort();
        setcookie("remember_me", "", time() - 3600);
    }

    if (isset($_REQUEST["changeSecQuestions"]) && isset($_REQUEST["question"]) && isset($_REQUEST["response"]) && isset($_REQUEST["password"])) {
        /*
        Sezione per impostare una domanda di sicurezza 
        (Di solito sta cosa si fa con email e verifica, che si poteva anche fare
        con phpMailer, ma il server Altervista su cui volevo hostarlo
        non permette di usare il servizio SMTP, quindi ho dovuto improvvisare
        usando il modo più vecchio e meno sicuro, ovvero le domande di sicurezza)

        È semplice, viene impostata una domanda, una risposta, se le due cose
        corrispondono allora il sito permette di cambiare la password
        */
        if (
            !isset($_REQUEST["question"]) &&
            !isset($_REQUEST["response"]) &&
            !isset($_REQUEST["password"])
        ) {
            exit("Controlla i campi! - PASS");
        }

        $question = $_REQUEST["question"];
        $response = password_hash($_REQUEST["response"], PASSWORD_DEFAULT);
        //Il motivo per cui la risposta è criptata è per evitare che qualcuno
        //con accesso al database possa usarla per cambiare la password ad un utente
        //dal sito, d'altronde è come se fosse una seconda password
        $password = $_REQUEST["password"];

        sanitize($db, $question);
        if (
            empty($question) ||
            empty($response) ||
            empty($password)
        ) {
            exit("Controlla i campi! - PASS");
        }

        $takePass = $db->query("SELECT Id_user,password FROM users WHERE Id_user = $id_user");
        while ($row = $takePass->fetch_assoc()) {
            if (password_verify($row["password"], $password)) exit("password sbagliata!");
        }

        $query = "UPDATE users SET recover_question = ?,recover_answer = ? WHERE Id_user = ?;";
        $prep = $db->prepare($query);
        $prep->bind_param("ssi", $question, $response, $id_user);
        $prep->execute();
    }
    echo "<div>";
}

if (isset($_REQUEST["loadPic"])) {
    /*
    Sezione per impostare immagine profilo e descrizione
    */
    $description = $_REQUEST["desc"];
    $pathFile;
    
    
    if (empty($description)) $description = null;
    else $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
    sanitize($db, $description);
    if(badWordsList($description)){$db->close(); exit("NON PUOI USARE QUELLA PAROLA");}
    if (isset($_FILES["pic"]) && $_FILES["pic"]["size"] > 0) {
        if (!uploadImage($_FILES["pic"], $pathFile, "uploads/users_Pic/")) exit("ERRORE NELLA RICHIESTA");
    } else $pathFile = $pic_path;


    $query = "UPDATE users SET pic_path=? WHERE Id_user = ?;";
    $queryDes = "UPDATE users SET description = ? where Id_user=?";
    $pr1 = $db->prepare($query);
    $pr2 = $db->prepare($queryDes);
    $pr1->bind_param("si", $pathFile, $id_user);
    $pr1->execute();
    $pr2->bind_param("si", $description, $id_user);
    $pr2->execute();
}

if (isset($_REQUEST["deleteAccount"])) {
    /*
    Questa è la sezione più delicata, permette di cancellare l'account e tutto quello che
    si è fatto con esso, cancella sessioni, post, commenti e infine il profilo
    */

    if (!isset($_REQUEST["password"])) exit("Controlla di aver inserito la password");
    $password = $_REQUEST["password"];
    if (empty($password)) exit("La password non può essere vuota");
    if (strlen($password) < 8) exit("la password non può esser più piccola di 8 caratteri");
    $takeOldPass = $db->query("SELECT Id_user,password FROM users WHERE Id_user = $id_user");
    $oldPass;
    while ($row = $takeOldPass->fetch_assoc()) {
        $oldPass = $row["password"];
    }
    if (password_verify($password, $oldPass)) {
        $Id_user = $id_user;
        if (!(verAccount($db, $id, $role) && ($role >= 3 || $id == $Id_user))) die();
        echo "<div style='display:block; position:fixed;top:0;left:0;width:100%;height:100%;background-color:black;color:white;z-index:9999;text-align:center;'><h1>Post cancellati... Attendere cancellazione dell'account...</h1></div>";
        
        deleteAllPostsWithMediaFromUser($db, $Id_user); //<- Questa funzione cancella i media e i post

        $queryDes = "DELETE FROM sessions WHERE Id_user=$Id_user";
        $db->query($queryDes);
        $queryDes = "DELETE FROM comments WHERE Id_user=$Id_user;";
        $db->query($queryDes);
        $queryDes = "DELETE FROM reportedusers WHERE Id_repUs=$Id_user;";
        $db->query($queryDes);
        $queryDes = "DELETE FROM banned_users WHERE Id_banned=$Id_user;";
        $db->query($queryDes);
        $queryDes = "DELETE FROM feedbacks WHERE Id_user=$Id_user;";
        $db->query($queryDes);
        $queryDes = "DELETE FROM followers WHERE Id_user=$Id_user;";
        $db->query($queryDes);
        $queryDes = "DELETE FROM likes WHERE Id_user=$Id_user;";
        $db->query($queryDes);
        
        sleep(10);
        $queryDes = "DELETE FROM notifications WHERE Id_user=$Id_user;";
        $db->query($queryDes);
        $queryDes = "DELETE FROM users WHERE Id_user=$Id_user;";
        $db->query($queryDes);

        session_destroy();
        setcookie("remember_me", "", time() - 3600);
        echo "<script>window.location.href = 'index.php';</script>";
    }
}


?>