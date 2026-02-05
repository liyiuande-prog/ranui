<?php require APP_PATH . '/Views/admin/header.php'; ?>
<?php require APP_PATH . '/Views/admin/sidebar.php'; ?>

<div class="md:pl-64 flex flex-col min-h-screen h-screen overflow-hidden transition-all duration-300">
    <!-- Header -->
    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 shrink-0 z-20">
        <div class="flex items-center gap-4">
            <button onclick="toggleSidebar()" class="md:hidden text-gray-500 hover:text-black focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <h2 class="font-bold text-gray-800">主题编辑: <span class="text-purple-600"><?= $themeName ?></span></h2>
            <span id="currentFileLabel" class="text-sm text-gray-400 bg-gray-100 px-2 py-1 rounded hidden font-mono"></span>
        </div>
        <div class="flex gap-2">
            <button onclick="saveFile()" class="bg-black text-white px-4 py-2 rounded-lg text-sm font-bold hover:opacity-80 flex items-center gap-2">
                <i class="fas fa-save"></i> 保存文件 (Save)
            </button>
            <a href="<?= url('/admin/themes') ?>" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-200">
                返回
            </a>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar: File Tree -->
        <div class="w-64 bg-gray-50 border-r border-gray-200 overflow-y-auto p-4 shrink-0 select-none">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Files Explorer</div>
            <div class="space-y-1 font-mono text-sm">
                <?php 
                function renderTree($nodes, $depth = 0) {
                    foreach($nodes as $node) {
                        $padding = $depth * 12;
                        if($node['type'] == 'folder') {
                            echo "<div class='py-1 flex items-center gap-2 text-gray-500 font-bold' style='padding-left:{$padding}px'>";
                            echo "<i class='fas fa-folder text-yellow-500'></i> {$node['name']}";
                            echo "</div>";
                            if(!empty($node['children'])) renderTree($node['children'], $depth + 1);
                        } else {
                            echo "<div onclick=\"loadFile('{$node['path']}', this)\" class='file-item cursor-pointer py-1.5 flex items-center gap-2 text-gray-600 hover:text-purple-600 hover:bg-white rounded px-2 transition-colors' style='padding-left:{$padding}px'>";
                            echo "<i class='fas fa-file-code text-gray-400 text-xs'></i> {$node['name']}";
                            echo "</div>";
                        }
                    }
                }
                renderTree($files);
                ?>
            </div>
        </div>

        <!-- Editor Area -->
        <div class="flex-1 flex flex-col bg-[#1e1e1e] relative">
            <div id="loadingOverlay" class="absolute inset-0 flex items-center justify-center bg-black/20 backdrop-blur-sm z-10 hidden">
                <i class="fas fa-spinner fa-spin text-white text-3xl"></i>
            </div>
            <textarea id="codeEditor" class="w-full h-full bg-[#1e1e1e] text-gray-300 font-mono p-4 text-sm leading-6 resize-none outline-none border-none"
                placeholder="Select a file from the sidebar to start editing..." spellcheck="false"></textarea>
        </div>
    </div>
</div>

<script>
let currentFile = '';
const themeName = '<?= $themeName ?>';
let isDirty = false;

function loadFile(path, el) {
    if(isDirty && !confirm("您有未保存的修改，切换文件将丢失这些修改。确定要切换吗？")) return;
    
    // UI Update
    document.querySelectorAll('.file-item').forEach(i => i.classList.remove('bg-purple-100', 'text-purple-700'));
    if(el) el.classList.add('bg-purple-100', 'text-purple-700');
    
    document.getElementById('loadingOverlay').classList.remove('hidden');
    
    // AJAX Get
    fetch(`<?= url('/admin/themes/file') ?>?theme=${themeName}&file=${path}`)
    .then(res => {
        if(!res.ok) throw new Error("Load failed");
        return res.text();
    })
    .then(text => {
        document.getElementById('codeEditor').value = text;
        currentFile = path;
        isDirty = false;
        
        document.getElementById('currentFileLabel').innerText = path;
        document.getElementById('currentFileLabel').classList.remove('hidden');
        document.getElementById('loadingOverlay').classList.add('hidden');
    })
    .catch(err => {
        alert(err.message);
        document.getElementById('loadingOverlay').classList.add('hidden');
    });
}

function saveFile() {
    if(!currentFile) return alert("Select a file first");
    
    const content = document.getElementById('codeEditor').value;
    const formData = new FormData();
    formData.append('theme', themeName);
    formData.append('file', currentFile);
    formData.append('content', content);
    
    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    formData.append('csrf_token', csrfToken);
    
    document.getElementById('loadingOverlay').classList.remove('hidden');
    
    fetch('<?= url('/admin/themes/save') ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('loadingOverlay').classList.add('hidden');
        if(data.success) {
            isDirty = false;
            alert("保存成功!");
        } else {
            alert("Save failed: " + (data.error || 'Unknown error'));
        }
    })
    .catch(err => {
        document.getElementById('loadingOverlay').classList.add('hidden');
        alert("Network Error");
    });
}

// Track changes
document.getElementById('codeEditor').addEventListener('input', function() {
    if(currentFile) isDirty = true;
});

// Allow Tab indentation
document.getElementById('codeEditor').addEventListener('keydown', function(e) {
  if (e.key == 'Tab') {
    e.preventDefault();
    var start = this.selectionStart;
    var end = this.selectionEnd;
    this.value = this.value.substring(0, start) + "\t" + this.value.substring(end);
    this.selectionStart = this.selectionEnd = start + 1;
    isDirty = true;
  }
});
</script>

<?php require APP_PATH . '/Views/admin/footer.php'; ?>
