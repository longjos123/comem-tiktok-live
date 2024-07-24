const { WebcastPushConnection } = require('tiktok-live-connector');
const express = require('express');
const axios = require('axios');
const cors = require('cors');
const app = express();

app.use(cors());

let tiktokUsername = process.argv[2]; // Nhận username từ command line argument

if (!tiktokUsername) {
    console.error('No TikTok username provided');
    process.exit(1);
}

let connection = new WebcastPushConnection(tiktokUsername);

let clients = [];

connection.connect().then(state => {
    console.log(`Connected to roomId ${state.roomId}`);
}).catch(err => {
    console.error('Failed to connect', err);
});

connection.on('chat', data => {
    console.log(`${data.uniqueId} (userId: ${data.userId}) sent: ${data.comment}`);

    // Gửi comment tới API Laravel để lưu vào database
    axios.post('http://localhost:8000/api/comments', {
        unique_id: data.uniqueId,
        user_id: data.userId,
        comment: data.comment
    }).catch(error => {
        console.error('Error saving comment:', error);
    });

    // Gửi comment tới tất cả client đang kết nối
    clients.forEach(client => client.res.write(`data: ${JSON.stringify(data)}\n\n`));
});

app.get('/comments-stream', (req, res) => {
    res.setHeader('Content-Type', 'text/event-stream');
    res.setHeader('Cache-Control', 'no-cache');
    res.setHeader('Connection', 'keep-alive');
    res.flushHeaders();

    clients.push({ req, res });

    req.on('close', () => {
        clients = clients.filter(client => client.req !== req);
    });
});

app.listen(3000, () => {
    console.log('Server is running on port 3000');
});
