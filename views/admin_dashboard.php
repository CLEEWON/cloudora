<?php
// views/admin_dashboard.php
// Single-file Admin Dashboard (CRUD ringan + stats)
// USAGE: simpan di folder views, pastikan path ke db/database.php benar

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------------- Security: ensure logged in and is admin ----------------
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../auth/formLogin.php");
    exit;
}

// CSRF token simple
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
}
$CSRF = $_SESSION['csrf_token'];

// Database
require_once __DIR__ . '/../db/database.php'; // sesuaikan jika path berbeda

// Helper: flash messages
function flash($k, $v = null) {
    if ($v === null) {
        if (isset($_SESSION[$k])) {
            $v = $_SESSION[$k];
            unset($_SESSION[$k]);
            return $v;
        }
        return null;
    }
    $_SESSION[$k] = $v;
}

// ---------------- Handle POST actions ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic CSRF check
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        flash('error', 'Invalid CSRF token.');
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Action router
    $action = $_POST['action'] ?? '';

    if ($action === 'set_storage') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        $limit = (int)($_POST['storage_limit'] ?? 0);
        if ($user_id > 0) {
            $stmt = $conn->prepare("UPDATE users SET storage_limit = ? WHERE id = ?");
            $stmt->bind_param("ii", $limit, $user_id);
            if ($stmt->execute()) {
                flash('success', 'Storage limit updated.');
            } else {
                flash('error', 'Failed to update storage limit.');
            }
            $stmt->close();
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($action === 'reset_password') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        if ($user_id > 0) {
            // default temporary password
            $temp = 'ChangeMe123!';
            $hash = password_hash($temp, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hash, $user_id);
            if ($stmt->execute()) {
                flash('success', "Password reset. Temp: {$temp}");
            } else {
                flash('error', 'Failed to reset password.');
            }
            $stmt->close();
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($action === 'delete_user') {
        $user_id = (int)($_POST['user_id'] ?? 0);
        if ($user_id > 0) {
            // safer: delete user's files entries, optionally files on disk
            $stmt = $conn->prepare("DELETE FROM files WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            $stmt2 = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt2->bind_param("i", $user_id);
            if ($stmt2->execute()) {
                flash('success', 'User and related files deleted.');
            } else {
                flash('error', 'Failed to delete user.');
            }
            $stmt2->close();
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($action === 'delete_file') {
        $file_id = (int)($_POST['file_id'] ?? 0);
        if ($file_id > 0) {
            // Get file_name to delete physical file (optional)
            $stmt = $conn->prepare("SELECT file_name FROM files WHERE id = ?");
            $stmt->bind_param("i", $file_id);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $filename = $res['file_name'] ?? null;
            $stmt->close();

            if ($filename) {
                // delete record
                $stmt2 = $conn->prepare("DELETE FROM files WHERE id = ?");
                $stmt2->bind_param("i", $file_id);
                if ($stmt2->execute()) {
                    // attempt physical deletion from uploads (best-effort)
                    $path = __DIR__ . "/../uploads/" . $filename;
                    if (file_exists($path)) {
                        @unlink($path);
                    }
                    flash('success', 'File deleted.');
                } else {
                    flash('error', 'Failed to delete file record.');
                }
                $stmt2->close();
            } else {
                flash('error', 'File not found.');
            }
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// ---------------- Fetch stats ----------------
// Total users
$totalUsers = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM users");
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$totalUsers = (int)($res['c'] ?? 0);
$stmt->close();

// Total files and total storage
$totalFiles = 0;
$totalStorage = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS c, COALESCE(SUM(file_size),0) AS s FROM files WHERE is_deleted = 0");
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$totalFiles = (int)($res['c'] ?? 0);
$totalStorage = (int)($res['s'] ?? 0);
$stmt->close();

// Per-user usage (bytes)
$users = [];
$stmt = $conn->prepare("
    SELECT u.id, u.nama, u.email, u.storage_limit,
           COALESCE(SUM(f.file_size),0) AS usage_bytes,
           COUNT(f.id) AS file_count
    FROM users u
    LEFT JOIN files f ON u.id = f.user_id AND f.is_deleted = 0
    GROUP BY u.id
    ORDER BY usage_bytes DESC
");
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $users[] = $r;
}
$stmt->close();

// Recent files (last 50)
$recentFiles = [];
$stmt = $conn->prepare("SELECT f.id, f.original_name, f.file_name, f.file_size, f.upload_date, u.nama AS owner_name FROM files f LEFT JOIN users u ON f.user_id = u.id WHERE f.is_deleted = 0 ORDER BY f.upload_date DESC LIMIT 50");
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $recentFiles[] = $r;
}
$stmt->close();

// Format helper
function fmtBytes($b) {
    if ($b < 1024) return $b . ' B';
    if ($b < 1024*1024) return round($b/1024,2) . ' KB';
    if ($b < 1024*1024*1024) return round($b/1024/1024,2) . ' MB';
    return round($b/1024/1024/1024,2) . ' GB';
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard - Cloudora</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f6f9; }
        .card { box-shadow: 0 6px 18px rgba(0,0,0,0.04); }
        .small-muted { font-size:0.85rem; color:#666; }
        .usage-bar { height:10px; border-radius:6px; background:#e9ecef; overflow:hidden; }
        .usage-bar > i { display:block; height:100%; background:linear-gradient(90deg,#33595e,#c9a961); }
        .file-name { max-width:320px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .btn-compact { padding: .25rem .5rem; font-size:.85rem; }
        pre.session-debug { background:#fff; padding:12px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.06); }
    </style>
</head>
<body>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Admin Dashboard — Cloudora</h3>
        <div>
            <span class="small-muted me-3">Admin: <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?></span>
            <a href="../auth/logout.php" class="btn btn-sm btn-outline-secondary">Logout</a>
        </div>
    </div>

    <?php if ($msg = flash('success')): ?>
        <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = flash('error')): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card p-3">
                <h6>Total Users</h6>
                <h2><?= $totalUsers ?></h2>
                <div class="small-muted">Registered users</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <h6>Total Files</h6>
                <h2><?= $totalFiles ?></h2>
                <div class="small-muted">Active files</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3">
                <h6>Total Storage Used</h6>
                <h2><?= fmtBytes($totalStorage) ?></h2>
                <div class="small-muted">Sum of non-deleted file sizes</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Left: Per-user usage -->
        <div class="col-lg-7">
            <div class="card p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">Penggunaan per User</h5>
                    <small class="small-muted">urut berdasarkan penggunaan terbesar</small>
                </div>

                <?php if (count($users) === 0): ?>
                    <div class="p-3 small-muted">Belum ada user.</div>
                <?php else: ?>
                    <?php foreach ($users as $u): 
                        $limit = (int)$u['storage_limit'];
                        $usage = (int)$u['usage_bytes'];
                        $percent = ($limit > 0) ? min(100, round($usage / max(1,$limit) * 100,1)) : 0;
                    ?>
                        <div class="mb-3 border-bottom pb-2">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong><?= htmlspecialchars($u['nama']) ?></strong>
                                    <div class="small-muted"><?= htmlspecialchars($u['email']) ?></div>
                                </div>
                                <div class="text-end">
                                    <div class="small-muted"><?= fmtBytes($usage) ?> / <?= $limit > 0 ? fmtBytes($limit) : 'Unlimited' ?></div>
                                    <div class="small-muted"><?= (int)$u['file_count'] ?> file</div>
                                </div>
                            </div>

                            <div class="mt-2">
                                <div class="usage-bar">
                                    <?php $w = $limit>0 ? ($usage/$limit*100) : 0; if ($w<0) $w=0; if ($w>100) $w=100; ?>
                                    <i style="width:<?= $w ?>%"></i>
                                </div>
                                <div class="d-flex gap-2 mt-2">
                                    <!-- Set storage form -->
                                    <form method="POST" class="d-inline-flex" onsubmit="return confirm('Simpan perubahan storage limit?');">
                                        <input type="hidden" name="csrf_token" value="<?= $CSRF ?>">
                                        <input type="hidden" name="action" value="set_storage">
                                        <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                        <input type="number" name="storage_limit" min="0" step="1" value="<?= (int)$u['storage_limit'] ?>" class="form-control form-control-sm" style="width:160px" title="Bytes">
                                        <button class="btn btn-sm btn-primary btn-compact">Simpan (bytes)</button>
                                    </form>

                                    <form method="POST" class="d-inline" onsubmit="return confirm('Reset password user ke password sementara?');">
                                        <input type="hidden" name="csrf_token" value="<?= $CSRF ?>">
                                        <input type="hidden" name="action" value="reset_password">
                                        <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                        <button class="btn btn-sm btn-warning btn-compact">Reset Password</button>
                                    </form>

                                    <form method="POST" class="d-inline" onsubmit="return confirm('Hapus user dan semua filenya? Tindakan ini tidak bisa dikembalikan.');">
                                        <input type="hidden" name="csrf_token" value="<?= $CSRF ?>">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                        <button class="btn btn-sm btn-danger btn-compact">Hapus User</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Recent files -->
        <div class="col-lg-5">
            <div class="card p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">File Terbaru</h5>
                    <small class="small-muted">50 terakhir</small>
                </div>

                <?php if (count($recentFiles) === 0): ?>
                    <div class="p-3 small-muted">Belum ada file.</div>
                <?php else: ?>
                    <div style="max-height:520px; overflow:auto;">
                    <?php foreach ($recentFiles as $f): ?>
                        <div class="d-flex align-items-center justify-content-between mb-2 border-bottom pb-2">
                            <div>
                                <div class="file-name"><strong><?= htmlspecialchars($f['original_name']) ?></strong></div>
                                <div class="small-muted"><?= htmlspecialchars($f['owner_name'] ?? 'Unknown') ?> • <?= fmtBytes($f['file_size']) ?> • <?= date('d M Y H:i', strtotime($f['upload_date'])) ?></div>
                            </div>
                            <div class="text-end">
                                <a href="../download.php?filename=<?= urlencode($f['file_name']) ?>" class="btn btn-sm btn-outline-success btn-compact" title="Download">Download</a>

                                <form method="POST" class="d-inline" style="display:inline;" onsubmit="return confirm('Hapus file ini?');">
                                    <input type="hidden" name="csrf_token" value="<?= $CSRF ?>">
                                    <input type="hidden" name="action" value="delete_file">
                                    <input type="hidden" name="file_id" value="<?= (int)$f['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger btn-compact">Hapus</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Optional: quick admin tools -->
            <div class="card p-3">
                <h6>Tools</h6>
                <p class="small-muted">Quick actions</p>
                <a href="../views/halamanDashboard.php" class="btn btn-sm btn-primary">Kembali ke Dashboard User</a>
            </div>
        </div>
    </div>

    <footer class="mt-4 small-muted">Cloudora Admin Panel — built-in single-file dashboard</footer>

    <!-- DEBUG: session (optional) -->
    <!-- <pre class="session-debug"><?php // print_r($_SESSION); ?></pre> -->

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
