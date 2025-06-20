---
description:
globs:
alwaysApply: false
---
---
description: Enforce UTF-8 without BOM encoding for all project files
globs: **/*.php, **/*.js, **/*.css, **/*.json, **/*.md, **/*.txt, **/*.xml, **/*.yml, **/*.yaml
alwaysApply: true
---

# UTF-8 Encoding Rule

**CRITICAL: Always save files with UTF-8 encoding WITHOUT BOM (Byte Order Mark). NEVER use UTF-16 LE, UTF-16 BE, or UTF-8 with BOM.**

## Encoding Requirements

- ✅ **ONLY USE**: UTF-8 without BOM
- ❌ **NEVER USE**: UTF-16 LE (Little Endian)
- ❌ **NEVER USE**: UTF-16 BE (Big Endian)
- ❌ **NEVER USE**: UTF-8 with BOM
- ❌ **NEVER USE**: Windows-1252 or ANSI

## Why UTF-8 without BOM?

- **WordPress Compatibility**: BOM can cause "headers already sent" errors
- **PHP Compatibility**: BOM bytes can appear in output before headers
- **Cross-Platform**: Ensures consistent behavior across different systems
- **Character Support**: Handles international characters correctly
- **Web Standards**: Standard encoding for web applications

## File Types Affected

This rule applies to all text-based files:

- ✅ **PHP files** (`.php`) - Critical for WordPress
- ✅ **JavaScript files** (`.js`) - For proper script execution
- ✅ **CSS files** (`.css`) - For proper stylesheet rendering
- ✅ **JSON files** (`.json`) - For API compatibility
- ✅ **Markdown files** (`.md`) - For documentation
- ✅ **Text files** (`.txt`) - For configuration
- ✅ **XML/YAML files** (`.xml`, `.yml`, `.yaml`) - For data exchange

## Editor Configuration

### VS Code / Roo Code Settings

Add to your settings.json:

```json
{
  "files.encoding": "utf8",
  "files.autoGuessEncoding": false,
  "files.autoSave": "afterDelay",
  "files.insertFinalNewline": true,
  "files.trimTrailingWhitespace": true,
  "files.associations": {
    "*.php": "php"
  }
}
```

### Manual File Conversion

If you encounter files with wrong encoding:

1. **Open file in VS Code/Roo Code**
2. **Check encoding** in bottom status bar
3. **If not UTF-8**: Click encoding → "Reopen with Encoding" → "UTF-8"
4. **Save file** → "Save with Encoding" → "UTF-8"

### PhpStorm / IntelliJ Configuration

1. **Settings** → **Editor** → **File Encodings**
2. Set **Global Encoding**: `UTF-8`
3. Set **Project Encoding**: `UTF-8`
4. **Uncheck** "Transparent native-to-ascii conversion"
5. **Check** "Create UTF-8 files without BOM"

## Detecting Problematic Files

### Windows PowerShell Commands

```powershell
# Check first 10 bytes of a file
Get-Content "file.php" -Encoding Byte -TotalCount 10 | Format-Hex

# UTF-8 without BOM should start with: 3C 3F 70 68 70 (<?php)
# UTF-16 LE with BOM would show: FF FE 3C 00 3F 00 70 00 68 00 70 00
# UTF-8 with BOM would show: EF BB BF 3C 3F 70 68 70
```

### Linux/Mac Commands

```bash
# Check for BOM
head -c 10 file.php | hexdump -C

# Check file encoding
file -i file.php
```

## Common Encoding Problems

### UTF-16 LE Issues

```
❌ UTF-16 LE encoding causes:
- Null bytes between characters
- Parsing errors in PHP
- Broken AJAX responses
- WordPress fatal errors
```

### BOM Issues

```
❌ UTF-8 with BOM causes:
- "headers already sent" errors
- JSON parsing failures
- Whitespace before HTML output
- WordPress plugin activation failures
```

## WordPress Specific Issues

BOM can cause these WordPress problems:

```php
// ❌ DON'T: Files with BOM can cause this error
Warning: Cannot modify header information - headers already sent by...

// ✅ DO: Files without BOM work correctly
<?php
header('Content-Type: application/json');
echo json_encode($data);
```

## Validation Examples

### ✅ Correct File (UTF-8 without BOM)

```
Hex dump:
00000000  3C 3F 70 68 70 0D 0A     |<?php..|

This is correct - starts directly with <?php
```

### ❌ Incorrect File (UTF-16 LE with BOM)

```
Hex dump:
00000000  FF FE 3C 00 3F 00 70 00  |..<?php.|
00000008  68 00 70 00              |hp..|

This is wrong - has BOM (FF FE) and null bytes
```

### ❌ Incorrect File (UTF-8 with BOM)

```
Hex dump:
00000000  EF BB BF 3C 3F 70 68 70  |...<?php|

This is wrong - has UTF-8 BOM (EF BB BF)
```

## File Conversion Process

### Step 1: Identify Problematic Files

```powershell
# Find files that might have encoding issues
Get-ChildItem -Recurse -Filter "*.php" | ForEach-Object {
    $bytes = Get-Content $_.FullName -Encoding Byte -TotalCount 3
    if ($bytes.Length -ge 3 -and $bytes[0] -eq 0xFF -and $bytes[1] -eq 0xFE) {
        Write-Host "UTF-16 LE: $($_.FullName)"
    }
    elseif ($bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF) {
        Write-Host "UTF-8 with BOM: $($_.FullName)"
    }
}
```

### Step 2: Convert Files

```powershell
# Convert UTF-16 LE to UTF-8 without BOM
$content = Get-Content "file.php" -Encoding Unicode
$content | Out-File -Encoding UTF8NoBOM "file.php"

# Convert UTF-8 with BOM to UTF-8 without BOM
$content = Get-Content "file.php" -Raw
$content | Out-File -Encoding UTF8NoBOM "file.php"
```

## Development Workflow

1. **Always check** encoding when creating new files
2. **Convert existing files** if they have BOM or wrong encoding
3. **Configure your editor** to default to UTF-8 without BOM
4. **Test AJAX/API endpoints** to ensure no BOM issues
5. **Validate after** making changes to ensure encoding is correct

## Testing Checklist

After fixing encoding issues:

- [ ] Plugin activates without errors
- [ ] Admin pages load correctly
- [ ] AJAX requests work properly
- [ ] No "headers already sent" errors
- [ ] JSON responses parse correctly
- [ ] No unexpected whitespace in output

## Troubleshooting

If you encounter "headers already sent" errors:

1. **Check file encoding** of the problematic file
2. **Look for BOM** at the beginning of PHP files
3. **Convert to UTF-8 without BOM**
4. **Clear any caches** (WordPress, browser, CDN)
5. **Test thoroughly** after conversion

**Remember: UTF-8 without BOM is the ONLY acceptable encoding for this project.**
