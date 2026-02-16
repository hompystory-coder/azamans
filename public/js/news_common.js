/**
 * MVC 뉴스 공통 JavaScript
 * 모든 뉴스에서 사용하는 좋아요, 댓글 기능
 */

// 좋아요 토글
function likePost(newsId, postUid) {
    if (!newsId || !postUid) {
        console.error('newsId or postUid is missing');
        return;
    }
    
    fetch('/news/' + newsId + '/like/' + postUid, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'}
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // 하트 아이콘 업데이트
            const likeBtn = document.getElementById('likeBtn');
            const heartIcon = likeBtn.querySelector('.heart-icon');
            const likeCount = document.getElementById('likeCount');
            
            if (data.liked) {
                likeBtn.classList.add('liked');
                heartIcon.textContent = '❤️';
            } else {
                likeBtn.classList.remove('liked');
                heartIcon.textContent = '🤍';
            }
            
            // 카운트 업데이트
            likeCount.textContent = data.like_count || 0;
        } else {
            alert(data.message || '좋아요 처리 중 오류가 발생했습니다.');
        }
    })
    .catch(err => {
        console.error('Like error:', err);
        alert('좋아요 처리 중 오류가 발생했습니다.');
    });
}

// 댓글 작성
function submitComment(e, newsId, postUid) {
    e.preventDefault();
    
    const content = document.getElementById('commentContent').value.trim();
    
    if (!content) {
        alert('댓글 내용을 입력해주세요.');
        return;
    }
    
    const formData = new FormData();
    formData.append('content', content);
    
    fetch('/news/' + newsId + '/comment/' + postUid, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message || '댓글이 등록되었습니다.');
            location.reload();
        } else {
            alert(data.message || '댓글 등록 중 오류가 발생했습니다.');
        }
    })
    .catch(err => {
        console.error('Comment error:', err);
        alert('댓글 등록 중 오류가 발생했습니다.');
    });
}

// 댓글 삭제
function deleteComment(newsId, postUid, commentUid) {
    if (!confirm('댓글을 삭제하시겠습니까?')) return;
    
    fetch('/news/' + newsId + '/' + postUid + '/comment/' + commentUid + '/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'}
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message || '댓글이 삭제되었습니다.');
            location.reload();
        } else {
            alert(data.message || '댓글 삭제에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error('Delete comment error:', err);
        alert('댓글 삭제 중 오류가 발생했습니다.');
    });
}

// 뉴스 삭제
function deletePost(newsId, postUid) {
    if (!confirm('정말 삭제하시겠습니까?')) return;
    
    fetch('/news/' + newsId + '/delete/' + postUid, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'}
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            location.href = data.redirect;
        }
    })
    .catch(err => {
        console.error('Delete post error:', err);
        alert('삭제 중 오류가 발생했습니다.');
    });
}
