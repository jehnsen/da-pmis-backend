# File Upload & Storage Security Guide

This document describes the comprehensive file upload and storage management security features implemented in DA-PMIS to handle edge cases and security vulnerabilities.

## Table of Contents

1. [Overview](#overview)
2. [Security Features](#security-features)
3. [Configuration](#configuration)
4. [Storage Management](#storage-management)
5. [Maintenance Commands](#maintenance-commands)
6. [Error Handling](#error-handling)
7. [Best Practices](#best-practices)

---

## Overview

The file upload system implements multiple layers of security to protect against:

- **Storage Exhaustion**: Disk space monitoring and limits
- **Orphaned Files**: Automatic cleanup of files without database records
- **Filename Collisions**: Unique filename generation with collision prevention
- **Upload Timeouts**: Transaction-based uploads with rollback
- **Malicious Files**: Virus scanning and content validation
- **Permission Issues**: Proper file permissions and error handling
- **Path Traversal**: Filename sanitization to prevent directory traversal attacks

---

## Security Features

### 1. Storage Monitoring & Disk Space Validation

**Problem**: Storage can fill up without warning, causing upload failures.

**Solution**: Pre-upload disk space checks with configurable thresholds.

```php
// Automatic disk space check before upload
if (!$this->fileUploadService->hasSufficientDiskSpace($totalSize)) {
    return response()->json([
        'message' => 'Insufficient disk space for upload',
    ], 507); // HTTP 507 Insufficient Storage
}
```

**Configuration** ([config/filesystems.php](../config/filesystems.php)):
```php
'upload_security' => [
    'min_free_space' => env('UPLOAD_MIN_FREE_SPACE', 1024 * 1024 * 1024), // 1GB
    'min_free_space_percentage' => env('UPLOAD_MIN_FREE_SPACE_PCT', 10), // 10%
],
```

**Monitoring Command**:
```bash
# Check storage usage
php artisan storage:monitor

# Custom thresholds
php artisan storage:monitor --warn-threshold=80 --critical-threshold=90
```

---

### 2. Orphaned File Cleanup

**Problem**: Files remain in storage when database records are deleted due to errors or direct database manipulation.

**Solution**: Automated cleanup system with scheduled tasks and manual commands.

**Automatic Cleanup** (Observer Pattern):
```php
// ProjectImageObserver automatically deletes files when records are deleted
ProjectImage::observe(ProjectImageObserver::class);
```

**Manual Cleanup Command**:
```bash
# Dry run to see what would be deleted
php artisan files:cleanup-orphaned --dry-run

# Delete orphaned files older than 7 days (default)
php artisan files:cleanup-orphaned

# Custom age threshold
php artisan files:cleanup-orphaned --age-days=30

# Specific directory
php artisan files:cleanup-orphaned --directory=projects/123
```

**Scheduled Cleanup** (add to [app/Console/Kernel.php](../app/Console/Kernel.php)):
```php
protected function schedule(Schedule $schedule)
{
    // Run weekly cleanup of orphaned files
    $schedule->command('files:cleanup-orphaned')
        ->weekly()
        ->sundays()
        ->at('02:00');
}
```

---

### 3. Filename Collision Prevention

**Problem**: Multiple concurrent uploads with the same filename can overwrite each other.

**Solution**: Multi-layered unique filename generation.

```php
// Generated filename format:
// {basename}_{timestamp}_{hash}_{random}.{extension}
// Example: project_photo_1706486400_a1b2c3d4_xyz12345.jpg

$filename = $this->fileUploadService->generateUniqueFilename($file, $directory);
```

**Features**:
- Timestamp precision
- Content-based hash (MD5 of file content)
- Random string component
- Collision detection loop with max attempts
- Sanitized basename

---

### 4. Path Traversal Prevention

**Problem**: Malicious filenames like `../../etc/passwd` can escape intended directory.

**Solution**: Aggressive filename sanitization.

```php
public function sanitizeFilename(string $filename): string
{
    // Removes: .., /, \, null bytes
    // Replaces special characters with underscores
    // Limits filename length
    // Ensures not empty
}
```

**Examples**:
```
Input:  ../../etc/passwd.jpg
Output: etc_passwd.jpg

Input:  <script>alert(1)</script>.jpg
Output: script_alert_1_script_.jpg

Input:  image with spaces & special@chars!.png
Output: image_with_spaces___special_chars_.png
```

---

### 5. Large File Upload Handling

**Problem**: Large uploads can timeout or fail mid-upload.

**Solution**: Transaction-based uploads with automatic rollback.

```php
DB::beginTransaction();
try {
    // Upload all files
    foreach ($files as $file) {
        $this->fileUploadService->uploadSecurely($file, $directory);
    }
    DB::commit();
} catch (Exception $e) {
    DB::rollBack();
    // Clean up any uploaded files
    $this->cleanupUploadedFiles($uploaded);
    throw $e;
}
```

**Configuration**:
```php
// .env
UPLOAD_MAX_FILE_SIZE=5242880  # 5MB in bytes
```

**PHP Settings** ([php.ini](../php.ini)):
```ini
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 300
max_input_time = 300
```

---

### 6. Virus Scanning

**Problem**: Uploaded files could contain viruses or malware.

**Solution**: Optional ClamAV integration with configurable behavior.

**Requirements**:
```bash
# Install ClamAV (Ubuntu/Debian)
sudo apt-get install clamav clamav-daemon

# Update virus definitions
sudo freshclam

# Start daemon
sudo systemctl start clamav-daemon
```

**Configuration** ([config/filesystems.php](../config/filesystems.php)):
```php
'virus_scanning' => [
    'enabled' => env('VIRUS_SCAN_ENABLED', false),
    'method' => env('VIRUS_SCAN_METHOD', 'clamav'),
    'clamav_socket' => env('CLAMAV_SOCKET', '/var/run/clamav/clamd.ctl'),
    'strict' => env('VIRUS_SCAN_STRICT', false), // Reject if scanner unavailable
    'timeout' => env('VIRUS_SCAN_TIMEOUT', 30),
],
```

**Environment Variables** ([.env](../.env)):
```bash
# Enable virus scanning
VIRUS_SCAN_ENABLED=true
VIRUS_SCAN_METHOD=clamav
VIRUS_SCAN_STRICT=false  # false = allow uploads if scanner down, true = reject

# ClamAV socket path (Unix systems)
CLAMAV_SOCKET=/var/run/clamav/clamd.ctl

# Windows: Use TCP instead
# CLAMAV_HOST=127.0.0.1
# CLAMAV_PORT=3310
```

**Behavior**:
- If enabled + strict mode: Reject uploads if scan fails or scanner unavailable
- If enabled + non-strict: Allow uploads if scanner unavailable, scan if available
- If disabled: Skip scanning entirely

---

### 7. File Content Validation

**Problem**: Malicious files disguised as images (e.g., PHP code in .jpg file).

**Solution**: Multiple validation layers.

```php
// 1. Extension validation
'images.*' => 'mimes:jpeg,jpg,png,gif,webp'

// 2. MIME type validation against extension
if (!in_array($mimeType, $allowedMimeTypes[$extension])) {
    throw new Exception('MIME type does not match extension');
}

// 3. Check for PHP code in file content
if (stripos($content, '<?php') !== false) {
    throw new Exception('File contains potentially malicious code');
}

// 4. Verify it's actually an image
if (!getimagesize($file->getRealPath())) {
    throw new Exception('File is not a valid image');
}
```

---

### 8. Permission Handling

**Problem**: Files uploaded with wrong permissions are not readable by web server.

**Solution**: Explicit permission setting after upload.

```php
// Set file permissions to 0644 (owner: rw, group: r, world: r)
$fullPath = Storage::disk('public')->path($filePath);
chmod($fullPath, 0644);
```

**Verification**:
```php
// Verify file was actually written
if (!Storage::disk($disk)->exists($filePath)) {
    throw new Exception('File upload verification failed');
}
```

---

## Configuration

### Environment Variables

Add to [.env](../.env):

```bash
# File Upload Security
UPLOAD_MAX_FILE_SIZE=5242880           # 5MB in bytes
UPLOAD_MIN_FREE_SPACE=1073741824       # 1GB minimum free space
UPLOAD_MIN_FREE_SPACE_PCT=10           # 10% minimum free space percentage
UPLOAD_STRICT_VALIDATION=true          # Strict MIME type validation

# Virus Scanning
VIRUS_SCAN_ENABLED=false               # Enable ClamAV scanning
VIRUS_SCAN_METHOD=clamav               # Scanning method
VIRUS_SCAN_STRICT=false                # Reject if scanner unavailable
VIRUS_SCAN_TIMEOUT=30                  # Scan timeout in seconds
CLAMAV_SOCKET=/var/run/clamav/clamd.ctl

# Orphaned File Cleanup
ORPHANED_CLEANUP_ENABLED=true          # Enable automatic cleanup
ORPHANED_CLEANUP_AGE_DAYS=7            # Delete files older than X days
ORPHANED_CLEANUP_AUTO_SCHEDULE=false   # Auto-schedule cleanup job
ORPHANED_CLEANUP_FREQUENCY=weekly      # daily, weekly, monthly
```

### Filesystem Configuration

See [config/filesystems.php](../config/filesystems.php) for detailed configuration options.

---

## Storage Management

### Check Storage Statistics

**API Endpoint**:
```http
GET /api/storage/stats
Authorization: Bearer {token}
```

**Response**:
```json
{
  "success": true,
  "data": {
    "total": 107374182400,
    "free": 53687091200,
    "used": 53687091200,
    "usage_percentage": 50.0,
    "formatted": {
      "total": "100 GB",
      "free": "50 GB",
      "used": "50 GB"
    }
  }
}
```

### Monitor Storage Usage

```bash
# Basic monitoring
php artisan storage:monitor

# Output:
# =================================================
# Storage Statistics:
# =================================================
# Total space:    100 GB
# Used space:     50 GB
# Free space:     50 GB
# Usage:          50.0%
# =================================================
```

---

## Maintenance Commands

### 1. Cleanup Orphaned Files

```bash
# Dry run (see what would be deleted)
php artisan files:cleanup-orphaned --dry-run

# Execute cleanup (delete files older than 7 days)
php artisan files:cleanup-orphaned

# Custom age threshold (30 days)
php artisan files:cleanup-orphaned --age-days=30

# Specific directory only
php artisan files:cleanup-orphaned --directory=projects/123
```

**Output**:
```
Starting orphaned file cleanup...
Mode: LIVE
Age threshold: 7 days
Scanning 25 directories...

Scanning: projects/123
  Found 3 orphaned file(s)
    - old_photo_1.jpg (2.5 MB, 10.2 days old)
    - unused_doc.pdf (1.2 MB, 15.0 days old)
    - temp_upload.png (500 KB, 8.5 days old)
  Deleted 3 file(s)

=================================================
Cleanup Summary:
=================================================
Total orphaned files found: 3
Total size: 4.2 MB
Total files deleted: 3

Storage Statistics:
  Total space: 100 GB
  Used space: 49.99 GB
  Free space: 50.01 GB
  Usage: 49.99%
```

### 2. Monitor Storage

```bash
# Default monitoring
php artisan storage:monitor

# Custom thresholds
php artisan storage:monitor --warn-threshold=70 --critical-threshold=85

# Specific disk
php artisan storage:monitor --disk=local
```

**Exit Codes**:
- `0` (SUCCESS): Storage usage is healthy
- `1` (FAILURE): Storage usage is critical or monitoring failed

---

## Error Handling

### Upload Errors

| Error | HTTP Code | Cause | Solution |
|-------|-----------|-------|----------|
| Insufficient disk space | 507 | Storage full | Free up space or expand storage |
| File validation failed | 422 | Invalid file type/content | Upload valid image files only |
| File failed virus scan | 422 | Virus detected | Scan file locally, upload clean file |
| File too large | 413 | Exceeds max size | Reduce file size or increase limit |
| Filename collision | 500 | Unable to generate unique name | Retry upload, contact admin if persists |

### Partial Upload Success

If some files upload successfully and others fail, the API returns:

```json
{
  "success": true,
  "message": "5 image(s) uploaded successfully (2 failed)",
  "images": [...],
  "partial_errors": [
    {
      "file": "corrupted_image.jpg",
      "error": "File is not a valid image"
    },
    {
      "file": "infected_file.jpg",
      "error": "File failed virus scan"
    }
  ]
}
```

### Automatic Cleanup on Failure

If a transaction fails, all uploaded files are automatically deleted:

```php
catch (Exception $e) {
    DB::rollBack();

    // Clean up any uploaded files
    foreach ($uploaded as $image) {
        Storage::disk('public')->delete($image->file_path);
    }
}
```

---

## Best Practices

### 1. Regular Monitoring

Schedule regular storage monitoring:

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Monitor storage daily
    $schedule->command('storage:monitor')
        ->daily()
        ->at('06:00');

    // Cleanup orphaned files weekly
    $schedule->command('files:cleanup-orphaned')
        ->weekly()
        ->sundays()
        ->at('02:00');
}
```

### 2. Alerting

Set up alerts for critical storage levels:

```php
// Send email/notification when storage critical
if ($usagePercentage >= 90) {
    Notification::route('mail', 'admin@example.com')
        ->notify(new StorageCriticalNotification($stats));
}
```

### 3. Backup Before Cleanup

Always backup before running cleanup commands:

```bash
# Backup storage directory
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app/public/projects/

# Then run cleanup
php artisan files:cleanup-orphaned
```

### 4. Production Configuration

For production environments:

```bash
# .env (Production)
VIRUS_SCAN_ENABLED=true              # Always enable virus scanning
VIRUS_SCAN_STRICT=true               # Strict mode for security
UPLOAD_STRICT_VALIDATION=true        # Strict file validation
UPLOAD_MIN_FREE_SPACE=10737418240    # 10GB minimum free space
ORPHANED_CLEANUP_AUTO_SCHEDULE=true  # Auto cleanup enabled
```

### 5. Testing

Test upload security features:

```bash
# Test storage monitoring
php artisan storage:monitor

# Test orphaned file detection (dry run)
php artisan files:cleanup-orphaned --dry-run

# Test file upload with various scenarios
# - Large files
# - Invalid file types
# - Concurrent uploads
# - Low disk space simulation
```

### 6. Logging

Monitor logs for upload issues:

```bash
# Watch upload errors
tail -f storage/logs/laravel.log | grep "upload"

# Check for virus scan failures
tail -f storage/logs/laravel.log | grep "virus"

# Monitor orphaned file cleanup
tail -f storage/logs/laravel.log | grep "orphaned"
```

---

## API Endpoints

### Upload Images

```http
POST /api/projects/{project}/images
Authorization: Bearer {token}
Content-Type: multipart/form-data

images[]: (file)
captions[]: (optional string)
image_types[]: (optional: cover|progress|documentation|before|after|other)
```

### Get Storage Statistics

```http
GET /api/storage/stats
Authorization: Bearer {token}
```

### Delete Image

```http
DELETE /api/projects/{project}/images/{image}
Authorization: Bearer {token}
```

---

## Security Checklist

- [x] Disk space monitoring before uploads
- [x] Orphaned file cleanup (manual + automatic)
- [x] Filename collision prevention
- [x] Large file timeout handling with transactions
- [x] Virus scanning integration (optional)
- [x] File permission verification
- [x] Path traversal attack prevention
- [x] File content validation (not just extension)
- [x] MIME type validation
- [x] Automatic file cleanup on record deletion
- [x] Comprehensive error logging
- [x] Partial upload success handling
- [x] Transaction rollback on errors

---

## Troubleshooting

### Issue: "Insufficient disk space for upload"

**Cause**: Storage usage exceeds configured thresholds.

**Solution**:
```bash
# Check storage stats
php artisan storage:monitor

# Clean up orphaned files
php artisan files:cleanup-orphaned

# Check if projects have unnecessary files
du -sh storage/app/public/projects/* | sort -h
```

### Issue: "File failed virus scan"

**Cause**: ClamAV detected a threat or scanner is unavailable.

**Solution**:
```bash
# Check ClamAV status
sudo systemctl status clamav-daemon

# Update virus definitions
sudo freshclam

# Test ClamAV
clamscan /path/to/test/file
```

### Issue: Files not deleting automatically

**Cause**: Observer not registered.

**Solution**:
```bash
# Clear config cache
php artisan config:clear

# Verify observer is registered in AppServiceProvider
grep "ProjectImageObserver" app/Providers/AppServiceProvider.php
```

### Issue: Permission denied errors

**Cause**: Web server can't write to storage directory.

**Solution**:
```bash
# Fix permissions (Ubuntu/Debian)
sudo chown -R www-data:www-data storage/app/public
sudo chmod -R 775 storage/app/public

# Fix permissions (CentOS/RHEL)
sudo chown -R apache:apache storage/app/public
sudo chmod -R 775 storage/app/public
```

---

## Related Documentation

- [SETUP_GUIDE.md](SETUP_GUIDE.md) - Initial setup and configuration
- [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) - Complete project overview
- [POSTMAN_GUIDE.md](POSTMAN_GUIDE.md) - API testing guide

---

**Version**: 1.0
**Last Updated**: 2026-01-29
**Maintainer**: DA-PMIS Development Team
