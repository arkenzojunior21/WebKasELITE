<?php
// KasELITE PostgreSQL connection for Neon/Vercel.
// Set DATABASE_URL in Vercel Environment Variables.
if (!defined('MYSQLI_ASSOC')) define('MYSQLI_ASSOC', 1); // compatibility for existing fetch_all calls

class KaseliteResult {
    private array $rows;
    private int $cursor = 0;
    public function __construct(array $rows = []) { $this->rows = $rows; }
    public function fetch_all($mode = null): array { $rows = array_slice($this->rows, $this->cursor); $this->cursor = count($this->rows); return $rows; }
    public function fetch_row(): array|false { if (!isset($this->rows[$this->cursor])) return false; return array_values($this->rows[$this->cursor++]); }
    public function fetch_assoc(): array|false { if (!isset($this->rows[$this->cursor])) return false; return $this->rows[$this->cursor++]; }
}

class KaseliteConnection {
    private PDO $pdo;
    public int $insert_id = 0;
    public function __construct(string $url) {
        $parts = parse_url($url);
        if (!$parts || empty($parts['host']) || empty($parts['path']) || !isset($parts['user'], $parts['pass'])) {
            throw new RuntimeException('DATABASE_URL tidak valid.');
        }
        $db = ltrim($parts['path'], '/');
        $dsn = 'pgsql:host=' . $parts['host'] . ';port=' . ($parts['port'] ?? 5432) . ';dbname=' . $db . ';sslmode=require';
        $this->pdo = new PDO($dsn, rawurldecode($parts['user']), rawurldecode($parts['pass']), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    public function real_escape_string(string $value): string {
        $quoted = $this->pdo->quote($value);
        return substr($quoted, 1, -1);
    }
    public function query(string $sql): KaseliteResult|false {
        $this->insert_id = 0;
        $sql = preg_replace('/\bCURDATE\(\)/i', 'CURRENT_DATE', $sql);
        // MySQL's INSERT IGNORE equivalent.
        if (preg_match('/^\s*INSERT\s+IGNORE\s+INTO\s+/i', $sql)) {
            $sql = preg_replace('/^\s*INSERT\s+IGNORE\s+INTO\s+/i', 'INSERT INTO ', $sql);
            if (!preg_match('/\bON\s+CONFLICT\b/i', $sql)) $sql .= ' ON CONFLICT DO NOTHING';
        }
        // Capture IDs from the inserts used by this app.
        if (preg_match('/^\s*INSERT\s+INTO\s+(anggota|minggu_kas)\b/i', $sql) && !preg_match('/\bRETURNING\b/i', $sql)) {
            $sql .= ' RETURNING id';
            $stmt = $this->pdo->query($sql);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && isset($row['id'])) $this->insert_id = (int)$row['id'];
            return new KaseliteResult($row ? [$row] : []);
        }
        $stmt = $this->pdo->query($sql);
        if ($stmt === false) return false;
        if ($stmt->columnCount() > 0) return new KaseliteResult($stmt->fetchAll(PDO::FETCH_ASSOC));
        return new KaseliteResult();
    }
}

$url = getenv('DATABASE_URL') ?: '';
if ($url === '') {
    http_response_code(500);
    exit('Database belum dikonfigurasi. Tambahkan DATABASE_URL dari Neon di Vercel Environment Variables.');
}
try {
    $conn = new KaseliteConnection($url);
} catch (Throwable $e) {
    error_log('KasELITE PostgreSQL connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Koneksi PostgreSQL gagal. Periksa DATABASE_URL dan dukungan driver PDO PostgreSQL pada runtime.');
}
?>
