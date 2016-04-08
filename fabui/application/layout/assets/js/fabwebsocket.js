FabWebSocket = function(n, t) {
    this.port = t, this.host = n;
    var o = {};
    this.getHost = function() {
        return this.host
    }, this.getPort = function() {
        return this.port
    }, this.getConn = function() {
        return this.conn
    }, this.bind = function(n, t) {
        return o[n] = o[n] || [], o[n].push(t), this
    }, this.send = function(n, t) {
        return this.conn.send(t), this
    }, this.connect = function() {
        var n = "ws://" + this.host + ":" + this.port;
        this.conn = window.MozWebSocket ? new MozWebSocket(n) : new WebSocket(n), this.conn.onmessage = function(n) {
            i("message", n.data)
        }, this.conn.onclose = function() {
            i("close", null)
        }, this.conn.onopen = function() {
            i("open", null)
        }, this.conn.onerror = function(n) {
            i("error", null)
        }
    }, this.disconnect = function() {
        this.conn.close()
    };
    var i = function(n, t) {
        var i = o[n];
        if ("undefined" != typeof i)
            for (var s = 0; s < i.length; s++) i[s](t)
    }
};