function writeCookie (cookie) {
	var name = cookie.getName();
	var value = cookie.getValue();
	var days = cookie.getValidity();
	var expires = "";
	if (days) {
		var date = new Date();
		date.setTime(date.getTime() + days * 86400 * 1000);
		expires = "; expires=" + date.toGMTString();
	}
	document.cookie = name + "=" + value + expires + "; path=/";
}
function readCookie (name) {
	var searchName = name + "=";
	var value, valid = 0, expires = "expires=";
	var cookies = document.cookie.split(';');
	for (var i = 0; i < cookies.length; i++) {
		var c = cookies[i];
		while(c.charAt(0) == ' ')
			c = c.substring(1, c.length);
		if (c.indexOf(searchName) == 0) {
			value = c.substring(searchName.length, c.length);
			return new Cookie(name, value, valid);
		}
	}
	return null;
}
function eraseCookie (name) {
	writeCookie(new Cookie(name, '' , -1));
}
function Cookie( name, value, valid) {
	this.name = name;
	this.value = value;
	this.valid = valid;
}
Cookie.prototype.getName = function() {
	return this.name;
}
Cookie.prototype.getValue = function() {
	return this.value;
}
Cookie.prototype.getValidity = function() {
	return this.valid;
}
Cookie.prototype.setValidity = function(valid) {
	this.valid = valid;
}
Cookie.prototype.setValue = function(value) {
	this.value = value;
}