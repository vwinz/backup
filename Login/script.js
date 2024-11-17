function validateForm() {
    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;

    if (username == "" || password == "") {
        alert("Username and Password must be filled out");
        return false;
    }

    return true;
}
