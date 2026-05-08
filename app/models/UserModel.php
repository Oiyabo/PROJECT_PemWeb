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
    $stmt = $this->db->prepare("SELECT * FROM users WHERE role = 'Pelanggan' ORDER BY nama ASC");
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function register(string $nama, string $email, string $password): bool
  {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $this->db->prepare(
      'INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)'
    );
    return $stmt->execute([$nama, $email, $hashedPassword, 'Pelanggan']);
  }
}
