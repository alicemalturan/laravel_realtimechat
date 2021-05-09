window._ = require('lodash');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

 import Echo from 'laravel-echo';

 window.Pusher = require('pusher-js');

 window.Echo = new Echo({
     broadcaster: 'pusher',
     wsHost: window.location.hostname,
     key: process.env.MIX_PUSHER_APP_KEY,
     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
     forceTLS: false,
     encrypted:false,
     wsPort: 6001,

     auth:{
         headers: {
             'X-CSRF-Token':$("meta[name=token]").attr('content'),
             'X-App-ID': '1121833'
         }
     },
     authEndpoint:'laravel-websockets/auth/',
     enabledTransports:['ws','wss']
});

window.Echo.channel('private-chat').listen('.message',(val)=>{
    var me_username=$("meta[name=me]").attr("content");
    var cls="by-me";
    var float="left";
    if (me_username==val.from.username.toString()){float="right";cls="by-other";}
    var appended=$(
        '<li class="'+cls+'">  <span class="avatar-letter float-'+float+' avatar-letter-'+val.from.username[0].toLowerCase()+' circle"></span> <div class="chat-content"> <div class="chat-meta">'+val.from.username+'  </div> '+val.message+' <div class="clearfix"></div> </div> </li>'
    );
    $(".mainchat_list").append(appended);
});
