
<?php
session_start();

require_once "../config/koneksi.php";
require_once "../config/functions.php";

require_login();

$user_id = (int) $_SESSION['id'];
$project_id = isset($_GET['project_id']) ? (int) $_GET['project_id'] : 0;

if ($project_id <= 0) {
    die("Project tidak ditemukan.");
}

// Memastikan project milik pengguna yang sedang login
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nama_project, jenis
     FROM projects
     WHERE id = ? AND user_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "ii", $project_id, $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$project = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$project) {
    die("Project tidak ditemukan atau bukan milik Anda.");
}

// Token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];

// PROSES SIMPAN DESAIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {

    header('Content-Type: application/json; charset=utf-8');

    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals($csrf_token, $token)) {
        http_response_code(419);
        echo json_encode([
            "success" => false,
            "message" => "Token keamanan tidak valid. Muat ulang halaman."
        ]);
        exit;
    }

    $canvas_data = $_POST['canvas_data'] ?? '';
    $width = (int) ($_POST['width'] ?? 1200);
    $height = (int) ($_POST['height'] ?? 800);

    if ($width < 100 || $width > 3000 || $height < 100 || $height > 3000) {
        echo json_encode([
            "success" => false,
            "message" => "Ukuran canvas tidak valid."
        ]);
        exit;
    }

    json_decode($canvas_data);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            "success" => false,
            "message" => "Data desain tidak valid."
        ]);
        exit;
    }

    // Cari dokumen desain terakhir milik project
    $find = mysqli_prepare(
        $koneksi,
        "SELECT id FROM design_documents
         WHERE project_id = ?
         ORDER BY id DESC
         LIMIT 1"
    );

    mysqli_stmt_bind_param($find, "i", $project_id);
    mysqli_stmt_execute($find);

    $find_result = mysqli_stmt_get_result($find);
    $document = mysqli_fetch_assoc($find_result);

    mysqli_stmt_close($find);

    if ($document) {

        $document_id = (int) $document['id'];

        $save = mysqli_prepare(
            $koneksi,
            "UPDATE design_documents
             SET canvas_data = ?, ukuran_width = ?, ukuran_height = ?
             WHERE id = ? AND project_id = ?"
        );

        mysqli_stmt_bind_param(
            $save,
            "siiii",
            $canvas_data,
            $width,
            $height,
            $document_id,
            $project_id
        );

    } else {

        $nama_desain = "Desain " . $project['nama_project'];

        $save = mysqli_prepare(
            $koneksi,
            "INSERT INTO design_documents
             (project_id, nama_desain, canvas_data, ukuran_width, ukuran_height)
             VALUES (?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $save,
            "issii",
            $project_id,
            $nama_desain,
            $canvas_data,
            $width,
            $height
        );
    }

    if (mysqli_stmt_execute($save)) {
        echo json_encode([
            "success" => true,
            "message" => "Desain berhasil disimpan."
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Gagal menyimpan desain."
        ]);
    }

    mysqli_stmt_close($save);
    exit;
}

// MEMUAT DESAIN
$objects = [];

$load = mysqli_prepare(
    $koneksi,
    "SELECT canvas_data
     FROM design_documents
     WHERE project_id = ?
     ORDER BY id DESC
     LIMIT 1"
);

mysqli_stmt_bind_param($load, "i", $project_id);
mysqli_stmt_execute($load);

$load_result = mysqli_stmt_get_result($load);
$saved_document = mysqli_fetch_assoc($load_result);

mysqli_stmt_close($load);

if ($saved_document && !empty($saved_document['canvas_data'])) {
    $decoded = json_decode($saved_document['canvas_data'], true);

    if (is_array($decoded)) {
        $objects = $decoded;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Design Studio - VISUALOGIC</title>

    <link rel="stylesheet" href="../assets/css/design.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>

<div class="studio">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <div class="brand">
            <div class="brand-icon">V</div>
            <div>
                <h2>VISUALOGIC</h2>
                <small>Design Studio</small>
            </div>
        </div>

        <a href="../dashboard/index.php" class="back-link">
            ← Kembali ke Dashboard
        </a>

        <div class="sidebar-content">

            <h3>TOOLS</h3>

            <div class="tool-grid">
                <button class="tool-button" id="addText">
                    <span>T</span>
                    <small>Teks</small>
                </button>

                <button class="tool-button" id="addRectangle">
                    <span>▭</span>
                    <small>Kotak</small>
                </button>

                <button class="tool-button" id="addCircle">
                    <span>○</span>
                    <small>Lingkaran</small>
                </button>

                <button class="tool-button" id="addButton">
                    <span>▰</span>
                    <small>Tombol</small>
                </button>

                <button class="tool-button" id="addLine">
                    <span>／</span>
                    <small>Garis</small>
                </button>

                <label class="tool-button upload-button" for="imageUpload">
                    <span>▧</span>
                    <small>Upload</small>
                </label>

                <input
                    type="file"
                    id="imageUpload"
                    accept="image/png,image/jpeg,image/webp"
                    hidden
                >
            </div>

            <div class="sidebar-divider"></div>

            <h3>TEMPLATE DESAIN</h3>

            <button class="template-button" data-template="login">
                <span>▣</span>
                Template Login
            </button>

            <button class="template-button" data-template="landing">
                <span>▤</span>
                Template Landing Page
            </button>

            <button class="template-button" data-template="dashboard">
                <span>▦</span>
                Template Dashboard
            </button>

            <div class="sidebar-divider"></div>

            <h3>AKSI</h3>

            <button class="action-button danger" id="deleteObject">
                Hapus Objek Terpilih
            </button>

            <button class="action-button" id="clearCanvas">
                Kosongkan Canvas
            </button>

        </div>

    </aside>

    <!-- MAIN AREA -->
    <main class="main-area">

        <header class="topbar">

            <div class="project-info">
                <span class="topbar-label">PROJECT</span>
                <h3><?= e($project['nama_project']); ?></h3>
            </div>

            <div class="topbar-actions">
                <span id="saveStatus">Belum disimpan</span>

                <button class="save-button" id="saveDesign">
                    Simpan Desain
                </button>
            </div>

        </header>

        <div class="workspace">

            <div class="canvas-heading">
                <div>
                    <h2>Design Workspace</h2>
                    <p>Buat desain UI sesuai kreativitasmu.</p>
                </div>

                <div class="canvas-size">
                    1200 × 800 PX
                </div>
            </div>

            <div class="canvas-container">
                <canvas id="designCanvas" width="1200" height="800"></canvas>
            </div>

            <div class="workspace-hint">
                Tips: Klik objek untuk memilih, lalu geser menggunakan mouse.
            </div>

        </div>

    </main>

    <!-- PROPERTIES -->
    <aside class="properties-panel">

        <div class="properties-header">
            <h3>Properties</h3>
            <p>Pengaturan objek desain</p>
        </div>

        <div class="property-content">

            <label for="objectText">Teks Objek</label>
            <input type="text" id="objectText" placeholder="Masukkan teks">

            <label for="objectColor">Warna Objek</label>
            <input type="color" id="objectColor" value="#176B87">

            <label for="objectFontSize">Ukuran Teks</label>
            <input type="number" id="objectFontSize" min="8" max="150" value="32">

            <div class="property-row">
                <div>
                    <label for="objectWidth">Lebar</label>
                    <input type="number" id="objectWidth" min="1" max="3000">
                </div>

                <div>
                    <label for="objectHeight">Tinggi</label>
                    <input type="number" id="objectHeight" min="1" max="3000">
                </div>
            </div>

            <button class="apply-button" id="applyProperties">
                Terapkan Perubahan
            </button>

            <p class="property-note">
                Pilih objek pada canvas terlebih dahulu untuk mengubah propertinya.
            </p>

        </div>

    </aside>

</div>

<script>
const projectId = <?= (int) $project_id ?>;
const csrfToken = <?= json_encode($csrf_token) ?>;
const initialObjects = <?= json_encode(
    $objects,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
) ?>;

const canvas = document.getElementById("designCanvas");
const ctx = canvas.getContext("2d");

let objects = Array.isArray(initialObjects) ? initialObjects : [];
let selectedIndex = -1;
let dragging = false;
let dragOffsetX = 0;
let dragOffsetY = 0;

const imageCache = {};

function createId() {
    return "obj_" + Date.now() + "_" + Math.floor(Math.random() * 100000);
}

function drawCanvas() {

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    ctx.fillStyle = "#ffffff";
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    objects.forEach((obj, index) => {

        ctx.save();

        ctx.globalAlpha = obj.opacity ?? 1;

        if (obj.type === "rectangle") {

            ctx.fillStyle = obj.color || "#176B87";
            ctx.fillRect(obj.x, obj.y, obj.width, obj.height);

        } else if (obj.type === "circle") {

            ctx.beginPath();
            ctx.fillStyle = obj.color || "#176B87";
            ctx.arc(
                obj.x + obj.width / 2,
                obj.y + obj.height / 2,
                Math.min(obj.width, obj.height) / 2,
                0,
                Math.PI * 2
            );
            ctx.fill();

        } else if (obj.type === "text") {

            ctx.fillStyle = obj.color || "#243B53";
            ctx.font = `600 ${obj.fontSize || 32}px Inter, Arial`;
            ctx.textBaseline = "top";
            ctx.fillText(obj.text || "Teks", obj.x, obj.y);

        } else if (obj.type === "button") {

            ctx.fillStyle = obj.color || "#176B87";
            ctx.beginPath();

            if (typeof ctx.roundRect === "function") {
                ctx.roundRect(obj.x, obj.y, obj.width, obj.height, 10);
            } else {
                ctx.rect(obj.x, obj.y, obj.width, obj.height);
            }

            ctx.fill();

            ctx.fillStyle = "#ffffff";
            ctx.font = `600 ${obj.fontSize || 20}px Inter, Arial`;
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";

            ctx.fillText(
                obj.text || "Button",
                obj.x + obj.width / 2,
                obj.y + obj.height / 2
            );

            ctx.textAlign = "left";
            ctx.textBaseline = "alphabetic";

        } else if (obj.type === "line") {

            ctx.strokeStyle = obj.color || "#176B87";
            ctx.lineWidth = obj.lineWidth || 4;
            ctx.beginPath();
            ctx.moveTo(obj.x, obj.y);
            ctx.lineTo(obj.x + obj.width, obj.y + obj.height);
            ctx.stroke();

        } else if (obj.type === "image") {

            if (!imageCache[obj.src]) {

                const image = new Image();

                image.onload = function() {
                    drawCanvas();
                };

                image.onerror = function() {
                    console.error("Gambar gagal dimuat:", obj.src);
                };

                image.src = obj.src;
                imageCache[obj.src] = image;

            } else if (imageCache[obj.src].complete) {

                try {
                    ctx.drawImage(
                        imageCache[obj.src],
                        obj.x,
                        obj.y,
                        obj.width,
                        obj.height
                    );
                } catch (error) {
                    console.error(error);
                }
            }
        }

        ctx.restore();

        // Garis seleksi
        if (index === selectedIndex) {

            ctx.save();
            ctx.strokeStyle = "#F59E0B";
            ctx.lineWidth = 3;
            ctx.setLineDash([8, 5]);

            if (obj.type === "circle") {
                ctx.strokeRect(obj.x, obj.y, obj.width, obj.height);

            } else if (
                obj.type === "rectangle" ||
                obj.type === "image" ||
                obj.type === "button"
            ) {
                ctx.strokeRect(obj.x, obj.y, obj.width, obj.height);

            } else if (obj.type === "text") {
                ctx.font = `600 ${obj.fontSize || 32}px Inter, Arial`;

                const width = ctx.measureText(obj.text || "Teks").width;
                const height = obj.fontSize || 32;

                ctx.strokeRect(
                    obj.x - 3,
                    obj.y - 3,
                    width + 6,
                    height + 6
                );

            } else if (obj.type === "line") {
                ctx.beginPath();
                ctx.moveTo(obj.x, obj.y);
                ctx.lineTo(obj.x + obj.width, obj.y + obj.height);
                ctx.stroke();
            }

            ctx.restore();
        }

    });
}

function addObject(object) {

    object.id = createId();

    objects.push(object);

    selectedIndex = objects.length - 1;

    drawCanvas();
    updateProperties();
}

document.getElementById("addText").addEventListener("click", function() {

    addObject({
        type: "text",
        text: "Teks Baru",
        x: 150,
        y: 150,
        width: 200,
        height: 45,
        fontSize: 32,
        color: "#243B53"
    });

});

document.getElementById("addRectangle").addEventListener("click", function() {

    addObject({
        type: "rectangle",
        x: 150,
        y: 150,
        width: 250,
        height: 150,
        color: "#176B87"
    });

});

document.getElementById("addCircle").addEventListener("click", function() {

    addObject({
        type: "circle",
        x: 150,
        y: 150,
        width: 150,
        height: 150,
        color: "#F59E0B"
    });

});

document.getElementById("addButton").addEventListener("click", function() {

    addObject({
        type: "button",
        text: "Klik Saya",
        x: 150,
        y: 150,
        width: 180,
        height: 55,
        fontSize: 20,
        color: "#176B87"
    });

});

document.getElementById("addLine").addEventListener("click", function() {

    addObject({
        type: "line",
        x: 150,
        y: 150,
        width: 250,
        height: 0,
        lineWidth: 4,
        color: "#176B87"
    });

});

// KOORDINAT MOUSE
function getCanvasPosition(event) {

    const rect = canvas.getBoundingClientRect();

    return {
        x: (event.clientX - rect.left) * (canvas.width / rect.width),
        y: (event.clientY - rect.top) * (canvas.height / rect.height)
    };
}

// CEK OBJEK YANG DIKLIK
function hitTest(x, y) {

    for (let i = objects.length - 1; i >= 0; i--) {

        const obj = objects[i];

        if (obj.type === "line") {

            const x1 = obj.x;
            const y1 = obj.y;
            const x2 = obj.x + obj.width;
            const y2 = obj.y + obj.height;

            const dx = x2 - x1;
            const dy = y2 - y1;
            const lengthSquared = dx * dx + dy * dy;

            if (lengthSquared === 0) continue;

            const t = Math.max(
                0,
                Math.min(
                    1,
                    ((x - x1) * dx + (y - y1) * dy) / lengthSquared
                )
            );

            const nearestX = x1 + t * dx;
            const nearestY = y1 + t * dy;

            const distance = Math.hypot(x - nearestX, y - nearestY);

            if (distance < 12) return i;

        } else {

            let width = obj.width || 100;
            let height = obj.height || 40;

            if (obj.type === "text") {
                ctx.font = `600 ${obj.fontSize || 32}px Inter, Arial`;
                width = Math.max(
                    ctx.measureText(obj.text || "Teks").width,
                    width
                );
                height = obj.fontSize || 32;
            }

            if (
                x >= obj.x &&
                x <= obj.x + width &&
                y >= obj.y &&
                y <= obj.y + height
            ) {
                return i;
            }
        }
    }

    return -1;
}

// MOUSE DOWN
canvas.addEventListener("pointerdown", function(event) {

    const position = getCanvasPosition(event);

    selectedIndex = hitTest(position.x, position.y);

    if (selectedIndex !== -1) {

        const obj = objects[selectedIndex];

        dragging = true;

        dragOffsetX = position.x - obj.x;
        dragOffsetY = position.y - obj.y;

        canvas.setPointerCapture(event.pointerId);

    }

    updateProperties();
    drawCanvas();

});

// MOUSE MOVE
canvas.addEventListener("pointermove", function(event) {

    if (!dragging || selectedIndex === -1) return;

    const position = getCanvasPosition(event);
    const obj = objects[selectedIndex];

    obj.x = position.x - dragOffsetX;
    obj.y = position.y - dragOffsetY;

    drawCanvas();
});

// MOUSE UP
canvas.addEventListener("pointerup", function() {
    dragging = false;
});

canvas.addEventListener("pointercancel", function() {
    dragging = false;
});

// UPDATE PANEL PROPERTIES
function updateProperties() {

    const textInput = document.getElementById("objectText");
    const colorInput = document.getElementById("objectColor");
    const fontInput = document.getElementById("objectFontSize");
    const widthInput = document.getElementById("objectWidth");
    const heightInput = document.getElementById("objectHeight");

    if (selectedIndex === -1) {

        textInput.value = "";
        fontInput.value = 32;
        widthInput.value = "";
        heightInput.value = "";

        return;
    }

    const obj = objects[selectedIndex];

    textInput.value = obj.text || "";
    colorInput.value = obj.color || "#176B87";
    fontInput.value = obj.fontSize || 32;
    widthInput.value = Math.round(obj.width || 0);
    heightInput.value = Math.round(obj.height || 0);
}

// TERAPKAN PROPERTIES
document.getElementById("applyProperties").addEventListener("click", function() {

    if (selectedIndex === -1) {
        alert("Pilih objek terlebih dahulu.");
        return;
    }

    const obj = objects[selectedIndex];

    const newWidth = Number(document.getElementById("objectWidth").value);
    const newHeight = Number(document.getElementById("objectHeight").value);

    obj.color = document.getElementById("objectColor").value;

    if (obj.type === "text" || obj.type === "button") {
        obj.text = document.getElementById("objectText").value;
        obj.fontSize = Math.min(
            150,
            Math.max(
                8,
                Number(document.getElementById("objectFontSize").value) || 32
            )
        );
    }

    if (obj.type !== "text") {
        if (Number.isFinite(newWidth) && newWidth > 0) {
            obj.width = Math.min(newWidth, 3000);
        }

        if (Number.isFinite(newHeight) && newHeight > 0) {
            obj.height = Math.min(newHeight, 3000);
        }
    }

    updateProperties();
    drawCanvas();

});

// HAPUS OBJEK
document.getElementById("deleteObject").addEventListener("click", function() {

    if (selectedIndex === -1) {
        alert("Pilih objek yang ingin dihapus.");
        return;
    }

    objects.splice(selectedIndex, 1);
    selectedIndex = -1;

    updateProperties();
    drawCanvas();

});

// KOSONGKAN CANVAS
document.getElementById("clearCanvas").addEventListener("click", function() {

    if (!confirm("Yakin ingin mengosongkan seluruh canvas?")) return;

    objects = [];
    selectedIndex = -1;

    updateProperties();
    drawCanvas();

});

// UPLOAD GAMBAR
document.getElementById("imageUpload").addEventListener("change", async function() {

    const file = this.files[0];

    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
        alert("Ukuran gambar maksimal 5 MB.");
        this.value = "";
        return;
    }

    const allowedTypes = [
        "image/jpeg",
        "image/png",
        "image/webp"
    ];

    if (!allowedTypes.includes(file.type)) {
        alert("Format gambar harus JPG, PNG, atau WEBP.");
        this.value = "";
        return;
    }

    const formData = new FormData();

    formData.append("image", file);
    formData.append("project_id", projectId);
    formData.append("csrf_token", csrfToken);

    try {

        const response = await fetch("upload-image.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || "Upload gagal.");
        }

        const image = new Image();

        image.onload = function() {

            const scale = Math.min(
                1,
                350 / image.naturalWidth,
                350 / image.naturalHeight
            );

            const width = image.naturalWidth * scale;
            const height = image.naturalHeight * scale;

            imageCache[result.image_url] = image;

            addObject({
                type: "image",
                src: result.image_url,
                x: 150,
                y: 150,
                width: width,
                height: height,
                color: "#ffffff"
            });

        };

        image.onerror = function() {
            alert("Gambar berhasil diunggah, tetapi gagal ditampilkan.");
        };

        image.src = result.image_url;

    } catch (error) {

        alert(error.message);
        console.error(error);

    }

    this.value = "";

});

// TEMPLATE DESAIN
const templates = {

    login: [
        {
            type: "rectangle",
            x: 250, y: 100,
            width: 700, height: 600,
            color: "#F5F7FA"
        },
        {
            type: "text",
            text: "WELCOME BACK",
            x: 440, y: 190,
            fontSize: 42,
            width: 350, height: 50,
            color: "#102A43"
        },
        {
            type: "text",
            text: "Silakan masuk ke akun Anda",
            x: 440, y: 260,
            fontSize: 22,
            width: 350, height: 30,
            color: "#627D98"
        },
        {
            type: "rectangle",
            x: 410, y: 330,
            width: 400, height: 60,
            color: "#D9E2EC"
        },
        {
            type: "text",
            text: "Email atau Username",
            x: 435, y: 348,
            fontSize: 18,
            width: 350, height: 25,
            color: "#627D98"
        },
        {
            type: "rectangle",
            x: 410, y: 420,
            width: 400, height: 60,
            color: "#D9E2EC"
        },
        {
            type: "text",
            text: "Password",
            x: 435, y: 438,
            fontSize: 18,
            width: 350, height: 25,
            color: "#627D98"
        },
        {
            type: "button",
            text: "LOGIN",
            x: 410, y: 520,
            width: 400, height: 60,
            fontSize: 22,
            color: "#176B87"
        }
    ],

    landing: [
        {
            type: "rectangle",
            x: 0, y: 0,
            width: 1200, height: 800,
            color: "#F5F7FA"
        },
        {
            type: "rectangle",
            x: 0, y: 0,
            width: 1200, height: 90,
            color: "#102A43"
        },
        {
            type: "text",
            text: "VISUALOGIC",
            x: 70, y: 28,
            width: 250, height: 40,
            fontSize: 30,
            color: "#FFFFFF"
        },
        {
            type: "text",
            text: "Design Beyond Pixels",
            x: 100, y: 250,
            width: 650, height: 70,
            fontSize: 54,
            color: "#102A43"
        },
        {
            type: "text",
            text: "Where Visual Creativity Meets Code Intelligence",
            x: 105, y: 340,
            width: 700, height: 40,
            fontSize: 22,
            color: "#627D98"
        },
        {
            type: "button",
            text: "GET STARTED",
            x: 105, y: 430,
            width: 230, height: 65,
            fontSize: 20,
            color: "#176B87"
        },
        {
            type: "rectangle",
            x: 800, y: 200,
            width: 300, height: 350,
            color: "#D9EAF0"
        }
    ],

    dashboard: [
        {
            type: "rectangle",
            x: 0, y: 0,
            width: 1200, height: 800,
            color: "#F5F7FA"
        },
        {
            type: "rectangle",
            x: 0, y: 0,
            width: 250, height: 800,
            color: "#102A43"
        },
        {
            type: "text",
            text: "VISUALOGIC",
            x: 35, y: 45,
            width: 200, height: 40,
            fontSize: 27,
            color: "#FFFFFF"
        },
        {
            type: "text",
            text: "Dashboard",
            x: 300, y: 60,
            width: 300, height: 50,
            fontSize: 38,
            color: "#102A43"
        },
        {
            type: "rectangle",
            x: 300, y: 150,
            width: 250, height: 150,
            color: "#D9EAF0"
        },
        {
            type: "rectangle",
            x: 590, y: 150,
            width: 250, height: 150,
            color: "#D9EAF0"
        },
        {
            type: "rectangle",
            x: 880, y: 150,
            width: 250, height: 150,
            color: "#D9EAF0"
        },
        {
            type: "text",
            text: "TOTAL PROJECT",
            x: 325, y: 190,
            width: 200, height: 30,
            fontSize: 20,
            color: "#102A43"
        },
        {
            type: "text",
            text: "RECENT PROJECT",
            x: 325, y: 370,
            width: 300, height: 35,
            fontSize: 28,
            color: "#102A43"
        },
        {
            type: "rectangle",
            x: 300, y: 430,
            width: 830, height: 230,
            color: "#FFFFFF"
        }
    ]

};

document.querySelectorAll(".template-button").forEach(button => {

    button.addEventListener("click", function() {

        const name = this.dataset.template;

        if (!templates[name]) return;

        if (!confirm("Ganti desain canvas dengan template ini?")) return;

        objects = JSON.parse(JSON.stringify(templates[name]));

        selectedIndex = -1;

        updateProperties();
        drawCanvas();

    });

});

// SIMPAN DESAIN
document.getElementById("saveDesign").addEventListener("click", async function() {

    const button = this;
    const status = document.getElementById("saveStatus");

    button.disabled = true;
    button.textContent = "Menyimpan...";
    status.textContent = "Sedang menyimpan";

    const formData = new FormData();

    formData.append("action", "save");
    formData.append("csrf_token", csrfToken);
    formData.append("canvas_data", JSON.stringify(objects));
    formData.append("width", canvas.width);
    formData.append("height", canvas.height);

    try {

        const response = await fetch(window.location.href, {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || "Gagal menyimpan desain.");
        }

        status.textContent = "Berhasil disimpan";
        alert(result.message);

    } catch (error) {

        status.textContent = "Gagal menyimpan";
        alert(error.message);
        console.error(error);

    } finally {

        button.disabled = false;
        button.textContent = "Simpan Desain";

    }

});

// TAMPILKAN CANVAS PERTAMA KALI
drawCanvas();
updateProperties();

</script>

</body>
</html>
