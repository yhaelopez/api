<template>
  <div class="filepond-upload">
    <div ref="pondElement" class="filepond-container"></div>
    
    <!-- Hidden input to store the temp folder for form submission -->
    <input 
      type="hidden" 
      name="temp_folder" 
      :value="tempFolder" 
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import 'filepond/dist/filepond.min.css'
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css'

// Import FilePond and plugins
import * as FilePond from 'filepond'
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type'
import FilePondPluginImageExifOrientation from 'filepond-plugin-image-exif-orientation'
import FilePondPluginImagePreview from 'filepond-plugin-image-preview'
import FilePondPluginImageResize from 'filepond-plugin-image-resize'
import FilePondPluginImageTransform from 'filepond-plugin-image-transform'

// Register plugins
FilePond.registerPlugin(
  FilePondPluginFileValidateType,
  FilePondPluginImageExifOrientation,
  FilePondPluginImagePreview,
  FilePondPluginImageResize,
  FilePondPluginImageTransform
)

// Props
const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  }
})

// Emits
const emit = defineEmits(['update:modelValue', 'file-processed', 'file-removed'])

// Reactive data
const files = ref([])
const tempFolder = ref('')
const pondElement = ref(null)
const pond = ref(null)

// Server configuration for FilePond
const serverConfig = {
  url: '/api/v1/upload/temp',
  headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    'Authorization': `Bearer ${localStorage.getItem('auth_token') || ''}`
  },
  process: {
    method: 'POST',
    withCredentials: false,
    onload: (response) => {
      try {
        const result = JSON.parse(response)
        if (result.success) {
          tempFolder.value = result.folder
          emit('update:modelValue', result.folder)
          emit('file-processed', result)
        }
      } catch (e) {
        console.error('Failed to parse upload response:', e)
      }
    },
    onerror: (response) => {
      console.error('Upload failed:', response)
    }
  },
  revert: {
    url: '/api/v1/upload/temp',
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      'Authorization': `Bearer ${localStorage.getItem('auth_token') || ''}`
    }
  }
}

// Event handlers
const handleAddFile = (error, file) => {
  if (error) {
    console.error('Error adding file:', error)
    return
  }
  console.log('File added:', file)
}

const handleRemoveFile = (error, file) => {
  if (error) {
    console.error('Error removing file:', error)
    return
  }
  tempFolder.value = ''
  emit('update:modelValue', '')
  emit('file-removed', file)
}

const handleProcessFile = (error, file) => {
  if (error) {
    console.error('Error processing file:', error)
    return
  }
  console.log('File processed:', file)
}

const handleError = (error, file, status) => {
  console.error('FilePond error:', error, file, status)
}

// Watch for external changes
onMounted(() => {
  if (props.modelValue) {
    tempFolder.value = props.modelValue
  }
  
  // Initialize FilePond after component is mounted
  if (pondElement.value) {
    pond.value = FilePond.create(pondElement.value, {
      allowMultiple: false,
      allowReplace: true,
      allowDrop: true,
      allowPaste: true,
      allowBrowse: true,
      allowImport: true,
      allowRevert: true,
      allowRemove: true,
      allowDownload: true,
      allowZoom: true,
      allowFullscreen: true,
      allowRotate: true,
      allowCrop: true,
      allowResize: true,
      allowEdit: true,
      allowPrint: true,
      allowExport: true,
      allowShare: true,
      allowUpload: true,
      acceptedFileTypes: ['image/jpeg', 'image/png', 'image/webp'],
      maxFileSize: 10485760,
      imagePreview: true,
      imageResize: true,
      imageTransform: true,
      imageExifOrientation: true,
      server: serverConfig,
      onaddfile: handleAddFile,
      onremovefile: handleRemoveFile,
      onprocessfile: handleProcessFile,
      onerror: handleError,
      name: 'file' // This ensures the file field is named 'file' when sent to the server
    })
  }
})
</script>

<style>
@import 'filepond/dist/filepond.min.css';
@import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';

.filepond-upload {
  width: 100%;
}

.filepond--root {
  font-family: inherit;
}

.filepond--panel-root {
  background-color: #f8fafc;
  border: 2px dashed #cbd5e1;
  border-radius: 0.5rem;
}

.filepond--drop-label {
  color: #64748b;
}

.filepond--item {
  width: 100%;
}

.filepond--file-info {
  color: #1e293b;
}

.filepond--progress-indicator {
  background-color: #3b82f6;
}

.filepond--success-indicator {
  background-color: #10b981;
}

.filepond--error-indicator {
  background-color: #ef4444;
}
</style>
