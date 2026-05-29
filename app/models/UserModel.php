<?php

class UserModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function getAllPelanggan(): array
    {
        $stmt = $this->db->query('SELECT * FROM v_pelanggan ORDER BY nama ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function register(string $nama, string $email, string $password): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)'
        );

        return $stmt->execute([
            $nama,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            'Pelanggan',
        ]);
    }

    public function updatePasswordByEmail(string $email, string $password): bool
    {
        $hashPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare(
            'UPDATE users SET password = ? WHERE email = ?'
        );

        return $stmt->execute([
            $hashPassword,
            $email
        ]);
    }
}