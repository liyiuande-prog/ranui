<?php

namespace App\Controllers\Home;

use Core\Controller;

use App\Models\Post;
use App\Models\Category;

class IndexController extends Controller
{
    public function index()
    {
        $postModel = new Post();
        $categoryModel = new Category();
        
        $categories = $categoryModel->getAll();
        
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $tab = $_GET['tab'] ?? 'recommend';
        $categorySlug = null; // Default null as category tabs removed
        $tagSlug = $_GET['tag'] ?? null;
        
        // Priority: Tag > Tab
        if ($tagSlug) {
            $posts = $postModel->getByTagSlug($tagSlug, $limit, $offset);
        } elseif ($tab === 'newest') {
             $posts = $postModel->getLatestPosts($limit, $offset);
        } elseif ($tab === 'new_comment') {
             $posts = $postModel->getPostsByLatestComment($limit, $offset);
        } elseif ($tab === 'follow') {
             $uid = $_SESSION['user']['id'] ?? 0;
             if ($uid && function_exists('is_plugin_active') && is_plugin_active('Ran_Follow')) {
                 $posts = $postModel->getFollowedPosts($uid, $limit, $offset);
             } else {
                 $posts = [];
             }
        } else {
             // Default: recommend
             $posts = $postModel->getRecommendedPosts($limit, $offset);
        }
        
        if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
            if (empty($posts)) exit;
            $this->view('index', ['posts' => $posts, 'ajax' => true]);
            exit;
        }
        
        $commentModel = new \App\Models\Comment();
        $latestComments = $commentModel->getLatest(5);
        
        $pinnedPost = $postModel->getPinnedPost();

        $userModel = new \App\Models\User();
        $newUsers = $userModel->getLatestUsers(5);

        $tagModel = new \App\Models\Tag();
        $hotTags = $tagModel->getHotTags(10);

        // Stats Logic
        $totalPosts = $postModel->getPublishedCount();
        $totalComments = $commentModel->countAll();
        $totalUsers = $userModel->countAll();
        
        // Calculate running days
        $installDate = get_option('site_install_date');
        if (!$installDate) {
             // Fallback: use the creation date of the first user
             $firstUser = $userModel->query("SELECT created_at FROM users ORDER BY id ASC LIMIT 1")->fetch();
             $installDate = $firstUser['created_at'] ?? date('Y-m-d');
        }
        $runningDays = floor((time() - strtotime($installDate)) / 86400);

        // Format stats for display (k/w format)
        $formatStat = function($n) {
            if ($n > 10000) return round($n/10000, 1).'w';
            if ($n > 1000) return round($n/1000, 1).'k';
            return $n;
        };

        $data = [
             'app_name' => get_option('site_title', 'RanUI Blog'),
            'posts' => $posts,
            'categories' => $categories,
            'current_category' => $categorySlug,
            'latest_comments' => $latestComments,
            'pinned_post' => $pinnedPost,
            'new_users' => $newUsers,
            'hot_tags' => $hotTags,
            'site_stats' => [
                'post_count' => $formatStat($totalPosts),
                'running_days' => $runningDays,
                'user_count' => $formatStat($totalUsers),
                'comment_count' => $formatStat($totalComments)
            ]
        ];
        
        $this->view('index', $data);
    }
}
