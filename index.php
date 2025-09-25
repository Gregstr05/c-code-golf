<?php
require_once 'GolfLinter.php';

$linter = new GolfLinter();
$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['code_file'])) {
    if ($_FILES['code_file']['error'] === UPLOAD_ERR_OK) {
        $uploadedFile = $_FILES['code_file']['tmp_name'];
        $fileContent = file_get_contents($uploadedFile);
        
        if ($fileContent !== false) {
            $linter->setCode($fileContent);
            $result = [
                'originalWithNumbers' => $linter->getOriginalCodeWithLineNumbers(),
                'lintedWithNumbers' => $linter->getLintedCodeWithLineNumbers(),
                'count' => $linter->getStatementCount()
            ];
        } else {
            $error = "Failed to read the uploaded file.";
        }
    } else {
        $error = "Error uploading file: " . $_FILES['code_file']['error'];
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
        <a href="https://github.com/Gregstr05/c-code-golf"><img src="assets/images/github-mark-white.svg" height="20" alt="Github"></a>
    </header>

    <h1>C Code Golf</h1>

    <p>You can check out the rules <a href=https://github.com/Gregstr05/c-code-golf>here</a></p>

    <div class="upload-form">
        <h2>Upload your C code file</h2>
        <form method="POST" enctype="multipart/form-data">
            <div>
                <label for="code_file">Choose a C file (.c, .cpp, .h):</label><br>
                <input type="file" id="code_file" name="code_file" accept=".c,.cpp,.h,.cc,.cxx" required>
            </div>
            <br>
            <button type="submit">Analyze Code</button>
        </form>
    </div>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($result): ?>
        <div class="result-container">
            <div class="statement-count">
                Statement Count: <?php echo $result['count']; ?>
            </div>

            <div class="section-title">Original Code</div>
            <?php echo $result['originalWithNumbers']; ?>

            <div class="section-title">Linted Code</div>
            <?php echo $result['lintedWithNumbers']; ?>
        </div>
    <?php endif; ?>

    <!-- highlight.js JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/c.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/cpp.min.js"></script>
    
    <script>
        // Initialize syntax highlighting after page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Apply syntax highlighting to all code elements
            document.querySelectorAll('code.language-c').forEach((block) => {
                hljs.highlightElement(block);
            });
        });
    </script>
</body>
</html>