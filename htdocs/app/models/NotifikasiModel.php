<?php
class NotifikasiModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    public function create(
        ?int $userId,
        string $role,
        string $title,
        string $message,
        ?string $url = null
    ): bool {
        $stmt = $this->db->prepare(
            'INSERT INTO notifikasi (user_id, role, title, message, url, is_read, created_at)
             VALUES (?, ?, ?, ?, ?, 0, NOW())'
        );
        return $stmt->execute([
            $userId,
            $role,
            $title,
            $message,
            $url
        ]);
    }

    public function getLatest(int $userId, string $role, int $limit = 8): array
    {
        if ($role === 'Admin') {
            $stmt = $this->db->prepare(
                'SELECT * FROM notifikasi 
                 WHERE role = \'Admin\' 
                 ORDER BY created_at DESC 
                 LIMIT ?'
            );
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare(
                'SELECT * FROM notifikasi 
                 WHERE user_id = ? AND role = \'Pelanggan\' 
                 ORDER BY created_at DESC 
                 LIMIT ?'
            );
            $stmt->bindValue(1, $userId, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnreadCount(int $userId, string $role): int
    {
        if ($role === 'Admin') {
            $stmt = $this->db->query(
                'SELECT COUNT(*) FROM notifikasi WHERE role = \'Admin\' AND is_read = 0'
            );
        } else {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM notifikasi WHERE user_id = ? AND role = \'Pelanggan\' AND is_read = 0'
            );
            $stmt->execute([$userId]);
        }
        return (int) $stmt->fetchColumn();
    }

    public function markAllAsRead(int $userId, string $role): bool
    {
        if ($role === 'Admin') {
            $stmt = $this->db->prepare(
                'UPDATE notifikasi SET is_read = 1 WHERE role = \'Admin\' AND is_read = 0'
            );
            return $stmt->execute();
        } else {
            $stmt = $this->db->prepare(
                'UPDATE notifikasi SET is_read = 1 WHERE user_id = ? AND role = \'Pelanggan\' AND is_read = 0'
            );
            return $stmt->execute([$userId]);
        }
    }
}
