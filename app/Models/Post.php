<?php

namespace App\Models;

use Core\Model;

class Post extends Model
{
    protected $table = 'posts';

    public function getLatestPosts($limit = 10, $offset = 0)
    {
        $circleJoin = '';
        $circleField = '';
        if (function_exists('is_plugin_active') && is_plugin_active('Ran_Circle')) {
             $circleJoin = "LEFT JOIN circles ci ON p.circle_id = ci.id";
             $circleField = ", ci.name as circle_name, ci.slug as circle_slug";
        }

        // Join users and categories to get author name and category name
        $sql = "SELECT p.*, u.username as author_name, u.avatar as author_avatar, c.name as category_name $circleField
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                $circleJoin
                WHERE p.status = 'published'
                ORDER BY p.created_at DESC
                LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        return $this->db->query($sql)->fetchAll();
    }

    public function getByCategorySlug($slug, $limit = 10, $offset = 0)
    {
        $sql = "SELECT p.*, u.username as author_name, u.avatar as author_avatar, c.name as category_name 
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'published' AND c.slug = :slug
                ORDER BY p.created_at DESC
                LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        
        return $this->db->query($sql, [':slug' => $slug])->fetchAll();
    }

    public function getRandomPosts($limit = 5)
    {
        $sql = "SELECT p.id, p.title, p.created_at, p.user_id, u.username as author_name, u.avatar as author_avatar
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.status = 'published' 
                ORDER BY RAND() 
                LIMIT " . (int)$limit;
        return $this->db->query($sql)->fetchAll();
    }

    public function getPinnedPost()
    {
        $circleJoin = '';
        $circleField = '';
        if (function_exists('is_plugin_active') && is_plugin_active('Ran_Circle')) {
             $circleJoin = "LEFT JOIN circles ci ON p.circle_id = ci.id";
             $circleField = ", ci.name as circle_name, ci.slug as circle_slug";
        }

        $sql = "SELECT p.*, u.username as author_name, u.avatar as author_avatar, c.name as category_name $circleField
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                $circleJoin
                WHERE p.status = 'published' AND p.is_pinned = 1
                LIMIT 1";
        
        return $this->db->query($sql)->fetch();
    }

    public function getPostById($id)
    {
        $circleJoin = '';
        $circleField = '';
        if (function_exists('is_plugin_active') && is_plugin_active('Ran_Circle')) {
             $circleJoin = "LEFT JOIN circles ci ON p.circle_id = ci.id";
             $circleField = ", ci.name as circle_name, ci.slug as circle_slug";
        }

        $sql = "SELECT p.*, u.username as author_name, u.avatar as author_avatar, u.bio as author_bio, c.name as category_name $circleField
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                $circleJoin
                WHERE p.id = :id AND p.status = 'published'";
        
        return $this->db->query($sql, [':id' => $id])->fetch();
    }

    public function search($query)
    {
        $sql = "SELECT p.*, u.username as author_name, u.avatar as author_avatar, c.name as category_name
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'published' AND (p.title LIKE :q OR p.content LIKE :q)
                ORDER BY p.created_at DESC LIMIT 20";
        return $this->db->query($sql, [':q' => "%$query%"])->fetchAll();
    }

    public function incrementViewCount($id)
    {
        $sql = "UPDATE posts SET view_count = view_count + 1 WHERE id = :id";
        return $this->db->query($sql, [':id' => $id]);
    }

    public function incrementCommentCount($id)
    {
        $sql = "UPDATE posts SET comment_count = comment_count + 1 WHERE id = :id";
        return $this->db->query($sql, [':id' => $id]);
    }

    public function getByUserId($userId, $limit = 10, $offset = 0)
    {
        $circleJoin = '';
        $circleField = '';
        if (function_exists('is_plugin_active') && is_plugin_active('Ran_Circle')) {
             $circleJoin = "LEFT JOIN circles ci ON p.circle_id = ci.id";
             $circleField = ", ci.name as circle_name, ci.slug as circle_slug";
        }

        $sql = "SELECT p.*, u.username as author_name, u.avatar as author_avatar, c.name as category_name $circleField
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                $circleJoin
                WHERE p.user_id = :uid AND p.status = 'published'
                ORDER BY p.created_at DESC
                LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        return $this->db->query($sql, [':uid' => $userId])->fetchAll();
    }

    public function searchAdvanced($q, $options = [], $limit = 10, $offset = 0)
    {
        // Options: sort (newest, hot), time (1, 7), scope (follow, view)
        $where = ["p.status = 'published'"];
        $params = [];

        if (!empty($q)) {
            $where[] = "(p.title LIKE :q OR p.content LIKE :q)";
            $params[':q'] = "%$q%";
        }

        // Time Filter
        if (!empty($options['time'])) {
            if ($options['time'] === 'day') {
                $where[] = "p.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
            } elseif ($options['time'] === 'week') {
                $where[] = "p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            }
        }

        // Scope Filter
        // 'follow' requires knowing current user. Passed in options?
        if (!empty($options['scope']) && $options['scope'] === 'follow' && !empty($options['user_id'])) {
             // Join follows
             $where[] = "p.user_id IN (SELECT following_id FROM follows WHERE follower_id = :cuid)";
             $params[':cuid'] = $options['user_id'];
        }
        // 'view' (History) - requires `post_views` log or similar. Assuming we don't have it fully tracked or user wants "Recently Viewed".
        // If 'view' is requested we might need a separate query on history table. Skipping for now unless requested explicit logic.
        
        $whereSql = implode(' AND ', $where);

        // Order By
        $orderBy = "p.created_at DESC";
        if (!empty($options['sort'])) {
            if ($options['sort'] === 'hot') {
                $orderBy = "p.view_count DESC, p.created_at DESC";
            } elseif ($options['sort'] === 'recommend') {
                $orderBy = "((p.view_count + (SELECT COUNT(*) FROM comments WHERE post_id = p.id) * 5) * (0.9 + RAND() * 0.2)) DESC";
            }
        }

        $circleJoin = '';
        $circleField = '';
        if (function_exists('is_plugin_active') && is_plugin_active('Ran_Circle')) {
             $circleJoin = "LEFT JOIN circles ci ON p.circle_id = ci.id";
             $circleField = ", ci.name as circle_name, ci.slug as circle_slug";
        }

        $sql = "SELECT p.*, u.username as author_name, u.avatar as author_avatar, c.name as category_name $circleField
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                $circleJoin
                WHERE $whereSql
                ORDER BY $orderBy
                LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        return $this->db->query($sql, $params)->fetchAll();
    }
    public function getRecommendedPosts($limit = 10, $offset = 0)
    {
        // Algorithm V5: Time Decay Ranking (Gravity Method)
        // Formula: Score = (BaseScore + 1) / (HoursOld + 2)^1.6 * (0.9 + RAND() * 0.2)
        // BaseScore = log10(views)*10 + likes*50 + comments*100
        
        $circleJoin = '';
        $circleField = '';
        if (function_exists('is_plugin_active') && is_plugin_active('Ran_Circle')) {
             $circleJoin = "LEFT JOIN circles ci ON p.circle_id = ci.id";
             $circleField = ", ci.name as circle_name, ci.slug as circle_slug";
        }

        $sql = "SELECT p.*, u.username as author_name, u.avatar as author_avatar, c.name as category_name $circleField,
                (
                    (
                        -- 1. Base Score calculation
                        (LOG10(p.view_count + 1) * 10) + (p.like_count * 50) + ((SELECT COUNT(*) FROM comments WHERE post_id = p.id) * 100) + 1
                    )
                    /
                    -- 2. Time Decay (Hours since created, +2 to prevent division by zero and smooth early growth)
                    -- Gravity = 1.6 (Strong decay for older posts)
                    POW(TIMESTAMPDIFF(HOUR, p.created_at, NOW()) + 2, 1.6)
                    
                    * 
                    -- 3. Slight Randomization (±10% to keep feed fresh)
                    (0.9 + RAND() * 0.2)
                    
                ) as score
                
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                $circleJoin
                WHERE p.status = 'published' AND p.is_pinned = 0
                ORDER BY score DESC
                LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
                
        return $this->db->query($sql)->fetchAll();
    }

    public function getFollowedPosts($userId, $limit = 10, $offset = 0)
    {
        $circleJoin = '';
        $circleField = '';
        if (function_exists('is_plugin_active') && is_plugin_active('Ran_Circle')) {
             $circleJoin = "LEFT JOIN circles ci ON p.circle_id = ci.id";
             $circleField = ", ci.name as circle_name, ci.slug as circle_slug";
        }

        $sql = "SELECT p.*, u.username as author_name, u.avatar as author_avatar, c.name as category_name $circleField
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                $circleJoin
                WHERE p.user_id IN (SELECT following_id FROM follows WHERE follower_id = :uid)
                AND p.status = 'published'
                ORDER BY p.created_at DESC
                LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        return $this->db->query($sql, [':uid' => $userId])->fetchAll();
    }

    public function getPostsByLatestComment($limit = 10, $offset = 0)
    {
        $circleJoin = '';
        $circleField = '';
        if (function_exists('is_plugin_active') && is_plugin_active('Ran_Circle')) {
             $circleJoin = "LEFT JOIN circles ci ON p.circle_id = ci.id";
             $circleField = ", ci.name as circle_name, ci.slug as circle_slug";
        }

        // Sort by the latest comment in the 'comments' table
        $sql = "SELECT p.*, u.username as author_name, u.avatar as author_avatar, c.name as category_name $circleField,
                (SELECT MAX(created_at) FROM comments WHERE post_id = p.id) as last_comment_at
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                $circleJoin
                WHERE p.status = 'published'
                ORDER BY last_comment_at DESC, p.created_at DESC
                LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        return $this->db->query($sql)->fetchAll();
    }

    public function getByTagSlug($tagSlug, $limit = 10, $offset = 0)
    {
        $sql = "SELECT p.*, u.username as author_name, u.avatar as author_avatar, c.name as category_name
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                JOIN post_tags pt ON p.id = pt.post_id
                JOIN tags t ON pt.tag_id = t.id
                WHERE p.status = 'published' AND t.slug = :slug
                ORDER BY p.created_at DESC
                LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        return $this->db->query($sql, [':slug' => $tagSlug])->fetchAll();
    }

    public function getRelatedPosts($categoryId, $currentPostId, $limit = 5)
    {
        $sql = "SELECT p.id, p.title, p.cover_image, p.view_count, p.created_at,
                u.username as author_name, u.avatar as author_avatar
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.status = 'published' 
                AND p.category_id = :category_id 
                AND p.id != :current_id
                ORDER BY p.view_count DESC, p.created_at DESC
                LIMIT " . (int)$limit;
        return $this->db->query($sql, [
            ':category_id' => $categoryId,
            ':current_id' => $currentPostId
        ])->fetchAll();
    }

    public function getAuthorPosts($userId, $currentPostId, $limit = 5)
    {
        $sql = "SELECT p.id, p.title, p.cover_image, p.view_count, p.created_at
                FROM posts p
                WHERE p.status = 'published' 
                AND p.user_id = :user_id 
                AND p.id != :current_id
                ORDER BY p.view_count DESC, p.created_at DESC
                LIMIT " . (int)$limit;
        return $this->db->query($sql, [
            ':user_id' => $userId,
            ':current_id' => $currentPostId
        ])->fetchAll();
    }

    public function getPublishedCount()
    {
        $sql = "SELECT COUNT(*) as total FROM posts WHERE status = 'published'";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getVideoPosts($limit = 10, $offset = 0)
    {
        // Posts that have standard video or dmooji video settings
        // Note: 'video_url' column might not exist in posts table if using plugins only.
        // We rely on 'is_video' flag or existence of Dmooji settings.
        $sql = "SELECT p.*, u.username as author_name, u.avatar as author_avatar, c.name as category_name
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'published'
                AND (
                    p.is_video = 1
                    OR EXISTS (SELECT 1 FROM dmooji_post_settings d WHERE d.post_id = p.id AND d.videos IS NOT NULL AND d.videos != '' AND d.videos != '[]')
                )
                ORDER BY p.created_at DESC
                LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        return $this->db->query($sql)->fetchAll();
    }
}
