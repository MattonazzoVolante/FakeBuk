<?php
/*
    lo script subito sotto contiene funzioni che verifica se l'utente è loggato, e
    la sua sessione esiste ed'è convalidata, inoltre controlla anche se l'utente è bannato.
    Viene eseguita sempre ogni volta che un utente accede ad una parte del sito web

    LA SESSIONE DEVE ESSERE APERTA PRIMA DEL RICHIAMO
*/

/*
Controlla se l'utente sta accedendo da un dispostivo mobile
(Il sito ha un grafica orribile per i dispositivi mobili visto che
non è stato progettato per essere usato su di essi,
quindi quando viene rilevato un dispositivo viene semplicemente bloccato)
*/
function is_mobile()
{
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_agents = array('iPhone', 'Android', 'BlackBerry', 'Windows Phone', 'Mobile');

    foreach ($mobile_agents as $agent) {
        if (stripos($user_agent, $agent) !== false) {
            return true;
        }
    }

    return false;
}


/*
Controlla se l'utente è stato Bannato 
(normalmente, con possibilità di fare appeal)
*/
function isUserBanned($db, $id_user, &$reason, &$banDuration)
{
    $query = "SELECT Id_banned,reason,DATEDIFF(expireAt,CURDATE()) as giorniMancanti FROM banned_users WHERE Id_banned = ?";
    $prep = $db->prepare($query);
    $prep->bind_param("i", $id_user);
    $prep->bind_result($id_banned, $reason, $banDuration);
    $prep->execute();
    $prep->store_result();
    $prep->fetch();
    if ($prep->num_rows > 0) {
        return true;
    } else {
        return false;
    }
}


/*
Controlla se l'utente è IP bannato, se l'hash dell Ip dell'utente corrisponde
all'hash della lista di IP bannati allora l'utente non viene lasciato entrare

Fun Fact: l'IP del Marconi è Ipbannato dal sito hostato su Altervista
*/
function checkIfUserIpBanned($db, $ip)
{
    $query = "SELECT Ip_addressHash FROM banned_ipaddresses;";
    $prep = $db->query($query);

    while ($row = $prep->fetch_assoc()) {
        $ipHash = $row["Ip_addressHash"];
        if (password_verify($ip, $ipHash) == 1) {
            return true;
        }
    }
    return false;
}

/*
Questa è la funzione più importante di tutto il sito.
Controlla tutto dell'utente ogni volta che ricarica la pagina.
Controlla:
    - Se è loggato
    - Se la sessione è valida
    - Se è bannato
    - Se è IP bannato
    - Se il suo ruolo è valido

    Per poi ritornare una variabile booleana che indica se è andato tutto a buon fine 
    
    NOTA: blockBanned è un parametro di test, se viene impostato a false, non controllera 
    se gli utenti sono bannati.
*/

function verAccount($db, &$id_user, &$role = 0, $blockBanned = true)
{
    if (is_mobile()) {
        exit("<div style=background-color:black;color:white;><h1>Sorry, the website has not been released for mobile devices yet<h1></div>");
    }
    if (!isset($_SESSION)) {
        exit("Sessione non avviata!");
    }
    $token = "";
    $logged = false;
    if (isset($_SESSION["ses_token"])) {
        $token = $_SESSION["ses_token"];
    }

    //Verifica
    $query = "SELECT Id_session,Id_user,Ip_address FROM sessions WHERE Id_session = ? AND CURRENT_TIMESTAMP() BETWEEN data_start AND data_end";
    $prep = $db->prepare($query);
    $prep->bind_param("s", $token);
    $prep->execute();
    $prep->store_result();
    if ($prep->num_rows > 0) {
        $prep->bind_result($id_ses, $id_us, $ip);
        while ($prep->fetch()) {
            if (strcmp($id_ses, $token) == 0) {
                $query = "SELECT Id_user,username,pic_path,role FROM users WHERE Id_user = ? AND Id_user IS NOT NULL;";
                $prep2 = $db->prepare($query);
                $prep2->bind_param("i", $id_us);
                $prep2->execute();
                $prep2->store_result();
                $prep2->bind_result($id_usNv, $username, $picPath,$role);
                if ($prep2->num_rows > 0) {
                    while ($prep2->fetch()) {
                        if ($id_usNv != $id_us) {
                            $logged = false;
                        } else {
                            $logged = true;
                            $id_user = $id_usNv;
                            $_SESSION["username"] = $username;
                            $_SESSION["pic_path"] = $picPath;
                            $_SESSION["role"] = $role;
                        }
                    }
                } else {
                    exit("sessione non valida!");
                }


                //Verifica IP
                /*$transmissionData = base64_decode($ip); // Decode received data
                $iv = substr($transmissionData, 0, 16); // Extract the first 16 bytes (IV)
                $encryptedData = substr($transmissionData, 16); // Extract remaining bytes
                $decryptedData = openssl_decrypt($encryptedData, "aes-256-cbc", getenv("ENCRYPTION_KEY"), 0, $iv);*/
                if (!password_verify($_SERVER['REMOTE_ADDR'], $ip)) {
                    echo "IP non valido!";
                    $logged = false;
                } else {
                    $logged = true;
                    $_SESSION["id_user"] = $id_us;
                }


                if ($blockBanned) {
                    $reason = "";
                    $banDuration = 0;
                    if (isUserBanned($db, $id_usNv, $reason, $banDuration)) {
                        echo "BANNATO, RAGIONE:" . $reason;
                        echo "<BR>Giorni mancanti: " . $banDuration;
                        echo "<button onclick='logOut()'>Logout</button>";
                        echo "<a href='banAppeal.php'> Fai ricorso </a>";
                        die();
                    }
                }

                //VERIFICA RUOLO
                $query = "SELECT u.Id_user,role FROM users AS u WHERE u.Id_user = ? AND u.Id_user IS NOT NULL;";
                $prep3 = $db->prepare($query);
                $prep3->bind_param("i", $id_us);
                $prep3->execute();
                $prep3->store_result();
                $prep3->bind_result($id_usNv, $roleT);
                if ($prep3->num_rows > 0 && $logged) {
                    while ($prep3->fetch()) {
                        $role = $roleT;
                    }
                } else {
                    $role = 0;
                    echo "Ruolo non valido!";
                    return false;
                }
            } else {
                exit("Token non valido!");
            }
        }
    }
    //VERIFICA se bannato
    if ($blockBanned) {
        if (checkIfUserIpBanned($db, $_SERVER['REMOTE_ADDR'])) {
            echo "Il tuo indirizzo IP è stato bannato dal sito!  <br>";
            echo "Caro mio, per esser stato bannato per ip vuol dire che hai continuato ad usare il sito con altri account <br>";
            echo "Continuando a fare post poco adatti al sito <br>";
            echo "L'unico modo che hai per riaccedere al sito è facendo un ricorso specificando cosa hai fatto, inoltre dovrai fornire il tuo username e la tua email <br>";
            echo "<a href='banAppeal.php'>Ban appeal</a>";
            die();
        }
    }

    return $logged;
}


/*
Questa funzione prende e controlla se l'utente dato come parametro esiste ed ha impostato una domanda di sicurezza
*/
function takeRecQaAFromUsName($db, $username, &$id, &$recover_question, &$recover_answer)
{
    echo $username;
    $query = "SELECT u.Id_user,username,recover_question, recover_answer FROM users AS u WHERE username = ?;";
    $prep = $db->prepare($query);
    $prep->bind_param("s", $username);
    $prep->bind_result($id, $username, $recover_question, $recover_answer);
    $prep->execute();
    $prep->store_result();
    while ($prep->fetch()) {
    }
    return ($prep->num_rows > 0) ? true : false;
}
