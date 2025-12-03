<?php
// CEK APAKAH FUNGSI SUDAH ADA SEBELUM MENDEFINISIKAN
if (!function_exists('buildFilterQuery')) {
    function buildFilterQuery($baseUrl, $currentParams = []) {
        $params = [];
        
        if(isset($_GET['search']) && !empty($_GET['search'])) {
            $params['search'] = $_GET['search'];
        }
        
        if(isset($_GET['jenis_kelamin']) && !empty($_GET['jenis_kelamin'])) {
            $params['jenis_kelamin'] = $_GET['jenis_kelamin'];
        }
        
        if(isset($_GET['sort']) && !empty($_GET['sort'])) {
            $params['sort'] = $_GET['sort'];
        }
        
        // Merge dengan parameter tambahan
        if(!empty($currentParams)) {
            $params = array_merge($params, $currentParams);
        }
        
        // Buat query string
        if(!empty($params)) {
            return $baseUrl . '?' . http_build_query($params);
        }
        
        return $baseUrl;
    }
}

if (!function_exists('highlightSearchTerm')) {
    function highlightSearchTerm($text, $searchTerm) {
        if(empty($searchTerm)) {
            return htmlspecialchars($text);
        }
        
        $pattern = '/' . preg_quote($searchTerm, '/') . '/i';
        $replacement = '<mark class="bg-warning px-1 rounded">$0</mark>';
        return preg_replace($pattern, $replacement, htmlspecialchars($text));
    }
}

if (!function_exists('calculateAge')) {
    function calculateAge($birthDate) {
        $birth = new DateTime($birthDate);
        $today = new DateTime();
        $age = $today->diff($birth);
        return $age->y;
    }
}

if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'd/m/Y') {
        if(empty($date) || $date == '0000-00-00') {
            return '-';
        }
        return date($format, strtotime($date));
    }
}

if (!function_exists('formatDateTime')) {
    function formatDateTime($datetime, $format = 'd/m/Y H:i') {
        if(empty($datetime) || $datetime == '0000-00-00 00:00:00') {
            return '-';
        }
        return date($format, strtotime($datetime));
    }
}
?>