<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>To-Do List</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Tambahan styling kecil agar tombol sejajar */
        .task-buttons {
            display: flex;
            justify-content: flex-start;
            gap: 12px;
            margin-top: 10px;
            flex-wrap: wrap;
        }

        .task-buttons a {
            background: linear-gradient(90deg, #0066cc, #004a99);
            color: #fff;
            padding: 8px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0,102,204,0.3);
            transition: 0.2s;
        }

        .task-buttons a:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0,102,204,0.4);
        }

        /* Hilangkan tanda list marker sepenuhnya */
        .task-buttons,
        .task-buttons li {
            list-style: none !important;
            margin: 0;
            padding: 0;
        }

        .task-buttons li::marker {
            content: none !important;
        }

        /* Jangan coret teks task selesai */
        .task.done {
            text-decoration: none;
            opacity: 0.9;
        }

        .task.done strong {
            color: #006633;
        }
    </style>
</head>
<body>

<h1>📋 To-Do List</h1>

<div class="container">

    <!-- FORM: Tambah List Baru -->
    <form action="" method="POST" class="box">
        <h2>Buat List Baru</h2>
        <input type="text" name="list_name" placeholder="Nama List Baru" required>
        <div class="buttons">
            <button type="submit" name="save_list">Simpan</button>
            <button type="reset">Batal</button>
        </div>
    </form>

    <?php
    // === SIMPAN LIST BARU ===
    if (isset($_POST['save_list'])) {
        $list_name = trim($_POST['list_name']);
        if (!empty($list_name)) {
            $conn->query("INSERT INTO lists (name) VALUES ('$list_name')");
            echo "<script>alert('List berhasil disimpan!');window.location='index.php';</script>";
        }
    }

    // === HAPUS LIST ===
    if (isset($_GET['delete_list'])) {
        $list_id = $_GET['delete_list'];
        $conn->query("DELETE FROM tasks WHERE list_id=$list_id");
        $conn->query("DELETE FROM lists WHERE id=$list_id");
        header("Location: index.php");
    }

    // === SIMPAN TASK BARU ===
    if (isset($_POST['save_task'])) {
        $list_id = $_POST['list_id'];
        $task_name = $_POST['task_name'];
        $deadline = $_POST['deadline'];
        if (!empty($task_name) && !empty($deadline)) {
            $conn->query("INSERT INTO tasks (list_id, task_name, deadline) VALUES ('$list_id', '$task_name', '$deadline')");
            echo "<script>alert('Tugas berhasil disimpan!');window.location='index.php';</script>";
        }
    }

    // === TANDAI SELESAI ===
    if (isset($_GET['done'])) {
        $id = $_GET['done'];
        $conn->query("UPDATE tasks SET status='done' WHERE id=$id");
        header("Location: index.php");
    }

    // === BATALKAN SELESAI ===
    if (isset($_GET['undo'])) {
        $id = $_GET['undo'];
        $conn->query("UPDATE tasks SET status='pending' WHERE id=$id");
        header("Location: index.php");
    }

    // === HAPUS TASK ===
    if (isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $conn->query("DELETE FROM tasks WHERE id=$id");
        header("Location: index.php");
    }

    // === UPDATE (EDIT) TASK ===
    if (isset($_POST['update_task'])) {
        $id = $_POST['task_id'];
        $task_name = $_POST['task_name'];
        $deadline = $_POST['deadline'];
        $conn->query("UPDATE tasks SET task_name='$task_name', deadline='$deadline' WHERE id=$id");
        echo "<script>alert('Tugas berhasil diperbarui!');window.location='index.php';</script>";
    }
    ?>

    <!-- DAFTAR LIST -->
    <h2>Daftar List</h2>
    <div class="tasks">
        <?php
        $lists = $conn->query("SELECT * FROM lists ORDER BY id DESC");
        if ($lists->num_rows > 0) {
            while ($l = $lists->fetch_assoc()) {
                echo "
                    <div class='task'>
                        <strong>{$l['name']}</strong><br>
                        <a href='?delete_list={$l['id']}' onclick='return confirm(\"Yakin ingin menghapus list ini beserta semua tugasnya?\")'>Hapus List</a>
                    </div>
                ";
            }
        } else {
            echo "<p>Belum ada list dibuat.</p>";
        }
        ?>
    </div>

    <!-- FORM: Tambah Tugas -->
    <form action="" method="POST" class="box">
        <h2>Buat Task Baru</h2>
        <select name="list_id" required>
            <option value="">Pilih List</option>
            <?php
            $lists = $conn->query("SELECT * FROM lists");
            while ($row = $lists->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select>
        <input type="text" name="task_name" placeholder="Nama Tugas" required>
        <input type="datetime-local" name="deadline" required>
        <div class="buttons">
            <button type="submit" name="save_task">Simpan</button>
            <button type="reset">Batal</button>
        </div>
    </form>

    <!-- DAFTAR TUGAS -->
    <h2>Daftar Tugas</h2>
    <div class="tasks">
        <?php
        $tasks = $conn->query("
            SELECT t.*, l.name AS list_name 
            FROM tasks t 
            JOIN lists l ON t.list_id=l.id 
            ORDER BY t.created_at DESC
        ");
        if ($tasks->num_rows > 0) {
            while ($t = $tasks->fetch_assoc()) {
                $status_text = $t['status'] == 'done' ? '✅ Selesai' : '🕓 Belum Selesai';
                $added = date("d M Y, H:i", strtotime($t['created_at']));
                $deadline = date("d M Y, H:i", strtotime($t['deadline']));

                echo "<div class='task ".($t['status']=='done'?'done':'')."'>
                        <strong>{$t['task_name']}</strong><br>
                        <span>📁 List: {$t['list_name']}</span><br>
                        <span>🕓 Ditambahkan: $added</span><br>
                        <span>📅 Deadline: $deadline</span><br>
                        <span>Status: $status_text</span>
                        <div class='task-buttons'>
                ";

                if ($t['status'] == 'done') {
                    echo "
                        <a href='?undo={$t['id']}'>Batalkan</a>
                        <a href='?edit={$t['id']}'>Edit</a>
                        <a href='?delete={$t['id']}' onclick='return confirm(\"Yakin ingin menghapus tugas ini?\")'>Hapus</a>
                    ";
                } else {
                    echo "
                        <a href='?done={$t['id']}'>Tandai Selesai</a>
                        <a href='?edit={$t['id']}'>Edit</a>
                        <a href='?delete={$t['id']}' onclick='return confirm(\"Yakin ingin menghapus tugas ini?\")'>Hapus</a>
                    ";
                }

                echo "</div></div>";
            }
        } else {
            echo "<p>Tidak ada tugas saat ini.</p>";
        }

        // === MODE EDIT ===
        if (isset($_GET['edit'])) {
            $id = $_GET['edit'];
            $task = $conn->query("SELECT * FROM tasks WHERE id=$id")->fetch_assoc();
            echo "
                <div class='box'>
                    <h2>Edit Task</h2>
                    <form method='POST'>
                        <input type='hidden' name='task_id' value='{$task['id']}'>
                        <input type='text' name='task_name' value='{$task['task_name']}' required>
                        <input type='datetime-local' name='deadline' value='".date('Y-m-d\TH:i', strtotime($task['deadline']))."' required>
                        <div class='buttons'>
                            <button type='submit' name='update_task'>Update</button>
                            <a href='index.php'>Batal</a>
                        </div>
                    </form>
                </div>
            ";
        }
        ?>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
