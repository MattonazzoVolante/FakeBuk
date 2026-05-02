<?php
session_start();
include("scripts/verScript.php");
include("scripts/db_connection.php");
include("scripts/post_functions.php");
$logged = verAccount($db, $id_user, $role);
$idPost = null;
// Inizializza la sessione e autentica l'utente



?>

<!-- 
Questo è il file index del sito, qui metterò alcune considerazioni generali
tecniche di questo sito web.
Per iniziare l'index è una pagina che carica casualmente i post degli utenti, utenti consigliati da seguire
e anche altre sezioni come la barra di ricerca e il pulsante per guardare il proprio profilo.

I post -> sono casuali, in modo completo, non c'è nessun algoritmo che li gestisce
I follower consigliati -> sono quelli con più follower, in modo da spingere gli utenti più seguiti (il metodo più semplice da implementare)

Header:
L'header si trova in un file a parte (per modularità )
L'header contiene:
La barra di ricerca -> permette di cercare post per titolo o per tag, oppure di cercare utenti
Il pulsante per ritornare all'index
Il pulsante per accedere al proprio profilo (se loggati), o per registrarsi o loggarsi in caso contrario.
-->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets\browser_head_images\main_logo.png" type="image/png">
    <title>Pagina Principale</title>
</head>

<body>
    <script src="app.js"></script> <!--Vengono caricate anche tutte le altre funzioni-->
    <script src="structure\darkmode.js"></script>
    <?php
    include("structure/header.php"); // Qua è dove viene incluso l'header 
    echo "<div class='content'>"; 
    // ^ Viene aperto un div di classe content... con React questo sarebbe stato più facile, ma ricorda che è stato tutto creato con php vanilla
    if (isset($_REQUEST["idPost"])) {

        echo "<div class='posts'>";
        $idPost = $_REQUEST["idPost"];
        getSinglePost($id_user, $idPost, $db, true, $role);
    } else {

        echo "<div id='postsSec' class='posts'>";
    ?>
        <div class="announcementsButton" id='feedback'>
            <a style="display:block; color:white;width: 100%; height:40px; font-size:25px; border: 1px solid black; border-radius: 3px" class='button' id="creaPost" href="announcements.php">Sezione annunci</a>
        </div>
        <?php
        $tags = "";
        if (!$logged) $tags = "casual";
        getMorePost($id_user, $tags, $db, true, $role);
        ?>
        </div>
        <div class="lateralSection sticky">
            <?php if ($logged) { ?> <a class='button' id="creaPost" href="post.php">Crea un post!</a><?php } ?>
            <p>Utenti consigliati!</p> <!-- Mostra gli utenti consigliati -->
            <div class="suggestedUsers">
                <?php {
                    $querySugUs = "SELECT users.Id_user,username,pic_path,COUNT(Id_following) AS follower FROM users INNER JOIN followers ON Id_following = users.Id_user GROUP BY Id_user ORDER BY follower DESC LIMIT 3;";
                    $resSugUs = $db->query($querySugUs);
                    while ($row = $resSugUs->fetch_assoc()) {
                        $id_us = $row["Id_user"];
                        echo "<div class='singleUser'><a href='userArea.php?id_User=$id_us'>";

                        $username = $row['username'];
                        $picPath = $row['pic_path'];
                        $follower = $row['follower'];
                        echo "<img src=$picPath>";
                        echo "<p>$username</p>";
                        echo "<p>$follower</p>";
                        echo "</a></div>";
                    }
                }
                ?>
            </div>
            <div class="suggestedPosts"> <!-- Mostra i post consigliati -->
                <?php {
                    $querySugUs = "SELECT * FROM (SELECT Id_post,pic_path,title,attachedFile_path,posts.description,tags,pubblication_datetime,username,isVideo FROM posts INNER JOIN users ON Id_pubblisher = Id_user WHERE attachedFile_path IS NULL AND isPublic = 1 ORDER BY RAND()) as q LIMIT 2;";
                    $getNumLike = "SELECT COUNT(Id_post) AS numLikes FROM likes WHERE Id_post = ?;";
                    $getLike = $db->prepare($getNumLike);
                    $getLike->bind_param("i", $Id_post);
                    $getLike->bind_result($likes);
                    $getNumComm = "SELECT COUNT(Id_post) AS numComm FROM comments WHERE Id_post = ?;";
                    $getComm = $db->prepare($getNumComm);
                    $getComm->bind_param("i", $Id_post);
                    $getComm->bind_result($numComm);
                    $resSugUs = $db->query($querySugUs);
                    while ($row = $resSugUs->fetch_assoc()) {
                        $id_post = $row['Id_post'];
                        $pubblisher = $row['username'];
                        $picPath = $row['pic_path'];
                        $title = $row['title'];
                        $text = $row['description'];
                        $pubDate = $row['pubblication_datetime'];

                        $Id_post = $row['Id_post'];
                        $getLike->execute();
                        while ($getLike->fetch()) {
                        }
                        echo "<div class='singleSugPost'>";
                        echo "<a href='index.php?idPost=$id_post'>";
                        echo "<div>";
                        echo "<div class='imgPubl'>";
                        echo "<img src=$picPath >";
                        echo "<p>$pubblisher</p>";
                        echo "</div>";
                        echo "<h1 id='title'> $title </h1><br>";
                        echo "<p id='description'> $text </p><br>";
                        echo "</div>";
                        echo "<div class='interactions'>";
                        echo "<div class='likeSecP'><p style='display:inline-block;' class='likes' id='likeP_$Id_post'>$likes</p>";
                        echo "<button onclick=leaveAlike($Id_post,'likeP_$Id_post') >";
                        if ($darkMode) echo "<img src='assets\icons\like_lightm.png'>";
                        else echo "<img src='assets\icons\like.png'>";
                        echo "</button></div>";
                        echo "<div class='commentSecP'><p id='comments'>$numComm  <p>";
                        if ($darkMode) echo " <img src='assets\icons\comment_lightm.png'>";
                        else echo " <img src='assets\icons\comment.png'>";
                        echo "</div>";
                        echo "</div></a></div>";
                    }
                }
                ?>
            </div>
            <?php if ($logged) { ?>
                <div class='button' id='feedback'>
                    <a href="leaveFeedback.php">Leave a feedback</a> <!-- Porta alla pagina per mostrare i feedback -->
                </div>
                <?php if ($role > 0) { ?>
                    <div class='button' style="background-color: red; color:white">
                        <a href="moderator/modsArea.php">Vai nell'area moderatore</a>
                    </div>
            <?php }
            } ?>
        </div>

        </div>

    <?php } ?>
    <div style="display:none" id="repDiv" class="centerPosDiv">
        <button onclick="showReportUs(0,0)" style="  background-color: black;"><img style="display: block;width: 30px;" src="assets\icons\x.png" alt=""></button>
        <form action="functions.php" method="POST">
            <input type="text" name="reason" value="motivo report">
            <input type="hidden" name="id_post" id="id_post">
            <input type="submit" name="reportPost">

        </form>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.documentElement.scrollTop = 0;
        });


        requiredAmount = 300;
        document.addEventListener("scroll", () => {
            const scrollAmount = window.scrollY || document.documentElement.scrollTop;
            if (scrollAmount >= requiredAmount) {
                loadNewPosts(10)
                requiredAmount += 5000;
            }
        });

        function loadNewPosts(howM) {
            postSec = document.getElementById("postsSec");
            fetch('functions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        loadNewPosts: "",
                        howMany: howM
                    })
                })
                .then(response => response.text())
                .then(data => {
                    postSec.innerHTML += data; // Append posts instead of replacing
                })
                .catch(error => console.error("Error:", error));
        }
    </script>

</body>

</html>

<?php

//Serve per lasciare un commento sotto ad un post
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_SESSION["time_out"])){if((time()-$_SESSION["time_out"]<10)){exit("Non commentare così tanto in fretta! ");}}
    if (isset($_REQUEST["commentAct"]) && !$alrCommented) {
        $query = "INSERT INTO comments(id_post,id_user,contenuto) VALUES (?,?,?);";
        $prepAcCom = $db->prepare($query);
        $id_user = $_SESSION["id_user"];
        $contenuto = $_REQUEST["content"];
        if (empty($contenuto)) die();
        sanitize($db, $contenuto);
        if (badWordsList($contenuto)) {
            $db->close();
            exit("Non puoi commentare utilizzando quella parola!");
        }
        $prepAcCom->bind_param("iis", $idPost, $id_user, $contenuto);
        $res = $prepAcCom->execute();
        if ($res) {

            $username = $_SESSION["username"];
            $idPubblisher = NULL;
            $query = "SELECT Id_pubblisher FROM posts WHERE Id_post = ?;";
            $prep = $db->prepare($query);
            $prep->bind_param("i", $idPost);
            $prep->execute();
            $prep->bind_result($idPubblisher);
            while ($prep->fetch()) {
            }
            $prep->close();
            $query = "SELECT title FROM posts WHERE Id_post = ?;";
            $prep = $db->prepare($query);
            $prep->bind_param("i", $idPost);
            $prep->execute();
            $prep->bind_result($title);
            while ($prep->fetch()) {
            }
            $prep->close();
            $query = "INSERT INTO notifications (Id_user, content) VALUES ($idPubblisher,'Hai ricevuto un commento da $username nel tuo post: <a href=index.php?idPost=$idPost> $title </a> : $contenuto');";
            $db->query($query);
            $_SESSION['time_out']= time();
        }
        echo "<script>window.location.href = 'index.php?idPost=$idPost';</script>";
    }
}

$db->close();
?>