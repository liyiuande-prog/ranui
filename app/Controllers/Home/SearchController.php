<?php

namespace App\Controllers\Home;

use Core\Controller;

class SearchController extends Controller
{
    public function index()
    {
        $q = $_GET['q'] ?? '';
        $type = $_GET['type'] ?? 'post'; // post, comment, user, product
        $sort = $_GET['sort'] ?? 'recommend'; // recommend, newest, hot
        $time = $_GET['time'] ?? 'all'; // all, day, week
        $scope = $_GET['scope'] ?? 'all'; // all, follow
        
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $hasIntegral = class_exists('\Plugins\Ran_Integral\Plugin');
        $userId = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
        
        // Prevent product search if plugin inactive
        if ($type === 'product' && !$hasIntegral) {
            $type = 'post';
        }
        
        $results = [];
        $partial = '';
        
        if ($type === 'post') {
            $postModel = new \App\Models\Post();
            $results = $postModel->searchAdvanced($q, ['sort' => $sort, 'time' => $time, 'scope' => $scope, 'user_id' => $userId], $limit, $offset);
            
            // Process results for snippets
            if (!empty($q)) {
                foreach ($results as &$item) {
                    $item['description'] = $this->generateSnippet($item, $q);
                }
            }
            
            $partial = 'search_item_post.php';
        } elseif ($type === 'comment') {
            $commentModel = new \App\Models\Comment();
            $results = $commentModel->search($q, $limit, $offset);
            
             // Process comments for snippets
             if (!empty($q)) {
                foreach ($results as &$item) {
                    // Comments main content is 'content'
                    $item['content'] = $this->generateTextSnippet($item['content'], $q);
                }
            }
            
            $partial = 'search_item_comment.php';
        } elseif ($type === 'user') {
             $userModel = new \App\Models\User();
             $results = $userModel->search($q, $limit, $offset);
             $partial = 'search_item_user.php';
        } elseif ($type === 'product' && $hasIntegral) {
             $results = \Plugins\Ran_Integral\Plugin::searchGoods($q, $limit, $offset, $sort);
             $partial = 'search_item_product.php';
        }
        
        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            if (empty($results)) exit;
            
            $this->view('search', [
                'q' => $q,
                'results' => $results,
                'type' => $type,
                'sort' => $sort,
                'time' => $time,
                'scope' => $scope,
                'hasIntegral' => $hasIntegral,
                'ajax' => true
            ]);
            exit;
        }

        $titles = [
            'post' => '文章搜索',
            'comment' => '评论搜索',
            'user' => '用户搜索',
            'product' => '商品搜索'
        ];
        $pageTitle = ($titles[$type] ?? '搜索') . ($q ? ' - ' . $q : '');
        $pageKeywords = $q ? "$q, RanUI搜索, 搜索结果" : "RanUI搜索, 搜索文章, 搜索评论";
        $pageDescription = $q ? "RanUI 搜索: $q 的相关内容结果。" : "RanUI 站内搜索功能，查找您感兴趣的文章、评论或用户。";

        $this->view('search', [
            'page_title' => $pageTitle,
            'page_keywords' => $pageKeywords,
            'page_description' => $pageDescription,
            'q' => $q,
            'results' => $results,
            'type' => $type,
            'sort' => $sort,
            'time' => $time,
            'scope' => $scope,
            'hasIntegral' => $hasIntegral
        ]);
    }

    private function generateSnippet($item, $q)
    {
        $cleanContent = strip_tags($item['content'] ?? '');
        $cleanDesc = strip_tags($item['description'] ?? '');
        
        // Strategy: If keyword exists in content, SHOW content snippet. 
        // Descriptions are often just the first few lines, which might not contain the keyword if the user is searching for something deep in the text.
        if (!empty($q) && mb_stripos($cleanContent, $q) !== false) {
            return $this->generateTextSnippet($cleanContent, $q);
        }
        
        // Fallback to description or start of content
        return $cleanDesc ?: mb_substr($cleanContent, 0, 150);
    }
    
    private function generateTextSnippet($text, $q)
    {
        $text = str_replace(["\n", "\r", "\t"], ' ', strip_tags($text));
        $text = preg_replace('/\s+/', ' ', $text); // Clean whitespace
        
        $pos = mb_stripos($text, $q);
        
        if ($pos === false) {
            return mb_substr($text, 0, 150) . (mb_strlen($text) > 150 ? '...' : '');
        }
        
        // Calculate window: try to show ~150 chars total
        $padding = 70;
        $start = max(0, $pos - $padding);
        $length = mb_strlen($q) + ($padding * 2);
        
        $snippet = mb_substr($text, $start, $length);
        
        // Clean up: try to start at a boundary if not at 0
        if ($start > 0) {
            $snippet = '...' . ltrim($snippet);
        }
        
        if ($start + $length < mb_strlen($text)) {
            $snippet = rtrim($snippet) . '...';
        }
        
        return $snippet;
    }
}
