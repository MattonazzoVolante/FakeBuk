<?php
/*
    QUESTO SINGOLO SCRIPT CONTIENE TUTTE LE FUNZIONI RICHIAMABILI PER
    CARICARE NELLA PAGINA DELL'UTENTE DEI POST.
*/
define("POSTS_LIMIT", 2);
define("POST_DELAY", 7);

/*
^^^^
    In questo modo l'utente può postare solo 2 post ogni 7 minuti

*/

/*
Questo è la funzione che prende i post di un singolo utente
(Sezione principale della pagina utente)
*/

function getPostByUser($user_id, $db, $includeUserInfo = true, $includePrivates = false, $role = 0, $howM = 10, $offset = 0)
{
    $query;
    global $darkMode;
    if(isset($_COOKIE['darkMode']) && $_COOKIE['darkMode']) $darkMode = 1;
    if ($includePrivates) $query = "SELECT * FROM (SELECT Id_post,Id_pubblisher,title,posts.description,attachedFile_path,tags,pubblication_datetime,username,pic_path,isVideo FROM posts INNER JOIN users ON Id_pubblisher = Id_user WHERE Id_user = ?) AS p ORDER BY pubblication_datetime LIMIT $howM OFFSET $offset;";
    else $query = "SELECT * FROM (SELECT Id_post,Id_pubblisher,title,posts.description,attachedFile_path,tags,pubblication_datetime,username,pic_path,isVideo FROM posts INNER JOIN users ON Id_pubblisher = Id_user WHERE Id_user = ? AND isPublic = true) AS p ORDER BY pubblication_datetime LIMIT $howM OFFSET $offset;";
    $getNumLike = "SELECT COUNT(Id_post) AS numLikes FROM likes WHERE Id_post = ?;";
    $getNumComm = "SELECT COUNT(Id_post) AS numComm FROM comments WHERE Id_post = ?;";
    $getLike = $db->prepare($getNumLike);
    $getLike->bind_param("i", $Id_post);
    $getLike->bind_result($likes);
    $getComm = $db->prepare($getNumComm);
    $getComm->bind_param("i", $Id_post);
    $getComm->bind_result($numComm);
    $prep = $db->prepare($query);
    $prep->bind_param("i", $user_id);
    $prep->execute();
    $prep->store_result();
    $prep->bind_result($Id_post, $Id_pubblisher, $title, $description, $imagePath, $tags, $pubblication_datetime, $pubblisher, $picPath, $isVideo);
    while ($prep->fetch()) {

        $getLike->execute();
        while ($getLike->fetch()) {
        }
        $getComm->execute();
        while ($getComm->fetch()) {
        }
        echo "<div class='container'>";
        if ($includeUserInfo) {
            echo "<div>";
            echo "<a href='userArea.php?id_User=$Id_pubblisher'>";
            echo "<div class='imgPubl'>";
            echo "<img src=$picPath >";
            echo "<p>$pubblisher</p>";
            echo "</div>";
            echo "</a>";
            echo "</div>";
        }
        echo "<a class='contentP' href='index.php?idPost=$Id_post'>";
        echo "<h1 id='title'> $title </h1><br>";
        echo "<p id='description'> $description </p><br>";
        if ($imagePath != null) {
            if (!$isVideo) echo "<img src='$imagePath'>";
            else
                echo " 
                    <video width='640' height='360' controls>
                        <source src='$imagePath' type='video/mp4'>
                        Your browser does not support the video tag.
                    </video>
                    ";
        }
        echo "</a>";
        echo "<br><p id='tags'>tags: $tags</p><br>";
        echo "<div class='interactions'>";
        echo "<div class='likeSecP'><p style='display:inline-block;' class='likes' id='likeP_$Id_post'>$likes</p>";
        echo "<button onclick=leaveAlike($Id_post,'likeP_$Id_post') >";
            if($darkMode) echo "<img src='assets\icons\like_lightm.png'>";
            else echo "<img src='assets\icons\like.png'>";
        echo "</button></div>";
        echo "<div class='commentSecP'><p id='comments'>$numComm  <p>";
        if($darkMode) echo " <img src='assets\icons\comment_lightm.png'>";
        else echo " <img src='assets\icons\comment.png'>";
        echo "</div>";
        echo "<p id='data'>DATA: $pubblication_datetime </p>";
        if (isset($_SESSION["id_user"])) {
            if ($_SESSION["id_user"] == $Id_pubblisher || $role > 0) {
                echo "<button onclick=" . "" . "deletePost($Id_post)" . "" . ">DELETE POST</button>";
            }
        }
        echo "<button onclick='showReportUs(1,$Id_post)'>REPORT</button>";
        echo "</div></div>";
    }
}

/*
Prende un singolo post, dipende da alcuni parametri modificherà il come verrà visualizzato
(nota bene VISUALIZZATO, certe operazioni richiedono più verifiche)
*/
function getSinglePost($user_id, $idPost, $db, $includeUserInfo = true, $role = 0)
{
    global $darkMode;
    global $logged;
    if(isset($_COOKIE['darkMode']) && $_COOKIE['darkMode']) $darkMode = 1;
    $ver = "SELECT Id_pubblisher FROM posts WHERE Id_post = ?";
    $query = "SELECT Id_post,Id_pubblisher,title,attachedFile_path,posts.description,tags,pubblication_datetime,username,role,pic_path,isVideo,isPublic FROM posts INNER JOIN users ON Id_pubblisher = Id_user WHERE Id_post = ?";
    $queryComm = "SELECT Id_comment,comments.Id_post,users.Id_user,comments.Id_user as pubblisher,contenuto,created_at,username FROM comments INNER JOIN posts ON comments.Id_post = posts.Id_post INNER JOIN users ON users.Id_user = comments.Id_user WHERE comments.Id_post = ?;";
    $getNumLike = "SELECT COUNT(Id_post) AS numLikes FROM likes WHERE Id_post = ?";
    $prep = $db->prepare($getNumLike);
    $prep->bind_param("i", $idPost);
    $prep->bind_result($likes);
    $prep->execute();
    $likes = 0;
    while ($prep->fetch()) {
    }
    $prepComm = $db->prepare($queryComm);
    $prepComm->bind_param("i", $idPost);
    $prepComm->execute();
    $prepComm->store_result();
    $prepComm->bind_result($Id_comment, $Id_post, $Id_usComm, $commPubblisher, $contenuto, $created_At, $usernameComm);
    $prep = $db->prepare($query);
    $prep->bind_param("i", $idPost);
    $prep->execute();
    $prep->store_result();
    if ($prep->num_rows == 0) {
        echo "Nessun post trovato :(";
        die();
    }
    $prep->bind_result($Id_post, $Id_pubblisher, $title, $imagePath, $description, $tags, $pubblication_datetime, $pubblisher, $rolePub, $picPath, $isVideo, $isPublic);
    while ($prep->fetch()) {
        if (!$isPublic && ($Id_pubblisher != $user_id)) {
            exit("POST PRIVATO");
        }
        echo "<div class='container'>";
        if ($includeUserInfo) {
            echo "<div>";
            echo "<a href='userArea.php?id_User=$Id_pubblisher'>";
            echo "<div class='imgPubl'>";
            echo "<img src=$picPath >";
            echo "<p ";
            if ($rolePub >= 3) echo "style='color:red'";
            else if ($rolePub >= 1) echo "style='color:blue'";
            echo ">$pubblisher</p>";
            echo "</div>";
            echo "</a>";
            echo "</div>";
        }
        echo "<h1 id='title'> $title </h1><br>";
        echo "<p id='description'> $description </p><br>";
        if ($imagePath != null) {
            if (!$isVideo) echo "<img src='$imagePath'>";
            else
                echo " 
                        <video width='640' height='360' controls>
                            <source src='$imagePath' type='video/mp4'>
                            Your browser does not support the video tag.
                        </video>
                        ";
        }
        echo "<br><p id='tags'>tags: $tags</p><br>";
        echo "<div class='interactions'>";
        echo "<div class='likeSecP'><p style='display:inline-block;' class='likes' id='likeP_$Id_post'>$likes</p>";
        echo "<button onclick=leaveAlike($Id_post,'likeP_$Id_post') >";
            if($darkMode) echo "<img src='assets\icons\like_lightm.png'>";
            else echo "<img src='assets\icons\like.png'>";
        echo "</button></div>";
        /*echo " <script>
            if(getCookie('darkMode') == 1){
    changeDarkModeImages('com_$Id_post',1);
    changeDarkModeImages('like_$Id_post',0);
    }

        </script>";*/
        echo "<p id='data'>DATA: $pubblication_datetime </p>";
        if ($user_id == $Id_pubblisher || $role > 0) {
            echo "<button onclick=" . "" . "deletePost($Id_post)" . "" . ">DELETE POST</button>";
        }
        if($logged){echo "<button onclick='showReportUs(1,$Id_post)'>REPORT</button>";}
        echo "</div></div>";

        if ($logged) {
            echo "<div class='leaveAcomment'>
            <form method='post'>
                <input type='text' name='content'>
                <input type='submit' name='commentAct' value='lascia un commento'> 
            </form></div>";
        } else {
            echo "<h1> Logga per commentare! </h1>";
        }
        while ($prepComm->fetch()) {
            echo "<div class='container'>";
            echo "<h1> $usernameComm </h1>";
            echo "<p> $contenuto </p>";
            echo "<p> $created_At </p>";
            if (($user_id == $commPubblisher || $user_id == $Id_pubblisher) || $role > 0) {
                echo "<button onclick='deleteComment($Id_comment,$idPost)'>Cancella commento</button>";
            }
            echo "</div>";
        }
        echo "</div></div>";
    }
}


/*
Questa è una funzione fatta apposta per poter essere chiamata via JavaScript http request

Prende dei post casuali OPPURE di un utente e li carica man mano che si scrolla.
*/

function getMorePost($user_id, $tags, $db, $includeUserInfo = true, $role = 0, $howM = 20)
{
    global $darkMode;
    if(isset($_COOKIE['darkMode']) && $_COOKIE['darkMode']) $darkMode = 1;
    $query = "SELECT Id_post,Id_pubblisher,title,posts.description,attachedFile_path,tags,pubblication_datetime,username,role,pic_path,isVideo FROM posts INNER JOIN users ON Id_pubblisher = Id_user WHERE isPublic = true ORDER BY RAND() LIMIT $howM;";
    $getNumLike = "SELECT COUNT(Id_post) AS numLikes FROM likes WHERE Id_post = ?;";
    $getNumComm = "SELECT COUNT(Id_post) AS numComm FROM comments WHERE Id_post = ?;";
    $getLike = $db->prepare($getNumLike);
    $getLike->bind_param("i", $Id_post);
    $getLike->bind_result($likes);
    $getComm = $db->prepare($getNumComm);
    $getComm->bind_param("i", $Id_post);
    $getComm->bind_result($numComm);
    $prep = $db->prepare($query);
    $prep->execute();
    $prep->store_result();
    $prep->bind_result($Id_post, $Id_pubblisher, $title, $description, $imagePath, $tags, $pubblication_datetime, $pubblisher, $rolePub, $picPath, $isVideo);
    while ($prep->fetch()) {

        $getLike->execute();
        while ($getLike->fetch()) {
        }
        $getComm->execute();
        while ($getComm->fetch()) {
        }
        echo "<div class='container'>";
        if ($includeUserInfo) {
            echo "<div>";
            echo "<a href='userArea.php?id_User=$Id_pubblisher'>";
            echo "<div class='imgPubl'>";
            echo "<img src=$picPath >";
            echo "<p ";
            if ($rolePub >= 3) echo "style='color:red'";
            else if ($rolePub >= 1) echo "style='color:blue'";
            echo ">$pubblisher</p>";
            echo "</div>";
            echo "</a>";
            echo "</div>";
        }
        echo "<a class='contentP' href='index.php?idPost=$Id_post'>";
        echo "<h1 id='title'> $title </h1><br>";
        echo "<p id='description'> $description </p><br>";
        if ($imagePath != null) {
            if (!$isVideo) echo "<img src='$imagePath'>";
            else
                echo " 
                    <video width='640' height='360' controls>
                        <source src='$imagePath' type='video/mp4'>
                        Your browser does not support the video tag.
                    </video>
                    ";
        }
        echo "</a>";
        echo "<br><p id='tags'>tags: $tags</p><br>";
        echo "<div class='interactions'>";
        echo "<div class='likeSecP'><p style='display:inline-block;' class='likes' id='likeP_$Id_post'>$likes</p>";
        echo "<button onclick=leaveAlike($Id_post,'likeP_$Id_post') >";
            if($darkMode) echo "<img src='assets\icons\like_lightm.png'>";
            else echo "<img src='assets\icons\like.png'>";
        echo "</button></div>";
        echo "<div class='commentSecP'><p id='comments'>$numComm  <p>";
        if($darkMode) echo " <img src='assets\icons\comment_lightm.png'>";
        else echo " <img src='assets\icons\comment.png'>";
        echo "</div>";
        echo "<p id='data'>DATA: $pubblication_datetime </p>";
        if(($user_id != null) && $user_id != $Id_pubblisher){echo "<button onclick='showReportUs(1,$Id_post)'>REPORT</button>";}
        if ($user_id == $Id_pubblisher || $role > 0) {
            echo "<button onclick=" . "" . "deletePost($Id_post)" . "" . ">DELETE POST</button>";
        }
        echo "</div></div>";
    }
}

/*
Prende i commenti di un utente che ha lasciato su più post
(è la barra laterale della sezione utenti)
*/
function getCommentsByUser($id_user, $db)
{
    $query = "SELECT comments.Id_user,posts.Id_post,posts.title as titolo,contenuto,created_at FROM comments INNER JOIN posts ON posts.Id_post = comments.Id_post WHERE comments.Id_user = ? ORDER BY created_at;";
    $prep = $db->prepare($query);
    $prep->bind_param("i", $id_user);
    $prep->execute();
    $prep->bind_result($id_user, $Id_post, $titlePost, $content, $created_At);
    while ($prep->fetch()) {
        echo "<div>";
        echo "<a href='index.php?idPost=$Id_post'>";
        echo "<p>post name: </p><h1>$titlePost</h1>";
        echo "<p>$content</p>";
        echo "<p>$created_At</p>";
        echo "</a>";
        echo "</div>";
    }
}




/*Questo script qua sotto impedisce ad un qualsiasi script annidato nella richiesta
    ad essere inserito nel database causando una minaccia stored XSS*/



function sanitize($conn, &$content)
{
    $content = trim($content);
    $content = stripslashes($content);
    if ($conn instanceof mysqli) {
        $content = mysqli_real_escape_string($conn, $content);
    }

    $content = str_replace(["\n", "\r"], ["<newline>", "<return>"], $content);
    $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
    $content = str_replace(["<newline>", "<return>"], ["\n", "\r"], $content);
    $content = str_replace('\\r\\n', "<br>", $content);
    $content = str_replace("\\'", "'", $content);
}

function uploadMedia($file, &$isVideo, &$pathFile, $uploadDir)
{
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if ($file["size"] > 0 && $file["size"] < 5120000) { // Limit file size to 5MB
        $fileName = basename(bin2hex(random_bytes(32)));

        // Get file extension and MIME type
        $fileMimeType = mime_content_type($file['tmp_name']);
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        $uploadFilePath = $uploadDir . $fileName . "." . $fileExtension;

        // Allowed image MIME types
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/x-matroska'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mkv'];



        if (!in_array($fileMimeType, $allowedMimeTypes) && !in_array($fileExtension, $allowedExtensions)) {
            echo "Invalid file type. Only images are allowed.";
            return false;
            die();
        }
        echo "Estensione file: " . $fileExtension . "<br>";
        echo strcmp($fileExtension, "mp4");


        // Move uploaded file to the target directory
        if (move_uploaded_file($file["tmp_name"], $uploadFilePath)) {
            echo "File uploaded successfully to: " . $uploadFilePath;
            $pathFile = $uploadFilePath;
            if (strcmp($fileExtension, "mp4") === 0 || strcmp($fileExtension, "mkv") === 0) {
                $isVideo = 1;
            }
            return true;
        } else {
            echo "File upload failed.";
            return false;
        }
    } else {
        echo "File too large or empty.";
        return false;
    }
    return false;
}


/*
Questa funzione permette di uploadare delle immagini nella cartella /uploads
*/
function uploadImage($file, &$pathFile, $uploadDir)
{
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if ($file["size"] > 0) {
        $fileName = basename(bin2hex(random_bytes(32)));

        
        $fileMimeType = mime_content_type($file['tmp_name']);
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        $uploadFilePath = $uploadDir . $fileName . "." . $fileExtension;

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];



        if (!in_array($fileMimeType, $allowedMimeTypes) && !in_array($fileExtension, $allowedExtensions)) {
            echo "Invalid file type. Only images are allowed.";
            die();
        }



        if (move_uploaded_file($file["tmp_name"], $uploadFilePath)) {
            echo "File uploaded successfully";
            $pathFile = $uploadFilePath;
            return true;
        } else {
            echo "File upload failed.";
            return false;
        }
    }
    return false;
}

/*
Questa è una funzione avanzata che controlla se il post
contiene parole vietate, rileva anche tentativi di sostituzione
con simboli o numeri.

Come funziona?
Prende in input il post dal titolo fino al contenuto, e unifica TUTTO il testo
dopo aver unificato tutto il testo, rimuove gli spazi e converte tutto in minuscolo
e sostituisce i simboli più comuni usati per sostiuire certe parole con quelle parole corrispondenti.

Dopo di ché usando un file chiamato lista_badwords.txt che contiene tutte le parolaccie italiane
(è un file opensource che ho trovato online)

Prende parola per parola dalla lista e controlla se è presente nel testo

In caso positivo, fa un return true;
*/

function badWordsList($textToCheck)
{
    $badWordsList = array();
    $filename = 'assets\lista_parole\lista_badwords.txt';
    $file = fopen($filename, 'r');
    if ($file) {
        while (!feof($file)) {
            array_push($badWordsList, (string)fgets($file));
        }
        fclose($file);
    }

    $wordsSubstitute = array(
        "|" => "i",
        "1" => "i",
        "!" => "i",
        "3" => "e",
        "&" => "e",
        "£" => "e",
        "€" => "e",
        "4" => "a",
        "@" => "a",
        "à" => "a",
        "0" => "o",
        "ò" => "o",
        "à" => "o",
        "°" => "o",
        "ù" => "u",
        "ç" => "c"
    );
    $textToCheck = str_replace(" ", "", $textToCheck);
    $textToCheck = strtolower($textToCheck);
    for ($i = 0; $i < strlen($textToCheck); $i++) {
        try {
            if (array_key_exists($textToCheck[$i], $wordsSubstitute)) {
                $textToCheck[$i] = $wordsSubstitute[$textToCheck[$i]];
            }
        } catch (Exception $e) { echo "";
        }
    }
    foreach ($badWordsList as $word) {
        $word = trim($word);
        if (str_contains($textToCheck, $word)) return true;
    }

    return false;
}

function checkPostDelay($conn, $id_user)
{
    $numPost = 0;
    $post_delay = POST_DELAY;
    $query = "
        SELECT COUNT(pubblication_datetime) as Num_post 
        FROM posts 
        INNER JOIN users ON Id_pubblisher = Id_user 
        WHERE Id_user = ? 
        AND TIMESTAMPDIFF(MINUTE, pubblication_datetime, CURRENT_TIMESTAMP()) < ?;
    ";
    $prep = $conn->prepare($query);
    $prep->bind_param("ii", $id_user, $post_delay);
    $prep->bind_result($numPost);
    $prep->execute();
    while ($prep->fetch()) {;
        if ($numPost >= POSTS_LIMIT) {
            echo "Limite di post raggiunto, attendi " . POST_DELAY . " minuti prima di pubblicare un nuovo post.";
            return true;
        }
    }
    return false;
}

function deleteMedia($pathfile)
{
    if ($pathfile == "") return false;
    if (!file_exists($pathfile)) return false;
    if (unlink($pathfile))  return true;
    else return true;
}

function deleteAllPostsWithMediaFromUser($conn, $id_user){
    $filePath = "";
    $query = "
        SELECT attachedFile_path
        FROM posts
        WHERE Id_pubblisher = ?";
    $prep = $conn->prepare($query);
    $prep->bind_param("i", $id_user);
    $prep->bind_result($filePath);
    $prep->execute();
    while ($prep->fetch()) {;
        deleteMedia($filePath);
    }
    $queryDes = "DELETE FROM posts WHERE Id_pubblisher=$id_user";
    $conn->query($queryDes);
}
