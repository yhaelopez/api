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
  },
  existingFile: {
    type: Object,
    default: null
  },
  user: {
    type: Object,
    default: undefined
  },
  // FilePond configuration options
  allowMultiple: {
    type: Boolean,
    default: false
  },
  allowReplace: {
    type: Boolean,
    default: true
  },
  allowDrop: {
    type: Boolean,
    default: true
  },
  allowPaste: {
    type: Boolean,
    default: true
  },
  allowBrowse: {
    type: Boolean,
    default: true
  },
  allowImport: {
    type: Boolean,
    default: true
  },
  allowRevert: {
    type: Boolean,
    default: true
  },
  allowRemove: {
    type: Boolean,
    default: true
  },
  allowDownload: {
    type: Boolean,
    default: true
  },
  allowZoom: {
    type: Boolean,
    default: true
  },
  allowFullscreen: {
    type: Boolean,
    default: true
  },
  allowRotate: {
    type: Boolean,
    default: true
  },
  allowCrop: {
    type: Boolean,
    default: true
  },
  allowResize: {
    type: Boolean,
    default: true
  },
  allowEdit: {
    type: Boolean,
    default: true
  },
  allowPrint: {
    type: Boolean,
    default: true
  },
  allowExport: {
    type: Boolean,
    default: true
  },
  allowShare: {
    type: Boolean,
    default: true
  },
  allowUpload: {
    type: Boolean,
    default: true
  },
  instantUpload: {
    type: Boolean,
    default: true
  },
  acceptedFileTypes: {
    type: Array,
    default: () => ['image/jpeg', 'image/png', 'image/webp']
  },
  maxFileSize: {
    type: Number,
    default: 10485760 // 10MB
  },
  maxFiles: {
    type: Number,
    default: null
  },
  maxParallelUploads: {
    type: Number,
    default: 2
  },
  imagePreview: {
    type: Boolean,
    default: true
  },
  imageResize: {
    type: Boolean,
    default: true
  },
  imageTransform: {
    type: Boolean,
    default: true
  },
  imageExifOrientation: {
    type: Boolean,
    default: true
  },
  allowFilePoster: {
    type: Boolean,
    default: true
  },
  filePosterHeight: {
    type: Number,
    default: 150
  },
  labelIdle: {
    type: String,
    default: 'Drop files here or click to browse'
  },
  labelFileProcessing: {
    type: String,
    default: 'Uploading'
  },
  labelFileProcessingComplete: {
    type: String,
    default: 'Upload complete'
  },
  labelFileProcessingAborted: {
    type: String,
    default: 'Upload cancelled'
  },
  labelFileProcessingError: {
    type: String,
    default: 'Error during upload'
  },
  labelTapToCancel: {
    type: String,
    default: 'tap to cancel'
  },
  labelTapToRetry: {
    type: String,
    default: 'tap to retry'
  },
  labelTapToUndo: {
    type: String,
    default: 'tap to undo'
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
  },
  remove: (source, load, error) => {
    // Handle removal of already uploaded files (permanent media)
    // Check if this is a profile photo for a specific user
    if (props.user && props.existingFile) {
      // This is a user profile photo, use the specific user endpoint
      fetch(`/api/v1/users/${props.user.id}/profile-photo`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          'Authorization': `Bearer ${localStorage.getItem('auth_token') || ''}`,
          'Content-Type': 'application/json'
        }
      })
      .then(response => response.json())
      .then(result => {
        if (result.success) {
          load() // Successfully deleted from server
        } else {
          error(result.message || 'Failed to delete profile photo')
        }
      })
      .catch(err => {
        console.error('Error deleting profile photo:', err)
        error('Failed to delete profile photo')
      })
    } else {
      // For temporary files or other cases, just remove from UI
      load()
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
    // Prepare initial files array if we have an existing file
    const initialFiles = props.existingFile ? [{
      source: props.existingFile.url,
      options: {
        type: 'local',
        file: {
          name: props.existingFile.name,
          size: props.existingFile.size || 0,
          type: props.existingFile.mime_type || 'image/jpeg'
        },
        metadata: {
          poster: props.existingFile.url
        }
      }
    }] : [];

    pond.value = FilePond.create(pondElement.value, {
      allowMultiple: props.allowMultiple,
      allowReplace: props.allowReplace,
      allowDrop: props.allowDrop,
      allowPaste: props.allowPaste,
      allowBrowse: props.allowBrowse,
      allowImport: props.allowImport,
      allowRevert: props.allowRevert,
      allowRemove: props.allowRemove,
      allowDownload: props.allowDownload,
      allowZoom: props.allowZoom,
      allowFullscreen: props.allowFullscreen,
      allowRotate: props.allowRotate,
      allowCrop: props.allowCrop,
      allowResize: props.allowResize,
      allowEdit: props.allowEdit,
      allowPrint: props.allowPrint,
      allowExport: props.allowExport,
      allowUpload: props.allowUpload,
      instantUpload: props.instantUpload,
      acceptedFileTypes: props.acceptedFileTypes,
      maxFileSize: props.maxFileSize,
      maxFiles: props.maxFiles,
      maxParallelUploads: props.maxParallelUploads,
      imagePreview: props.imagePreview,
      imageResize: props.imageResize,
      imageTransform: props.imageTransform,
      imageExifOrientation: props.imageExifOrientation,
      allowFilePoster: props.allowFilePoster,
      filePosterHeight: props.filePosterHeight,
      labelIdle: props.labelIdle,
      labelFileProcessing: props.labelFileProcessing,
      labelFileProcessingComplete: props.labelFileProcessingComplete,
      labelFileProcessingAborted: props.labelFileProcessingAborted,
      labelFileProcessingError: props.labelFileProcessingError,
      labelTapToCancel: props.labelTapToCancel,
      labelTapToRetry: props.labelTapToRetry,
      labelTapToUndo: props.labelTapToUndo,
      server: serverConfig,
      files: initialFiles,
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

</style>
