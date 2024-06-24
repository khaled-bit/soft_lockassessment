import Echo from "laravel-echo";

window.Pusher = require('pusher-js');

// Assuming you have the following environment variables set in your .env file
// and they are passed correctly to your JavaScript via Mix or another method
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    encrypted: true,
    forceTLS: true
});
