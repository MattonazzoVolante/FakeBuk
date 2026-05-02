<?php
session_start();
include("../scripts/verScript.php");
include("../scripts/db_connection.php");
include("../scripts/post_functions.php");

$logged = verAccount($db, $id_user, $role);
$idPost = null;
if (!$logged) header("Location: ..\index.php");
if ($role <= 1) header("Location: ..\index.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Announcement </title>
    <link rel="icon" href="assets\browser_head_images\ann_logo.png" type="image/png">
    <link rel="stylesheet" href="../structure/style.css">
</head>

<body>
    <?php include("headerMods.php"); ?>
    <main>
        <div class="createAnnContainer">
            <h2>Attenzione!!!!</h2>
            <h2> Tutti gli annunci che crei saranno visibili da tutti gli utenti e fruitori del sito</h2>
            <h2> Fai molta attenzione a ciò che scrivi e pubblichi</h2>
            <div class="createAnnouncement">
                <form action="" method="POST">
                    <input type="text" name="title" id="" placeholder="Titolo Annuncio" required>
                    <textarea name="content" id="" cols="30" rows="10" placeholder="Contenuto"></textarea>
                    <input type="submit" value="Crea Annuncio" name="createAnnouncement">
                </form>
            </div>
        </div>
    </main>
</body>

</html>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["createAnnouncement"])) {
    $title = $_POST["title"];
    $content = $_POST["content"];
    if (empty($title) || empty($content)) {
        echo "<script>alert('Compila tutti i campi');</script>";
    } else {
        $queryInsertAnn = "INSERT INTO announcements (title, content) VALUES (?, ?)";
        $stmt = $db->prepare($queryInsertAnn);
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        sanitize($db, $title);
        sanitize($db, $content);
        $stmt->bind_param("ss", $title, $content);
        if ($stmt->execute()) {
            echo "<script>alert('Annuncio creato con successo');</script>";
        } else {
            echo "<script>alert('Errore nella creazione dell\'annuncio');</script>";
        }
        $stmt->close();
    }
}
$db->close();

?>