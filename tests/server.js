#!/usr/bin/env node
'use strict'

let server = require('net').createServer()
let fs = require('fs')
let path = require('path')

let cid = 0

module.exports = server // for testing

onEmit(server, { ignore: ['connection', 'listening', 'error'] }, function (eventName) {
  console.log('[server] event:', eventName)
})

server.on('connection', function (c) {
  let gotData = false
  let _cid = ++cid

  console.log('[server] event: connection (socket#%d)', _cid)

  onEmit(c, { ignore: ['lookup', 'error'] }, function (eventName) {
    console.log('[socket#%d] event:', _cid, eventName)
  })

  c.on('lookup', function (err, address, family) {
    if (err) {
      console.log('[socket#%d] event: lookup (error: %s)', _cid, err.message)
    } else {
      console.log('[socket#%d] event: lookup (address: %s, family: %s)', _cid, address, family)
    }
  })

  c.on('data', function (chunk) {
    if ( chunk.toString().indexOf('DELETE / HTTP') >= 0 ) {
      server.close();
    }
    if ( chunk.toString().indexOf('GET /download') >= 0 ) {
      c.write('HTTP/1.1 200 OK\r\n')
        c.write('Date: ' + (new Date()).toString() + '\r\n')
        c.write('Connection: close\r\n')
        c.write('Content-Type: text/plain\r\n')
        c.write('Access-Control-Allow-Origin: *\r\n')
        c.write('\r\n')
        fs.readFile(path.join(__dirname, 'data.txt'), function(error, content) {
          c.end(content, 'utf-8')
        })
    } else {
      console.log('--> ' + chunk.toString().split('\n').join('\n--> '))
      if (!gotData) {
        gotData = true
        let content = '';
        content += 'HTTP/1.1 200 OK\r\n'
        content += 'Date: ' + (new Date()).toString() + '\r\n'
        content += 'Connection: close\r\n'
        content += 'Content-Type: text/plain\r\n'
        content += 'Access-Control-Allow-Origin: *\r\n'
        content += '\r\n'
        content += chunk.toString()
        c.end(content)
      }
    }
  })

  c.on('error', function (err) {
    console.log('[socket#%d] event: error (msg: %s)', _cid, err.message)
  })
})

server.on('listening', function () {
  let port = server.address().port
  console.log('[server] event: listening (port: %d)', port)
})

server.on('error', function (err) {
  console.log('[server] event: error (msg: %s)', err.message)
})

let port = process.argv[2] || process.env.PORT

if (port) {
  server.listen(port)
}

function onEmit (emitter, opts, cb) {
  let emitFn = emitter.emit
  emitter.emit = function (eventName) {
    if (opts.ignore.indexOf(eventName) === -1) cb.apply(null, arguments)
    return emitFn.apply(emitter, arguments)
  }
}
