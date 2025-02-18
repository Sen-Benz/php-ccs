<?php
require_once __DIR__ . '/../config/database.php';

class Notification {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Send a notification to a user
     * @param int $user_id The ID of the user
     * @param string $title The notification title
     * @param string $message The notification message
     * @param string $type The type of notification (info, success, warning, error)
     * @return bool Whether the notification was sent successfully
     */
    public function send($user_id, $title, $message, $type = 'info') {
        try {
            $stmt = $this->db->getConnection()->prepare("
                INSERT INTO notifications (user_id, title, message, type, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            return $stmt->execute([$user_id, $title, $message, $type]);
        } catch (Exception $e) {
            error_log("Error sending notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send exam notifications to all eligible applicants
     * @param int $exam_id The ID of the published exam
     * @return array Result of the notification process
     */
    public function sendExamNotifications($exam_id) {
        try {
            // Get exam details
            $stmt = $this->db->getConnection()->prepare("
                SELECT title, type, part 
                FROM exams 
                WHERE id = ?
            ");
            $stmt->execute([$exam_id]);
            $exam = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$exam) {
                throw new Exception("Exam not found");
            }

            // Get eligible applicants (those who haven't taken this exam yet)
            $stmt = $this->db->getConnection()->prepare("
                SELECT u.id, u.email 
                FROM users u
                JOIN applicants a ON a.user_id = u.id
                LEFT JOIN exam_results er ON er.user_id = u.id AND er.exam_id = ?
                WHERE er.id IS NULL AND u.status = 'active' AND u.role = 'applicant'
            ");
            $stmt->execute([$exam_id]);
            $applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sent_count = 0;
            foreach ($applicants as $applicant) {
                $title = "New Exam Available";
                $message = "A new exam '{$exam['title']}' (Part {$exam['part']}) has been published and is now available for you to take.";

                if ($this->send($applicant['id'], $title, $message, 'info')) {
                    $sent_count++;
                }
            }

            return [
                'success' => true,
                'notifications_sent' => $sent_count,
                'total_applicants' => count($applicants)
            ];
        } catch (Exception $e) {
            error_log("Error sending exam notifications: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mark notifications as read for a user
     * @param int $user_id The ID of the user
     * @param array $notification_ids Optional array of specific notification IDs to mark as read
     * @return bool Whether the operation was successful
     */
    public function markAsRead($user_id, $notification_ids = []) {
        try {
            if (empty($notification_ids)) {
                $stmt = $this->db->getConnection()->prepare("
                    UPDATE notifications 
                    SET is_read = 1 
                    WHERE user_id = ? AND is_read = 0
                ");
                return $stmt->execute([$user_id]);
            } else {
                $placeholders = str_repeat('?,', count($notification_ids) - 1) . '?';
                $stmt = $this->db->getConnection()->prepare("
                    UPDATE notifications 
                    SET is_read = 1 
                    WHERE user_id = ? AND id IN ($placeholders) AND is_read = 0
                ");
                
                $params = array_merge([$user_id], $notification_ids);
                return $stmt->execute($params);
            }
        } catch (Exception $e) {
            error_log("Error marking notifications as read: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get notifications for a user
     * @param int $user_id The ID of the user
     * @param bool $unread_only Whether to get only unread notifications
     * @param int $limit Optional limit on number of notifications to return
     * @return array Array of notifications
     */
    public function getNotifications($user_id, $unread_only = false, $limit = null) {
        try {
            $sql = "SELECT * FROM notifications WHERE user_id = ?";
            if ($unread_only) {
                $sql .= " AND is_read = 0";
            }
            $sql .= " ORDER BY created_at DESC";
            if ($limit) {
                $sql .= " LIMIT " . (int)$limit;
            }

            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete notifications for a user
     * @param int $user_id The ID of the user
     * @param array $notification_ids Optional array of specific notification IDs to delete
     * @return bool Whether the operation was successful
     */
    public function delete($user_id, $notification_ids = []) {
        try {
            if (empty($notification_ids)) {
                $stmt = $this->db->getConnection()->prepare("
                    DELETE FROM notifications 
                    WHERE user_id = ?
                ");
                return $stmt->execute([$user_id]);
            } else {
                $placeholders = str_repeat('?,', count($notification_ids) - 1) . '?';
                $stmt = $this->db->getConnection()->prepare("
                    DELETE FROM notifications 
                    WHERE user_id = ? AND id IN ($placeholders)
                ");
                
                $params = array_merge([$user_id], $notification_ids);
                return $stmt->execute($params);
            }
        } catch (Exception $e) {
            error_log("Error deleting notifications: " . $e->getMessage());
            return false;
        }
    }
}
