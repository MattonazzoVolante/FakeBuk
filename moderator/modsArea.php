<?php
    session_start();
    include("../scripts/verScript.php");
    include ("../scripts/db_connection.php");
    include("../scripts/post_functions.php");   
    $logged = verAccount($db,$id_user,$role);
    $idPost = null;
    if(!$logged) header("Location: ../index.php");
    if($role <=0) header("Location: ../index.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="assets\browser_head_images\mod_logo.png" type="image/png">
    <title>Mod Area</title>
    <link rel="stylesheet" href="../structure/style.css">
</head>


<body>
    <?php include("headerMods.php"); ?>
    <div>
        <div style="text-align: center;">
            <div class='button' style="background-color: #003d6f; color:white">
                <a href="../index.php"><p>Ritorna alla main page</p></a>
            </div>
            <?php if($role > 2){ ?>
                <div class='button' style="background-color: #e40800; color:white">
                    <a href="adminArea.php"><p>Vai nell'area admin</p></a>
                </div>
            <?php } ?>
        </div>
        <div class="buttonArea">
            <div>
                <button id="buttonRep" onclick="changeMod(1)">Vedi Report</button>
                <button id="buttonUser" onclick="changeMod(2)">Vedi Utenti reportati</button>
                <button id="buttonBanned" onclick="changeMod(3)">Vedi Utenti Bannati</button>
                <button id="buttonAppeal" onclick="changeMod(4)">Vedi Appeals</button>
            </div>
        </div>
        <div class="modAreaBase modAreaPost" id="liArea">
            
            <div class="listArea">
                <?php
                    /*$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
                    $encryptedData = openssl_encrypt($_SESSION["ses_token"], "aes-256-cbc", getenv("ENCRYPTION_KEY"), 0, $iv);
                    $p = base64_encode($iv.$encryptedData);
                    $crypTok = "'$p'";*/


                    $query = "SELECT * FROM (SELECT Id_repPost,COUNT(Id_repPost) AS num_rep FROM reportedposts WHERE DATEDIFF(createdAt,CURRENT_TIMESTAMP) < 360 GROUP BY Id_repPost) as T ORDER BY num_rep LIMIT 20;";
                    $queryGetUser = "SELECT Id_post,title,posts.description,pubblication_datetime,tags,Id_user,username FROM posts INNER JOIN users ON posts.Id_pubblisher = Id_user WHERE Id_post = ?";
                    $prep = $db->prepare($queryGetUser);
                    $prep->bind_param("i",$idUs);
                    $prep->bind_result($idPost,$title,$description,$data,$tags,$idPub,$nomPub);
                    $result = $db->query($query);
                    while($row = $result->fetch_assoc()){
                        $idUs = $row["Id_repPost"];
                        $getUs = $prep->execute();
                        while($prep->fetch()){}
                        echo "<div>";
                        echo "<button class='gridButton' onclick=getRepInfo($idPost,1) >";
                        echo "<i><a href='index.php?idPost=$idPost'>$title</a></i>"; 
                        echo "<i>". $row["num_rep"] ."</i>";
                        echo "<i><p>$description</p></i>";
                        echo "<i><p>$data</p></i>";
                        echo "<i><p>$tags</p></i>";
                        echo "<i><a href='../userArea.php?id_User = $idPub' >$nomPub</a></i>";
                        echo "<i><button onclick=deletePost($idPost)>Delete Post</button></i>";
                        echo "</button>";
                        echo "</div>";
                    }

                ?>
            </div>
            <div class="repInfo" id="repPostInfo">

            </div>
        </div>
        <div class="modAreaBase modAreaUser" id="usArea" style="display:none">
            <div class="listArea">
                <?php
                    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
                    $encryptedData = openssl_encrypt($_SESSION["ses_token"], "aes-256-cbc", getenv("ENCRYPTION_KEY"), 0, $iv);
                    $p = base64_encode($iv.$encryptedData);
                    $crypTok = "'$p'";


                    $query = "SELECT * FROM (SELECT Id_repUs,COUNT(Id_repUs) AS num_rep FROM reportedusers WHERE  DATEDIFF(createdAt,CURRENT_TIMESTAMP) < 360 GROUP BY Id_repUs) as T ORDER BY num_rep LIMIT 20;";
                    $queryGetUser = "SELECT Id_user,username,name,surname,email FROM users WHERE Id_user = ?";
                    $prep = $db->prepare($queryGetUser);
                    $prep->bind_param("i",$idUs);
                    $prep->bind_result($id,$userN,$name,$surname,$email);
                    $result = $db->query($query);
                    while($row = $result->fetch_assoc()){
                        $idUs = $row["Id_repUs"];
                        $getUs = $prep->execute();
                        while($prep->fetch()){}
                        echo "<div>";
                        echo "<button class='gridButton' onclick=getRepInfo($idUs,0) >";
                        echo "<i><a href='userArea.php?id_User=$idUs'>$userN</a></i>"; 
                        echo "<i>". $row["num_rep"] ."</i>";
                        echo "<i><p>$name</p></i>";
                        echo "<i><p>$surname</p></i>";
                        echo "<i><p>$email</p></i>";
                        echo "</button>";
                        echo "</div>";
                    }

                ?>
            </div>
            <div class="repInfo" id="repInfo">
                    
            </div>
        </div>
        <div class="modAreaBase modAreaBan" id="banArea" style="display:none">
             <?php
                    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
                    $encryptedData = openssl_encrypt($_SESSION["ses_token"], "aes-256-cbc", getenv("ENCRYPTION_KEY"), 0, $iv);
                    $p = base64_encode($iv.$encryptedData);
                    $crypTok = "'$p'";


                    $query = "SELECT Id_banned,username,reason,bannedAt,expireAt FROM `banned_users` INNER JOIN users ON Id_user = Id_banned WHERE expireAt >CURRENT_DATE;";
                    $result = $db->query($query);
                    while($row = $result->fetch_assoc()){
                        $idBanned = $row["Id_banned"];
                        $username = $row["username"];
                        $reason = $row["reason"];
                        $bannedAt = $row["bannedAt"];
                        $expireAt = $row["expireAt"];
                        echo "<div>";
                        echo "<i><a href='../userArea.php?id_User=$idBanned'>$username</a></i>"; 
                        echo "<i><p>$reason</p></i>";
                        echo "<i><p>$bannedAt</p></i>";
                        echo "<i><p>$expireAt</p></i>";
                        echo "<i><button onclick='unBanUser($idBanned)'>UNBAN</button></i>";
                        echo "</button>";
                        echo "</div>";
                    }

                ?>    
        </div>
        <div class="modAreaBase modAreaAppeal" id="appealArea" style="display:none">
                <?php
                    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
                    $encryptedData = openssl_encrypt($_SESSION["ses_token"], "aes-256-cbc", getenv("ENCRYPTION_KEY"), 0, $iv);
                    $p = base64_encode($iv.$encryptedData);
                    $crypTok = "'$p'";


                    $query = "SELECT u.Id_user,u.username,appealText,appealDateTime,ban_reason FROM ban_appeals INNER JOIN users AS u ON u.Id_user = ban_appeals.Id_user INNER JOIN banned_users AS bu ON bu.Id_Banned = ban_appeals.Id_user WHERE actTaked = 0;";

                    $result = $db->query($query);
                    while($row = $result->fetch_assoc()){
                        $idBanned = $row["Id_user"];
                        $username = $row["username"];
                        $appeal = $row["appealText"];
                        $appealAt = $row["appealDateTime"];
                        $banReason = $row["ban_reason"];
                        echo "<div>";
                        echo "<i><a href='../userArea.php?id_User=$idBanned'>$username</a></i>";
                        echo "<i><p>Nome: $username</p></i>";
                        echo "<i><p>Motivo: $banReason</p></i>";
                        echo "<i><p>Appeal: $appeal</p></i>";
                        echo "<i><p>Data: $appealAt</p></i>";
                        echo "<i><button onclick='unBanUser($idBanned)'>UNBAN</button></i>";
                        echo "</button>";
                        echo "</div>";
                    }

                ?>     
        </div>
    </div>
    <script src="..\app.js"></script>
    <script>
        function changeMod(s){
            let firstP = document.getElementById("liArea");
            let secondP = document.getElementById("usArea");
            let thirdP = document.getElementById("banArea");
            let fourthP = document.getElementById("appealArea");
            let button1 = document.getElementById("buttonRep");
            let button2 = document.getElementById("buttonUser");
            let button3 = document.getElementById("buttonBanned");
            let button4 = document.getElementById("buttonAppeal");
            firstP.style.display = (s==1)? "grid" : "none";
            secondP.style.display = (s==2)? "grid" : "none";
            thirdP.style.display = (s==3)? "grid" : "none";
            fourthP.style.display = (s==4)? "grid" : "none";

            button1.style.backgroundColor = (s==1)? "white" : "inherit";
            button2.style.backgroundColor = (s==2)? "white" : "inherit";
            button3.style.backgroundColor = (s==3)? "white" : "inherit";
            button4.style.backgroundColor = (s==4)? "white" : "inherit";
        }


        function getRepInfo(id_repUs,user){
            /*Se mod = 0 -> vedere info report sui Post
              Se mod = 1 -> vedere info report sui User
            */
            let divId = "repInfo";
            if(user) divId = "repPostInfo";
            fetch('../functions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ viewRepUs:"", id_rep:id_repUs, mode: user })
            })
            .then(response => response.text())
            .then(data => {
                repInfo = document.getElementById(divId);
                repInfo.innerHTML = data;
            })
            .catch(error => console.error("Error:", error));
        }

        function unBanUser(id_unbanUs){
            /*Se mod = 0 -> vedere info report sui Post
              Se mod = 1 -> vedere info report sui User
            */
            fetch('../functions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ unban:"" , id_user: id_unbanUs })
            })
            .then(response => response.text())
            .catch(error => console.error("Error:", error));
        }
        
    </script>
</body>
</html>