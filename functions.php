
<?php
/*
Ogni riga if() può essere interpretata come una funzione.
Perché non ho fatto funzioni separate per rendere più pulito il codice?

Avevo provato a farlo però sarebbe risultato più complicato nel
ritornare dei risultati o passare come parametro certi valori, 
però riconosco il come si possa fare e si poteva decisamente scrivere meglio>
*/

session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include("scripts/db_connection.php");
    include("scripts/verScript.php");
    include("scripts/post_functions.php");

    if (isset($_POST["loadNewPosts"])) {
        $a=0;$role=0;
        if ($db->connect_error) {
            echo "ERRORE database";
            die();
        }
        if (isset($_SESSION['id_user']) && isset($_SESSION['role'])) {
            $a = $_SESSION['id_user'];
            $role = $_SESSION["role"];
        }
        $i = (int) $_POST['howMany'];


        getMorePost($a, 'casual', $db, true, $role, $i); // Call function BEFORE closing DB

        $db->close(); // Move this AFTER all queries have been executed
        die();
    }

    if (isset($_POST["loadNewUserPosts"])) {
        getPostByUser($_POST["id_user"], $db, false, false, 0, $_POST["howMany"], $_POST["offset"]);
        $db->close();
        die();
    }

    /*CONTROLLA GLI INPUT, VULNERABILE ALLE SQLi*/
    if (isset($_POST["LOGOUT"])) {
        setcookie('remember_me', "", 0, '/', '', true, true);
        $tok = $_SESSION['ses_token'];
        $db->query("DELETE FROM sessions WHERE Id_session = '$tok';");
        session_destroy();
        echo "<script>window.location.href = 'index.php';</script>";
        die();
    }

    //script per lasciare un like
    if (isset($_POST['leaveAlike']) && isset($_POST["id_post"])) {
        $tok = $_SESSION['ses_token'];
        $res = $db->query("SELECT users.Id_user FROM users INNER JOIN sessions ON sessions.Id_user = users.Id_user WHERE sessions.Id_session = '$tok';");
        $add = true;
        while ($row = $res->fetch_assoc()) {
            $id_p = $_POST["id_post"];
            $id_u = $row["Id_user"];
            $query = "INSERT INTO likes(Id_post,Id_user) VALUES ($id_p,$id_u);";


            try {
                $db->query($query);
                $postTitle = "";
                $id_pubblisher = 0;
                $getPostTitle = "SELECT * FROM posts WHERE Id_post = $id_p;";
                $res = $db->query($getPostTitle);
                if ($res->num_rows >= 1) {

                    while ($row = $res->fetch_assoc()) {
                        $postTitle = $row["title"];
                        $id_pubblisher = $row["Id_pubblisher"];
                    }
                    $not = "INSERT INTO notifications (Id_user, content)
VALUES ($id_pubblisher, 'Hai ricevuto un like sotto al tuo post: <a href=index.php?idPost=$id_p> $postTitle </a>');";
                    $db->query($not);
                }
            } catch (Exception $e) {
                // Se il like è già stato lasciato, lo rimuove
                $query = "DELETE FROM likes WHERE Id_post = $id_p AND Id_user = $id_u;";
                $db->query($query);
                $add = false;
            }
        }
        $add_ar = array("add" => $add);
        echo json_encode($add_ar);
        $db->close();
        die();
    }


    //Script per cancellare un account
    
    /*
    Questo script è stato spostato in settings.php
    if(isset($_POST["delete_Account"]) && isset($_POST["id_user"])){
            $Id_user = $id_user;
            if(!(verAccount($db,$id_user,$role) && ($role >= 3 || $id_user == $Id_user))) die();
            
            $queryDes = "DELETE FROM posts WHERE Id_pubblisher=$Id_user";
            $db->query($queryDes);
            $queryDes = "DELETE FROM sessions WHERE Id_user=$Id_user";
            $db->query($queryDes);
            $queryDes = "DELETE FROM user_addinfo WHERE Id_user=$Id_user";
            $db->query($queryDes);
            $queryDes = "DELETE FROM users WHERE Id_user=$Id_user;";
            $db->query($queryDes);
            session_destroy();
            setcookie("remember_me","",time()-3600);
            header("Location: index.php");
        }*/

    //script per cancellare un post
    if (isset($_POST['delete_Post']) && isset($_POST["id_post"])) {
        $verifiedM = 0;
        $verifiedU = 0;
        $id_post = $_POST['id_post'];
        $role = 0;
        $id_user;
        if (!verAccount($db, $id_user, $role)) die();
        // check if it is the user
        if ($role > 1) $verifiedM = 1;

        //check if it is the actual USER 

        $query = "SELECT users.Id_user,Id_session FROM sessions INNER JOIN users ON users.Id_user = sessions.Id_user WHERE users.Id_user = $id_user";
        $query2 = "SELECT Id_user,attachedFile_path as Fpath FROM users INNER JOIN posts ON Id_user = Id_pubblisher WHERE Id_post = $id_post;";
        $res = $db->query($query);
        $res2 = $db->query($query2);
        $id_pubblisher;
        $filePath = "";
        while ($row = $res2->fetch_assoc()) {
            $id_pubblisher = $row["Id_user"];
            if (!is_null($row["Fpath"])) {
                $filePath = $row["Fpath"];
            }
        }
        if ($res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                if (strcmp($row["Id_session"], $_SESSION["ses_token"]) == 0) {
                    if (strcmp($row["Id_session"], $_SESSION["ses_token"]) == 0 &&  $id_pubblisher == $_SESSION["id_user"]) $verifiedU = true;
                }
            }
        }
        if ($verifiedM || $verifiedU) {
            if (!$verifiedU) {

                $getPostTitle = "SELECT title FROM posts WHERE Id_post = ?;";
                $res = $db->prepare($getPostTitle);
                $res->bind_param("i", $id_post);
                $res->execute();
                $res->bind_result($post_title);
                $res->fetch();
                $queryNot = "INSERT INTO notifications (Id_user, content)
    VALUES ($id_pubblisher,'Il tuo post: $post_title è stato eliminato da un moderatore' )";
                $res->close();
                $db->query($queryNot);
            }
            deleteMedia($filePath);
            $queryDes = "DELETE FROM posts WHERE Id_post=$id_post;";
            $db->query($queryDes);
        } else die();
        exit("window.location.href = 'index.php?idPost=$id_post';");

    }

    //script per cancella un commento:
    if (isset($_POST['delete_Comment']) && isset($_POST["id_comment"]) && isset($_POST["id_post"])) {
        $verifiedM = 0;
        $verifiedU = 0;
        $id_comment = $_POST['id_comment'];
        $id_post = $_POST['id_post'];
        $role = 0;
        $id_user;
        if (!verAccount($db, $id_user, $role)) die();
        // check if it is the user
        if ($role > 1) $verifiedM = 1;

        //check if it is the actual USER 

        $query = "SELECT users.Id_user,Id_session FROM sessions INNER JOIN users ON users.Id_user = sessions.Id_user WHERE users.Id_user = $id_user";
        $query2 = "SELECT Id_comment,c.Id_user AS Id_pubblisher FROM users AS u INNER JOIN comments AS c ON c.Id_user = u.Id_user WHERE Id_comment=$id_comment;";
        $query3 = "SELECT Id_pubblisher FROM posts WHERE Id_post = $id_post;";
        $res = $db->query($query);
        $res2 = $db->query($query2);
        $res3 = $db->query($query3);
        $id_pubblisher;
        $id_Postpubblisher;
        while ($row = $res2->fetch_assoc()) {
            $id_pubblisher = $row["Id_pubblisher"];
        }
        while ($row = $res3->fetch_assoc()) {
            $id_Postpubblisher = $row["Id_pubblisher"];
        }
        if ($res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                if (strcmp($row["Id_session"], $_SESSION["ses_token"]) == 0) {
                    if (strcmp($row["Id_session"], $_SESSION["ses_token"]) == 0 &&  ($id_pubblisher == $_SESSION["id_user"] || $id_Postpubblisher == $_SESSION["id_user"])) $verifiedU = true;
                }
            }
        }
        if ($verifiedM || $verifiedU) {
            $res = $db->query("SELECT contenuto FROM comments WHERE Id_comment = $id_comment;");
            $content = "";
            while ($row = $res->fetch_assoc()) {
                $content = $row["contenuto"];
            }
            $res = $db->query("SELECT title FROM posts WHERE Id_post = $id_post;");
            $post_title = "";
            while ($row = $res->fetch_assoc()) {
                $post_title = $row["title"];
            }
            $query = "INSERT INTO notifications (Id_user, content) VALUES ($id_pubblisher, 'Il tuo commento: $content  è stato eliminato sotto il post: <a href=index.php?idPost=$id_post>$post_title</a>');";
            $db->query($query);
            $queryDes = "DELETE FROM comments WHERE Id_comment=$id_comment;";
            $db->query($queryDes);
        } else die();
        exit("window.location.href = 'index.php?idPost=$id_post';");

    }

    //scripts per seguire un utente
    if (isset($_POST['followUser']) && isset($_POST['id_user']) && isset($_POST['id_follower'])) {
        $followed = $_POST["id_user"];
        $follower = $_POST["id_follower"];
        $query = "INSERT INTO followers(Id_following,Id_user) VALUES (?,?);";
        $p = $db->prepare($query);
        $p->bind_param("ss", $followed, $follower);
        try {
            $p->execute();
            $username = "";
            $id_pubblisher = 0;
            $getUsername = "SELECT username FROM users WHERE Id_user = $follower;";
            $res = $db->query($getUsername);
            if ($res->num_rows >= 1) {
                while ($row = $res->fetch_assoc()) {
                    $username = $row["username"];
                }
                $not = "INSERT INTO notifications (Id_user, content)
VALUES ($followed, 'Sei stato seguito da <a href=userArea.php?id_User=$follower>$username </a>');";
                echo $not;
                $db->query($not);
            }
        } catch (Exception $e) {
            $query = "DELETE FROM followers WHERE Id_following = ? AND Id_user = ?;";
            $p = $db->prepare($query);
            $p->bind_param("ss", $followed, $follower);
            $p->execute();
            die();
        }
    }


    //Script per reportare un post
    if (isset($_POST["reportPost"]) && isset($_POST["id_post"]) && isset($_POST["reason"])) {
        $idP = $_POST["id_post"];
        $usId = $_SESSION["id_user"];
        $reason = htmlspecialchars($_POST["reason"], ENT_QUOTES, 'UTF-8');
        sanitize($db, $reason);
        $query = "INSERT INTO `reportedposts` (`Id_reporter`, `Id_repPost`, `reason`) VALUES ($usId, $idP, '$reason');";
        try {
            $db->query($query);
            echo "Report eseguito con successo!";
        } catch (Exception $e) {
            echo "Errore durante il report del post! Probabilmente il post è già stato segnalato da te.";
        }
        echo "<script>setTimeout(function(){ 
                window.location.href = 'index.php?idPost=$idP';
            }, 3000);  </script>";
        die();
    }

    //Script per reportare un utente
    if (isset($_POST["reportUser"]) && isset($_POST["id_user"]) && isset($_POST["reason"])) {
        $idUs = $_POST["id_user"];
        $repId = $_SESSION["id_user"];
        $reason = htmlspecialchars($_POST["reason"], ENT_QUOTES, 'UTF-8');
        sanitize($db, $reason);

        try {
            $query = "INSERT INTO reportedusers(Id_reporter,Id_repUs,reason) VALUES (?,?,?);";
            $prep = $db->prepare($query);
            $prep->bind_param("iis", $repId, $idUs, $reason);
            if (!$prep->execute()) echo "Report non eseguito!";
            else echo "Report eseguito con successo!";
        } catch (Exception $e) {
            echo "Errore!";
            echo "Probabilmente hai già reportato questo utente in passato";
        }
        echo "<script>setTimeout(function(){ 
                window.location.href = 'index.php?idPost=$idP';
            }, 3000);  </script>";
        die();
        $db->close();
        die();
    }
    //Visualizza i report degli utenti
    if (isset($_POST["viewRepUs"]) && isset($_POST["id_rep"]) && isset($_POST["mode"])) {
        $id_rep = $_POST["id_rep"];
        $mode = $_POST["mode"];

        if (!verAccount($db, $id_user, $role) || $role < 1) die();

        $nameQuery = "";
        if (!$mode) $nameQuery = "SELECT username FROM users WHERE Id_user = ?;";
        else $nameQuery = "SELECT title FROM posts WHERE Id_post = ?;";

        $prepName = $db->prepare($nameQuery);
        $prepName->bind_param("i", $id_rep);
        $prepName->execute();
        $prepName->bind_result($name);

        while ($prepName->fetch()) {
            echo "Report di: $name";
        }
        $query = "";
        if (!$mode) $query = "SELECT Id_reporter,createdAt,reason FROM reportedusers WHERE Id_repUs = ?;";
        else $query = "SELECT Id_reporter,createdAt,reason FROM reportedposts WHERE Id_repPost = ?;";
        $prepReasons = $db->prepare($query);
        $prepReasons->bind_param("i", $id_rep);
        $prepReasons->execute();
        $prepReasons->bind_result($Id_reporter, $datetime, $reason);

        while ($prepReasons->fetch()) {
            echo "<div>";
            echo "<i><a href='userArea.php?id_User=$Id_reporter'>Reporter</a></i>";
            echo "<i><p>$datetime</p></i>";
            echo "<i><p>$reason</p></i>";
            echo "</div>";
        }
        $db->close();
        die();
    }

    //Script per bannare un utente
    if ((isset($_POST["ban"]) || isset($_POST["banIp"])) && isset($_POST["id_user"]) && isset($_POST["reason"]) && isset($_POST["giorni"])) {
        $idToBan = $_POST["id_user"];
        $reason = $_POST["reason"];
        $giorni = $_POST["giorni"];
        if (empty($reason) || empty($giorni)) {
            exit("riempi i campi!");
        }
        if (!verAccount($db, $idUser, $role)) {
            $db->close();
            die();
        }
        if ($role < 1) {
            $db->close();
            die();
        }

        if (isset($_POST["banIp"])) {
            $reason .= " (IP BANNATO)";
        }
        $dataInizio = new DateTime('now');
        $dataFine = new DateTime('now');
        $dataFine = clone $dataInizio;
        $dataFine->modify('+' . $giorni . 'day');
        $dStart = $dataInizio->format('Y-m-d H:i:s');
        $dEnd = $dataFine->format('Y-m-d H:i:s');
        $query = "INSERT INTO banned_users(Id_banned,reason,bannedAt,expireAt) VALUES (?,?,?,?);";
        $prep = $db->prepare($query);
        $prep->bind_param("isss", $idToBan, $reason, $dStart, $dEnd);
        try {

            if (isset($_POST["banIp"])) {
                $queryGetIp = "SELECT Ip_address FROM sessions WHERE Id_user = ?;";
                $prepGetIp = $db->prepare($queryGetIp);
                $prepGetIp->bind_param("i", $idToBan);
                $prepGetIp->bind_result($ip);
                $prepGetIp->execute();
                $arr = array();
                while ($prepGetIp->fetch()) {
                    $arr[] = "INSERT INTO banned_ipaddresses(Id_banned,Ip_addressHash) VALUES ($idToBan,'$ip');";
                }
                foreach ($arr as $queryIp) {
                    $db->query($queryIp);
                }
            }
            $res = $prep->execute();
            if ($res) {
                $db->query("DELETE FROM sessions WHERE Id_user = $idToBan;");
                echo "Utente bannato con successo! da $dStart a $dEnd per $reason";
                $db->close();
                die();
            }
        } catch (Exception $e) {
            echo "Errore durante il ban dell'utente! Probabilmente l'utente è già bannato." . $e->getMessage();
            $db->close();
            die();
        }
    }

    //Script per unbannare
    if (isset($_POST["unban"]) && isset($_POST["id_user"])) {
        $idToUnban = $_POST["id_user"];
        echo "prova";
        if (!verAccount($db, $idUser, $role) || $role < 1) die();
        $query = "delete from banned_users where Id_banned = ?;";
        $prep = $db->prepare($query);
        $prep->bind_param("i", $idToUnban);
        $prep->execute();
        $query = "UPDATE ban_appeals SET actTaked = 1 WHERE Id_user = ?;";
        $prep = $db->prepare($query);
        $prep->bind_param("i", $idToUnban);
        $prep->execute();
        die();
    }

    //Script per downgradare un utente
    if (isset($_POST["downGradeUs"]) && isset($_POST["id_user"])) {
        $idToDGrade = $_POST["id_user"];
        echo "prova";
        if (!verAccount($db, $idUser, $role) || $role < 3) exit("NOT ENOUGH PERMISSIONS");
        $queryTakeRole = "SELECT role FROM users WHERE Id_user = $idToDGrade;";
        $res = $db->query($queryTakeRole);
        $currRole = 0;
        while ($row = $res->fetch_assoc()) {
            $currRole = $row["role"];
        }
        $currRole--;
        if ($currRole < 0) $currRole = 0;
        $query = "UPDATE users SET role = $currRole WHERE Id_user = $idToDGrade;";
        if (!$db->query($query)) {
            echo "Down grade non avvenuto!";
        }
    }

    $db->close();
}

//header("Location: index.php");
?>