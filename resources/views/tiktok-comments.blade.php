<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TikTok Live Comments</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1>TikTok Live Comments</h1>
        <form id="tiktok-form">
            <div class="mb-3">
                <label for="username" class="form-label">TikTok Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <button type="submit" class="btn btn-primary">Get Comments</button>
        </form>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <h2>Chưa trả lời</h2>
                <div id="unreplied-comments"></div>
            </div>
            <div class="col-md-6">
                <h2>Đã trả lời</h2>
                <div id="replied-comments"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.getElementById('tiktok-form').addEventListener('submit', function(e) {
            e.preventDefault();

            let username = document.getElementById('username').value;
            axios.post('{{ route('tiktok.comments.get') }}', {
                    username
                })
                .then(response => {
                    console.log(response.data);
                    alert('Script is running');
                })
                .catch(error => {
                    console.error(error);
                });
        });

        // Lắng nghe comment theo thời gian thực
        let eventSource = new EventSource('http://localhost:3000/comments-stream');
        eventSource.onmessage = function(e) {
            let comment = JSON.parse(e.data);
            addCommentToDOM(comment);
        };

        function addCommentToDOM(comment) {
            let columnDiv = comment.replied ? document.getElementById('replied-comments') : document.getElementById(
                'unreplied-comments');
            console.log(comment);
            let commentElem = document.createElement('div');
            let uniqueCmtId = comment.uniqueId + comment.createTime;
            commentElem.className = 'comment border p-2 mb-2';
            commentElem.setAttribute('comment-id', uniqueCmtId); // Gán thuộc tính comment-id

            let commentText = document.createElement('span');
            commentText.textContent = `${comment.uniqueId}: ${comment.comment}`;
            commentElem.appendChild(commentText);

            let replyLink = document.createElement('a');
            replyLink.href = '#';
            replyLink.className = 'reply-link text-primary ml-3';
            replyLink.textContent = 'Trả lời';
            replyLink.onclick = function(e) {
                e.preventDefault();
                showReplyInput(uniqueCmtId, comment.uniqueId);
            };
            commentElem.appendChild(replyLink);

            columnDiv.appendChild(commentElem);
        }

        function showReplyInput(commentId, uniqueId) {
            let commentElem = document.querySelector(`.comment[comment-id="${commentId}"]`);

            if (!commentElem) {
                console.error('Comment element not found');
                return;
            }

            let replyInputElem = document.getElementById(`reply-input-${commentId}`);
            if (!replyInputElem) {
                replyInputElem = document.createElement('div');
                replyInputElem.id = `reply-input-${commentId}`;
                replyInputElem.className = 'mt-2';

                let input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control';
                input.placeholder = `Trả lời @${uniqueId}`;
                replyInputElem.appendChild(input);

                let sendButton = document.createElement('button');
                sendButton.className = 'btn btn-primary mt-2';
                sendButton.textContent = 'Gửi';
                sendButton.onclick = function() {
                    replyToComment(commentId, input.value);
                };
                replyInputElem.appendChild(sendButton);

                commentElem.appendChild(replyInputElem);
            }
        }

        // Hàm gửi phản hồi đến server
        function replyToComment(commentId, replyText) {
            fetch('/reply-comment', { // Thay đổi endpoint nếu cần
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        commentId: commentId,
                        replyText: replyText
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Reply sent:', data);
                    alert('Phản hồi đã được gửi!');
                    // Xóa input sau khi gửi thành công
                    let replyInputElem = document.getElementById(`reply-input-${commentId}`);
                    if (replyInputElem) {
                        replyInputElem.remove();
                    }
                })
                .catch(error => {
                    console.error('Error sending reply:', error);
                });
        }
    </script>
</body>

</html>
