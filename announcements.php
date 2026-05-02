<?php
session_start();
include("scripts/verScript.php");
include("scripts/db_connection.php");
include("scripts/post_functions.php");
$logged = verAccount($db, $id_user, $role);
$idAnn = null;
if (isset($_GET["IdAnn"])) {
    $idAnn = $_GET["IdAnn"];
}
?>

<!--
Gli annunci sono una feature che è stata aggiunga all'ultimo.
Permette di visualizzare dei post generali visualizzabili da tutti.
Quindi non si sa l'autore del post.

Per creare annunci bisogna essere admin.
-->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets\browser_head_images\ann_logo.png" type="image/png">

    <title>Sezione annunci</title>
    <link rel="stylesheet" href="structure/style.css">
</head>

<body>
    <?php include("structure/header.php"); ?>
    
    <div class="announcementsContainer">
        <?php
        $prepAnn = null;
        $queryAnn = "SELECT * FROM announcements ";
        if ($idAnn != null) {
            $queryAnn .= " WHERE Id_announcement = ?;";
            $prepAnn = $db->prepare($queryAnn);
            $prepAnn->bind_param("i", $idAnn);
            $prepAnn->bind_result($id, $title, $content, $date);
            $prepAnn->execute();
        } else {
            echo "
                <h2>Benvenuto nella sezione annunci! </h2>
    <p>Qui potrai vedere tutti gli annunci riguardanti il sito web, compresi gli aggiornamenti e le novità</p>
            ";
            $queryAnn .= "WHERE 1=1 ";
            $queryAnn .= "ORDER BY pubblication_date DESC";

            $prepAnn = $db->prepare($queryAnn);
            $prepAnn->bind_result($id, $title, $content, $date);
            $prepAnn->execute();
        }
        echo "<div class='announcements'>";
        while ($prepAnn->fetch()) {
            echo "<div>";
            echo "<a href='announcements.php?IdAnn=" . $id . "'>";
            echo "<h3>" . $title . "</h3>";
            echo "<p>" . $content . "</p>";
            echo "<small>Pubblicato il " . htmlspecialchars($date) . "</small>";
            echo "</a>";
            echo "</div>";
        }
        echo "</div>";
        ?>
    </div>
    <script src="structure/darkmode.js"></script>
</body>

</html>