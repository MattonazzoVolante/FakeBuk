<?php
    session_start();
    include("..\scripts\db_connection.php");
    include("headerMods.php");
    include("..\scripts\\verScript.php");

    //Verifica
    $id_us;
    $role;
    if(!verAccount($db,$id_us,$role) && $role <=0) exit("NON HAI I PERMESSI NECESSARI!");
    
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(isset($_REQUEST["search"])){
            if(!isset($_REQUEST["searchText"]) || !isset($_REQUEST["filter"])){exit("Errore");}
            $searchText = "%".$_REQUEST["searchText"]."%";
            
            $query;
            switch($_REQUEST["filter"]){
                case 'title':
                    $query = "SELECT Id_post,Id_pubblisher,title,description,reason FROM posts INNER JOIN reportedposts ON Id_post = Id_repPost WHERE title LIKE ?;";
                    break;
                case 'tags':
                    $query = "SELECT Id_post,Id_pubblisher,title,description,reason FROM posts INNER JOIN reportedposts ON Id_post = Id_repPost WHERE tags LIKE ?;";
                    break;
                case 'users':
                    $query = "SELECT u.Id_user,name,username,u.description,pic_path FROM users AS u INNER JOIN reportedusers ON u.Id_user= Id_repUs WHERE username LIKE ?";
                    break;
                default:
                    exit("Error with the filter");
            }
            echo $query;
            $prep = $db->prepare($query);
            $prep->bind_param("s",$searchText);
            $prep->execute();
            $prep->bind_result($res1,$res2,$res3,$res4,$res5);
            while($prep->fetch()){
                echo "<div>";
                echo "<a>";
                echo "<p>$res5 ||$res3 </p>";
                echo "<p>$res4</p>";
                echo "</a>";
                echo "</div>";
                echo "<br>";
            }
        }
    }else{
        echo "<script>window.location = 'searchReport.php';</script>";
    }
?>