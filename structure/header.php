<?php
$ver = false;
$not = false;
if ((isset($logged) && isset($_SESSION["id_user"])) && $logged) {
    $ver = true;
    $id_user = $_SESSION["id_user"];
    $query = "SELECT COUNT(Id_notification) AS c FROM notifications WHERE Id_user = ? AND readed = 0";
    try {
        if (isset($db) && !$db->connect_error) {
            $c = 0;
            $prep = $db->prepare($query);
            $prep->bind_param("i", $id_user);
            $prep->execute();
            $prep->store_result();
            $prep->bind_result($c);
            while ($prep->fetch()) {
            }
            if ($c > 0) {
                $not = true;
            }
            $prep->close();
        }
    } catch (Exception $e) {
    }
}

?>

<!--
L'header è un file a parte, perché deve essere caricato per ogni pagina del sito.
Quindi conviene creare tutto il codice qui ed includerlo con php negli altri
-->


<head>
    <link rel="stylesheet" href="structure/style.css">
</head>
<header>
    <nav>
        <i><a href="index.php"><img src="assets\icons\logo.png" class="logo" alt=""></a></i>
        <i class="searchBar">
            <form action="search.php" method="GET">
                <i><input type="search" name="searchText" id="sText" placeholder="Cerca..."></i>
                <i> <label for="inpSearch" id="sSend"> <img id='searchButton' src="assets\icons\search.png" alt=""> </label> <input style="display:none;" id="inpSearch" type="submit" name="search" value=""></i>
                <i></i>
                <i>
                    <select type="search" name="filter" id="sFilter">
                        <option value="title">per titolo</option>
                        <option value="tags">per tag</option>
                        <option value="users">utente</option>
                    </select>
                </i>
            </form>
        </i>
        <i style="text-align: center;"><?php if ($ver) {
                if (!$not) { ?> <a href="notificationCenter.php"><img id="notificationBell" src="assets\icons\bell.png" alt=""></a><?php } else { ?> <a href="notificationCenter.php"><img id="notificationBell" src="assets\icons\bell_not.png" alt=""></a> <?php }
            }else{
                echo "<button onclick='toggleDarkMode()' id='darkModeBut'>Abilita DarkMode</button>";
            } ?></i>
        <i class="userSec">
            <?php global $logged;
            if (!$logged) { ?>
                <i style="grid-column: 1/3;"><a href="login.php">Registrati/accedi!</a></i> <i></i>
            <?php } else { ?>
                <i><a href='userArea.php'><img id="immagineUtente" src="<?php echo $_SESSION["pic_path"] ?>" alt=""></a></i>
                <i>
                    <p><?php echo $_SESSION["username"]; ?></p>
                </i>
            <?php } ?>
        </i>
    </nav>
</header>