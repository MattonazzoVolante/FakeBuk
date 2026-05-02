window.onload = function() {
    let x= getCookie("darkMode");
    if(x == 1)enableDarkMode();
    else disableDarkMode();
}

function getCookie(cname) {
  let name = cname + "=";
  let decodedCookie = decodeURIComponent(document.cookie);
  let ca = decodedCookie.split(';');
  for(let i = 0; i <ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function setCookie(cname, cvalue, exdays) {
  const d = new Date();
  d.setTime(d.getTime() + (exdays*24*60*60*1000));
  let expires = "expires="+ d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}


function enableDarkMode() {
    document.body.classList.toggle("dark_mode");
    setCookie("darkMode", 1, 365); // Set cookie to expire in 1 year
}

function disableDarkMode() {
    document.body.classList.remove("dark_mode");
    setCookie("darkMode", 0, 365); // Set cookie to expire in 1 year
}

function toggleDarkMode() {
    const isDarkMode = document.body.classList.contains("dark_mode");
    setCookie("darkMode", 0, 0); // Set cookie to expire in 1 year
    if (isDarkMode) {
        disableDarkMode();
    } else {
        enableDarkMode();
    }
    location.reload();
}

function changeDarkModeImages(id,c){
  x = document.getElementById(id);
  var imagePath = "";
  if(c)
    imagePath = "assets/icons/comment_lightm.png";
  else
    imagePath = "assets/icons/like_lightm.png";
  
  x.src = imagePath;
}