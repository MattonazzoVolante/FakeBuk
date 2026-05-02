<?php
    include("../scripts/verScript.php");
    include ("../scripts/db_connection.php");
    include("../scripts/post_functions.php");
    session_start();
    $logged = verAccount($db,$id_user,$role);
    $idPost = null;
    if(!$logged) header("Location: ..\index.php");
    if($role <=1) header("Location: ..\index.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets\browser_head_images\admin_logo.png" type="image/png">
    <title>Admin area</title>
    <link rel="stylesheet" href="../structure/style.css">
</head>



<body>
    <?php include("headerMods.php"); ?>
    <div>
        <div style="text-align: center;">
            <div class='button' style="background-color: #003d6f; color:white">
                <a href="createAnnouncement.php">Crea un annuncio</a>
                <a href="modsArea.php"><p>Ritorna nell'area moderatore</p></a>
            </div>
        </div>
        <div class="buttonArea">
            <div>
                <button id="buttonRep" onclick="changeMod(1)">Lista mod</button>
                <button id="buttonUser" onclick="changeMod(2)">Lista capo mod</button>
                <button id="buttonBanned" onclick="changeMod(3)">Lista feedbacks</button>
            </div>
        </div>
        <div class="modAreaBase modAreaPost" id="liModArea">
            
            <div class="listModArea">
                <?php
                    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
                    $encryptedData = openssl_encrypt($_SESSION["ses_token"], "aes-256-cbc", getenv("ENCRYPTION_KEY"), 0, $iv);
                    $p = base64_encode($iv.$encryptedData);
                    $crypTok = "'$p'";


                    $queryCountMods = "SELECT * FROM (SELECT COUNT(u.Id_user) AS num_mod FROM users as u WHERE role = 1) as T;";
                    $queryModInfo = "SELECT u.Id_user as id,username,name,surname,email,role FROM users AS u WHERE role = 1";
                    
                    $resultC = $db->query($queryCountMods);
                    $countMod=0;
                    while($row = $resultC->fetch_assoc()){$countMod = $row["num_mod"];}
                    $resultMi = $db->query($queryModInfo);
                    while($row = $resultMi->fetch_assoc()){
                        $id = $row["id"];
                        $username = $row["username"];
                        $name = $row["name"];
                        $surname = $row["surname"];
                        $email = $row["email"];
                        $role = $row["role"];

                        echo "<div>";
                        echo "<i><a href='userArea.php?id_User=$id'>$username</a></i>"; 
                        echo "<i>". $username ."</i>";
                        echo "<i><p>$name</p></i>";
                        echo "<i><p>$surname</p></i>";
                        echo "<i><p>$email</p></i>";
                        echo "<i><p>$role</p></i>";
                        echo "<i><button onclick='downGradeUser($id)'>Downgrade</button></i>";
                        echo "</div>";
                    }

                ?>
            </div>
            <div class="repInfo" id="repPostInfo">

            </div>
        </div>
        <div class="adminAreaBase adminAreaUser" id="CMArea" style="display:none">
            <div class="listCapoModArea">
                <?php
                    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
                    $encryptedData = openssl_encrypt($_SESSION["ses_token"], "aes-256-cbc", getenv("ENCRYPTION_KEY"), 0, $iv);
                    $p = base64_encode($iv.$encryptedData);
                    $crypTok = "'$p'";


                    $queryCountCMods = "SELECT * FROM (SELECT COUNT(u.Id_user) AS num_mod FROM users as u WHERE role = 2) as T;";
                    $queryCModInfo = "SELECT u.Id_user as id,username,name,surname,email,role FROM users AS u WHERE role = 2";
                    
                    $resultC = $db->query($queryCountCMods);
                    $countMod=0;
                    while($row = $resultC->fetch_assoc()){$countCMod = $row["num_mod"];}
                    $resultMi = $db->query($queryCModInfo);
                    while($row = $resultMi->fetch_assoc()){
                        $id = $row["id"];
                        $username = $row["username"];
                        $name = $row["name"];
                        $surname = $row["surname"];
                        $email = $row["email"];
                        $role = $row["role"];

                        echo "<div>";
                        echo "<i><a href='userArea.php?id_User=$id'>$username</a></i>"; 
                        echo "<i>". $username ."</i>";
                        echo "<i><p>$name</p></i>";
                        echo "<i><p>$surname</p></i>";
                        echo "<i><p>$email</p></i>";
                        echo "<i><p>$role</p></i>";
                        echo "<i><button onclick='downGradeUser($id)'>Downgrade</button></i>";
                        echo "</div>";
                    }

                ?>
            </div>
            <div class="repInfo" id="repInfo">

            </div>
        </div>
        <div class="adminAreaBase feedbackArea" id="Feedback" style="display:none">
            <div class="listFeedback">
                <?php
                    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
                    $encryptedData = openssl_encrypt($_SESSION["ses_token"], "aes-256-cbc", getenv("ENCRYPTION_KEY"), 0, $iv);
                    $p = base64_encode($iv.$encryptedData);
                    $crypTok = "'$p'";
                    $id = 0;

                    $queryTakeFeedbacks = "SELECT Id_feedback,Id_user,message FROM feedbacks;";
                    $takeUsName = "SELECT username FROM users WHERE Id_user = ?;";
                    $prep = $db->prepare($takeUsName);
                    $prep->bind_param("i",$id_user);
                    $prep->bind_result($username);
                    
                    $result = $db->query($queryTakeFeedbacks);

                    while($row = $result->fetch_assoc()){
                        $id = $row["Id_feedback"];
                        $id_user = $row["Id_user"];
                        $message = $row["message"];
                        $prep->execute();
                        while($prep->fetch());
                        echo "<div>";
                        echo "<i><a href='userArea.php?id_User=$id_user'>$username</a></i>"; 
                        echo "<i><p>$message</p></i>";
                        echo "</div>";
                    }

                ?>
            </div>
            <div class="repInfo" id="repInfo">

            </div>
        </div>
    </div>
    <script src="app.js"></script>
    <script>
        function changeMod(s){
            let firstP = document.getElementById("liModArea");
            let secondP = document.getElementById("CMArea");
            let thirdP = document.getElementById("Feedback");
            let button1 = document.getElementById("buttonRep");
            let button2 = document.getElementById("buttonUser");
            let button3 = document.getElementById("buttonBanned");
            firstP.style.display = (s==1)? "grid" : "none";
            secondP.style.display = (s==2)? "grid" : "none";
            thirdP.style.display = (s==3)? "grid" : "none";

            button1.style.backgroundColor = (s==1)? "white" : "inherit";
            button2.style.backgroundColor = (s==2)? "white" : "inherit";
            button3.style.backgroundColor = (s==3)? "white" : "inherit";
        }


        function getRepInfo(CryptTok,id_repUs,user){
            /*Se mod = 0 -> vedere info report sui Post
              Se mod = 1 -> vedere info report sui User
            */
            let divId = "repInfo";
            if(user) divId = "repPostInfo";
            fetch('functions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ viewRepUs:"" , modSession:CryptTok, id_rep:id_repUs, mode: user })
            })
            .then(response => response.text())
            .then(data => {
                repInfo = document.getElementById(divId);
                repInfo.innerHTML = data;
            })
            .catch(error => console.error("Error:", error));
        }
    </script>
</body>
</html>