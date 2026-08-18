<?php
// =====================================================
//  CRUD sederhana dengan penyimpanan data di MariaDB
//  Tabel: items (dibuat otomatis oleh mariadb/init/)
// =====================================================

// ---------- Koneksi ke MariaDB via PDO ----------
$host = getenv('MARIADB_HOST') ?: 'mariadb';
$port = getenv('MARIADB_PORT') ?: '3306';
$db   = getenv('MARIADB_DATABASE') ?: 'app_db';
$user = getenv('MARIADB_USER') ?: 'app_user';
$pass = getenv('MARIADB_PASSWORD') ?: 'app_pass';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$action = $_GET['action'] ?? 'list';
$msg = $_GET['msg'] ?? '';

// ---------- CREATE ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($name !== '') {
        $stmt = $pdo->prepare(
            'INSERT INTO items (name, description) VALUES (:name, :description)'
        );
        $stmt->execute([':name' => $name, ':description' => $description]);
        $id = (int)$pdo->lastInsertId();
        header('Location: mariadb_crud.php?msg=' . urlencode("Data berhasil ditambahkan (ID: $id)"));
        exit;
    }
    $msg = 'Nama tidak boleh kosong!';
}

// ---------- UPDATE ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $id          = (int)($_POST['id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($id > 0 && $name !== '') {
        // Cek apakah data dengan ID tersebut ada
        $check = $pdo->prepare('SELECT id FROM items WHERE id = :id');
        $check->execute([':id' => $id]);
        if ($check->fetch()) {
            $stmt = $pdo->prepare(
                'UPDATE items SET name = :name, description = :description WHERE id = :id'
            );
            $stmt->execute([':name' => $name, ':description' => $description, ':id' => $id]);
            header('Location: mariadb_crud.php?msg=' . urlencode("Data ID $id berhasil diperbarui"));
            exit;
        }
        $msg = 'Data tidak ditemukan!';
    } else {
        $msg = 'Gagal memperbarui data!';
    }
}

// ---------- DELETE ----------
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('DELETE FROM items WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $msg = $stmt->rowCount() > 0
        ? "Data ID $id berhasil dihapus"
        : "Data ID $id tidak ditemukan";
    header('Location: mariadb_crud.php?msg=' . urlencode($msg));
    exit;
}

// ---------- EDIT (tampilkan form terisi) ----------
$editItem = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('SELECT * FROM items WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $editItem = $stmt->fetch();
    if (!$editItem) {
        $editItem = null;
        $msg = "Data ID $id tidak ditemukan";
    }
}

// ---------- LIST ----------
$items = $pdo->query('SELECT * FROM items ORDER BY id ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD MariaDB - PHP + Nginx</title>
    <style>
        * { box-sizing: border-box; margin: 0; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            padding: 2rem 1rem;
            background: linear-gradient(135deg, #3a1c71 0%, #d76d77 50%, #ffaf7b 100%);
            color: #fff;
        }
        .container { max-width: 860px; margin: 0 auto; }
        h1 { font-size: 1.7rem; margin-bottom: 0.25rem; }
        .subtitle { opacity: 0.85; font-size: 0.95rem; margin-bottom: 1.5rem; }
        a { color: #ffe082; }

        .card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(6px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
        }
        .card h2 { font-size: 1.1rem; margin-bottom: 1rem; }

        label { display: block; font-size: 0.85rem; opacity: 0.9; margin-bottom: 0.3rem; }
        input[type="text"], textarea {
            width: 100%;
            padding: 0.55rem 0.7rem;
            margin-bottom: 0.9rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            font-size: 0.95rem;
        }
        input[type="text"]:focus, textarea:focus { outline: none; border-color: #ffe082; }
        textarea { resize: vertical; min-height: 70px; font-family: inherit; }

        button, .btn {
            display: inline-block;
            border: none;
            border-radius: 8px;
            padding: 0.55rem 1.1rem;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            color: #fff;
            background: #ffaf7b;
            color: #3a1c71;
        }
        .btn-cancel { background: #888; color: #fff; margin-left: 0.4rem; }
        .btn-edit { background: #ffd54f; color: #3a2a00; }
        .btn-delete { background: #e57373; color: #3a0000; }

        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th, td { text-align: left; padding: 0.6rem 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.15); }
        th { opacity: 0.75; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; }
        td.actions { white-space: nowrap; }
        td.actions .btn { padding: 0.35rem 0.7rem; font-size: 0.8rem; }

        .empty { text-align: center; opacity: 0.8; padding: 1.5rem 0; }
        .badge {
            display: inline-block;
            background: rgba(255, 175, 123, 0.25);
            color: #ffe0b2;
            border-radius: 999px;
            padding: 0.15rem 0.6rem;
            font-size: 0.75rem;
        }
        .msg {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.2rem;
            background: rgba(129, 199, 132, 0.2);
            color: #a5d6a7;
            border: 1px solid rgba(129, 199, 132, 0.4);
        }
        .msg.error { background: rgba(229, 115, 115, 0.2); color: #ef9a9a; border-color: rgba(229, 115, 115, 0.4); }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ CRUD dengan MariaDB</h1>
        <p class="subtitle">Container: <code>testdocker-mariadb</code> · Database: <code><?= htmlspecialchars($db) ?></code> · Tabel: <code>items</code></p>

        <?php if ($msg): ?>
            <div class="msg<?= str_starts_with($msg, 'Gagal') ? ' error' : '' ?>"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <!-- Form CREATE / EDIT -->
        <div class="card">
            <h2><?= $editItem ? '✏️ Edit Data (ID: ' . $editItem['id'] . ')' : '➕ Tambah Data Baru' ?></h2>
            <form method="POST" action="mariadb_crud.php?action=<?= $editItem ? 'update' : 'create' ?>">
                <?php if ($editItem): ?>
                    <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
                <?php endif; ?>
                <label for="name">Nama</label>
                <input type="text" id="name" name="name" required
                       value="<?= htmlspecialchars($editItem['name'] ?? '') ?>"
                       placeholder="Contoh: Membeli susu">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description"
                          placeholder="Catatan tambahan (opsional)"><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea>
                <button type="submit"><?= $editItem ? 'Simpan Perubahan' : 'Simpan' ?></button>
                <?php if ($editItem): ?>
                    <a class="btn btn-cancel" href="mariadb_crud.php">Batal</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Daftar Data -->
        <div class="card">
            <h2>📋 Daftar Data (<?= count($items) ?>)</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="5" class="empty">Belum ada data. Tambahkan data pertama di form di atas. 🚀</td></tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><span class="badge"><?= $item['id'] ?></span></td>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= htmlspecialchars($item['description']) ?: '—' ?></td>
                                <td><?= htmlspecialchars($item['created_at']) ?></td>
                                <td class="actions">
                                    <a class="btn btn-edit" href="mariadb_crud.php?action=edit&id=<?= $item['id'] ?>">Edit</a>
                                    <a class="btn btn-delete" href="mariadb_crud.php?action=delete&id=<?= $item['id'] ?>"
                                       onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <p class="subtitle" style="margin-top:1rem;">
            <a href="index.php">← Kembali ke halaman utama</a>
        </p>
    </div>
</body>
</html>
