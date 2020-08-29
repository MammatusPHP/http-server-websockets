import {Client} from "thruway.js";

const wamp = new Client(
    'ws://' + location.host + '/',
    'healthz'
);

wamp.topic('healthz').map(args => args.args[0]).subscribe((broadcast => {
    document.getElementById('websocket_subscription').innerHTML = broadcast.status;
}));

setInterval(() => fetch('http://' + location.host + '/healthz').then(response => response.json()).then(response => {
    document.getElementById('xhr').innerHTML = response.result;
}), 1338);

setInterval(() => wamp.call('healthz').toPromise().then(args => args.args[0]).then(response => {
    document.getElementById('websocket_rpc').innerHTML = response.result;
}), 1337);

setInterval(() => wamp.publish('client_healthz', {
    message: 'Healthy client reporting in!',
    href: location.href,
}), 1336);
