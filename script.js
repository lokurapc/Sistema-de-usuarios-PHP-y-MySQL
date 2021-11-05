const togglePassword = document.querySelector("#togglePassword");
if (togglePassword) {
	togglePassword.addEventListener('click', function (e) {
		var tipo = document.getElementById("password");
		if (tipo.type == "password") {
			tipo.type = "text";
			this.classList.replace('fa-eye-slash','fa-eye');
		} else {
			tipo.type = "password";
			this.classList.replace('fa-eye','fa-eye-slash');
		}
	});
}

const refreshButton = document.querySelector(".refresh-captcha");
if (refreshButton) {
	refreshButton.addEventListener('click', function (e) {
		img = document.getElementById("captcha");
		img.src="img.php?rand_number=" + Math.random();
	});
}