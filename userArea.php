
<?php
session_start();
include("scripts/verScript.php");
include("scripts/db_connection.php");
$logged = verAccount($db, $id_user, $role);
include("scripts/post_functions.php");
if (!$logged && !isset($_REQUEST['id_User'])) header("Location: index.php");
$or_Id_user = $id_user;

/* Questa è la pagina atta a visualizzare il singolo utente.
    Quindi: descrizione, post, commenti ecc... 
    
    Se si visualizza un utente diverso da se stessi, 
    si avranno a disposizione i bottoni per seguire e reportare l'utente,
    altrimenti si avrà il bottone per accedere alle impostazioni 
    del proprio profilo.
    
    */

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets\browser_head_images\main_logo.png" type="image/png">
    <title>Sezione profilo</title>
    <link rel="stylesheet" href="structure/style.css">
    
</head>
 
<body>

    <?php
    include("structure/header.php");
    $followers;
    $seeMode = false;
    $isIdUsSetted = isset($_REQUEST['id_User']);
    if ($isIdUsSetted && $id_user != $_REQUEST['id_User']) {
        $id_user = $_REQUEST['id_User'];
        $seeMode = true;
    }

    $queryUsInfo = "SELECT users.Id_user,username,pic_path,description FROM users WHERE users.Id_user = ?;";
    $prep1 = $db->prepare($queryUsInfo);
    $prep1->bind_param("i", $id_user);
    try{$prep1->execute();}catch(Exception $e){echo "Errore in una query!";}
    $prep1->store_result();
    $prep1->bind_result($id_user, $username, $pic_path, $desc);
    if ($prep1->num_rows <= 0) exit("Utente inesistente!");
    while ($prep1->fetch()) {
    }

    $numFolQuery = "SELECT COUNT(Id_following) as fol_count FROM followers WHERE Id_following = ?;";
    $prep2 = $db->prepare($numFolQuery);
    $prep2->bind_param("i", $id_user);
    try{$prep2->execute();} catch(Exception $e){echo "Errore in una query!";}
    $prep2->bind_result($fol_count);
    while ($prep2->fetch()) {
        $followers = $fol_count;
    }



    ?>
    <script src='app.js'></script>
    <script src="structure\darkmode.js"></script>
    <main>
        <div class="userArea">
            <div class="topSection">
                <div class="userBasicInfo">
                    <?php
                    echo "<img src='$pic_path' alt=''>";
                    echo "<h1>$username</h1>";
                    echo "<h1>Followers: $followers</h1>"
                    ?>
                </div>
                <div class="userDetailedInfo">
                    <i><p><?php if(!is_null($desc)) echo "<p>$desc</p>"; else echo "Nessuna descrizione"; ?></p></i>
                    <div class="actionToUser">
                        <?php if (!$seeMode) { ?>
                            <a href="settings.php">Impostazioni</a>
                        <?php } else {
                            echo "<button onclick='follow($id_user,$or_Id_user)'> SEGUI </button>";
                            echo "<button onclick='showReportUs(1,$id_user)'>REPORT</button>";
                            if ($role > 0) {
                                echo "<button onclick='showBanSec()'>Ban</button>";
                            }
                        } ?>
                    </div>
                </div>
   
            

            </div>
            <div class="bottomSection">
                <div class="UsPosts posts" id="userPosts">
                    <h2>Post dell'utente:</h2>
                    <?php if (!$seeMode){ ?> <a class='button' id="creaPost" href="post.php">Crea un post!</a><?php } ?>
                    <?php getPostByUser($id_user, $db, false, !$seeMode, $role) ?>
                </div>
                <div class="recentComments">
                    <h2>Commenti dell'utente:</h2>
                    <?php getCommentsByUser($id_user, $db); ?>
                </div>
            </div>
        </div>
        </div>
        <div style="display:none" id="repDiv" class="centerPosDiv">
            <form action="functions.php" method="POST">
                <input type="text" name="reason" value="motivo report">
                <input type="hidden" name="id_user" id="id_post" value="<?php echo $id_user; ?>">
                <input type="submit" name="reportUser">
            </form>
        </div>
        <?php if ($role > 0) { ?>
            <div style="display: none;" id="banSec">
                <form action="functions.php" method="POST">
                    <input type="text" name="reason" value="motivo report">
                    <input type="hidden" name="id_user" id="id_post" value="<?php echo $id_user; ?>">
                    <input type="number" name="giorni" placeholder="numero giorni">
                    <input type="submit" name="ban">
                    <br>
                    <br>
                    <br>
                    <input type="submit" name="banIp" value="Ban Per IP">
                </form>
            </div>
        <?php } ?>
    </main>
    
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.documentElement.scrollTop = 0;
        });


        requiredAmount = 300;
        l = 10;
        document.addEventListener("scroll", () => {
            const scrollAmount = window.scrollY || document.documentElement.scrollTop;
            if (scrollAmount >= requiredAmount) {
                loadNewPosts(10)
                requiredAmount += 3000;
            }
        });

        function loadNewPosts(howM) {
            postSec = document.getElementById("userPosts");
            fetch('functions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        loadNewUserPosts: "",
                        id_user: <?php echo $id_user; ?>,
                        howMany: howM,
                        offset: l
                    })
                })
                .then(response => response.text())
                .then(data => {
                    postSec.innerHTML += data; // Append posts instead of replacing
                    l += 10;
                })
                .catch(error => console.error("Error:", error));
        }
    </script>
    
    
</body>

</html>