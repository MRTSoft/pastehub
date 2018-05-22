function AJAX_call(resource, method, action, postData = null){
	xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			console.log("Reply from " + resource + " at " + method + " request.");
			action();
			console.log(this.responseText);
		}
	};

	xmlhttp.open(method, resource, true);
	if (method == "POST" && postData != null){
		xmlhttp.send(postData);
	} else {
		xmlhttp.send();
	}
}

function changeLanguage(lan){
	var postData = new FormData();
	postData.append('language', lan);
	AJAX_call("./api/misc.php", 'POST', function(){
		window.location.reload(true);
	}, postData);
}

function logout(){
	AJAX_call("logout.php", 'POST', function(){
		window.location.reload(true);
	});
}

function valid_email(email){
	//Dark magic from:
	//https://stackoverflow.com/questions/46155/how-can-you-validate-an-email-address-in-javascript#46181
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

function redBorder(elem){
	elem.style.border = "3px solid red";
}

function validate_login(){
	//validate the log in form on login.php
	var email = document.forms['login_form']['email'].value;
	var valid = valid_email(email);
	if (!valid){
		redBorder(document.forms['login_form']['email']);
	}
	return valid;
	//NOTE: The only requrement for the password is to not be empty
	// This is solved by the required attribute
}



function validate_signup(){
	var email1 = document.forms['signup_form']['email'];
	var email2 = document.forms['signup_form']['email2'];
	var pwd1 = document.forms['signup_form']['pwd'];
	var pwd2 = document.forms['signup_form']['pwd2'];

	var aux;
	var valid = true;
	aux = valid_email(email1.value);
	valid &= aux;
	if (!aux){
		redBorder(email1);
	}
	aux = valid_email(email2.value);
	valid &= aux;
	if (!aux){
		redBorder(email2);
	}
	aux = email2.value == email1.value;
	valid &= aux;
	if (!aux){
		redBorder(email1);
		redBorder(email2);
	}

	aux = pwd2.value == pwd1.value;
	valid &= aux;
	if (!aux){
		redBorder(pwd1);
		redBorder(pwd2);
	}
	return (valid != 0);
}


function clearTableRow(row_id) {
	var elem = document.getElementById(row_id);
	elem.parentNode.removeChild(elem);
}

function removeFile(button, file_id){
	var postData = new FormData();
	postData.append('remove_file', file_id);
	button.innerHTML = "Loading...";
	AJAX_call("./api/misc.php", 'POST', function(){
		clearTableRow(file_id);
	}, postData);
}

function tryRemoveFile(file_id){
	var postData = new FormData();
	postData.append('remove_file', file_id);
	AJAX_call("./api/misc.php", 'POST', function(){
		window.location = "index.php";
	}, postData);
}