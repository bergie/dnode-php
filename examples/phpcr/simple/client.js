#!/usr/bin/env node

require.paths.push('/usr/local/lib/node_modules');

var dnode = require('dnode');

dnode.connect(7070, function (remote, conn) {
	remote.getNodes('default', '/', function (value, exception, error) {
		console.log(value);
		console.log('Exception: ' + exception + ' thrown with message: ' + error);
		conn.end();
	});
});

dnode.connect(7070, function (remote, conn) {
	remote.getPropertyValue('default', '/jcr:primaryType', function (value, exception, error) {
		console.log(value);
		console.log('Exception: ' + exception + ' thrown with message: ' + error);
		conn.end();
	});
});

dnode.connect(7070, function (remote, conn) {
	remote.itemExists('default', '/Sir/Lancelot/Likes/Blue', function (value, exception, error) {
		console.log('Item at specified path doesn\'t exist.');
		conn.end();
	});
});

