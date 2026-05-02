<?php
session_start();
include("scripts/verScript.php");
include("scripts/db_connection.php");
include("scripts/post_functions.php");

$logged = verAccount($db, $id_user, $role);
$idPost = null;
if (!$logged) exit("You can't leave a feedback without loging in");

/*
        Script molto semplice che permette all'utente di lasciare un feedback
        riguardo il sito web
*/
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lascia un feedback</title>
        <link rel="icon" href="assets\browser_head_images\main_logo.png" type="image/png">

    <link rel="stylesheet" href="structure\specific_sections.css">
</head>

<body>
    <?php include("structure\header.php"); ?>
    <main>
        <div class="feedbackDisclaimer">
            <h1>Ciao, se ti senti di voler lasciare un feedback al sito, sentiti libero di farlo quà sotto:</h1>
        </div>
        <div class="feedbackForm">
            <form action="" method="get">
                <i><textarea name="message" id="" cols="30" rows="10" placeholder=" feedback"></textarea></i>
                <i><input id="submit" type="submit" name="sendFeedback" value="send feedback"></i>
            </form>
        </div>
    </main>
    <script src="structure\darkmode.js"></script>
</body>

</html>

<?php
//Lo script semplicemente si segna l'id dell'utente e invia il feedback lasciato
if (isset($_REQUEST["sendFeedback"])) {
    $textFeedback = $_REQUEST["message"];
    $query = "INSERT INTO feedbacks(Id_user,message) VALUES (?,?);";
    $prep = $db->prepare($query);
    sanitize($db,$textFeedback);
    $prep->bind_param("is", $id_user, $textFeedback);
    if (!$prep->execute()) echo "Errore nell'invio del feedback";
}
?>