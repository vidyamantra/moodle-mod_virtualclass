(function (window) {
    /**
     * {virtualclass1} {virtualclass2} are elements you passes with getString function eg:-
     *   virtualclass.lang.getString('operaBrowserIssue', ['opeara', 27]);
     *   opera and 27 will be replaced over the {virtualclass1} and {virtualclass2} resepectively for particular line of language file.
     * @type type
     */
    var message = {
        'notSupportCanvas': 'This browser does not support Canvas. Please update your browser with the latest version' +
        'For more information about Canvas, click on the link given here <a href="http://en.wikipedia.org/wiki/Canvas_element/">Canvas</a>',
        'notSupportGetUserMedia': 'The browser does not support getUserMedia for webRtc. Please update your browser with the latest version'
        + ' For more information about getUuserMedia, click on the link given here <a href="http://dev.w3.org/2011/webrtc/editor/getusermedia.html">getUserMedia</a>',
        'notSupportPeerConnect': 'The browser is unable to create Peer Connection object for WebRtc' +
        ' Please update your browser with the latest version.' +
        ' For more information about WebRtc, click on the link given here <a href="http://www.webrtc.org/">WebRtc</a>',
        'notSupportWebSocket': 'This browser does not support WebSocket. Please update your browser with the latest version. ' +
        'For more information about WebSocket, click on the link given here <a href="http://www.websocket.org/">WebSocket</a>.',
        'notSupportWebRtc': 'This browser does not support WebRTC. Please update your browser with the latest version.' +
        ' For more information about WebRtc, click on the link given here <a href="http://www.webrtc.org/">WebRtc</a>',
        'line': 'Line',
        'rectangle': 'Rectangle',
        'triangle': 'Triangle',
        'oval': 'Oval',
        'assign': 'Assign',
        'reclaim': 'Reclaim',
        'freeDrawing': 'Free hand',
        'text': 'Text',
        'replay': 'Replay',
        'activeAll': 'Active All',
        'clearAll': 'Clear All',
        'drawArea': 'Draw Area',
        'totRcvdPackets': 'Total Received Packets',
        'perSecRcvdData': 'Per Second Recevied Packet',
        'totSentPackets': 'Total Sent Packets',
        'perSecSentPacket': 'Per Second Sent Packets',
        'perSecond': 'Per Second',
        'sentPackets': 'Sent <br/><span>Packets</span>',
        'receviedPackets': 'Received <br/><span>Pacekts</span>',
        'total': 'Total',
        'dataDetails': 'Data Details',
        'sentMessageInfo': 'Sent Message <br/><span>Information</span>',
        'receivedMessageInfo': 'Received Message <br/><span>information</span>',
        'wbrtcMsgFireFox': 'You can click on  "Share Selected Devices"' +
        ' to share your microphone and camera with other users',
        'wbrtcMsgChrome': 'You can click on deny button for not sharing your microphone and camera with other users.' +
        'or click on allow button to share your microphone and camera with other users.',
        'canvasDrawMsg': 'You can click on any tool to draw object ' +
        'with a mouse click, mouse move and mouse up in the Draw Area',
        'clearAllWarnMessage': 'Do you really want to clear this whiteboard?',
        'cof': 'connection off',
        'askForConnect': 'You will be able to perform this action only when other user get connected',
        'msgForReload': "Please reload this page to continue editing.",
        'msgStudentForReload': "Please reload this page.",
        'reload': "Reload",
        'whiteboard': 'Whiteboard',
        'screenshare': 'Screen Share',
        'sessionend': "Session End",
        'audioTest': "Your voice will be recorded and played back to you. \n Press Ok and speak something for few seconds.",
        'chatEnable': "Disable Chat",
        'chatDisable': "Enable Chat",
        'assignEnable': "Transfer Controls",
        'assignDisable': "Reclaim Controls",

        'editorRichDisable': "Write Mode",
        'editorRichEnable': "Read Only",

        'editorCodeDisable': "Writable",
        'editorCodeEnable': "Unwritable",

        'audioEnable': "Mute",
        'audioDisable': "Unmute",
        'audioOff': "Mute",
        'audioOn': "Mute",
        'minCommonChat': "Hide Chat Window",
        'maxCommonChat': "Show Chat Window",
        'miniuserbox': "Hide User Box",
        'maxuserbox': "Show User Box",
        'miniUserList': "Hide User List",
        'maxUserList': "Show User List",
        'startnewsession': "Session has not been saved, do you really want to end this session?",
        'DevicesNotFoundError': "Please check your Webcam(camera/microphone) connection.",
        'PermissionDeniedError': "Webcam access has been denied.",
        'PERMISSION_DENIED': "You denied to access your Webcam(camera/microphone).",
        'notSupportBrowser': "Firefox {virtualclass1} does not support Screen sharing.",
        'disableSpeaker': "Mute",
        'enableSpeaker': "Unmute",
        'notSupportChrome': 'Please update your browser {virtualclass1} {virtualclass2} to Google Chrome 40 or above',
        'errcanvas': 'canvas',
        'errwebSocket': 'Web Socket',
        'errgetusermedia': 'getUserMedia',
        'errindexeddb': 'indexedDb',
        'errwebworker': 'Web worker',
        'errwebaudio': 'Web audio',
        'errtypedarray': 'Typed array',
        'errscreenshare': 'Screen share',
        'operaBrowserIssue': 'Your browser {virtualclass1} {virtualclass2} is partially supported. You will not be able to share your screen with Learners, We fully support chrome and Firefox',
        'commonBrowserIssue': 'Your browser {virtualclass1} {virtualclass2} is not supported, We support Chrome and Firefox.',
        'chFireBrowsersIssue': 'Your browser {virtualclass1} {virtualclass2}  needs to updated, We support Chrome 40 and Firefox 35.',
        'studentSafariBrowserIssue': 'Your browser {virtualclass1} {virtualclass2} does not able to share your Cam with other users, We fully support Chrome  and Firefox.',
        'ieBrowserIssue': 'Your browser Internet Explorer is not supported, We fully support Chrome  and Firefox.',
        'ios7support': "For Apple, Virtual Class supports iOS 8 and higher versions.",
        'supportDesktop': "Virtual Class can be used by presenter on desktop only, not tablets or mobiles.",
        'notSupportIphone': "Sorry. Virtual Class doesn't support mobile phones.",
        'InternalError': "Please ensure that same webcam is not being used <br /> in multiple browsers or multiple applications. ",
        'SourceUnavailableError': 'Please ensure that same webcam is not being used in multiple browsers or multiple applications.',
        'teacherSafariBrowserIssue': 'For presenter, Safari is not supported. Please switch to Chrome or Firefox.',
        'safariBrowserIssue': 'Your browser Safari {virtualclass1} is not supported, We fully support Chrome  and Firefox',
        'savesession': 'Do you want to save current Session ?',
        'plswaitwhile': 'Please wait a while....',
        'downloadedFile': "Your files are downloading....,  <br />Number of file has been downloaded is {virtualclass1}",
        'overallprogress': 'Overall Progress',
        'playsessionmsg': 'Click ‘play’ to start playing session.',
        'askplayMessage': '<span id="askplaymsg"> Should we start playing session?</span><br /><span id="remaingmsg">Remaining data could be downloaded in background.</span>',
        'youTubeUrl': 'Enter YouTube Video URL.',
        'shareYouTubeVideo': 'Share YouTube Video',
        'shareAnotherYouTubeVideo': 'Share Another YouTube Video',
        'editorCode': 'Editor Code',
        'editorRich': 'Editor Rich',
        'teachermayshow': 'YouTube video will be shared shortly.',
        'youtubeshare': 'YouTube Video Share',
        'writemode': 'Write Mode',
        'readonlymode': 'Read Only',
        'msgForDownloadStart' : 'Unable to save data. <br /> Preparing data for download',
        'msgForWhiteboard' : 'Empty Whiteboard.',
        'educator' : 'Educator',
        'supportDesktopOnly' : 'We support only desktop computer not  any tablet and mobile for teacher.',
        'download' : 'Download',
        'downloadFile' : 'Download File',
        'synchMessage' : 'Please wait a while.  Synchronizing with new content.',
        'iosAudEnable' : 'Tap here for enable the audio',
        'studentAudEnable' : 'Student Audio Enable',
        'screensharealready' : "Already the whole screen is being shared.",
        'editorsynchmsg' : 'Editor is not in sync, please wait for few seconds and try again ',
        'canvasmissing' : 'Canvas is missing in your browsers. Please update the latest version of your browser',
        'downloadFile'  : 'Download File',
        'filenotsave'  : 'Your file could not be saved.',
        'muteAll' : 'Mute All',
        'unmuteAll' : 'Unmute All'
};
    window.message = message;
})(window);
