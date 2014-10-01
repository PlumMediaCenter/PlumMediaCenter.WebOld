start "Chrome" chrome --new-window http://127.0.0.1:8080/debug?port=5858 http://localhost:3000/index.html
start node-inspector
node --debug-brk ./server/app.js

