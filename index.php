<?php
require_once "GolfLinter.php";

$results = [];
$errors = [];
$totalStatements = 0;

function normalizeUploadedFiles($files)
{
    // Turns $_FILES['code_files'] into a flat array of file entries
    $normalized = [];

    if (!isset($files["name"])) {
        return $normalized;
    }

    // If not multiple, wrap it
    if (!is_array($files["name"])) {
        return [
                [
                        "name" => $files["name"],
                        "type" => isset($files["type"]) ? $files["type"] : "",
                        "tmp_name" => $files["tmp_name"],
                        "error" => $files["error"],
                        "size" => isset($files["size"]) ? $files["size"] : 0,
                ],
        ];
    }

    foreach ($files["name"] as $i => $name) {
        $normalized[] = [
                "name" => $name,
                "type" => isset($files["type"][$i]) ? $files["type"][$i] : "",
                "tmp_name" => isset($files["tmp_name"][$i]) ? $files["tmp_name"][$i] : "",
                "error" => isset($files["error"][$i]) ? $files["error"][$i] : UPLOAD_ERR_NO_FILE,
                "size" => isset($files["size"][$i]) ? $files["size"][$i] : 0,
        ];
    }

    return $normalized;
}

function guessHighlightLanguageFromFilename($filename)
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (in_array($ext, ["cpp", "cc", "cxx", "hpp", "hh", "hxx"], true)) {
        return "cpp";
    }

    // Treat .c, .h as C by default
    return "c";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["code_files"])) {
    $uploadedFiles = normalizeUploadedFiles($_FILES["code_files"]);

    if (count($uploadedFiles) === 0) {
        $errors[] = "No files received.";
    }

    foreach ($uploadedFiles as $file) {
        $name = isset($file["name"]) ? $file["name"] : "unknown";
        $error = isset($file["error"]) ? $file["error"] : UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if ($error !== UPLOAD_ERR_OK) {
            $errors[] = "Error uploading '{$name}': " . $error;
            continue;
        }

        $tmp = $file["tmp_name"];
        $fileContent = @file_get_contents($tmp);

        if ($fileContent === false) {
            $errors[] = "Failed to read uploaded file '{$name}'.";
            continue;
        }

        $linter = new GolfLinter();
        $linter->setCode($fileContent);

        $lang = guessHighlightLanguageFromFilename($name);
        $count = $linter->getStatementCount();

        $results[] = [
                "name" => $name,
                "language" => $lang,
                "count" => $count,
                "originalWithNumbers" => $linter->getOriginalCodeWithLineNumbers($lang),
                "lintedWithNumbers" => $linter->getLintedCodeWithLineNumbers($lang),
        ];

        $totalStatements += $count;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:image" content="assets/images/code_golf.webp"/>
    <title>Code Golf</title>
</head>
<body>
<header>
    <a href="https://github.com/Gregstr05/c-code-golf">
        <img src="assets/images/github-mark-white.svg" height="20" alt="Github">
    </a>
</header>

<h1>C Code Golf</h1>

<p>You can check out the rules <a href="https://github.com/Gregstr05/c-code-golf">here</a></p>

<div class="upload-form">
    <h2>Upload your C/C++ code files</h2>
    <form method="POST" enctype="multipart/form-data">
        <div>
            <label for="code_files">Choose file(s) (.c, .cpp, .h):</label><br>
            <input
                    type="file"
                    id="code_files"
                    name="code_files[]"
                    accept=".c,.cpp,.h,.cc,.cxx,.hpp,.hh,.hxx"
                    multiple
                    required
            >
        </div>
        <br>
        <button type="submit">Analyze Code</button>
    </form>
</div>

<?php if (count($errors) > 0): ?>
    <div class="error">
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (count($results) > 0): ?>
    <div class="result-container">
        <?php if (count($results) > 1): ?>
            <div class="statement-count total-statement-count">
                Total Statements (all files): <?php echo $totalStatements; ?>
            </div>
        <?php endif; ?>

        <?php foreach ($results as $idx => $r): ?>
            <div class="file-result">
                <div class="section-title">
                    File: <?php echo htmlspecialchars($r["name"]); ?>
                </div>

                <div class="statement-count">
                    Statements (this file): <?php echo $r["count"]; ?>
                </div>

                <details class="code-collapsible" open>
                    <summary>Original Code</summary>
                    <?php echo $r["originalWithNumbers"]; ?>
                </details>

                <details class="code-collapsible">
                    <summary>Linted Code</summary>
                    <?php echo $r["lintedWithNumbers"]; ?>
                </details>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/c.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/cpp.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll("code[class^='language-']").forEach((block) => {
            hljs.highlightElement(block);
        });
    });
</script>
</body>
</html>