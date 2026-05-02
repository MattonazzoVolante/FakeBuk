<?php 
    session_start();
    include("scripts/verScript.php");
    include("scripts/db_connection.php");
    include("scripts/post_functions.php");
    
    $logged = verAccount($db,$id_user,$role);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="structure/style.css">
</head>
<body>
    <main>
        <script src="structure\darkmode.js"></script>
        <?php include("structure/header.php") ?>
    </main>
<?php
/* Questo è lo script che restituisce risultati di ricerca.
Filtra ed effettua la ricerca nal database */
    
    $modRep = false;
    $post = true;
    $extraColumn = "tags";
    if($_SERVER["REQUEST_METHOD"] == "GET"){
        if(isset($_REQUEST["search"])){
            if(!isset($_REQUEST["searchText"]) || !isset($_REQUEST["filter"])){exit("You can't search something that wasn't specified!");}
            if(empty($_REQUEST["searchText"])){
                exit("Non puoi cercare per qualcosa che non è stato specificato!");
            }
            $searchText = "%".$_REQUEST["searchText"]."%";
            $query="";

            switch($_REQUEST["filter"]){
                case 'title':
                    $query = "SELECT Id_post,Id_pubblisher,title,description,tags,attachedFile_path,isVideo,pubblication_datetime FROM posts WHERE isPublic = 1 AND title LIKE ?;";
                    break;
                case 'tags':
                    $query = "SELECT Id_post,Id_pubblisher,title,description,tags,attachedFile_path,isVideo,pubblication_datetime FROM posts WHERE isPublic = 1 AND tags LIKE ?;";
                    break;
                case 'users':
                    $post = false;
                    $query = "SELECT u.Id_user,name,username,u.description,pic_path FROM users AS u WHERE username LIKE ?";
                    break;
                default:
                    exit("Error with the filter");
            }
            $getNumLike = "SELECT COUNT(Id_post) AS numLikes FROM likes WHERE Id_post = ?;";
            $getNumComm = "SELECT COUNT(Id_post) AS numComm FROM comments WHERE Id_post = ?;";
            $numComm;$likes;
            $prep = $db->prepare($query);
            $prep->bind_param("s",$searchText);
            
            $res1;$res2;$res3;$res4;$res5;$res6;$res7;$res8;
            $getLike;$getComm;
            if($post){
                $getLike = $db->prepare($getNumLike);
                $getLike->bind_param("i",$res1);
                $getLike->bind_result($likes);
                $getComm = $db->prepare($getNumComm);
                $getComm->bind_param("i",$res1);
                $getComm->bind_result($numComm);
                $prep->bind_result($res1,$res2,$res3,$res4,$res5,$res6,$res7,$res8);
            }else{
                $prep->bind_result($res1,$res2,$res3,$res4,$res5);
            }
            $prep->execute();
            $prep->store_result();
            
            if($post){
                echo "<div class='posts'>";
                /*
                $res1 = Id_post,
                $res2 = Id_pubblisher,
                $res3 = title,
                $res4 = description,
                $res5 = tags
                $res6= imagepath,
                $res7 = isVideo
              $res8 = pubblicationTime
                */
                while($prep->fetch()){
                $getLike->execute();
                while($getLike->fetch()){}
                $getComm->execute();
                while($getComm->fetch()){}
                echo "<div class='container'>";
                echo "<a href='index.php?idPost=$res1'>";
                echo "<h1 id='title'> $res3 </h1><br>";
                echo "<p id='description'> $res4 </p><br>";
                if($res6!=null){
                    if(!$res7)echo "<img src='$res6'>";
                    else
                        echo " 
                        <video width='640' height='360' controls>
                            <source src='$res6' type='video/mp4'>
                            Your browser does not support the video tag.
                        </video>
                        ";
                    
                }
                echo "<br><p id='tags'>tags: $res5</p><br>";
                echo "<div class='interactions'>";
                echo "<div class='likeSecP'><p style='display:inline-block;' class='likes'></p>";
                echo "<button onclick='leaveAlike($res1)' >$likes<img src='assets\icons\like.png'></button></div>";
                echo "<div class='commentSecP'><p id='comments'>$numComm  <p>";
                echo " <img src='assets\icons\comment.png'></div>";
                echo "<p id='data'>DATA: $res8 </p>";
                echo "</div></div>";
                echo "</a>";
                }
            }else{
                echo "<div class='users'>";
                /*
                    $res1 = Id_user,
                    $res2 = name,
                    $res3 = username,
                    $res4 = description,
                    $res5 = pic_path
                
                */
                while($prep->fetch()){
                    echo "<div class='singleUser'>";
                    echo "<a href='userArea.php?id_User=$res1'>";
                    echo "<div class='imgPubl'>";
                    echo "<img src=$res5 >";
                    echo "<p>$res3</p>";
                    echo "</div>";
                    echo "</a>";
                    echo "</div>";
                }
                echo "</div>";
            }
        }
    }else{
        header("Location: index.php");
    }
?>

</body>
</html>