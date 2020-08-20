import {Client} from "thruway.js";

const wamp = new Client(
    'ws://' + location.host + '/',
    'healthz'
);

setInterval(() => fetch('http://' + location.host + '/healthz').then(response => response.json()).then(response => {
    document.getElementById('xhr').innerHTML = response.result;
}), 1338);

setInterval(() => wamp.call('healthz').toPromise().then(args => args.args[0]).then(response => {
    document.getElementById('websocket').innerHTML = response.result;
}), 1337);
