<?php
/*
Questa pagina viene caricata SOLO se l'utente è bannato, permette di
creare delle richieste di sban che possono essere poi visualizzate
dai moderatori nella moderation area.
*/

session_start();
include("scripts/verScript.php");
include("scripts/db_connection.php");
include("scripts/post_functions.php");
$logged = false;
$id_user = 0;
$logged = verAccount($db, $id_user, $role, false);
if (!checkIfUserIpBanned($db, $_SERVER['REMOTE_ADDR'])) {
    if (!$logged || !isUserBanned($db, $id_user, $t, $t)) {
        header("Location: index.php");
    }
}else{
    ?>
        <h1>EHH VOLEVI</h1>
        <p> GUARDA CHE FACCIA, GUARDA CHE FACCIA NON SE LO ASPETTAVA</p>
        <p>MAUUNNNAA, PER ESSERE BANNATI PER IP VUOL DIRE PROPRIO CHE TE LO MERITI</p>
        <p>È COME UNA CONDANNA A MORTE, LE PERSONE NON SI ALZANO DALLA SEDIA ELETTRICA</p>
        <p>Comunque tranquillo, il sito si resetterà presto e quindi anche il tuo ban</p>
    <?php
    die();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ban Appeal</title>
    <link rel="stylesheet" href="structure/specific_sections.css">
</head>

<body>
    <?php include("structure/header.php"); ?>
    <main>
        <div class="banAppeal">
            <h1>Credi che il tuo ban sia infondato?</h1>
            <h2>Fai ricorso in questa sezione fornendo il motivo del ban, e con una dettagliata spiegazione il perché pensi che sia infondato</h2>
            <form action="" method="POST">
                <i><textarea name="ban_reason" rows="10" cols="50" placeholder="Inserisci qui il motivo del ban"></textarea><br>
                    <textarea name="appeal_reason" rows="10" cols="50" placeholder="Inserisci il testo del ricorso, spiega in dettaglio"></textarea><br></i>
                <input type="submit" name="submit_appeal" value="Invia Ricorso">
            </form>
        </div>
    </main>
    <script src="structure\darkmode.js"></script>
</body>

</html>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["submit_appeal"]) && !empty($_POST["appeal_reason"]) && !empty($_POST["ban_reason"])) {
        $ban_reason = $_POST["ban_reason"];
        $appeal_reason = $_POST["appeal_reason"];
        sanitize($db, $ban_reason);
        sanitize($db, $appeal_reason);
        $query = "SELECT Id_user,appealDateTime FROM ban_appeals WHERE Id_user = $id_user AND DATEDIFF(NOW(), appealDateTime) < 30;";
        $db->query($query);
        if ($db->affected_rows >= 2) {
            exit("Non puoi fare 2 ricorsi in meno di un mese!");
        }

        $query = "INSERT INTO ban_appeals(Id_user, appealText, ban_reason) VALUES (?, ?, ?);";
        $stmt = $db->prepare($query);
        $stmt->bind_param("iss", $id_user, $appeal_reason, $ban_reason);
        if ($stmt->execute()) {
            echo "<p>Ricorso inviato con successo!</p>";
        } else {
            echo "<p>Errore nell'invio del ricorso. Riprova più tardi.</p>";
        }
    } else {
        echo "<p>Per favore, inserisci un motivo valido per il ricorso.</p>";
    }
}

?>