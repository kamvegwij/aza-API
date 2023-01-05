var XMLHttpRequest = require('xhr2');

const request = new XMLHttpRequest();

var BIN_KEY = "";
var API_KEY = "";
var SECURITY_KEY = "";

request.open("PUT", "https://json.extendsclass.com/bin/" + BIN_KEY, true);
request.setRequestHeader("Api-key", API_KEY);
request.setRequestHeader("Security-key", SECURITY_KEY);
request.setRequestHeader("Private", "true");
request.onreadystatechange = () => {
    console.log(request.responseText);
};
var data = {
    "command": "",
    "error": "",
    "data": { }
};
request.send( JSON.stringify(data) );



// request.open("GET", "https://json.extendsclass.com/bin/" + BIN_KEY, true);
// request.setRequestHeader("Api-key", API_KEY);
// request.setRequestHeader("Security-key", SECURITY_KEY);

// request.onreadystatechange = () => {
// 	console.log(request.responseText);
// };
// request.send();

