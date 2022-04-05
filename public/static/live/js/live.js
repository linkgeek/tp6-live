var wsUrl = "ws://120.25.227.106:8088";

var websocket = new WebSocket(wsUrl);

//实例对象的onopen属性
websocket.onopen = function (evt) {
    console.log("conected-swoole-success");
};

// 实例化 onmessage
websocket.onmessage = function (evt) {
    formatData(evt.data);
    console.log("ws-server-return-data: " + evt.data);
};

//onclose
websocket.onclose = function (evt) {
    console.log("close");
};

//onerror
websocket.onerror = function (evt, e) {
    console.log("error:" + evt.data);
};

// 格式化数据，根据显示类型渲染
function formatData(data) {
    data = JSON.parse(data);
    if (data.show_type == 'chat') {
        initChat(data);
    } else {
        initOuts(data);
    }
}

// 渲染赛况
function initOuts(data) {
    let html = "<div class=\"frame\">";
    html += "<h3 class=\"frame-header\">";
    html += "<i class=\"icon iconfont icon-shijian\"></i>第" + data.type + "节 01：30";
    html += "</h3>";
    html += "<div class=\"frame-item\">";
    html += "<span class=\"frame-dot\"></span>";
    html += "<div class=\"frame-item-author\">";
    if (data.logo) {
        html += '<img src="' + data.logo + '" width="20px" height="20px"/> ';
    }
    html += data.title;
    html += "</div>";
    html += "<p>" + data.content + "</p>";
    if (data.image) {
        html += '<p><img src="' + data.image + '" width="40%" /></p>';
    }
    html += "</div>";
    html += "</div>";
    $("#match-result").prepend(html);
}

// 渲染聊天室
function initChat(data) {
    let html = '<div class="comment">';
    html += '<span>' + data.user_name + '：</span>';
    html += '<span>' + data.content + '</span>';
    $("#comments").prepend(html);
}