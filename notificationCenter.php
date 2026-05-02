<?php
session_start();
include("scripts/verScript.php");
include("scripts/db_connection.php");
include("scripts/post_functions.php");

$logged = verAccount($db, $id_user, $role);
if (!$logged) header("Location: index.php");



/*
Questa è il notification center, mostra le notifiche dell'utente
*/
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification</title>
    <link rel="stylesheet" href="structure/specific_sections.css">
    <link rel="icon" href="assets\browser_head_images\bell_logo.png" type="image/png">
</head>
<style>
    a {
        color: #003d6f;
        font: bold;
        font-size: 23px;

    }

    a:hover {
        color: rgb(0, 95, 173);
    }
</style>

<body>
    <?php include("structure/header.php"); ?>
    <div>
        <h2>Clicca sulla notifica per segnarla come già letta</h2>
    </div>
    <div>
        <h2>Azioni:</h2>
        <form action="" method="POST">
            <input type="submit" name="readsAll" value="Leggi tutto">
            <input type="submit" name="deleteAll" value="Cancella tutto">
        </form>
    </div>
    <div id="notification"> <!-- Area che mostra le notifiche -->
        <?php
        $query = "SELECT Id_notification,Id_user,createdAt,content,readed FROM notifications WHERE Id_user = $id_user ORDER BY createdAt DESC;";
        $res = $db->query($query);
        $c = 0;

        while ($row = $res->fetch_assoc()) {
            $c++;
            $idNot = $row['Id_notification'];
            $style = "notfNotRead";
            if ($row["readed"] == 1) $style = "notFRead";
            echo "<div>";
            echo "<form method='POST'>";
            echo "<label for='sub$c'>";
            echo "<div class='notBase $style'>";
            echo "<i><p>" . $row["createdAt"] . "</p></i>";
            echo "<i><p>" . $row["content"] . "</p></i>";
            echo "</div></label>";
            echo "<input style='display: none;' id='sub$c' name='read' type='submit' value='$idNot'>";
            echo "</form>";
            echo "</div>";
        }
        ?>
    </div>

    <script>
        window.onload = function() {
            window.scrollTo(0, localStorage.getItem("scrollPosition") || 0);
        };

        window.onbeforeunload = function() {
            localStorage.setItem("scrollPosition", window.scrollY);
        };
    </script>
    <script src="structure\darkmode.js"></script>
</body>

</html>

<?php
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST["readsAll"])) {
        $id_user = $_SESSION["id_user"];
        $query = "UPDATE notifications SET readed = 1 WHERE Id_user = ?";
        $prep = $db->prepare($query);
        $prep->bind_param("i", $id_user);
        $prep->execute();
        $prep->close();
    }

    if(isset($_POST["deleteAll"])){
        $id_user = $_SESSION["id_user"];
        $query = "DELETE FROM notifications WHERE Id_user = ?";
        $prep = $db->prepare($query);
        $prep->bind_param("i", $id_user);
        $prep->execute();
        $prep->close();
    }

    if (isset($_POST["read"])) {
        $read = $_POST["read"];
        $query = "UPDATE notifications SET readed = 1 WHERE Id_notification = ?";
        $prep = $db->prepare($query);
        $prep->bind_param("i", $read);
        $prep->execute();
        $prep->close();
    }
    echo "<script>window.location.href = 'notificationCenter.php';</script>";
}
?>