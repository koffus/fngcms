<script>
function chatSubmitForm() {
    var formID = document.getElementById('jChatForm');
    CHATTER.postMessage(formID.name.value, formID.text.value);
    UpdateTime();
}

function jChat(maxRows, refresh, tableID, msgOrder) {
    var thisObject = this;
    this.init = function(maxRows, refresh, tableID, msgOrder) {
        this.timerInterval = ((refresh < 5)?5:refresh) * 1000;
        this.timerActive = false;
        this.scanActive = false;
        this.timerID = 0;
        this.tickCount = 0;
        this.lastEventID = 0;
        this.maxLoadedID = 0;
        this.idleStart = 0;
        this.winMode = 0;
        this.messageOrder = msgOrder;
        this.maxRows = maxRows?maxRows:40;
        this.tableRef = document.getElementById(tableID);
        this.fatalError = (this.tableRef == null)?true:false;
        if (!this.fatalError) {
            while(this.tableRef.rows.length)
                this.tableRef.deleteRow(-1);
        } else {
            $.notify({message: 'fatal error: ' + tableID},{type: 'danger'});
        }
        return this.fatalError;
    }
    this.timerStart = function() {
        this.timerActive = true;
        this.scanActive = true;
        dateTime = new Date();
        thisObject.idleStart = Math.round(dateTime.getTime() / 1000);
        thisObject.timerID = setInterval(
            function() {
                thisObject.tickCount++;
                if (thisObject.scanActive) {
                    dateTime = new Date();
                    var url = '{admin_url}/rpc.php';
                    var method = 'plugin.jchat.show';
                    var params = {
                        'lastEvent': thisObject.lastEventID,
                        'start': thisObject.maxLoadedID,
                        'win': thisObject.winMode,
                        'timer': thisObject.timerInterval /1000,
                        'idle': Math.round((dateTime.getTime()/1000) - thisObject.idleStart),
                        };
                    $.reqJSON(url, method, params, function(json) {
                        thisObject.loadData(json.bundle);
                    }, true);
                }
            }, this.timerInterval);
    }
    this.timerStop = function() {
        this.timerActive = false;
        clearInterval(this.timerID);
    }
    this.timerRestart = function() {
        this.timerStop();
        this.timerStart();
    }
    this.loadData = function(bundle) {
        if (this.fatalError)
            return false;
        // Extract passed commands
        var cmdList = bundle[0];
        var cmdLen = cmdList.length;
        for (var i=0; i<cmdLen; i++) {
            var cmd = cmdList[i];
            if (cmd[0] == 'settimer') {
                this.timerInterval = cmd[1] * 1000;
                // alert('new timer interval: '+this.timerInterval);
                this.timerRestart();
            }
            if (cmd[0] == 'reload') {
                document.location = document.location;
                return;
            }
            if (cmd[0] == 'stop') {
                this.timerStop();
            }
            if (cmd[0] == 'clear') {
                while (this.tableRef.rows.length) { this.tableRef.deleteRow(0); }
                thisObject.maxLoadedID = 0;
            }
            if (cmd[0] == 'setLastEvent') {
                this.lastEventID = cmd[1];
            }
            if (cmd[0] == 'setWinMode') {
                this.winMode = cmd[1];
            }
        }
        // Extract passed data
        var data = bundle[1];
        // Add rows
        var len = data.length;
        var loadedRows = 0;
        var lastRow = this.tableRef.rows.length;
        for (var i=0; i<len; i++) {
            var rec = data[i];
            // Skip already loaded data
            if (thisObject.maxLoadedID >= rec['id']) {
                //alert('DUP: '+thisObject.maxLoadedID+' >= '+rec['id']);
                continue;
            }
            loadedRows++;
            var row = this.tableRef.insertRow(this.messageOrder?0:lastRow);
            row.className = ((rec['id'] % 2) == 0)?'jchat_ODD':'jchat_EVEN';
            lastRow++;
            var cell = row.insertCell(0);
            // ** **
            // ** NOTIFICATION FOR ADMIN **
            // ** YOU CAN MAKE CHANGES IN THIS LINE TO CHANGE VIEW OF jChat RECORD **
            // ** **
        cell.innerHTML =
                // 4. DELETE button (for admins)
                '[is.admin] <img src="{skins_url}/images/delete.gif" alt="x" style="cursor: pointer;float:right;" onclick="CHATTER.deleteMessage('+rec['id']+');"/>[/is.admin]'+
                // 2. Image to identify registered user. Also it will contain external link in case if uprofile plugin is enabled
                ((rec['author_id']>0)?('[isplugin uprofile]<a href="'+rec['profile_link']+'" class="user-avatar">[/isplugin]<img src="{home}/uploads/avatars/noavatar.png" class="img-thumbnail" />[isplugin uprofile]</a>[/isplugin] '):'<div class="user-avatar"><img src="{home}/uploads/avatars/noavatar.png" /></div>')+
                // 3. Author's name [ BOLD ]
                '<span class="user">'+rec['author']+'</span><br/>'+
                // 1. Floating DIV with add date/time
                rec['cdate']+ //'<span class="time" title="'+rec['datetime']+'">'+rec['time']+'</span>'+
                // 5. New line delimiter
                '<br/> '+
                // 6. Chat message test
                '<p> '+rec['text']+'</p>';
        thisObject.maxLoadedID = rec['id'];
        }
        if (loadedRows>0) {
            // Clear old rows from chat [ if needed ]
            while (thisObject.tableRef.rows.length > thisObject.maxRows)
                thisObject.tableRef.deleteRow(this.messageOrder?thisObject.tableRef.rows.length-1:0);
            thisObject.tableRef.parentNode.scrollTop = thisObject.tableRef.parentNode.scrollHeight;
        }
        UpdateTime();
    }
    this.addMessage = function(msg, className) {
        if (this.fatalError)
            return false;
        var lastRow = this.tableRef.rows.length;
        var row = this.tableRef.insertRow(this.messageOrder?0:lastRow);
        row.className = className;
        var cell = row.insertCell(0);
        cell.innerHTML = msg;
        this.tableRef.parentNode.scrollTop = this.tableRef.parentNode.scrollHeight;
    }
    // POST new message
    this.postMessage = function(name, text) {
        $('#jChatSubmit').attr('disabled','disabled');
        var url = '{admin_url}/rpc.php';
        var method = 'plugin.jchat.add';
        var params = {[not-logged]'name': name,[/not-logged] 'text': text,'win': this.winMode,'lastEvent': this.lastEventID,'start': this.maxLoadedID};
        $.reqJSON(url, method, params, function(json) {
            thisObject.loadData(json.bundle);
            $.notify({message: 'Message posted'},{type: 'success'});
            $('#jChatText').val('');
        });
        setTimeout(function() {
            $('#jChatSubmit').removeAttr('disabled','');
        }, {rate_limit} * 1000);
        // Restart idle timer
        dateTime = new Date();
        thisObject.idleStart = Math.round(dateTime.getTime() / 1000);
        // Restart scanner if it's turned off
        if (!this.timerActive)
            this.timerStart();
    }

    // DELETE message
    this.deleteMessage = function(id) {
        var url = '{admin_url}/rpc.php';
        var method = 'plugin.jchat.delete';
        var params = {'id': id,'win': this.winMode,'lastEvent': this.lastEventID,'start': this.maxLoadedID};
        $.reqJSON(url, method, params, function(json) {
            thisObject.loadData(json.bundle);
            $.notify({message: 'Message deleted'},{type: 'info'});
        });
        // Restart idle timer
        dateTime = new Date();
        thisObject.idleStart = Math.round(dateTime.getTime() / 1000);
        // Restart scanner if it's turned off
        if (!this.timerActive)
            this.timerStart();
    }
    this.init(maxRows, refresh, tableID, msgOrder);
}

function jchatCalculateMaxLen(oId, tName, maxLen) {
    var delta = maxLen - oId.value.length;
    var tId = document.getElementById(tName);

    if (tId) {
        tId.innerHTML = delta;
        tId.style.color = (delta > 0)?'black':'red';
    }
}

function jchatProcessAreaClick(event) {
    var evt=event?event:window.event;
    if (!evt)
        return;
    var trg=evt.target?evt.target:evt.srcElement;
    if (!trg)
        return;
    if (trg.className != 'user')
        return;
    var mText = document.getElementById('jChatText');
    if (mText) {
        mText.value += '@'+trg.innerHTML+': ';
        mText.focus();
    } else {
        $.notify({message: 'Cannot add this nickname, sorry.'},{type: 'info'});
    }
}

</script>
