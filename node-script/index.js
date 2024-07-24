const express = require('express');
const bodyParser = require('body-parser');
const { WebcastPushConnection } = require('tiktok-live-connector');

const app = express();
app.use(bodyParser.json());

const clients = [];

// Endpoint để stream các comment
app.get('/comments-stream', (req, res) => {
    res.setHeader('Content-Type', 'text/event-stream');
    res.setHeader('Cache-Control', 'no-cache');
    res.setHeader('Connection', 'keep-alive');
    res.flushHeaders();

    // Thêm client vào danh sách
    clients.push({ req, res });

    // Xử lý khi client ngắt kết nối
    req.on('close', () => {
        clients = clients.filter(client => client.req !== req);
    });
});

app.post('/reply-comment', (req, res) => {
    const { commentId, replyText } = req.body;
    console.log(`Received reply for comment ${commentId}: ${replyText}`);

    // Xử lý gửi phản hồi đến TikTok hoặc dịch vụ của bạn
    // Ví dụ: gửi phản hồi đến một API
    sendReplyToTikTok(commentId, replyText)
        .then(() => {
            res.json({ status: 'success', message: 'Reply sent successfully!' });
        })
        .catch(error => {
            console.error('Error sending reply:', error);
            res.status(500).json({ status: 'error', message: 'Failed to send reply.' });
        });
});

function sendReplyToTikTok(commentId, replyText) {
    return new Promise((resolve, reject) => {
        // Thay thế bằng mã gửi phản hồi thực tế đến TikTok
        console.log(`Sending reply to TikTok for comment ${commentId}: ${replyText}`);
        setTimeout(() => {
            resolve(); // Giả lập gửi thành công
        }, 1000); // Giả lập độ trễ 1 giây
    });
}

app.listen(3000, () => {
    console.log('Server is running on port 3000');
});
