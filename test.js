var XMLHttpRequest = require('xhr2');

const request = new XMLHttpRequest();

var bin_key = "4acde40497d4"

request.open("PUT", "https://json.extendsclass.com/bin/" + bin_key, true);
request.setRequestHeader("Api-key", "3347f9ef-70a6-11ed-8b32-0242ac110002");
request.setRequestHeader("Security-key", "12345");
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



// request.open("GET", "https://json.extendsclass.com/bin/" + bin_key, true);
// request.setRequestHeader("Api-key", "3347f9ef-70a6-11ed-8b32-0242ac110002");
// request.setRequestHeader("Security-key", "12345");

// request.onreadystatechange = () => {
// 	console.log(request.responseText);
// };
// request.send();

