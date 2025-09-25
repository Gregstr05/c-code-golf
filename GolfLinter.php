<?php

class GolfLinter {
    private $originalCode;
    private $lintedCode;
    private $statementCount = 0;
    private $lines = [];

    public function __construct($code = '') {
        if (!empty($code)) {
            $this->setCode($code);
        }
    }

    public function setCode($code) {
        $this->originalCode = $code;
        $this->processCode();
        return $this;
    }

    private function processCode() {
        // Split code into lines
        $lines = explode("\n", $this->originalCode);
        $this->lines = [];
        $this->statementCount = 0;
        
        $inBlockComment = false;
        $inFunction = false;
        $braceLevel = 0;
        
        foreach ($lines as $lineNum => $line) {
            $originalLine = $line;
            $trimmedLine = trim($line);
            
            // Handle block comments
            if ($inBlockComment) {
                if (strpos($line, '*/') !== false) {
                    $inBlockComment = false;
                    $line = substr($line, strpos($line, '*/') + 2);
                    $trimmedLine = trim($line);
                } else {
                    continue; // Skip entire line if in block comment
                }
            }
            
            // Remove block comments that start and end on same line
            $line = preg_replace('/\/\*.*?\*\//', '', $line);
            
            // Check for start of block comment
            if (strpos($line, '/*') !== false && strpos($line, '*/') === false) {
                $inBlockComment = true;
                $line = substr($line, 0, strpos($line, '/*'));
            }
            
            // Remove single line comments
            if (strpos($line, '//') !== false) {
                $line = substr($line, 0, strpos($line, '//'));
            }
            
            $trimmedLine = trim($line);
            
            // Skip blank lines
            if (empty($trimmedLine)) {
                continue;
            }
            
            // Skip #include statements
            if (preg_match('/^#include\s+[<"]/', $trimmedLine)) {
                continue;
            }
            
            // Track brace levels for function detection
            $braceLevel += substr_count($trimmedLine, '{') - substr_count($trimmedLine, '}');
            
            // Skip function definitions (simplified detection)
            if (preg_match('/^\w+\s+\w+\s*\([^)]*\)\s*\{?$/', $trimmedLine) ||
                preg_match('/^\w+\s+\*?\w+\s*\([^)]*\)\s*\{?$/', $trimmedLine)) {
                $inFunction = true;
                continue;
            }
            
            // Skip opening/closing braces on their own line
            if ($trimmedLine === '{' || $trimmedLine === '}') {
                if ($trimmedLine === '}' && $inFunction && $braceLevel == 0) {
                    $inFunction = false;
                }
                continue;
            }
            
            // This line should be counted - add it to linted code
            $this->lines[] = $trimmedLine;
            
            // Count statements
            if ($this->shouldCountAsStatement($trimmedLine)) {
                $this->statementCount++;
            }
        }
        
        $this->lintedCode = implode("\n", $this->lines);
    }
    
    private function shouldCountAsStatement($line) {
        $trimmedLine = trim($line);
        
        // Lines ending with semicolon
        if (preg_match('/;$/', $trimmedLine)) {
            return true;
        }
        
        // Other preprocessor directives (not #include)
        if (preg_match('/^#(?!include)/', $trimmedLine)) {
            return true;
        }
        
        // Conditional statements
        if (preg_match('/^(if|while|do|for)\s*\(/', $trimmedLine)) {
            return true;
        }
        
        // else statements
        if (preg_match('/^else(\s|$)/', $trimmedLine)) {
            return true;
        }
        
        return false;
    }
    
    public function getOriginalCode() {
        return $this->originalCode;
    }
    
    public function getLintedCode() {
        return $this->lintedCode;
    }
    
    public function getStatementCount() {
        return $this->statementCount;
    }
    
    public function getLintedLines() {
        return $this->lines;
    }

    public function getOriginalCodeWithLineNumbers() {
        $lines = explode("\n", $this->originalCode);
        $html = '<div class="code-with-numbers">';
        
        foreach ($lines as $index => $line) {
            $lineNumber = $index + 1;
            $html .= '<div class="code-line">';
            $html .= '<span class="line-number">' . str_pad($lineNumber, 2, '0', STR_PAD_LEFT) . '</span>';
            $html .= '<span class="code-content"><code class="language-c">' . htmlspecialchars($line) . '</code></span>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    public function getLintedCodeWithLineNumbers() {
        $lines = $this->getLintedLines();
        $html = '<div class="code-with-numbers">';
        
        foreach ($lines as $index => $line) {
            $lineNumber = $index + 1;
            $html .= '<div class="code-line">';
            $html .= '<span class="line-number">' . str_pad($lineNumber, 2, '0', STR_PAD_LEFT) . '</span>';
            $html .= '<span class="code-content"><code class="language-c">' . htmlspecialchars($line) . '</code></span>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        return $html;
    }
}
?>