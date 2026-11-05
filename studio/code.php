
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/functions.php';

require_login();

$user_id = (int) ($_SESSION['id'] ?? 0);

if ($user_id <= 0) {
    header("Location: ../auth/login.php");
    exit;
}

// =====================================
// DAFTAR PROYEK MILIK PENGGUNA
// =====================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nama_project
     FROM projects
     WHERE user_id = ?
     ORDER BY id DESC"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$projects = [];

while ($row = mysqli_fetch_assoc($result)) {
    $projects[] = $row;
}

mysqli_stmt_close($stmt);

// =====================================
// PILIH PROYEK
// =====================================

$project_id = (int) ($_GET['project_id'] ?? $_POST['project_id'] ?? 0);
$selected_project = null;

if ($project_id > 0) {
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id, nama_project
         FROM projects
         WHERE id = ? AND user_id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "ii", $project_id, $user_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $selected_project = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if (!$selected_project) {
        http_response_code(404);
        die("Proyek tidak ditemukan atau bukan milik Anda.");
    }
}

// =====================================
// TEMPLATE KODE DEFAULT
// =====================================

$default_html = <<<'HTML'
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Pertamaku</title>
</head>
<body>
    <div class="container">
        <h1>Halo, VISUALOGIC!</h1>
        <p>Selamat datang di Code Studio.</p>
        <button onclick="sapa()">Klik Saya</button>
    </div>
</body>
</html>
HTML;

$default_css = <<<'CSS'
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f0f4f8;
    color: #243b53;
}

.container {
    max-width: 700px;
    margin: 80px auto;
    padding: 40px;
    background: white;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
}

h1 {
    color: #176b87;
}

button {
    padding: 12px 22px;
    border: 0;
    border-radius: 8px;
    background: #176b87;
    color: white;
    cursor: pointer;
}
CSS;

$default_js = <<<'JS'
function sapa() {
    alert("Halo! Website berhasil dijalankan melalui VISUALOGIC.");
}
JS;

// =====================================
// MEMUAT KODE DARI DATABASE
// =====================================

$html_code = $default_html;
$css_code = $default_css;
$js_code = $default_js;

if ($selected_project) {
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT bahasa, isi_kode
         FROM code_documents
         WHERE project_id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $project_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        switch ($row['bahasa']) {
            case 'html':
                $html_code = $row['isi_kode'];
                break;

            case 'css':
                $css_code = $row['isi_kode'];
                break;

            case 'javascript':
                $js_code = $row['isi_kode'];
                break;
        }
    }

    mysqli_stmt_close($stmt);
}

// =====================================
// SIMPAN KODE
// =====================================

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selected_project) {

    if (!verify_csrf()) {
        die("Token keamanan tidak valid. Silakan muat ulang halaman.");
    }

    $html_code = $_POST['html_code'] ?? '';
    $css_code = $_POST['css_code'] ?? '';
    $js_code = $_POST['js_code'] ?? '';

    $dokumen = [
        'html' => [
            'nama' => 'index.html',
            'kode' => $html_code
        ],
        'css' => [
            'nama' => 'style.css',
            'kode' => $css_code
        ],
        'javascript' => [
            'nama' => 'script.js',
            'kode' => $js_code
        ]
    ];

    $berhasil = true;

    foreach ($dokumen as $bahasa => $data) {

        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id
             FROM code_documents
             WHERE project_id = ? AND bahasa = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "is",
            $project_id,
            $bahasa
        );

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $existing = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($existing) {

            $stmt = mysqli_prepare(
                $koneksi,
                "UPDATE code_documents
                 SET nama_file = ?, isi_kode = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ssi",
                $data['nama'],
                $data['kode'],
                $existing['id']
            );

        } else {

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO code_documents
                 (project_id, nama_file, bahasa, isi_kode)
                 VALUES (?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "isss",
                $project_id,
                $data['nama'],
                $bahasa,
                $data['kode']
            );
        }

        if (!mysqli_stmt_execute($stmt)) {
            $berhasil = false;
        }

        mysqli_stmt_close($stmt);
    }

    if ($berhasil) {
        $pesan = "Semua kode berhasil disimpan!";
    } else {
        $pesan = "Ada kode yang gagal disimpan. Silakan periksa database.";
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Code Studio | VISUALOGIC</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Inter, sans-serif;
    background: #0b1726;
    color: white;
}

.topbar {
    height: 65px;
    background: #102a43;
    border-bottom: 1px solid #263e54;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 22px;
}

.brand {
    color: white;
    text-decoration: none;
    font-size: 20px;
    font-weight: 800;
}

.brand span {
    color: #35c6bd;
}

.project-name {
    color: #b8c9d9;
    font-size: 13px;
}

.top-actions {
    display: flex;
    gap: 10px;
}

.btn-action {
    border: none;
    padding: 9px 15px;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}

.btn-run {
    background: #35c6bd;
    color: #102a43;
}

.btn-save {
    background: #176b87;
    color: white;
}

.btn-back {
    background: #263e54;
    color: white;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

.workspace {
    display: grid;
    grid-template-columns: 1fr 1fr;
    height: calc(100vh - 65px);
    min-height: 500px;
}

.editor-area {
    display: flex;
    flex-direction: column;
    min-width: 0;
    border-right: 1px solid #263e54;
}

.editor-header {
    height: 48px;
    background: #142a40;
    display: flex;
    align-items: center;
    padding: 0 15px;
    gap: 8px;
}

.tab {
    padding: 10px 15px;
    color: #9fb3c8;
    font-size: 12px;
    font-weight: 700;
    border: none;
    background: transparent;
    cursor: pointer;
}

.tab.active {
    color: #35c6bd;
    border-bottom: 2px solid #35c6bd;
}

.editor-box {
    display: none;
    flex: 1;
    min-height: 0;
}

.editor-box.active {
    display: block;
}

#htmlEditor,
#cssEditor,
#jsEditor {
    height: 100%;
    width: 100%;
}

.preview-area {
    display: flex;
    flex-direction: column;
    background: white;
    min-width: 0;
}

.preview-header {
    height: 48px;
    background: #142a40;
    display: flex;
    align-items: center;
    padding: 0 16px;
    color: #d9e2ec;
    font-size: 12px;
    font-weight: 700;
}

.preview-header i {
    margin-right: 9px;
    color: #35c6bd;
}

#preview {
    flex: 1;
    width: 100%;
    border: none;
    background: white;
}

.project-select {
    background: #142a40;
    color: white;
    border: 1px solid #38536c;
    padding: 10px;
    border-radius: 8px;
    width: 100%;
    margin-top: 15px;
}

.project-list {
    max-width: 600px;
    margin: 70px auto;
    padding: 30px;
    background: #142a40;
    border: 1px solid #263e54;
    border-radius: 15px;
}

.project-list h2 {
    font-size: 24px;
    font-weight: 800;
}

.project-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 15px;
    margin-top: 12px;
    background: #1b354d;
    border-radius: 9px;
}

.project-item a {
    color: #35c6bd;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
}

.empty {
    color: #b8c9d9;
    font-size: 14px;
    margin-top: 20px;
}

@media(max-width: 850px) {
    .workspace {
        grid-template-columns: 1fr;
        grid-template-rows: 1fr 1fr;
        height: auto;
        min-height: calc(100vh - 65px);
    }

    .editor-area {
        height: 550px;
    }

    .preview-area {
        height: 450px;
    }

    .topbar {
        padding: 0 12px;
    }

    .project-name {
        display: none;
    }

    .btn-action {
        padding: 8px 10px;
    }
}

</style>

</head>

<body>

<header class="topbar">

    <a href="../dashboard/index.php" class="brand">
        VISUAL<span>OGIC</span>
    </a>

    <?php if ($selected_project): ?>

        <div class="project-name">
            <i class="fa-solid fa-folder-open"></i>
            <?= e($selected_project['nama_project']); ?>
        </div>

        <div class="top-actions">

            <button type="button"
                    class="btn-action btn-run"
                    onclick="runCode()">

                <i class="fa-solid fa-play"></i>
                Run

            </button>

            <button type="submit"
                    form="codeForm"
                    class="btn-action btn-save">

                <i class="fa-solid fa-floppy-disk"></i>
                Simpan

            </button>

            <a href="../projects/index.php"
               class="btn-action btn-back">

                <i class="fa-solid fa-arrow-left"></i>

            </a>

        </div>

    <?php else: ?>

        <a href="../projects/index.php"
           class="btn-action btn-back">

            Kembali

        </a>

    <?php endif; ?>

</header>

<?php if (!$selected_project): ?>

    <div class="project-list">

        <h2>
            <i class="fa-solid fa-code" style="color:#35c6bd"></i>
            Code Studio
        </h2>

        <p style="color:#b8c9d9">
            Pilih proyek yang ingin kamu kerjakan.
        </p>

        <?php if (!empty($projects)): ?>

            <?php foreach ($projects as $project): ?>

                <div class="project-item">

                    <span>
                        <i class="fa-solid fa-folder"></i>
                        <?= e($project['nama_project']); ?>
                    </span>

                    <a href="code.php?project_id=<?= (int) $project['id']; ?>">
                        Buka Editor
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p class="empty">
                Belum ada proyek. Buat proyek terlebih dahulu.
            </p>

            <a href="../projects/tambah.php"
               class="btn-action btn-save"
               style="display:inline-block;text-decoration:none">

                Buat Proyek

            </a>

        <?php endif; ?>

    </div>

<?php else: ?>

    <?php if ($pesan): ?>

        <div style="padding:12px 20px;background:#176b87;color:white;font-size:13px">
            <?= e($pesan); ?>
        </div>

    <?php endif; ?>

    <form id="codeForm" method="POST">

        <input type="hidden"
               name="csrf_token"
               value="<?= e(csrf_token()); ?>">

        <input type="hidden"
               name="project_id"
               value="<?= (int) $project_id; ?>">

        <input type="hidden" name="html_code" id="htmlCode">

        <input type="hidden" name="css_code" id="cssCode">

        <input type="hidden" name="js_code" id="jsCode">

    </form>

    <main class="workspace">

        <section class="editor-area">

            <div class="editor-header">

                <button type="button"
                        class="tab active"
                        onclick="switchTab('html', this)">

                    <i class="fa-brands fa-html5"></i>
                    HTML

                </button>

                <button type="button"
                        class="tab"
                        onclick="switchTab('css', this)">

                    <i class="fa-brands fa-css3-alt"></i>
                    CSS

                </button>

                <button type="button"
                        class="tab"
                        onclick="switchTab('js', this)">

                    <i class="fa-brands fa-js"></i>
                    JavaScript

                </button>

            </div>

            <div class="editor-box active" id="box-html">
                <div id="htmlEditor"></div>
            </div>

            <div class="editor-box" id="box-css">
                <div id="cssEditor"></div>
            </div>

            <div class="editor-box" id="box-js">
                <div id="jsEditor"></div>
            </div>

        </section>


        <section class="preview-area">

            <div class="preview-header">

                <i class="fa-solid fa-eye"></i>

                LIVE PREVIEW

            </div>

            <iframe
                id="preview"
                title="Hasil preview website"
                sandbox="allow-scripts"
                referrerpolicy="no-referrer"
            ></iframe>

        </section>

    </main>

    <!-- MONACO EDITOR -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs/loader.min.js"></script>

    <script>

    let htmlEditor;
    let cssEditor;
    let jsEditor;

    let activeTab = 'html';

    const initialHTML = <?= json_encode(
        $html_code,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ); ?>;

    const initialCSS = <?= json_encode(
        $css_code,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ); ?>;

    const initialJS = <?= json_encode(
        $js_code,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ); ?>;

    require.config({
        paths: {
            vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs'
        }
    });

    require(['vs/editor/editor.main'], function () {

        const commonOptions = {
            theme: 'vs-dark',
            automaticLayout: true,
            minimap: {
                enabled: false
            },
            fontSize: 14,
            scrollBeyondLastLine: false,
            wordWrap: 'on'
        };

        htmlEditor = monaco.editor.create(
            document.getElementById('htmlEditor'),
            {
                ...commonOptions,
                value: initialHTML,
                language: 'html'
            }
        );

        cssEditor = monaco.editor.create(
            document.getElementById('cssEditor'),
            {
                ...commonOptions,
                value: initialCSS,
                language: 'css'
            }
        );

        jsEditor = monaco.editor.create(
            document.getElementById('jsEditor'),
            {
                ...commonOptions,
                value: initialJS,
                language: 'javascript'
            }
        );

        runCode();

        window.addEventListener('resize', function () {
            htmlEditor.layout();
            cssEditor.layout();
            jsEditor.layout();
        });

    });

    function switchTab(tab, button) {

        activeTab = tab;

        document.querySelectorAll('.tab').forEach(item => {
            item.classList.remove('active');
        });

        document.querySelectorAll('.editor-box').forEach(item => {
            item.classList.remove('active');
        });

        button.classList.add('active');

        document.getElementById('box-' + tab).classList.add('active');

        if (tab === 'html' && htmlEditor) htmlEditor.layout();
        if (tab === 'css' && cssEditor) cssEditor.layout();
        if (tab === 'js' && jsEditor) jsEditor.layout();

    }

    function runCode() {

        if (!htmlEditor || !cssEditor || !jsEditor) {
            return;
        }

        const html = htmlEditor.getValue();
        const css = cssEditor.getValue();
        const js = jsEditor.getValue();

        const documentContent = `
            ${html}
            <style>${css}</style>
            <script>
                ${js.replace(/<\/script/gi, '<\\/script')}
            <\/script>
        `;

        document.getElementById('preview').srcdoc = documentContent;

    }

    document.getElementById('codeForm').addEventListener(
        'submit',
        function () {

            document.getElementById('htmlCode').value =
                htmlEditor.getValue();

            document.getElementById('cssCode').value =
                cssEditor.getValue();

            document.getElementById('jsCode').value =
                jsEditor.getValue();

        }
    );

    </script>

<?php endif; ?>

</body>
</html>
