(function(){
var submitbuttons = [];
var responses = [];
var request_uri, request_time, request_challenge, waitmessage;

function init() {
	disableForms();
	if (responses.length > 0) invokeAjax();
}

function disableForms() {
	each(document.forms, function(){
		var submits = [];
		var found = false;
		each(this.elements, function(){
			if (this.type == "submit") {
				submits.push(this);
			}else if(this.name == "wbcos_comment_response") {
				responses.push(this);
				found = true;
			}else if(this.name == "wbcos_comment_time") {
				request_time = this.value;
			}else if(this.name == "wbcos_comment_challenge") {
				request_challenge = this.value;
			}else if(this.name == "wbcos_comment_requesturi") {
				request_uri = this.value;
			}else if(this.name == "wbcos_comment_waitmessage") {
				waitmessage = this.value;
			}
		});
		if (found) {
			each(submits, function(){
				this.disabled = true;
				var text = this.value;
				this.value = waitmessage;
				submitbuttons.push([this, text]);
			});
		}
	});
}

function invokeAjax() {
	var req = getRequest();
	var uri = request_uri + "?wbcos_request_time=" + request_time
		 + "&wbcos_request_challenge=" + request_challenge;
	try {
		req.abort();
		req.open("GET", uri, true);
		req.onreadystatechange = function () {
			if (req.readyState == 4) {	//complete
				if(req.status == 200) {
					updateForms(req.responseText);
				}else{
					error("request end with status: " + req.status);
				}
			}
		}
		req.send(null);
	} catch (e) {
		error("request error: " + e);
	}
}

function updateForms(key) {
	each(responses, function(){
		this.value = key;
	});
	each(submitbuttons, function(){
		this[0].value = this[1];
		this[0].disabled = false;
	});
}



function addEvent(element, eventname, fun, iscapture) {
	if (element.addEventListener) {
		element.addEventListener(eventname, fun, iscapture);
	} else {
		element.attachEvent("on" + eventname, fun);
	}
}

function getRequest() {
	return window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();
}

function each(arr, fun) {
	for(var i=0, l=arr.length; i<l; i++) {
		fun.apply(arr[i], [i]);
	}
}

function error(msg) {
	var d = document.createElement("div");
	d.innerHTML = msg;
	document.getElementsByTagName("body")[0].appendChild(d);
}

addEvent(window, "load", init, false);

})();
