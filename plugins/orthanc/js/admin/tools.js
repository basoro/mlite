// Global variables
console.log('DICOM Viewer tools.js loaded');
let loaded = false;
let series = [];
let currentFrame = 0;
let totalFrames = 0;
let isPlaying = false;
let playbackInterval = null;
let playbackSpeed = 100; // milliseconds between frames
let element = null;
let activeTool = null;
let dicomMetadata = {};

// CT Scan specific variables
let isCTScan = false;
let ctSlices = [];
let currentCTSlice = 0;
let ctPlaybackSpeed = 500; // milliseconds between CT slices
let ctPlaybackDirection = 1; // 1 for forward, -1 for backward
let ctLoopMode = 'continuous'; // 'once' or 'continuous'
let ctPlaybackInterval = null;
let isCTPlaying = false;
let orthancConfig = {
    server: '{$orthanc.server}',
    username: '{$orthanc.username}',
    password: '{$orthanc.password}'
};
let currentStudyId = null;
let studyData = null;

// Initialize when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DICOM Viewer: DOM loaded, initializing...');
    
    // Initialize Cornerstone and tools
    initializeCornerstone();
    setupEventListeners();
    initializeTools();
    
    // Check if study parameter exists in URL and load it
    const studyId = getUrlParameter('study');
    const seriesId = getUrlParameter('series');
    
    console.log('URL parameters - study:', studyId, 'series:', seriesId);
    
    if (studyId) {
        console.log('Loading study:', studyId);
        loadStudyFromOrthanc(studyId);
    } else if (seriesId) {
        console.log('Loading series directly:', seriesId);
        loadSeriesDirectly(seriesId);
    } else {
        console.log('No study or series parameter found in URL');
    }
});

function initializeCornerstone() {
    console.log('Initializing Cornerstone...');
    
    try {
        // Set up cornerstone external dependencies
        cornerstoneTools.external.Hammer = Hammer;
        cornerstoneTools.external.cornerstone = cornerstone;
        cornerstoneTools.external.cornerstoneMath = cornerstoneMath;
        cornerstoneWADOImageLoader.external.dicomParser = dicomParser;
        cornerstoneWADOImageLoader.external.cornerstone = cornerstone;
        
        console.log('Cornerstone external dependencies set');
        
        // Initialize cornerstone tools
        cornerstoneTools.init({
            showSVGCursors: true,
            mouseEnabled: true,
            touchEnabled: true
        });
        
        console.log('Cornerstone tools initialized');
        
        // Enable the element for cornerstone
        element = document.getElementById('dicomImage');
        if (!element) {
            console.error('DICOM image element not found!');
            return;
        }
        
        cornerstone.enable(element);
        console.log('Cornerstone element enabled:', element);
        
        // Set default status message
        updateStatus('Ready to load DICOM files');
        console.log('Cornerstone initialization complete');
        
    } catch (error) {
        console.error('Error initializing Cornerstone:', error);
        updateStatus('Error initializing DICOM viewer');
    }
}

function setupEventListeners() {
    // File upload handling
    // document.getElementById('uploadBtn').addEventListener('click', function() {
    //     document.getElementById('dicomUpload').click();
    // });
    
    // document.getElementById('dicomUpload').addEventListener('change', handleFileUpload);
    
    // Tool buttons
    document.getElementById('wwwcTool').addEventListener('click', function() { activateTool('Wwwc'); });
    document.getElementById('zoomTool').addEventListener('click', function() { activateTool('Zoom'); });
    document.getElementById('panTool').addEventListener('click', function() { activateTool('Pan'); });
    document.getElementById('magnifyTool').addEventListener('click', function() { activateTool('Magnify'); });
    document.getElementById('invertTool').addEventListener('click', function() { handleInvert(); });
    document.getElementById('resetTool').addEventListener('click', function() { handleReset(); });
    document.getElementById('eraserTool').addEventListener('click', function() { activateTool('Eraser'); });
    
    // Annotation tools
    document.getElementById('lengthTool').addEventListener('click', function() { activateTool('Length'); });
    document.getElementById('angleTool').addEventListener('click', function() { activateTool('Angle'); });
    document.getElementById('probeTool').addEventListener('click', function() { activateTool('Probe'); });
    document.getElementById('ellipticalRoiTool').addEventListener('click', function() { activateTool('EllipticalRoi'); });
    document.getElementById('rectangleRoiTool').addEventListener('click', function() { activateTool('RectangleRoi'); });
    document.getElementById('freehandRoiTool').addEventListener('click', function() { activateTool('FreehandRoi'); });
    document.getElementById('arrowAnnotateTool').addEventListener('click', function() { activateTool('ArrowAnnotate'); });
    document.getElementById('bidirectionalTool').addEventListener('click', function() { activateTool('Bidirectional'); });
    
    // Playback control
    document.getElementById('playClip').addEventListener('click', togglePlayback);
    
    // CT Playback controls
    document.getElementById('ctPlayBtn')?.addEventListener('click', toggleCTPlayback);
    document.getElementById('ctSpeedSlider')?.addEventListener('input', updateCTSpeed);
    document.getElementById('ctDirectionBtn')?.addEventListener('click', toggleCTDirection);
    document.getElementById('ctLoopBtn')?.addEventListener('click', toggleCTLoop);
    
    // Annotation dropdown toggle
    document.getElementById('annotationTools').addEventListener('click', function() {
        document.getElementById('annotationDropdown').classList.toggle('show');
    });
    
    // Close dropdown when clicking outside
    window.addEventListener('click', function(event) {
        if (!event.target.matches('.dropdown-toggle')) {
            const dropdowns = document.getElementsByClassName('dropdown-content');
            for (let i = 0; i < dropdowns.length; i++) {
                const openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    });
    
    // Frame slider
    document.getElementById('frameSlider').addEventListener('input', function() {
        if (!loaded) return;
        
        // Check if this is a CT series or multi-frame image
        const isCTSeries = series.length > 0 && series[0].modality === 'CT' && series.length > 1;
        const isMultiFrame = totalFrames > 1 && !isCTSeries;
        
        if (isCTSeries || isMultiFrame) {
            const newFrame = parseInt(this.value);
            if (newFrame !== currentFrame) {
                loadFrame(newFrame);
            }
        }
    });
    
    // Screenshot button
    document.getElementById('captureBtn').addEventListener('click', captureScreenshot);

    document.getElementById('InterpretasiAiBtn').addEventListener('click', interpretasiAi);
    
    // Mouse wheel for frame navigation
    element.addEventListener('wheel', handleMouseWheel);
}

function initializeTools() {
    try {
        console.log('Initializing cornerstone tools...');
        
        // Initialize all cornerstone tools
        cornerstoneTools.addTool(cornerstoneTools.WwwcTool);
        cornerstoneTools.addTool(cornerstoneTools.ZoomTool);
        cornerstoneTools.addTool(cornerstoneTools.PanTool);
        cornerstoneTools.addTool(cornerstoneTools.MagnifyTool);
        cornerstoneTools.addTool(cornerstoneTools.LengthTool);
        cornerstoneTools.addTool(cornerstoneTools.AngleTool);
        cornerstoneTools.addTool(cornerstoneTools.ProbeTool);
        cornerstoneTools.addTool(cornerstoneTools.EllipticalRoiTool);
        cornerstoneTools.addTool(cornerstoneTools.RectangleRoiTool);
        cornerstoneTools.addTool(cornerstoneTools.FreehandRoiTool);
        cornerstoneTools.addTool(cornerstoneTools.ArrowAnnotateTool);
        
        // Special handling for BidirectionalTool
        if (cornerstoneTools.BidirectionalTool) {
            console.log('Adding BidirectionalTool...');
            cornerstoneTools.addTool(cornerstoneTools.BidirectionalTool);
            console.log('BidirectionalTool added successfully');
        } else {
            console.error('BidirectionalTool not found in cornerstoneTools!');
            console.log('Available tools:', Object.keys(cornerstoneTools));
        }
        
        cornerstoneTools.addTool(cornerstoneTools.EraserTool);
        
        console.log('All tools initialized successfully');
        
        // Add custom tools if needed
        // cornerstoneTools.addTool(MyCustomTool);
        
    } catch (error) {
        console.error('Error initializing tools:', error);
        updateStatus('Error initializing tools: ' + error.message);
    }
}

function initializeToolsForElement() {
    if (!element) {
        console.error('Element not available for tool initialization');
        return;
    }
    
    try {
        console.log('Initializing tools for element...');
        
        // Add all tools to the element
        const toolsToAdd = [
            { name: 'Wwwc', class: cornerstoneTools.WwwcTool },
            { name: 'Zoom', class: cornerstoneTools.ZoomTool },
            { name: 'Pan', class: cornerstoneTools.PanTool },
            { name: 'Magnify', class: cornerstoneTools.MagnifyTool },
            { name: 'Length', class: cornerstoneTools.LengthTool },
            { name: 'Angle', class: cornerstoneTools.AngleTool },
            { name: 'Probe', class: cornerstoneTools.ProbeTool },
            { name: 'EllipticalRoi', class: cornerstoneTools.EllipticalRoiTool },
            { name: 'RectangleRoi', class: cornerstoneTools.RectangleRoiTool },
            { name: 'FreehandRoi', class: cornerstoneTools.FreehandRoiTool },
            { name: 'ArrowAnnotate', class: cornerstoneTools.ArrowAnnotateTool },
            { name: 'Bidirectional', class: cornerstoneTools.BidirectionalTool },
            { name: 'Eraser', class: cornerstoneTools.EraserTool }
        ];
        
        toolsToAdd.forEach(tool => {
            try {
                if (tool.class) {
                    cornerstoneTools.addToolForElement(element, tool.class);
                    console.log(`${tool.name} tool added to element`);
                } else {
                    console.warn(`${tool.name} tool class not found`);
                }
            } catch (error) {
                // Tool might already be added, which is fine
                console.log(`${tool.name} tool already added or error:`, error.message);
            }
        });
        
        console.log('All tools initialized for element');
        
    } catch (error) {
        console.error('Error initializing tools for element:', error);
    }
}

async function handleFileUpload(event) {
    const files = event.target.files;
    if (!files || files.length === 0) {
        updateStatus('No files were selected');
        return;
    }
    
    updateStatus(`Loading ${files.length} file(s)...`);
    
    // Reset series and current state
    series = [];
    currentFrame = 0;
    
    try {
        // Process each file
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const imageId = cornerstoneWADOImageLoader.wadouri.fileManager.add(file);
            series.push({
                imageId: imageId,
                fileName: file.name
            });
        }
        
        // Load the first image
        if (series.length > 0) {
            await loadAndViewImage(series[0].imageId);
            updateSeriesList();
            updateStatus('DICOM file(s) loaded successfully');
        }
    } catch (error) {
        console.error('Error loading files:', error);
        updateStatus('Error loading DICOM files');
    }
}

function onImageRendered(event) {
    try {
        const target = event.target || element;
        const viewport = cornerstone.getViewport(target);
        const bottomLeft = document.getElementById('bottomleft');
        if (bottomLeft && viewport && viewport.voi) {
            bottomLeft.textContent = `WW/WC: ${Math.round(viewport.voi.windowWidth)} / ${Math.round(viewport.voi.windowCenter)}`;
        }
        // Keep UI in sync when image is rendered
        if (typeof updateFrameInfo === 'function') {
            updateFrameInfo();
        }
    } catch (err) {
        console.warn('onImageRendered error:', err.message || err);
    }
}

async function loadAndViewImage(imageId) {
    try {
        updateStatus('Loading image...');
        
        const image = await cornerstone.loadImage(imageId);
        const viewport = cornerstone.getDefaultViewportForImage(element, image);
        
        // Adjust default viewport settings if needed
        viewport.voi.windowWidth = image.windowWidth || 400;
        viewport.voi.windowCenter = image.windowCenter || 200;
        
        cornerstone.displayImage(element, image, viewport);
        loaded = true;
        
        // Extract metadata
        extractMetadata(image);
        
        // Check if multi-frame or CT series
        const isMultiFrame = parseInt(image.data.intString('x00280008')) || 1;
        
        // For CT series, totalFrames is already set in loadSeriesInstances
        // For other series, use the multi-frame count
        if (series.length > 0 && series[0].modality === 'CT') {
            // totalFrames already set in loadSeriesInstances for CT
            console.log(`CT series loaded with ${totalFrames} slices`);
        } else {
            totalFrames = isMultiFrame;
            if (totalFrames <= 0) totalFrames = 1;
        }
        
        // Update UI
        updateFrameInfo();
        
        // Check if this is a CT series
        const isCTSeries = series.length > 0 && series[0].modality === 'CT' && series.length > 1;
        
        if (isCTSeries) {
            // For CT series: hide sidebar but keep frame slider for navigation
            document.body.classList.add('ct-single-slice');
            populateFramesSidebar(); // This will hide the sidebar for CT
            setupFrameSlider(); // Keep slider for CT navigation
        } else {
            // Remove CT class for non-CT images
            document.body.classList.remove('ct-single-slice');
            if (totalFrames > 1) {
                // For multi-frame non-CT: show both sidebar and slider
                populateFramesSidebar();
                setupFrameSlider();
            } else {
                // Single frame: hide both
                document.getElementById('framesSidebar').style.display = 'none';
                document.getElementById('frameSlider').parentElement.style.display = 'none';
            }
        }
        
        // Initialize all tools for the element
        initializeToolsForElement();
        
        // Set default tool
        activateTool('Wwwc');
        
        updateStatus('Image loaded successfully');
        
        // Setup viewport event listeners
        element.addEventListener('cornerstoneimagerendered', onImageRendered);
        
        return image;
    } catch (error) {
        console.error('Error loading DICOM image:', error);
        updateStatus('Error loading DICOM image');
        throw error;
    }
}

function extractMetadata(image) {
    if (!image || !image.data) return;
    
    const metadata = {};
    
    // Extract common DICOM tags
    const commonTags = [
        { tag: 'x00100010', name: 'Patient Name' },
        { tag: 'x00100020', name: 'Patient ID' },
        { tag: 'x00100030', name: 'Patient Birth Date' },
        { tag: 'x00100040', name: 'Patient Sex' },
        { tag: 'x00080020', name: 'Study Date' },
        { tag: 'x00080030', name: 'Study Time' },
        { tag: 'x00080060', name: 'Modality' },
        { tag: 'x00080090', name: 'Referring Physician' },
        { tag: 'x00081030', name: 'Study Description' },
        { tag: 'x00181030', name: 'Protocol Name' }
    ];
    
    commonTags.forEach(item => {
        const value = image.data.string(item.tag);
        if (value) {
            metadata[item.name] = value;
        }
    });
    
    // Add image-specific metadata
    metadata['Image Width'] = image.width;
    metadata['Image Height'] = image.height;
    metadata['Bits Allocated'] = image.data.uint16('x00280100');
    metadata['Bits Stored'] = image.data.uint16('x00280101');
    metadata['High Bit'] = image.data.uint16('x00280102');
    metadata['Pixel Representation'] = image.data.uint16('x00280103');
    metadata['Window Center'] = image.windowCenter;
    metadata['Window Width'] = image.windowWidth;
    
    dicomMetadata = metadata;
    displayMetadata();
}

function displayMetadata() {
    const container = document.getElementById('dicomMetadata');
    container.innerHTML = '';
    
    for (const [key, value] of Object.entries(dicomMetadata)) {
        const row = document.createElement('div');
        row.className = 'metadata-row';
        
        const keyElement = document.createElement('span');
        keyElement.className = 'metadata-key';
        keyElement.textContent = key + ':';
        
        const valueElement = document.createElement('span');
        valueElement.className = 'metadata-value';
        valueElement.textContent = value;
        
        row.appendChild(keyElement);
        row.appendChild(valueElement);
        container.appendChild(row);
    }
}

function updateSeriesList() {
    const container = document.getElementById('stackWrapper');
    container.innerHTML = '';
    
    // Check if we have series to display
    if (series.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    // Show stackWrapper for all modalities
    container.style.display = 'block';
    
    // Get modality from first series item
    const modality = series[0].modality || 'UNKNOWN';
    const isCTSeries = modality === 'CT' && series.length > 1;
    
    // Group series by seriesId to show one thumbnail per series
    const seriesGroups = new Map();
    series.forEach((item, index) => {
        if (!seriesGroups.has(item.seriesId)) {
            seriesGroups.set(item.seriesId, {
                firstItem: item,
                index: index,
                count: 1
            });
        } else {
            seriesGroups.get(item.seriesId).count++;
        }
    });
    
    // Create one thumbnail per series for all modalities
    seriesGroups.forEach((seriesGroup, seriesId) => {
        const item = seriesGroup.firstItem;
        const thumbnail = document.createElement('div');
        
        // Use different CSS classes based on modality
        let thumbnailClass = 'series-thumbnail';
        let labelText = '';
        
        if (modality === 'CT') {
            thumbnailClass += ' ct-series-thumbnail';
            labelText = `Series ${seriesGroup.index + 1} (${seriesGroup.count} slices)`;
        } else {
            thumbnailClass += ' general-series-thumbnail';
            labelText = `Series ${seriesGroup.index + 1} (${seriesGroup.count} images)`;
        }
        
        thumbnail.className = thumbnailClass;
        thumbnail.dataset.seriesId = seriesId;
        thumbnail.innerHTML = `<div class="thumbnail-wrapper"></div>
                               <div class="thumbnail-label">${labelText}</div>`;
        
        const thumbnailElement = thumbnail.querySelector('.thumbnail-wrapper');
        cornerstone.enable(thumbnailElement);
        
        cornerstone.loadImage(item.imageId).then(image => {
            const viewport = cornerstone.getDefaultViewportForImage(thumbnailElement, image);
            cornerstone.displayImage(thumbnailElement, image, viewport);
            cornerstone.resize(thumbnailElement);
        }).catch(error => {
            console.error(`Error loading ${modality} series thumbnail:`, error);
            thumbnailElement.innerHTML = '<div class="error-thumbnail">Error</div>';
        });
        
        // Add click handler to switch to this series
        thumbnail.addEventListener('click', () => {
            switchToSeries(seriesId);
        });
        
        container.appendChild(thumbnail);
    });
}

async function populateFramesSidebar() {
    const sidebar = document.getElementById('framesSidebar');
    sidebar.innerHTML = '';
    
    // Check if this is a CT series or multi-frame image
    const isCTSeries = series.length > 0 && series[0].modality === 'CT' && series.length > 1;
    const isMultiFrame = totalFrames > 1 && !isCTSeries;
    
    // Hide sidebar for CT series - show only one slice at a time
    if (isCTSeries) {
        sidebar.style.display = 'none';
        return;
    }
    
    // Don't show sidebar for single frame non-CT images
    if (!isCTSeries && !isMultiFrame) {
        sidebar.style.display = 'none';
        return;
    }
    
    sidebar.style.display = 'block';
    
    if (isCTSeries) {
        // For CT series, create thumbnails for each instance/slice
        for (let i = 0; i < series.length; i++) {
            const frameDiv = document.createElement('div');
            frameDiv.className = 'frame-item';
            frameDiv.dataset.frameIndex = i;
            
            const thumbnailCanvas = document.createElement('canvas');
            
            try {
                cornerstone.enable(thumbnailCanvas);
                // Use the specific imageId for each CT slice
                const image = await cornerstone.loadImage(series[i].imageId);
                const viewport = cornerstone.getDefaultViewportForImage(thumbnailCanvas, image);
                cornerstone.displayImage(thumbnailCanvas, image, viewport);
                cornerstone.resize(thumbnailCanvas, true);
            } catch (error) {
                console.error(`Error loading CT slice ${i} thumbnail:`, error);
                // Create a placeholder for failed thumbnails
                const ctx = thumbnailCanvas.getContext('2d');
                thumbnailCanvas.width = 100;
                thumbnailCanvas.height = 80;
                ctx.fillStyle = '#2196F3';
                ctx.fillRect(0, 0, 100, 80);
                ctx.fillStyle = 'white';
                ctx.font = '10px Arial';
                ctx.fillText(`Slice ${i+1}`, 30, 45);
            }
            
            const frameLabel = document.createElement('span');
            frameLabel.textContent = `Slice ${i + 1}`;
            
            frameDiv.appendChild(thumbnailCanvas);
            frameDiv.appendChild(frameLabel);
            
            frameDiv.addEventListener('click', () => {
                currentFrame = i;
                loadFrame(currentFrame);
                updateFrameSlider();
            });
            
            sidebar.appendChild(frameDiv);
        }
    } else if (isMultiFrame) {
        // For multi-frame images, validate actual frame count from DICOM data
        let actualFrameCount = totalFrames;
        
        try {
            // Load the first image to check actual frame count
            const firstImage = await cornerstone.loadImage(series[0].imageId);
            if (firstImage.data && firstImage.data.intString) {
                const numberOfFrames = parseInt(firstImage.data.intString('x00280008')) || 1;
                actualFrameCount = Math.min(numberOfFrames, totalFrames);
            }
        } catch (error) {
            console.warn('Could not validate frame count from DICOM data:', error);
        }
        
        const baseImageId = series[0].imageId;
        
        for (let i = 0; i < actualFrameCount; i++) {
            const frameDiv = document.createElement('div');
            frameDiv.className = 'frame-item';
            frameDiv.dataset.frameIndex = i;
            
            const thumbnailCanvas = document.createElement('canvas');
            
            try {
                cornerstone.enable(thumbnailCanvas);
                const image = await cornerstone.loadImage(`${baseImageId}?frame=${i}`);
                const viewport = cornerstone.getDefaultViewportForImage(thumbnailCanvas, image);
                cornerstone.displayImage(thumbnailCanvas, image, viewport);
                cornerstone.resize(thumbnailCanvas, true);
            } catch (error) {
                console.error(`Error loading frame ${i} thumbnail:`, error);
                // Create a placeholder for failed thumbnails
                const ctx = thumbnailCanvas.getContext('2d');
                thumbnailCanvas.width = 100;
                thumbnailCanvas.height = 80;
                ctx.fillStyle = '#f44336';
                ctx.fillRect(0, 0, 100, 80);
                ctx.fillStyle = 'white';
                ctx.font = '12px Arial';
                ctx.fillText(`Frame ${i+1}`, 25, 45);
            }
            
            const frameLabel = document.createElement('span');
            frameLabel.textContent = `Frame ${i + 1}`;
            
            frameDiv.appendChild(thumbnailCanvas);
            frameDiv.appendChild(frameLabel);
            
            frameDiv.addEventListener('click', () => {
                currentFrame = i;
                loadFrame(currentFrame);
                updateFrameSlider();
            });
            
            sidebar.appendChild(frameDiv);
        }
    }
}

function setupFrameSlider() {
    const slider = document.getElementById('frameSlider');
    
    // Check if this is a CT series or multi-frame image
    const isCTSeries = series.length > 0 && series[0].modality === 'CT' && series.length > 1;
    const isMultiFrame = totalFrames > 1 && !isCTSeries;
    
    if (isCTSeries) {
        // For CT series, use series length as frame count
        slider.min = 0;
        slider.max = series.length - 1;
        slider.value = currentFrame;
        slider.parentElement.style.display = 'flex';
        document.getElementById('frameCount').textContent = `Total Slices: ${series.length}`;
    } else if (isMultiFrame) {
        // For multi-frame images, validate actual frame count
        let actualFrameCount = totalFrames;
        
        // Try to get actual frame count from DICOM data if available
        if (series[0] && series[0].numberOfFrames) {
            actualFrameCount = Math.min(series[0].numberOfFrames, totalFrames);
        }
        
        slider.min = 0;
        slider.max = actualFrameCount - 1;
        slider.value = currentFrame;
        slider.parentElement.style.display = 'flex';
        document.getElementById('frameCount').textContent = `Total Frames: ${actualFrameCount}`;
    } else {
        // Single frame image
        slider.parentElement.style.display = 'none';
        document.getElementById('frameCount').textContent = 'Single Frame';
    }
}

function updateFrameSlider() {
    const slider = document.getElementById('frameSlider');
    slider.value = currentFrame;
}

async function loadFrame(frameIndex) {
    if (!loaded) return;
    
    // Check if this is a CT series or multi-frame image
    const isCTSeries = series.length > 0 && series[0].modality === 'CT' && series.length > 1;
    const isMultiFrame = totalFrames > 1 && !isCTSeries;
    
    // Validate frame index based on image type
    if (isCTSeries) {
        if (frameIndex < 0 || frameIndex >= series.length) {
            console.warn(`CT slice index ${frameIndex} out of range (0-${series.length-1})`);
            return;
        }
    } else if (isMultiFrame) {
        // For multi-frame images, validate against actual frame count
        let actualFrameCount = totalFrames;
        if (series[0] && series[0].numberOfFrames) {
            actualFrameCount = Math.min(series[0].numberOfFrames, totalFrames);
        }
        
        if (frameIndex < 0 || frameIndex >= actualFrameCount) {
            console.warn(`Frame index ${frameIndex} out of range (0-${actualFrameCount-1})`);
            return;
        }
    } else {
        // Single frame image
        if (frameIndex !== 0) {
            console.warn(`Single frame image, ignoring frame index ${frameIndex}`);
            return;
        }
    }
    
    try {
        let image;
        
        if (isCTSeries) {
            // For CT series, each frame is a separate instance
            image = await cornerstone.loadImage(series[frameIndex].imageId);
        } else if (isMultiFrame) {
            // For multi-frame images, use frame parameter
            image = await cornerstone.loadImage(`${series[0].imageId}?frame=${frameIndex}`);
        } else {
            // Single frame image
            image = await cornerstone.loadImage(series[0].imageId);
        }
        
        const viewport = cornerstone.getViewport(element);
        cornerstone.displayImage(element, image, viewport);
        
        currentFrame = frameIndex;
        updateFrameInfo();
        highlightCurrentFrameThumbnail();
    } catch (error) {
        console.error('Error loading frame:', error);
        updateStatus(`Error loading frame ${frameIndex + 1}: ${error.message}`);
    }
}

function highlightCurrentFrameThumbnail() {
    // Remove highlight from all thumbnails
    document.querySelectorAll('.frame-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Add highlight to current thumbnail
    const currentThumbnail = document.querySelector(`.frame-item[data-frame-index="${currentFrame}"]`);
    if (currentThumbnail) {
        currentThumbnail.classList.add('active');
    }
}

function updateFrameInfo() {
    // Check if this is a CT series or multi-frame image
    const isCTSeries = series.length > 0 && series[0].modality === 'CT' && series.length > 1;
    const isMultiFrame = totalFrames > 1 && !isCTSeries;
    
    if (isCTSeries) {
        const currentSlice = series[currentFrame];
        let sliceInfo = `CT Slice ${currentFrame + 1}/${series.length}`;
        
        // Add slice location if available
        if (currentSlice && currentSlice.sliceLocation !== undefined && currentSlice.sliceLocation !== 0) {
            sliceInfo += ` (${currentSlice.sliceLocation.toFixed(1)}mm)`;
        }
        
        document.getElementById('bottomleft').textContent = sliceInfo;
        document.getElementById('frameCounter').textContent = `Slice: ${currentFrame + 1}/${series.length}`;
    } else if (isMultiFrame) {
        // For multi-frame images, validate actual frame count
        let actualFrameCount = totalFrames;
        if (series[0] && series[0].numberOfFrames) {
            actualFrameCount = Math.min(series[0].numberOfFrames, totalFrames);
        }
        
        document.getElementById('bottomleft').textContent = `Frame ${currentFrame + 1}/${actualFrameCount}`;
        document.getElementById('frameCounter').textContent = `Frame: ${currentFrame + 1}/${actualFrameCount}`;
    } else {
        // Single frame image
        document.getElementById('bottomleft').textContent = 'Single Frame';
        document.getElementById('frameCounter').textContent = 'Frame: 1/1';
    }
}

function activateTool(toolName) {
    try {
        console.log('Activating tool:', toolName);
        
        // Deactivate all tools
        deactivateAllTools();
        
        // Update UI to show active tool
        document.querySelectorAll('.toolButton').forEach(button => {
            button.classList.remove('active');
        });
        
        // Find the button for this tool and mark it active
        const toolButtons = {
            'Wwwc': 'wwwcTool',
            'Zoom': 'zoomTool',
            'Pan': 'panTool',
            'Magnify': 'magnifyTool',
            'Length': 'lengthTool',
            'Angle': 'angleTool',
            'Probe': 'probeTool',
            'EllipticalRoi': 'ellipticalRoiTool',
            'RectangleRoi': 'rectangleRoiTool',
            'FreehandRoi': 'freehandRoiTool',
            'ArrowAnnotate': 'arrowAnnotateTool',
            'Bidirectional': 'bidirectionalTool',
            'Eraser': 'eraserTool'
        };
        
        const buttonId = toolButtons[toolName];
        if (buttonId) {
            const button = document.getElementById(buttonId);
            if (button) {
                button.classList.add('active');
            } else {
                console.warn('Button not found for tool:', toolName, buttonId);
            }
        }
        
        // Activate the chosen tool
        if (loaded) {
            activeTool = toolName;
            
            // Activate the tool (tools are already added to element in initializeToolsForElement)
            try {
                cornerstoneTools.setToolActiveForElement(element, toolName, { mouseButtonMask: 1 });
                
                // Verify activation
                const isActive = cornerstoneTools.isToolActiveForElement(element, toolName);
                
                if (isActive) {
                    if (toolName === 'Bidirectional') {
                        updateStatus(`${toolName} tool activated successfully - Ready to draw bidirectional measurements`);
                        console.log('BidirectionalTool activation status:', isActive);
                    } else {
                        updateStatus(`${toolName} tool activated`);
                    }
                } else {
                    updateStatus(`${toolName} tool activation failed`);
                    console.error(`Failed to activate ${toolName} tool`);
                }
            } catch (error) {
                console.error(`Error activating ${toolName} tool:`, error);
                updateStatus(`Error activating ${toolName} tool: ${error.message}`);
            }
        } else {
            updateStatus('Please load an image first');
        }
        
    } catch (error) {
        console.error('Error activating tool:', toolName, error);
        updateStatus(`Error activating ${toolName} tool: ${error.message}`);
    }
}

function deactivateAllTools() {
    const tools = ['Wwwc', 'Zoom', 'Pan', 'Magnify', 'Length', 'Angle', 'Probe', 
                   'EllipticalRoi', 'RectangleRoi', 'FreehandRoi', 'ArrowAnnotate', 
                   'Bidirectional', 'Eraser'];
    
    if (element) {
        tools.forEach(tool => {
            try {
                cornerstoneTools.setToolDisabledForElement(element, tool);
            } catch (error) {
                // Tool might not be added to element, ignore error
                console.log(`Tool ${tool} not found on element:`, error.message);
            }
        });
    }
}

function handleInvert() {
    if (!loaded) return;
    
    const viewport = cornerstone.getViewport(element);
    viewport.invert = !viewport.invert;
    cornerstone.setViewport(element, viewport);
    updateStatus('Image colors inverted');
}

function handleReset() {
    if (!loaded) return;
    
    cornerstone.reset(element);
    updateStatus('Image reset to default');
}

function handleMouseWheel(event) {
    if (!loaded) return;
    
    // Check if this is a CT series or multi-frame image
    const isCTSeries = series.length > 0 && series[0].modality === 'CT' && series.length > 1;
    const isMultiFrame = totalFrames > 1 && !isCTSeries;
    
    if (!isCTSeries && !isMultiFrame) return;
    
    event.preventDefault();
    
    // Calculate new frame based on wheel direction
    const delta = Math.max(-1, Math.min(1, (event.wheelDelta || -event.detail)));
    let newFrame = currentFrame - delta;
    
    // Ensure we stay within bounds based on image type
    let maxFrame;
    if (isCTSeries) {
        maxFrame = series.length - 1;
    } else if (isMultiFrame) {
        // For multi-frame images, validate actual frame count
        let actualFrameCount = totalFrames;
        if (series[0] && series[0].numberOfFrames) {
            actualFrameCount = Math.min(series[0].numberOfFrames, totalFrames);
        }
        maxFrame = actualFrameCount - 1;
    } else {
        return; // Single frame image
    }
    
    newFrame = Math.max(0, Math.min(maxFrame, newFrame));
    
    if (newFrame !== currentFrame) {
        loadFrame(newFrame);
        updateFrameSlider();
    }
}

function togglePlayback() {
    if (!loaded) return;
    
    // Check if this is a CT series or multi-frame image
    const isCTSeries = series.length > 0 && series[0].modality === 'CT' && series.length > 1;
    const isMultiFrame = totalFrames > 1 && !isCTSeries;
    
    if (!isCTSeries && !isMultiFrame) {
        const message = series.length > 0 && series[0].modality === 'CT' ? 
            'Playback not available - single CT slice' : 
            'Playback not available - single frame image';
        updateStatus(message);
        return;
    }
    
    isPlaying = !isPlaying;
    const playButton = document.getElementById('playClip');
    
    if (isPlaying) {
        playButton.innerHTML = '<i class="fas fa-pause"></i>';
        startPlayback();
        const message = isCTSeries ? 
            'CT slice playback started' : 'Cine playback started';
        updateStatus(message);
    } else {
        playButton.innerHTML = '<i class="fas fa-play"></i>';
        stopPlayback();
        const message = isCTSeries ? 
            'CT slice playback stopped' : 'Cine playback stopped';
        updateStatus(message);
    }
}

function startPlayback() {
    if (playbackInterval) {
        clearInterval(playbackInterval);
    }
    
    // Determine the correct frame count based on image type
    const isCTSeries = series.length > 0 && series[0].modality === 'CT' && series.length > 1;
    let maxFrames;
    
    if (isCTSeries) {
        maxFrames = series.length;
    } else {
        // For multi-frame images, validate actual frame count
        maxFrames = totalFrames;
        if (series[0] && series[0].numberOfFrames) {
            maxFrames = Math.min(series[0].numberOfFrames, totalFrames);
        }
    }
    
    playbackInterval = setInterval(() => {
        const nextFrame = (currentFrame + 1) % maxFrames;
        loadFrame(nextFrame);
        updateFrameSlider();
    }, playbackSpeed);
}

function stopPlayback() {
    if (playbackInterval) {
        clearInterval(playbackInterval);
        playbackInterval = null;
    }
}

function captureScreenshot() {
    if (!loaded) {
        updateStatus('No image loaded to capture');
        return;
    }
    
    try {
        const canvas = element.querySelector('canvas');
        if (!canvas) {
            throw new Error('Canvas not found');
        }
        
        // Create a downloadable image
        const dataURL = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.href = dataURL;
        link.download = `dicom-screenshot-${new Date().toISOString().slice(0, 19)}.png`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        updateStatus('Screenshot captured');
    } catch (error) {
        console.error('Error capturing screenshot:', error);
        updateStatus('Error capturing screenshot');
    }
}

// Interpretasi AI button click event handler
function interpretasiAi() {
    if (!loaded) {
        updateStatus('No image loaded to interpret');
        alert('Tidak ada gambar yang dimuat.');
        return;
    }

    // Ensure modal exists
    let modal = document.getElementById('aiInterpretasiModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'aiInterpretasiModal';
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-hidden', 'true');
        modal.innerHTML = `
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">AI Interpretasi Radiologi</h4>
              </div>
              <div class="modal-body">
                <div id="aiInterpretasiLoading" class="text-center" style="padding: 20px;">
                  <i class="fa fa-spinner fa-spin fa-3x" aria-hidden="true"></i>
                  <div style="margin-top:10px;">Memproses interpretasi...</div>
                </div>
                <div id="aiInterpretasiResult" style="white-space: pre-wrap; display:none;"></div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
              </div>
            </div>
          </div>`;
        document.body.appendChild(modal);
        console.log("Modal exists now?", document.getElementById('aiInterpretasiModal'));
    }

    // Show modal using Bootstrap's modal method if available; otherwise fallback
    if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
        $(modal).modal('show');
    } else {
        modal.style.display = 'block';
        // Bootstrap 3 uses 'in' for visible state; keep 'show' for our CSS
        modal.classList.add('in');
        modal.classList.add('show');
        document.body.classList.add('modal-open');
        // Add backdrop (Bootstrap 3 compatible)
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade in';
        backdrop.id = 'aiInterpretasiBackdrop';
        document.body.appendChild(backdrop);
    }

    // Add event listener for modal close button
    const closeBtn = modal.querySelector('[data-dismiss="modal"]');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
                $(modal).modal('hide');
            } else {
                modal.style.display = 'none';
                modal.classList.remove('show');
                modal.classList.remove('in');
                document.body.classList.remove('modal-open');
                const backdrop = document.getElementById('aiInterpretasiBackdrop');
                if (backdrop) backdrop.remove();
            }
        });
    }

    const btn = document.getElementById('InterpretasiAiBtn');
    if (btn) {
        btn.disabled = true;
        btn.classList.add('disabled');
    }

    const loadingEl = document.getElementById('aiInterpretasiLoading');
    const resultEl = document.getElementById('aiInterpretasiResult');
    if (loadingEl) loadingEl.style.display = 'block';
    if (resultEl) { resultEl.style.display = 'none'; resultEl.textContent = ''; }

    try {
        const canvas = element.querySelector('canvas');
        if (!canvas) throw new Error('Canvas not found');
        const dataURL = canvas.toDataURL('image/png');

        const baseURL = mlite.url + '/' + mlite.admin;
        const url = baseURL + '/orthanc/aiinterpretasi?t=' + mlite.token;

        const body = new URLSearchParams();
        body.append('token', mlite.token);
        body.append('imageData', dataURL);

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
        .then(async (resp) => {
            let payload;
            try {
                payload = await resp.json();
            } catch (e) {
                payload = { success: false, message: 'Gagal membaca respons' };
            }

            if (payload && payload.success) {
                const text = payload.interpretation || 'Tidak ada interpretasi yang dihasilkan';
                if (loadingEl) loadingEl.style.display = 'none';
                if (resultEl) { resultEl.style.display = 'block'; resultEl.textContent = text; }
                updateStatus('Interpretasi AI berhasil');
            } else {
                const msg = (payload && payload.message) ? payload.message : ('HTTP Error ' + resp.status);
                if (loadingEl) loadingEl.style.display = 'none';
                if (resultEl) { resultEl.style.display = 'block'; resultEl.textContent = 'Terjadi kesalahan: ' + msg; }
                updateStatus('Interpretasi AI gagal: ' + msg);
            }
        })
        .catch((error) => {
            console.error('Error interpretasi AI:', error);
            if (loadingEl) loadingEl.style.display = 'none';
            if (resultEl) { resultEl.style.display = 'block'; resultEl.textContent = 'Kesalahan: ' + error.message; }
            updateStatus('Kesalahan interpretasi AI');
        })
        .finally(() => {
            const btn = document.getElementById('InterpretasiAiBtn');
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('disabled');
            }
        });
    } catch (error) {
        console.error('Error interpretasi AI:', error);
        if (loadingEl) loadingEl.style.display = 'none';
        if (resultEl) { resultEl.style.display = 'block'; resultEl.textContent = 'Kesalahan: ' + error.message; }
        updateStatus('Kesalahan interpretasi AI');
        const btn2 = document.getElementById('InterpretasiAiBtn');
        if (btn2) {
            btn2.disabled = false;
            btn2.classList.remove('disabled');
        }
    }
}

function updateStatus(message) {
    const statusElement = document.getElementById('statusMessage');
    statusElement.textContent = message;
    
    // Clear status after 5 seconds
    setTimeout(() => {
        if (statusElement.textContent === message) {
            statusElement.textContent = '';
        }
    }, 5000);
}

// Handle window resizing
window.addEventListener('resize', function() {
    if (loaded) {
        cornerstone.resize(element, true);
    }
});

// URL Parameter Functions
function getUrlParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

// Orthanc API Functions
async function loadStudyFromOrthanc(studyId) {
    try {
        updateStatus('Loading study from Orthanc...');
        currentStudyId = studyId;
        
        // Get study information
         const studyResponse = await fetch(`/admin/orthanc/api?path=studies/${studyId}?&t=${mlite.token}`);
         if (!studyResponse.ok) {
             throw new Error(`Failed to fetch study: ${studyResponse.status}`);
         }
        
        studyData = await studyResponse.json();
        
        // Display study metadata
        displayStudyMetadata(studyData);
        
        // Load series from study
        await loadSeriesFromStudy(studyId);
        
        updateStatus('Study loaded successfully');
    } catch (error) {
        console.error('Error loading study from Orthanc:', error);
        updateStatus('Error loading study from Orthanc: ' + error.message);
    }
}

async function loadSeriesFromStudy(studyId) {
    try {
        updateStatus('Loading series from study...');
        
        // Get series list
         const seriesResponse = await fetch(`/admin/orthanc/api?path=studies/${studyId}/series&t=${mlite.token}`);
         console.log('seriesResponse', seriesResponse);
         if (!seriesResponse.ok) {
             throw new Error(`Failed to fetch series: ${seriesResponse.status}`);
         }
        
        const seriesList = await seriesResponse.json();
        console.log('seriesList structure:', seriesList);
        
        // Reset series array
        series = [];
        
        // Load each series - handle both string IDs and objects
        for (const seriesItem of seriesList) {
            // Check if seriesItem is a string ID or an object with ID property
            const seriesId = typeof seriesItem === 'string' ? seriesItem : seriesItem.ID || seriesItem.id;
            console.log('Processing series ID:', seriesId);
            if (seriesId) {
                await loadSeriesInstances(seriesId);
            }
        }
        
        // Load first image if available
        if (series.length > 0) {
            await loadAndViewImage(series[0].imageId);
            updateSeriesList();
            
            // Check if this is a CT scan and initialize CT playback
            if (detectCTScan()) {
                initializeCTPlayback();
            }
        }
        
        updateStatus(`Loaded ${series.length} series from study`);
    } catch (error) {
        console.error('Error loading series:', error);
        updateStatus('Error loading series: ' + error.message);
    }
}

async function loadSeriesInstances(seriesId) {
    try {
        // Get instances in series
         const instancesResponse = await fetch(`/admin/orthanc/api?path=series/${seriesId}/instances&t=${mlite.token}`);
         if (!instancesResponse.ok) {
             throw new Error(`Failed to fetch instances: ${instancesResponse.status}`);
         }
        
        const instances = await instancesResponse.json();
        
        // Validate instances response
        console.log('Instances response:', instances);
        console.log('Instances type:', typeof instances);
        console.log('Is instances array:', Array.isArray(instances));
        
        if (!instances || !Array.isArray(instances)) {
            console.error('Invalid instances response:', instances);
            updateStatus('Error: Invalid instances response from server');
            return; // Don't throw error, just return gracefully
        }
        
        if (instances.length === 0) {
            console.warn('No instances found in series:', seriesId);
            updateStatus('No instances found in series');
            return;
        }
        
        // Get series information to check modality
        const seriesResponse = await fetch(`/admin/orthanc/api?path=series/${seriesId}&t=${mlite.token}`);
        const seriesInfo = seriesResponse.ok ? await seriesResponse.json() : {};
        const modality = seriesInfo.MainDicomTags?.Modality || '';
        
        console.log(`Processing ${instances.length} instances for series ${seriesId}, modality: ${modality}`);
        
        // If CT scan, treat each instance as a frame for playback
        if (modality === 'CT') {
            console.log(`Processing CT scan with ${instances.length} instances`);
            
            // Get detailed instance information for sorting
            const detailedInstances = [];
            for (const instance of instances) {
                if (!instance || !instance.ID) {
                    console.warn('Invalid CT instance found:', instance);
                    continue; // Skip invalid instances
                }
                try {
                    const instanceResponse = await fetch(`/admin/orthanc/api?path=instances/${instance.ID}&t=${mlite.token}`);
                    if (instanceResponse.ok) {
                        const instanceInfo = await instanceResponse.json();
                        detailedInstances.push({
                            ...instance,
                            instanceNumber: parseInt(instanceInfo.MainDicomTags?.InstanceNumber) || 0,
                            sliceLocation: parseFloat(instanceInfo.MainDicomTags?.SliceLocation) || 0,
                            imagePosition: instanceInfo.MainDicomTags?.ImagePositionPatient || ''
                        });
                    } else {
                        detailedInstances.push({
                            ...instance,
                            instanceNumber: instance.IndexInSeries || 0,
                            sliceLocation: 0,
                            imagePosition: ''
                        });
                    }
                } catch (err) {
                    console.warn('Error getting instance details:', err);
                    detailedInstances.push({
                        ...instance,
                        instanceNumber: instance.IndexInSeries || 0,
                        sliceLocation: 0,
                        imagePosition: ''
                    });
                }
            }
            
            // Sort CT instances properly
            console.log('Calling sortCTSlices with detailedInstances:', detailedInstances);
            const sortedInstances = sortCTSlices(detailedInstances);
            console.log('sortCTSlices returned:', sortedInstances);
            
            // Validate sorted instances before processing
            if (!sortedInstances || !Array.isArray(sortedInstances)) {
                console.error('sortCTSlices returned invalid data:', sortedInstances);
                updateStatus('Error sorting CT instances for playback');
                return;
            }
            
            console.log(`Sorted ${sortedInstances.length} CT instances for playback`);
            
            if (sortedInstances.length === 0) {
                console.warn('No valid CT instances to process after sorting');
                updateStatus('No valid CT instances found for playback');
                return;
            }
            
            // Add sorted instances as frames
            sortedInstances.forEach((instance, index) => {
                if (!instance || !instance.ID) {
                    console.warn('Invalid sorted CT instance:', instance);
                    return; // Skip invalid instances
                }
                const imageId = `wadouri:/admin/orthanc/api?path=instances/${instance.ID}/file&t=${mlite.token}`;
                series.push({
                    imageId: imageId,
                    instanceId: instance.ID,
                    seriesId: seriesId,
                    frameIndex: index,
                    instanceNumber: instance.instanceNumber,
                    sliceLocation: instance.sliceLocation,
                    fileName: `CT Slice ${index + 1}`,
                    modality: 'CT'
                });
            });
            
            // Set total frames for CT playback
            totalFrames = sortedInstances.length;
            console.log(`Loaded ${totalFrames} CT slices for playback`);
            
        } else {
            // Regular handling for non-CT series
            console.log(`Processing ${instances.length} regular instances`);
            
            // Additional validation for non-CT instances
            if (!Array.isArray(instances)) {
                console.error('Instances is not an array for non-CT series:', instances);
                updateStatus('Error: Invalid instances format for non-CT series');
                return;
            }
            
            for (const instance of instances) {
                if (!instance || !instance.ID) {
                    console.warn('Invalid instance found:', instance);
                    continue; // Skip invalid instances
                }
                
                const imageId = `wadouri:/admin/orthanc/api?path=instances/${instance.ID}/file&t=${mlite.token}`;
                series.push({
                    imageId: imageId,
                    instanceId: instance.ID,
                    seriesId: seriesId,
                    fileName: `Instance ${instance.IndexInSeries || series.length + 1}`
                });
            }
            
            console.log(`Added ${instances.length} regular instances to series`);
        }
    } catch (error) {
        console.error('Error loading series instances:', error);
        console.error('Error details:', {
            seriesId: seriesId,
            errorMessage: error.message,
            errorStack: error.stack
        });
        
        // Don't throw error, just update status and continue
        updateStatus(`Error loading instances for series ${seriesId}: ${error.message}`);
        
        // Try to continue with other series instead of failing completely
        return;
    }
}

function displayStudyMetadata(study) {
    const container = document.getElementById('dicomMetadata');
    container.innerHTML = '';
    
    // Create study information display
    const studyInfo = {
        'Study ID': study.MainDicomTags?.StudyID || 'N/A',
        'Study Date': formatDicomDate(study.MainDicomTags?.StudyDate) || 'N/A',
        'Study Time': formatDicomTime(study.MainDicomTags?.StudyTime) || 'N/A',
        'Study Description': study.MainDicomTags?.StudyDescription || 'N/A',
        'Patient Name': study.PatientMainDicomTags?.PatientName || 'N/A',
        'Patient ID': study.PatientMainDicomTags?.PatientID || 'N/A',
        'Patient Sex': study.PatientMainDicomTags?.PatientSex || 'N/A',
        'Patient Birth Date': formatDicomDate(study.PatientMainDicomTags?.PatientBirthDate) || 'N/A',
        'Modalities': study.MainDicomTags?.ModalitiesInStudy || 'N/A',
        'Series Count': study.Series?.length || 0,
        'Instances Count': study.Instances?.length || 0
    };
    
    for (const [key, value] of Object.entries(studyInfo)) {
        const row = document.createElement('div');
        row.className = 'metadata-row';
        
        const keyElement = document.createElement('span');
        keyElement.className = 'metadata-key';
        keyElement.textContent = key + ':';
        
        const valueElement = document.createElement('span');
        valueElement.className = 'metadata-value';
        valueElement.textContent = value;
        
        row.appendChild(keyElement);
        row.appendChild(valueElement);
        container.appendChild(row);
    }
}

function formatDicomDate(dateStr) {
    if (!dateStr || dateStr.length !== 8) return dateStr;
    
    const year = dateStr.substring(0, 4);
    const month = dateStr.substring(4, 6);
    const day = dateStr.substring(6, 8);
    
    return `${day}/${month}/${year}`;
}

function formatDicomTime(timeStr) {
    if (!timeStr) return timeStr;
    
    // Handle different time formats
    if (timeStr.length >= 6) {
        const hours = timeStr.substring(0, 2);
        const minutes = timeStr.substring(2, 4);
        const seconds = timeStr.substring(4, 6);
        return `${hours}:${minutes}:${seconds}`;
    }
    
    return timeStr;
}

// Sort CT slices for proper playback sequence
function sortCTSlices(instances) {
    // Validate input
    if (!instances || !Array.isArray(instances)) {
        console.error('Invalid instances array passed to sortCTSlices:', instances);
        return [];
    }
    
    if (instances.length === 0) {
        console.warn('Empty instances array passed to sortCTSlices');
        return [];
    }
    
    console.log(`Sorting ${instances.length} CT instances`);
    
    try {
        return instances.sort((a, b) => {
        // Validate individual instances
        if (!a || !b) {
            console.warn('Invalid instance in sort comparison:', { a, b });
            return 0;
        }
        // First try to sort by Instance Number
        if (a.instanceNumber !== b.instanceNumber) {
            return a.instanceNumber - b.instanceNumber;
        }
        
        // If Instance Numbers are same or not available, sort by Slice Location
        if (a.sliceLocation !== b.sliceLocation) {
            return a.sliceLocation - b.sliceLocation;
        }
        
        // If both are same, try to sort by Image Position Patient Z-coordinate
        if (a.imagePosition && b.imagePosition) {
            try {
                const posA = a.imagePosition.split('\\').map(parseFloat);
                const posB = b.imagePosition.split('\\').map(parseFloat);
                if (posA.length >= 3 && posB.length >= 3) {
                    return posA[2] - posB[2]; // Z-coordinate
                }
            } catch (e) {
                console.warn('Error parsing image position:', e);
            }
        }
        
        // Fallback to IndexInSeries
            return (a.IndexInSeries || 0) - (b.IndexInSeries || 0);
        });
    } catch (error) {
        console.error('Error in sortCTSlices:', error);
        // Return original array if sorting fails
        return instances.slice(); // Return a copy of the original array
    }
}

// CT Scan Detection and Playback Functions
function detectCTScan() {
    // Check if any series has CT modality
    if (studyData && studyData.MainDicomTags && studyData.MainDicomTags.ModalitiesInStudy) {
        const modalities = studyData.MainDicomTags.ModalitiesInStudy;
        isCTScan = modalities.includes('CT');
        return isCTScan;
    }
    
    // Fallback: check individual series metadata
    for (const seriesItem of series) {
        if (seriesItem.modality === 'CT') {
            isCTScan = true;
            return true;
        }
    }
    
    return false;
}

function initializeCTPlayback() {
     if (!isCTScan) return;
     
     // Sort CT slices by slice location or instance number
     initializeCTSlicesArray();
     
     // Show CT-specific controls
     const ctControls = document.getElementById('ctPlaybackControls');
     if (ctControls) {
         ctControls.style.display = 'flex';
     }
     
     // Show CT progress bar
     const ctProgressContainer = document.getElementById('ctProgressContainer');
     if (ctProgressContainer) {
         ctProgressContainer.style.display = 'block';
     }
     
     // Show keyboard help
     const ctKeyboardHelp = document.getElementById('ctKeyboardHelp');
     if (ctKeyboardHelp) {
         ctKeyboardHelp.style.display = 'inline';
     }
     
     // Update UI for CT viewing
     updateCTPlaybackUI();
     
     updateStatus('CT scan detected - Enhanced playback controls available');
 }

function initializeCTSlicesArray() {
    if (!series.length) return;
    
    // Create array of slices with metadata for sorting
    ctSlices = series.map((item, index) => ({
        ...item,
        originalIndex: index,
        sliceLocation: 0, // Will be populated when metadata is available
        instanceNumber: index + 1
    }));
    
    // Sort by instance number as fallback
    ctSlices.sort((a, b) => a.instanceNumber - b.instanceNumber);
    
    currentCTSlice = 0;
}

function toggleCTPlayback() {
    if (!isCTScan) return;
    
    isCTPlaying = !isCTPlaying;
    
    if (isCTPlaying) {
        startCTPlayback();
    } else {
        stopCTPlayback();
    }
    
    updateCTPlaybackUI();
}

function startCTPlayback() {
     if (ctPlaybackInterval) {
         clearInterval(ctPlaybackInterval);
     }
     
     // Add visual indicator for CT playback
     document.body.classList.add('ct-playing');
     
     ctPlaybackInterval = setInterval(() => {
         let nextSlice = currentCTSlice + ctPlaybackDirection;
         
         // Handle loop modes
         if (nextSlice >= ctSlices.length) {
             if (ctLoopMode === 'continuous') {
                 nextSlice = 0;
             } else {
                 stopCTPlayback();
                 return;
             }
         } else if (nextSlice < 0) {
             if (ctLoopMode === 'continuous') {
                 nextSlice = ctSlices.length - 1;
             } else {
                 stopCTPlayback();
                 return;
             }
         }
         
         currentCTSlice = nextSlice;
         loadCTSlice(currentCTSlice);
         updateCTPlaybackUI();
     }, ctPlaybackSpeed);
     
     updateStatus(`CT playback started (${ctPlaybackDirection > 0 ? 'forward' : 'backward'})`);
 }

function stopCTPlayback() {
     if (ctPlaybackInterval) {
         clearInterval(ctPlaybackInterval);
         ctPlaybackInterval = null;
     }
     
     // Remove visual indicator for CT playback
     document.body.classList.remove('ct-playing');
     
     isCTPlaying = false;
     updateStatus('CT playback stopped');
 }

async function loadCTSlice(sliceIndex) {
     if (!ctSlices[sliceIndex]) return;
     
     try {
         const slice = ctSlices[sliceIndex];
         await loadAndViewImage(slice.imageId);
         
         // Update slice information
         const ctSliceInfo = document.getElementById('ctSliceInfo');
         if (ctSliceInfo) {
             ctSliceInfo.textContent = `CT Slice: ${sliceIndex + 1}/${ctSlices.length}`;
         }
             
         // Update progress bar
         const progressBar = document.getElementById('ctProgressBar');
         if (progressBar) {
             const progress = ((sliceIndex + 1) / ctSlices.length) * 100;
             progressBar.style.width = `${progress}%`;
         }
         
         // Update current slice index
         currentCTSlice = sliceIndex;
         
     } catch (error) {
         console.error('Error loading CT slice:', error);
         updateStatus(`Error loading CT slice ${sliceIndex + 1}`);
     }
 }

function updateCTSpeed(event) {
    const speed = parseInt(event.target.value);
    ctPlaybackSpeed = speed;
    
    // Restart playback with new speed if currently playing
    if (isCTPlaying) {
        stopCTPlayback();
        startCTPlayback();
    }
    
    updateStatus(`CT playback speed: ${speed}ms`);
}

function toggleCTDirection() {
    ctPlaybackDirection *= -1;
    
    const directionBtn = document.getElementById('ctDirectionBtn');
    if (directionBtn) {
        directionBtn.innerHTML = ctPlaybackDirection > 0 ? 
            '<i class="fas fa-arrow-right"></i>' : '<i class="fas fa-arrow-left"></i>';
        directionBtn.title = ctPlaybackDirection > 0 ? 'Forward' : 'Backward';
    }
    
    updateStatus(`CT direction: ${ctPlaybackDirection > 0 ? 'forward' : 'backward'}`);
}

function toggleCTLoop() {
    ctLoopMode = ctLoopMode === 'continuous' ? 'once' : 'continuous';
    
    const loopBtn = document.getElementById('ctLoopBtn');
    if (loopBtn) {
        loopBtn.innerHTML = ctLoopMode === 'continuous' ? 
            '<i class="fas fa-redo"></i>' : '<i class="fas fa-play"></i>';
        loopBtn.title = ctLoopMode === 'continuous' ? 'Continuous Loop' : 'Play Once';
    }
    
    updateStatus(`CT loop mode: ${ctLoopMode}`);
}

function updateCTPlaybackUI() {
     if (!isCTScan) return;
     
     // Update play/pause button
     const ctPlayBtn = document.getElementById('ctPlayBtn');
     if (ctPlayBtn) {
         ctPlayBtn.innerHTML = isCTPlaying ? 
             '<i class="fas fa-pause"></i>' : '<i class="fas fa-play"></i>';
         ctPlayBtn.title = isCTPlaying ? 'Pause CT Playback' : 'Play CT Playback';
         ctPlayBtn.classList.toggle('active', isCTPlaying);
     }
     
     // Update slice counter
     const sliceCounter = document.getElementById('ctSliceCounter');
     if (sliceCounter) {
         sliceCounter.textContent = `${currentCTSlice + 1}/${ctSlices.length}`;
     }
     
     // Update speed display
     const speedDisplay = document.getElementById('ctSpeedDisplay');
     if (speedDisplay) {
         speedDisplay.textContent = `${ctPlaybackSpeed}ms`;
     }
     
     // Update speed slider
     const speedSlider = document.getElementById('ctSpeedSlider');
     if (speedSlider) {
         speedSlider.value = ctPlaybackSpeed;
     }
 }

// Keyboard shortcuts for CT playback
 document.addEventListener('keydown', function(event) {
     if (!isCTScan || !loaded) return;
     
     // Don't interfere if user is typing in an input field
     if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') return;
     
     switch(event.code) {
         case 'Space':
             event.preventDefault();
             toggleCTPlayback();
             break;
         case 'ArrowLeft':
             event.preventDefault();
             if (currentCTSlice > 0) {
                 currentCTSlice--;
                 loadCTSlice(currentCTSlice);
                 updateCTPlaybackUI();
             }
             break;
         case 'ArrowRight':
             event.preventDefault();
             if (currentCTSlice < ctSlices.length - 1) {
                 currentCTSlice++;
                 loadCTSlice(currentCTSlice);
                 updateCTPlaybackUI();
             }
             break;
         case 'ArrowUp':
             event.preventDefault();
             // Increase speed
             if (ctPlaybackSpeed > 100) {
                 ctPlaybackSpeed -= 100;
                 document.getElementById('ctSpeedSlider').value = ctPlaybackSpeed;
                 updateCTPlaybackUI();
                 if (isCTPlaying) {
                     stopCTPlayback();
                     startCTPlayback();
                 }
             }
             break;
         case 'ArrowDown':
             event.preventDefault();
             // Decrease speed
             if (ctPlaybackSpeed < 2000) {
                 ctPlaybackSpeed += 100;
                 document.getElementById('ctSpeedSlider').value = ctPlaybackSpeed;
                 updateCTPlaybackUI();
                 if (isCTPlaying) {
                     stopCTPlayback();
                     startCTPlayback();
                 }
             }
             break;
         case 'Home':
             event.preventDefault();
             currentCTSlice = 0;
             loadCTSlice(currentCTSlice);
             updateCTPlaybackUI();
             break;
         case 'End':
             event.preventDefault();
             currentCTSlice = ctSlices.length - 1;
             loadCTSlice(currentCTSlice);
             updateCTPlaybackUI();
             break;
         case 'KeyR':
             event.preventDefault();
             toggleCTDirection();
             break;
         case 'KeyL':
             event.preventDefault();
             toggleCTLoop();
             break;
     }
 });

// Function to switch to a specific series (for CT series navigation)
async function switchToSeries(targetSeriesId) {
    try {
        console.log('Switching to series:', targetSeriesId);
        
        // Find the first item of the target series
        const targetSeriesItems = series.filter(item => item.seriesId === targetSeriesId);
        if (targetSeriesItems.length === 0) {
            console.error('Series not found:', targetSeriesId);
            return;
        }
        
        // Load the first image of the target series
        await loadAndViewImage(targetSeriesItems[0].imageId);
        
        // Update current frame to the first frame of this series
        const seriesStartIndex = series.findIndex(item => item.seriesId === targetSeriesId);
        if (seriesStartIndex !== -1) {
            currentFrame = seriesStartIndex;
            updateFrameSlider();
        }
        
        // Highlight the active series thumbnail
        highlightActiveSeries(targetSeriesId);
        
        updateStatus(`Switched to series with ${targetSeriesItems.length} slices`);
        
    } catch (error) {
        console.error('Error switching to series:', error);
        updateStatus('Error switching to series');
    }
}

// Function to highlight the active series thumbnail
function highlightActiveSeries(activeSeriesId) {
    const container = document.getElementById('stackWrapper');
    const thumbnails = container.querySelectorAll('.ct-series-thumbnail');
    
    thumbnails.forEach(thumbnail => {
        if (thumbnail.dataset.seriesId === activeSeriesId) {
            thumbnail.classList.add('active-series');
        } else {
            thumbnail.classList.remove('active-series');
        }
    });
}

// Function to load series directly from URL parameter
async function loadSeriesDirectly(seriesId) {
    try {
        updateStatus('Loading series from URL...');
        console.log('Loading series directly:', seriesId);
        console.log('Current mlite token:', mlite.token);
        
        // Reset series array
        series = [];
        currentFrame = 0;
        totalFrames = 0;
        
        console.log('Reset series array, loading instances...');
        
        // Load series instances
        await loadSeriesInstances(seriesId);
        
        console.log('Series instances loaded, series length:', series.length);
        
        // Load first image if available
        if (series.length > 0) {
            console.log('Loading first image:', series[0].imageId);
            await loadAndViewImage(series[0].imageId);
            updateSeriesList();
            
            // For CT series, highlight the first series as active
            const isCTSeries = series.length > 0 && series[0].modality === 'CT' && series.length > 1;
            if (isCTSeries && series[0].seriesId) {
                highlightActiveSeries(series[0].seriesId);
            }
            
            // Check if this is a CT scan and initialize CT playback
            if (detectCTScan()) {
                console.log('CT scan detected, initializing CT playback');
                initializeCTPlayback();
            }
            
            updateStatus(`Loaded series with ${series.length} instances`);
            console.log('Series loading completed successfully');
        } else {
            console.warn('No instances found in series');
            updateStatus('No instances found in series');
        }
        
    } catch (error) {
        console.error('Error loading series directly:', error);
        updateStatus('Error loading series: ' + error.message);
    }
}