function validateRegex (regex, input) {
	var value = input;
	return regex.test(input);
}
function validateEmail(input) {
	var regex = /^[\w\.-_\+]+@[\w-]+(\.\w{2,3})+$/;
	return validateRegex(regex, input);
}