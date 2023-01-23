$("<style>")
    .prop("type", "text/css")
    .html("\
    .modal {\
        display: none;\
        position: fixed;\
        z-index: 1;\
        padding-top: 100px;\
        left: 0;\
        top: 0;\
        width: 100%;\
        height: 100%;\
        overflow: auto;\
        background-color: rgb(0,0,0);\
        background-color: rgba(0,0,0,0.4);\
        }\
    .modal-content {\
        background-color:white;\
        color:#246299;\
        font-family:Verdana;\
        margin: auto;\
        padding: 20px;\
        border: 1px solid #246299;\
        width: 500px;\
        min-height:140px;\
        height: 160px;\
        border-radius:10px;\
        }\
    .yes {\
        background-color:#72BE45;\
        padding: 10px;\
        color: white;\
        float: left;\
        font-size: 17px;\
        font-weight: bold;\
        border-radius:10px;\
        padding:15px;\
        margin:5px;\
        width:180px;\
        text-align:center;\
        }\
    .yes:hover,\
    .yes:focus {\
        background-color:black;\
        color: #fff;\
        text-decoration: none;\
        cursor: pointer;\
        }\
    .no {\
        background-color:#F2724F;\
        padding: 10px;\
        color: white;\
        float: right;\
        font-size: 17px;\
        font-weight: bold;\
        border-radius:10px;\
        padding:15px;\
        margin:5px;\
        width:180px;\
        text-align:center;\
    }\
    .no:hover,\
    .no:focus {\
        background-color:black;\
        color: #fff;\
        text-decoration: none;\
        cursor: pointer;\
    }\
    .msgTitle {\
        text-align:center;\
        font-weight:bold;\
        font-size:20px;\
        margin:0px;\
    }\
    .msgText {\
        text-align:center;\
    }")
    .appendTo("head");
$(document.body).prepend('\
    <div id="msg" class="modal">\
        <div class="modal-content" id="msgSpinner" style="width: 45px;height: 45px;min-height: 45px;">\
            <img src="/legacy/img/loading.gif" style="width:40px;">\
        </div>\
        <div class="modal-content" id="msgBox">\
            <p class="msgTitle" id="msgTitle"></p>\
            <p class="msgText" id="msgText"></p>\
            <span class="no" id="msgNo"></span>\
            <span class="yes" id="msgYes"></span>\
        </div>\
    </div>');
class ModalDialog 
{
    showMask()
    {
        $("#msg").show();
        $("#msgSpinner").show();
        $("#msgBox").hide();
    }
    showDialog(title, body, yesText, noText, yesFunc, noFunc) {
        $("#msg").show();
        $("#msgBox").show();
        $("#msgSpinner").hide();

        $("#msgTitle").text(title);
        $("#msgText").text(body);
        $("#msgYes").text(yesText);
        $("#msgNo").text(noText);

        $("#msgYes").click({callMe:yesFunc},this.callBack);
        $("#msgNo").click({callMe:noFunc},this.callBack);
    }
    callBack(event)
    {
        $("#msg").hide();
        event.data.callMe();
    }

}
var modalDialog = new ModalDialog;
