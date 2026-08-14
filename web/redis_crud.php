<?php
// =====================================================
//  CRUD sederhana dengan penyimpanan data di Redis
//  Data disimpan sebagai Redis Hash: item:{id}
// =====================================================

$redis = new Redis();
$redis->connect(getenv('REDIS_HOST') ?: 'redis', (int)(getenv('REDIS_PORT') ?: 6379));

$action = $_GET['action'] ?? 'list';
$msg = $_GET['msg'] ?? '';

// ---------- CREATE ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($name !== '') {
        $id = $redis->incr('item:counter');
        $redis->hMSet("item:$id", [
            'name'        => $name,
            'description' => $description,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        $redis->sAdd('items', $id);
        header('Location: redis_crud.php?msg=' . urlencode("Data berhasil ditambahkan (ID: $id)"));
        exit;
    }
    $msg = 'Nama tidak boleh kosong!';
}

// ---------- UPDATE ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    $id          = (int)($_POST['id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($id > 0 && $name !== '' && $redis->exists("item:$id")) {
        $redis->hMSet("item:$id", [
            'name'        => $name,
            'description' => $description,
        ]);
        header('Location: redis_crud.php?msg=' . urlencode("Data ID $id berhasil diperbarui"));
        exit;
    }
    $msg = 'Gagal memperbarui data!';
}

// ---------- DELETE ----------
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id > 0 && $redis->exists("item:$id")) {
        $redis->del("item:$id");
        $redis->sRem('items', $id);
        $msg = "Data ID $id berhasil dihapus";
    } else {
        $msg = "Data ID $id tidak ditemukan";
    }
    header('Location: redis_crud.php?msg=' . urlencode($msg));
    exit;
}

// ---------- EDIT (tampilkan form terisi) ----------
$editItem = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($redis->exists("item:$id")) {
        $editItem = [
            'id'          => $id,
            'name'        => $redis->hGet("item:$id", 'name'),
            'description' => $redis->hGet("item:$id", 'description'),
        ];
    }
}

// ---------- LIST ----------
$ids = $redis->sMembers('items');
sort($ids);
$items = [];
foreach ($ids as $id) {
    $items[] = [
        'id'          => $id,
        'name'        => $redis->hGet("item:$id", 'name'),
        'description' => $redis->hGet("item:$id", 'description'),
        'created_at'  => $redis->hGet("item:$id", 'created_at'),
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Redis - PHP + Nginx</title>
    <style>
        * { box-sizing: border-box; margin: 0; }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            padding: 2rem 1rem;
            background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
            color: #fff;
        }
        .container { max-width: 860px; margin: 0 auto; }
        h1 { font-size: 1.7rem; margin-bottom: 0.25rem; }
        .subtitle { opacity: 0.8; font-size: 0.95rem; margin-bottom: 1.5rem; }
        a { color: #4fc3f7; }

        .card {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(6px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
        }
        .card h2 { font-size: 1.1rem; margin-bottom: 1rem; }

        label { display: block; font-size: 0.85rem; opacity: 0.85; margin-bottom: 0.3rem; }
        input[type="text"], textarea {
            width: 100%;
            padding: 0.55rem 0.7rem;
            margin-bottom: 0.9rem;
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 0.95rem;
        }
        input[type="text"]:focus, textarea:focus { outline: none; border-color: #4fc3f7; }
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
            background: #4fc3f7;
            color: #0f2027;
        }
        .btn-cancel { background: #888; color: #fff; margin-left: 0.4rem; }
        .btn-edit { background: #ffb74d; color: #3a2a00; }
        .btn-delete { background: #e57373; color: #3a0000; }

        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th, td { text-align: left; padding: 0.6rem 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.12); }
        th { opacity: 0.7; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; }
        td.actions { white-space: nowrap; }
        td.actions .btn { padding: 0.35rem 0.7rem; font-size: 0.8rem; }

        .empty { text-align: center; opacity: 0.7; padding: 1.5rem 0; }
        .badge {
            display: inline-block;
            background: rgba(79, 195, 247, 0.2);
            color: #4fc3f7;
            border-radius: 999px;
            padding: 0.15rem 0.6rem;
            font-size: 0.75rem;
            margin-bottom: 1rem;
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
        <h1>📦 CRUD dengan Redis</h1>
        <p class="subtitle">Container: <code>redis-server</code> · Data tersimpan di Redis Hash <code>item:{id}</code></p>

        <?php if ($msg): ?>
            <div class="msg"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <!-- Form CREATE / EDIT -->
        <div class="card">
            <h2><?= $editItem ? '✏️ Edit Data (ID: ' . $editItem['id'] . ')' : '➕ Tambah Data Baru' ?></h2>
            <form method="POST" action="redis_crud.php?action=<?= $editItem ? 'update' : 'create' ?>">
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
                    <a class="btn btn-cancel" href="redis_crud.php">Batal</a>
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
                                    <a class="btn btn-edit" href="redis_crud.php?action=edit&id=<?= $item['id'] ?>">Edit</a>
                                    <a class="btn btn-delete" href="redis_crud.php?action=delete&id=<?= $item['id'] ?>"
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
