# FilePond Integration with Temporary File Handling

This document explains how to use the FilePond integration with temporary file handling in your Laravel application.

## Overview

The system works as follows:
1. User uploads a file via FilePond
2. File is stored temporarily in `storage/app/tmp/{uuid}/` folder
3. A database record is created with expiration time (24 hours)
4. The folder name (UUID) is returned to the frontend
5. When the form is submitted, the folder name is sent instead of the file
6. The temporary files are moved to the Media Library and cleaned up

## API Endpoints

### Upload Temporary File
```
POST /api/v1/upload/temp
Content-Type: multipart/form-data

file: [image file]
```

**Response:**
```json
{
  "success": true,
  "folder": "uuid-string",
  "filename": "original-filename.jpg",
  "size": 12345
}
```

### User Form Submission
Instead of sending `profile_photo` file, send `temp_folder` string:

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "temp_folder": "uuid-string-from-upload"
}
```

## Frontend Usage

### Vue Component
```vue
<template>
  <FilePondUpload v-model="tempFolder" />
</template>

<script setup>
import FilePondUpload from '@/components/FilePondUpload.vue'
import { ref } from 'vue'

const tempFolder = ref('')
</script>
```

### Form Submission
```javascript
const formData = {
  name: 'John Doe',
  email: 'john@example.com',
  password: 'password123',
  temp_folder: tempFolder.value // This will be the UUID from FilePond
}

// Submit form with temp_folder instead of file
```

## Database Schema

### Temporary Files Table
```sql
CREATE TABLE temporary_files (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    folder VARCHAR(255) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(255) NOT NULL,
    size BIGINT NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_folder_filename (folder, filename),
    INDEX idx_expires_at (expires_at)
);
```

## Cleanup Commands

### Manual Cleanup
```bash
./vendor/bin/sail artisan cleanup:temporary-files
```

### Scheduled Cleanup (Recommended)
Add to `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('cleanup:temporary-files')->hourly();
}
```

## File Validation

The system accepts:
- **File types**: JPG, PNG, WebP
- **Max size**: 10MB
- **Expiration**: 24 hours

## Storage Structure

```
storage/app/
├── tmp/
│   ├── uuid-1/
│   │   └── temp_uuid-1.jpg
│   └── uuid-2/
│       └── temp_uuid-2.png
```

## Error Handling

- If temporary file is not found during form submission, the process continues
- Failed uploads are logged but don't break the form submission
- Expired files are automatically cleaned up

## Security

- Files are stored in `local` disk (not publicly accessible)
- Authentication required for upload endpoint
- Rate limiting: 60 requests per minute per user
- File type validation on both frontend and backend
