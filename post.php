<?php
session_start();
include("scripts/db_connection.php");
include("scripts/verScript.php");
include("scripts/post_functions.php");

$logged = verAccount($db, $id_user, $role);
if (!$logged) {
    header("Location: login.php");
    die();
}
if(checkPostDelay($db,$id_user)){
    echo "<script>setTimeout(function(){ 
                window.location.href = 'userArea.php';
            }, 3000);  </script>";
    exit("Non puoi postare più di 2 post ogni 7 minuti!");
}



//Controlla se l'utente è loggato, ed'è obbligatorio che lo sia per poter postare

/*
Questo è la funzione che permette di creare post.
Crea tutto il form e poi fa una verifica lato server, se tutto va bene
uploada i media (se presenti) e carica il tutto nel database
*/
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="structure/specific_sections.css">
    <link rel="icon" href="assets\browser_head_images\post_logo.png" type="image/png">
    <title>Crea un post!</title>
</head>

<body class="postPubl">
    <?php include("structure/header.php"); ?>
    <script src="structure\darkmode.js"></script>
        <div class="mainPostSec">
            <!-- Form per postare -->
            <h1>Crea un post!</h1>
            
            <form action="" method="post" enctype="multipart/form-data">
                <i>
                    <input type="text" name="title" class="inp" placeholder="Titolo post">
                    <textarea name="content" class="inp" id="content" rows=10 columns=40></textarea>
                    <input type="file" name="file" id="file">
                    <img class="media" style="display:none;float: left;" id="previewI" src="" alt="">
                    <video class="media" style="display:none" id="previewV" src="" controls></video>
                    <input type="text" name="tags" class="inp" placeholder="tags">
                    <select name="public" id="lang">
                        <option value="1">public</option>
                        <option value="0">private</option>
                    </select>

                </i>
                <i>
                    <input type="submit" value="Pubblica" id="submit">
                </i>
            </form>
        </div>
        
    </div>
    <script>
        //Questo script effettua una prima verifica lato client
        document.getElementById('file').addEventListener('change', function(event) {
            isVideo = false;
            let prevV = document.getElementById('previewV');
            let prevI = document.getElementById('previewI');
            prevV.src = "";
            prevI.src = "";
            prevV.style.display = "none";
            prevI.style.display = "none";
            document.getElementById('previewI').src = "";
            const file = event.target.files[0];

            if (!file) {
                console.log("No file selected.");
                return;
            }

            const allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/x-matroska'];

            if (!allowedMimeTypes.includes(file.type)) {
                alert("Invalid file type. Only images are allowed.");

                event.target.value = "";
            } else {
                console.log("Valid image file.");
                if (file.type.includes("video")) isVideo = true;
            }

            if(file.size > 5120000) {
                alert("File size exceeds 5MB limit."); //5mb di limite
                event.target.value = ""; 
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                str = "";
                if (isVideo) {
                    str = "previewV";
                    prevV.style.display = "none";
                } else {
                    str = "previewI";
                    prevI.style.display = "none";
                }
                const img = document.getElementById(str);
                img.src = e.target.result; 
                img.style.display = "block";
            };

            reader.readAsDataURL(file);
        });
    </script>
    
</body>

</html>

<?php
include("scripts/db_connection.php");
$isVideo = 0;


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pathFile = null;
    $isPublic;
    if (!isset($_REQUEST["title"]) && empty($_REQUEST["title"])) exit("IL TITOLO DEL POST NON PUO' ESSERE VUOTO!");
    if (!isset($_REQUEST["content"]) && empty($_REQUEST["content"])) exit("controlla il contenuto del post!");
    if (!isset($_REQUEST["tags"]) && empty($_REQUEST["tags"])) exit("controlla i tags del post!");
    if (!isset($_REQUEST["public"]) && empty($_REQUEST["public"])) {
        echo "CONTROLLA LA SELEZIONE DEI PRIVATI O PUBBLICI";
    }

    //Avviene qui la verifica lato server e infine il caricamento nel database del file
    if (isset($_FILES["file"])) {
        if ($_FILES["file"]["size"] > 0) {
            if (!uploadMedia($_FILES["file"], $isVideo, $pathFile, "uploads/")) {
                exit("errore nell'upload!");
            }
        }
    }


    if ($_REQUEST["public"] == "0") {
        $isPublic = 0;
    } else {
        $isPublic = 1;
    }

    $title = $_REQUEST["title"];
    $content = $_REQUEST["content"];
    $tags = $_REQUEST["tags"];

    if(strlen($title) > 120) exit("Il titolo del post non può superare i 120 caratteri!");
    if(strlen($content) > 500) exit("Il contenuto del post non può superare i 500 caratteri!");
    //Ed'è qui che le informazioni del post vengono inserite
    $query = "INSERT INTO posts(Id_pubblisher,title,description,attachedFile_path,tags,isVideo,isPublic) VALUES (?,?,?,?,?,?,?)";
    $prep = $db->prepare($query);
    echo $isVideo;
    echo $pathFile;
    sanitize($db, $title);
    sanitize($db, $content);
    sanitize($db, $tags);
    if(badWordsList($title)){$db->close(); exit("Non puoi usare quella parola! Nel titolo");}
    if(badWordsList($content)){$db->close(); exit("Non puoi usare quella parola! Nel contenuto");}
    if(badWordsList($tags)){$db->close();exit("Non puoi usare quella parola! Nel tags");}
    $prep->bind_param("issssii", $id_user, $title, $content, $pathFile, $tags, $isVideo, $isPublic);
    $prep->execute();
    echo $prep->error;
    if ($prep->affected_rows > 0) {
        echo "Post pubblicato con successo!";
        echo "<script>window.location.href = 'userArea.php';</script>";
    } else {
        echo "Errore nella pubblicazione del post!";
    }
}
$db->close();
?>