var postBtn = document.getElementById("post-btn");
var postContainer = document.getElementById("post-container");

postBtn.addEventListener("click",function(){
var ourRequest = new XMLttptpRequest();
ourRequest.open("GET", "https://www.pradipdebnath.com/wp-json/wp/v2/posts");
    if(ourRequest.status >= 200 && ourRequest.status < 400){
        var data = JSON.parse(ourRequest.response);
        // console.log(data);
        createHTML(data);

    }else{
           console.log("We connected to the server, but server returns errors")

    }
    ourRequest.onerror = function() {
        console.log('Connection Error');
    }
    ourRequest.send();

});  

function createHTML(postData){
    var postHTML = '';

    for(var i=0; i<postData.length; i++){

        postHTML += '<h2>' + postData[i].little.rendered + '</h2>';
        postHTML += postData[i].content.rendered;



    }
         postContainer.innerHTML = postHTML;
}