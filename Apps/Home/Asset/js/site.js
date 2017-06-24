window.onload = function(){
    var links = document.links;
    var lenth = links.length;

    for(var i=0;i<lenth;i++){
        links[i].onfocus = function(){
            this.blur();
        }
    }

    var platform = document.getElementById('platform');
    var platformPower = document.getElementById('platform-power');

    if(platform && platformPower){
        platformPower.onclick = function(){
            platform.style.display = 'block';
        }

        platform.onmouseout = function(){
            platform.style.display = 'none';

        }
    }

    var faceIcon = document.getElementById('face-icon');
    var comment = document.getElementById('comment');

    if(comment && faceIcon){
        var imgs = faceIcon.getElementsByTagName('img');
        for(var l = imgs.length,i=0;i<l;i++){
            imgs[i].onclick = function(){
                var str = this.name;
                var tclen = comment.value.length;

                comment.focus();

                if(typeof document.selection != "undefined")
                {
                    document.selection.createRange().text = str;
                }
                else
                {
                    comment.value = comment.value.substr(0,comment.selectionStart)+str+comment.value.substring(comment.selectionStart,tclen);
                }
            }
        }
    }
}