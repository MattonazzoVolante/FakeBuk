/*
Queste sono tutte le funzioni lato client,
Come si può vedere sono tutte funzioni che inviano richieste al server,
è SEMPRE il server ad effettuare operazione e sopratutto a verificare se possono
essere effettuate da dato utente.
SEMPRE

*/


window.onload = function() {
    if(!document.cookie.includes("disclaimerReaded")){
        setTimeout(function(){ 
                //window.location.href = 'index.php?idPost=$idP';
            
        const disclaimer = document.createElement("div");
        disclaimer.id = "disclaimer";
        disclaimer.innerHTML = `
            <div style='position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); color: white; z-index: 1000; display: flex; flex-direction: column; align-items: center;text-align:center; font-size: 40px; justify-content: center;'>
                <h1>Attenzione</h1>
                <p>Ciao! <br>
                Questo è un sito web in alpha, questo vuol dire che potresti trovare errori o effetti indesiderati durante la navigazione <br>
                Puoi segnalare gli eventuali problemi lasciando un feedback in basso a destra, per tutto il resto il sito dovrebbe funzionare a dovere</p>
                <br>
                <button style='background-color: white; color:black; font-size:35;' onclick='document.cookie = "disclaimerReaded=true; max-age=31536000; path=/";
            document.getElementById("disclaimer").remove();
'>Capisco</button>
            </div>
        `;}, 3000);
        
        document.body.appendChild(disclaimer);

    }
}


function logOut(){
    fetch('functions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ LOGOUT: "value" })
    })
    .then(response => response.text())
    .then(data => console.log("Server Response:", data))
    .catch(error => console.error("Error:", error));
    location.reload();
}

function leaveAlike(id_p, htmlId = "likeCounter") {
    let likeCounter = document.getElementById(htmlId);
    let currentLikes = parseInt(likeCounter.textContent) || 0; // Get current like count safely 
    fetch('functions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ leaveAlike: "", id_post: id_p })
    })
    .then(response => response.json()) 
    .then(data => {
        console.log("Server Response:", data);
        
        let addLike = data.add; 
        likeCounter.textContent = addLike ? currentLikes + 1 : currentLikes - 1;
        if (likeCounter.textContent < 0) {
            likeCounter.textContent = 0; // Ensure like count doesn't go negative
        }
    })
    .catch(error => {
        console.error("Fetch error:", error); 
    });


}
/*
function deleteAccount(id_u,tok){
    alert("aaaa");
    fetch('functions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ delete_Account: "", id_user: id_u })
    })
    .then(response => response.text())
    .then(data => console.log("Server Response:", data))
    .catch(error => console.error("Error:", error));
}*/

function deletePost(Id_post){
    fetch('functions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ delete_Post: "", id_post: Id_post})
    })
    .then(response => response.text())
    .then(data => {
        // Create a container for the server response and add it to the page
        const resultDiv = document.createElement('script');
        resultDiv.innerHTML = data;
        document.body.appendChild(resultDiv);
    })
    .catch(error => console.error("Error:", error));
    location.reload();
}

function deleteComment(Id_comment,Id_post){
    fetch('functions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ delete_Comment: "", id_comment: Id_comment, id_post: Id_post})
    })
    .then(response => response.text())
    .then(data => {
        // Create a container for the server response and add it to the page
        const resultDiv = document.createElement('script');
        resultDiv.innerHTML = data;
        document.body.appendChild(resultDiv);
    }
    )
    .catch(error => console.error("Error:", error));
    //location.reload();
}

function follow(id_us,or_Id_user){
    fetch('functions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ followUser: "", id_user: id_us,id_follower:or_Id_user})
    })
    .then(response => response.text())
    .then(data => console.log("Server Response:", data))
    .catch(error => console.error("Error:", error));
    location.reload();
}

function showReportUs(b,id){
    const repDiv = document.getElementById("repDiv");
    if(b) {
        repDiv.style.display = "block";
        let inp = document.getElementById("id_post");
        inp.value = id;
    }
    else repDiv.style.display = "none";
}

function showBanSec(){
    let banSec = document.getElementById("banSec");
    banSec.style.display = "block";
}

function downGradeUser(id_us){
    fetch('functions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ downGradeUs: "", id_user: id_us})
    })
    .then(response => response.text())
    .then(data => console.log("Server Response:", data))
    .catch(error => console.error("Error:", error));
}
