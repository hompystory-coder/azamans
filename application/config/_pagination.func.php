<?php
/**
 * Pagination Helper Functions
 * 페이징 처리 헬퍼 함수
 */

/**
 * 페이징 HTML 생성
 * 
 * @param int $currentPage 현재 페이지
 * @param int $totalPages 전체 페이지 수
 * @param string $baseUrl 기본 URL
 * @param array $params 추가 파라미터
 * @return string HTML
 */
function renderPagination($currentPage, $totalPages, $baseUrl, $params = []) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<div class="pagination">';
    
    // 파라미터 문자열 생성
    $paramString = '';
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            if (!empty($value)) {
                $paramString .= '&' . urlencode($key) . '=' . urlencode($value);
            }
        }
    }
    
    // 처음 버튼
    if ($currentPage > 1) {
        $html .= '<a href="' . $baseUrl . '?page=1' . $paramString . '" class="page-link page-first">처음</a>';
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage - 1) . $paramString . '" class="page-link page-prev">이전</a>';
    }
    
    // 페이지 번호
    $startPage = max(1, $currentPage - 5);
    $endPage = min($totalPages, $currentPage + 5);
    
    // 시작 페이지 조정
    if ($endPage - $startPage < 10) {
        if ($startPage == 1) {
            $endPage = min($totalPages, $startPage + 10);
        } else {
            $startPage = max(1, $endPage - 10);
        }
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        $activeClass = ($i == $currentPage) ? ' active' : '';
        $html .= '<a href="' . $baseUrl . '?page=' . $i . $paramString . '" class="page-link' . $activeClass . '">' . $i . '</a>';
    }
    
    // 다음 버튼
    if ($currentPage < $totalPages) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage + 1) . $paramString . '" class="page-link page-next">다음</a>';
        $html .= '<a href="' . $baseUrl . '?page=' . $totalPages . $paramString . '" class="page-link page-last">마지막</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * 페이징 정보 HTML 생성
 * 
 * @param int $currentPage 현재 페이지
 * @param int $totalPages 전체 페이지 수
 * @param int $total 전체 항목 수
 * @param int $perPage 페이지당 항목 수
 * @return string HTML
 */
function renderPaginationInfo($currentPage, $totalPages, $total, $perPage) {
    $start = ($currentPage - 1) * $perPage + 1;
    $end = min($currentPage * $perPage, $total);
    
    $html = '<div class="pagination-info">';
    $html .= '<span>전체 ' . number_format($total) . '개 중 ';
    $html .= number_format($start) . ' - ' . number_format($end) . '개 표시</span>';
    $html .= '<span class="page-number">(' . $currentPage . ' / ' . $totalPages . ' 페이지)</span>';
    $html .= '</div>';
    
    return $html;
}

/**
 * 간단한 페이징 버튼 생성 (이전/다음만)
 * 
 * @param int $currentPage 현재 페이지
 * @param int $totalPages 전체 페이지 수
 * @param string $baseUrl 기본 URL
 * @param array $params 추가 파라미터
 * @return string HTML
 */
function renderSimplePagination($currentPage, $totalPages, $baseUrl, $params = []) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<div class="pagination simple">';
    
    // 파라미터 문자열 생성
    $paramString = '';
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            if (!empty($value)) {
                $paramString .= '&' . urlencode($key) . '=' . urlencode($value);
            }
        }
    }
    
    // 이전 버튼
    if ($currentPage > 1) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage - 1) . $paramString . '" class="btn-pagination btn-prev">';
        $html .= '← 이전 페이지</a>';
    } else {
        $html .= '<span class="btn-pagination btn-prev disabled">← 이전 페이지</span>';
    }
    
    // 페이지 정보
    $html .= '<span class="pagination-current">' . $currentPage . ' / ' . $totalPages . '</span>';
    
    // 다음 버튼
    if ($currentPage < $totalPages) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage + 1) . $paramString . '" class="btn-pagination btn-next">';
        $html .= '다음 페이지 →</a>';
    } else {
        $html .= '<span class="btn-pagination btn-next disabled">다음 페이지 →</span>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * AJAX용 페이징 데이터 생성
 * 
 * @param int $currentPage 현재 페이지
 * @param int $totalPages 전체 페이지 수
 * @param int $total 전체 항목 수
 * @param int $perPage 페이지당 항목 수
 * @return array
 */
function getPaginationData($currentPage, $totalPages, $total, $perPage) {
    $startPage = max(1, $currentPage - 5);
    $endPage = min($totalPages, $currentPage + 5);
    
    $pages = [];
    for ($i = $startPage; $i <= $endPage; $i++) {
        $pages[] = [
            'number' => $i,
            'active' => ($i == $currentPage),
            'url' => '?page=' . $i
        ];
    }
    
    return [
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'total_items' => $total,
        'per_page' => $perPage,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
        'prev_page' => max(1, $currentPage - 1),
        'next_page' => min($totalPages, $currentPage + 1),
        'pages' => $pages
    ];
}
