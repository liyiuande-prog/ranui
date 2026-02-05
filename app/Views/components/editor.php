<?php
// Default args
$name = $name ?? 'content';
$id = $id ?? 'editor';
$content = $content ?? '';
$placeholder = $placeholder ?? 'Start writing...';
$height = $height ?? 'min-h-[500px]';
$uploadUrl = url("/upload");
$enableMentions = $enableMentions ?? false;
?>
<style>
    #<?= $id ?>_editor h1 { display: block; font-size: 2.25em !important; font-weight: 800 !important; margin-top: 1em; margin-bottom: 0.5em; padding-bottom: 0.3em; border-bottom: 1px solid #e5e7eb; color: #111827; }
    #<?= $id ?>_editor h2 { display: block; font-size: 1.75em !important; font-weight: 700 !important; margin-top: 1em; margin-bottom: 0.5em; color: #1f2937; }
    #<?= $id ?>_editor h3 { display: block; font-size: 1.5em !important; font-weight: 600 !important; margin-top: 1em; margin-bottom: 0.5em; color: #374151; }
    #<?= $id ?>_editor p { display: block; margin-bottom: 1em; line-height: 1.7; color: inherit; }
    #<?= $id ?>_editor blockquote { display: block; border-left: 4px solid #e5e7eb; padding-left: 1em; margin: 1em 0; color: #6b7280; font-style: italic; }
    #<?= $id ?>_editor pre { display: block; background-color: #1f2937; color: #f3f4f6; padding: 1em; border-radius: 0.5em; overflow-x: auto; margin-bottom: 1em; font-family: monospace; }
    #<?= $id ?>_editor ul { display: block; list-style-type: disc; padding-left: 1.5em; margin-bottom: 1em; }
    #<?= $id ?>_editor ol { display: block; list-style-type: decimal; padding-left: 1.5em; margin-bottom: 1em; }
    #<?= $id ?>_editor img { max-width: 100%; height: auto; border-radius: 0.5em; margin: 1em 0; }
    #<?= $id ?>_editor .text-left { text-align: left; }
    #<?= $id ?>_editor .text-center { text-align: center; }
    #<?= $id ?>_editor .text-right { text-align: right; }
    #<?= $id ?>_editor a { color: #3b82f6; text-decoration: none; border-bottom: 1px solid #3b82f6; transition: all 0.2s; }
    #<?= $id ?>_editor a:hover { color: #2563eb; border-bottom-width: 2px; }

    /* Dark Mode */
    .dark #<?= $id ?>_editor h1 { border-color: #374151; color: #f3f4f6; }
    .dark #<?= $id ?>_editor h2, .dark #<?= $id ?>_editor h3 { color: #e5e7eb; }
    .dark #<?= $id ?>_editor blockquote { border-color: #4b5563; color: #9ca3af; }
</style>
<div class="bg-white dark:bg-[#1e1e1e] rounded-2xl border border-gray-100 dark:border-white/10 shadow-sm overflow-hidden flex flex-col relative group <?= $height ?>">
    <!-- Toolbar -->
    <div class="bg-gray-50 dark:bg-white/5 border-b border-gray-100 dark:border-white/10 px-4 py-2 flex flex-wrap items-center gap-2 text-gray-600 dark:text-gray-400 select-none">
        
        <!-- Headings -->
        <div class="flex items-center gap-1 border-r border-gray-200 dark:border-white/10 pr-2">
            <button type="button" onmousedown="event.preventDefault()" onclick="editor_format('h1', '<?= $id ?>')" class="p-1.5 hover:bg-gray-200 dark:hover:bg-white/10 rounded text-xs font-bold transition-colors">H1</button>
            <button type="button" onmousedown="event.preventDefault()" onclick="editor_format('h2', '<?= $id ?>')" class="p-1.5 hover:bg-gray-200 dark:hover:bg-white/10 rounded text-xs font-bold transition-colors">H2</button>
            <button type="button" onmousedown="event.preventDefault()" onclick="editor_format('h3', '<?= $id ?>')" class="p-1.5 hover:bg-gray-200 dark:hover:bg-white/10 rounded text-xs font-bold transition-colors">H3</button>
        </div>
        
        <!-- Basic -->
        <div class="flex items-center gap-1 border-r border-gray-200 dark:border-white/10 pr-2">
            <button type="button" onmousedown="event.preventDefault()" onclick="editor_format('bold', '<?= $id ?>')" class="p-1.5 hover:bg-gray-200 dark:hover:bg-white/10 rounded transition-colors" title="Bold"><i class="fas fa-bold"></i></button>
            <button type="button" onmousedown="event.preventDefault()" onclick="editor_format('italic', '<?= $id ?>')" class="p-1.5 hover:bg-gray-200 dark:hover:bg-white/10 rounded transition-colors" title="Italic"><i class="fas fa-italic"></i></button>
            <button type="button" onmousedown="event.preventDefault()" onclick="editor_link('<?= $id ?>')" class="p-1.5 hover:bg-gray-200 dark:hover:bg-white/10 rounded transition-colors" title="Link"><i class="fas fa-link"></i></button>
        </div>

        <!-- Alignment -->
        <div class="flex items-center gap-1 border-r border-gray-200 dark:border-white/10 pr-2">
             <button type="button" onmousedown="event.preventDefault()" onclick="editor_format('left', '<?= $id ?>')" class="p-1.5 hover:bg-gray-200 dark:hover:bg-white/10 rounded transition-colors"><i class="fas fa-align-left"></i></button>
             <button type="button" onmousedown="event.preventDefault()" onclick="editor_format('center', '<?= $id ?>')" class="p-1.5 hover:bg-gray-200 dark:hover:bg-white/10 rounded transition-colors"><i class="fas fa-align-center"></i></button>
             <button type="button" onmousedown="event.preventDefault()" onclick="editor_format('right', '<?= $id ?>')" class="p-1.5 hover:bg-gray-200 dark:hover:bg-white/10 rounded transition-colors"><i class="fas fa-align-right"></i></button>
        </div>

        <!-- Media -->
        <div class="flex items-center gap-1 border-r border-gray-200 dark:border-white/10 pr-2">
            <button type="button" onmousedown="event.preventDefault()" onclick="editor_insertCode('<?= $id ?>')" class="p-1.5 hover:bg-gray-200 dark:hover:bg-white/10 rounded transition-colors" title="Code"><i class="fas fa-code"></i></button>
            <label class="p-1.5 hover:bg-gray-200 dark:hover:bg-white/10 rounded cursor-pointer transition-colors" title="Image">
                <i class="fas fa-image"></i>
                <input type="file" class="hidden" accept="image/*" onchange="editor_upload(this, 'image', '<?= $id ?>')">
            </label>
            <label class="p-1.5 hover:bg-gray-200 dark:hover:bg-white/10 rounded cursor-pointer transition-colors" title="Video">
                <i class="fas fa-video"></i>
                <input type="file" class="hidden" accept="video/*" onchange="editor_upload(this, 'video', '<?= $id ?>')">
            </label>
            <?php if ($enableMentions): ?>
            <button type="button" onclick="editor_toggleAtList(this, '<?= $id ?>')" class="p-1.5 hover:bg-gray-200 dark:hover:bg-white/10 rounded transition-colors" title="@User"><i class="fas fa-at"></i></button>
            <?php endif; ?>
        </div>
        
        <!-- Preview -->

    </div>

    <!-- WYSIWYG Container -->
    <div class="flex-1 relative min-h-[500px] bg-white dark:bg-[#1e1e1e] cursor-text" onclick="document.getElementById('<?= $id ?>_editor').focus()">
        
        <!-- Editable Content -->
        <div id="<?= $id ?>_editor" contenteditable="true" class="w-full h-full p-6 outline-none prose dark:prose-invert max-w-none overflow-y-auto min-h-[500px]" 
             oninput="editor_sync('<?= $id ?>')"></div>
             
        <!-- Hidden input for form submission -->
        <textarea id="<?= $id ?>" name="<?= $name ?>" class="hidden" required><?= $content ?></textarea>
    </div>
</div>

<?php if ($enableMentions): ?>
<!-- Mentions Dropdown -->
<div id="<?= $id ?>_at_dropdown" class="hidden fixed z-50 w-64 bg-white dark:bg-[#1e1e1e] rounded-xl shadow-2xl border border-gray-100 dark:border-white/10 flex flex-col overflow-hidden">
    <div class="p-3 border-b border-gray-100 dark:border-white/5">
        <input type="text" id="<?= $id ?>_at_input" placeholder="Search user..." class="w-full bg-gray-50 dark:bg-black/20 border-0 rounded-lg px-3 py-2 text-sm text-ink-900 dark:text-white focus:ring-1 focus:ring-black dark:focus:ring-white">
    </div>
    <div id="<?= $id ?>_at_list" class="flex-1 overflow-y-auto max-h-60 p-2 space-y-1"></div>
</div>
<?php endif; ?>

<script>
if (typeof window.editor_loaded === 'undefined') {
    window.editor_loaded = true;

    window.editor_link = function(id) {
        const url = prompt('请输入链接地址:', 'https://');
        if (url) {
            document.execCommand('createLink', false, url);
            editor_sync(id);
        }
    };

    window.editor_format = function(cmd, id) {
        document.execCommand('styleWithCSS', false, true);
        
        switch(cmd) {
            case 'h1': document.execCommand('formatBlock', false, 'H1'); break;
            case 'h2': document.execCommand('formatBlock', false, 'H2'); break;
            case 'h3': document.execCommand('formatBlock', false, 'H3'); break;
            case 'left': document.execCommand('justifyLeft', false, null); break;
            case 'center': document.execCommand('justifyCenter', false, null); break;
            case 'right': document.execCommand('justifyRight', false, null); break;
            case 'bold': document.execCommand('bold', false, null); break;
            case 'italic': document.execCommand('italic', false, null); break;
        }
        editor_sync(id);
    };

    window.editor_insertCode = function(id) {
        const pre = document.createElement('pre');
        pre.className = "bg-gray-800 text-white rounded p-4 my-2";
        const code = document.createElement('code');
        code.innerText = "Code block...";
        pre.appendChild(code);
        
        const sel = window.getSelection();
        if (sel.rangeCount) {
            const range = sel.getRangeAt(0);
            range.deleteContents();
            range.insertNode(pre);
            
            // UX: Select the text inside code block
            const newRange = document.createRange();
            newRange.selectNodeContents(code);
            sel.removeAllRanges();
            sel.addRange(newRange);
        }
        editor_sync(id);
    };

    window.editor_upload = function(input, type, id) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const formData = new FormData();
        formData.append('file', file);
        
        fetch('<?= $uploadUrl ?>', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.location) {
                // Custom Handler Hook for Video
                if (type === 'video' && typeof window.onEditorUploadVideo === 'function') {
                    window.onEditorUploadVideo(data.location, file.name);
                    
                    // Auto-set cover image if backend returned a thumbnail
                    const cover = document.querySelector("input[name='cover_image']");
                    if (cover && !cover.value && data.thumbnail) {
                        cover.value = data.thumbnail;
                    }
                    
                    // Clear input
                    input.value = '';
                    return; // Stop execution so we don't insert HTML into editor
                }

                let html = '';
                if(type === 'image') {
                    html = `<img src="${data.location}" class="max-w-full rounded-lg shadow-sm my-2">`;
                } else {
                    html = `<video src="${data.location}" controls class="w-full rounded-lg shadow-sm my-2"></video>`;
                }
                
                document.execCommand('insertHTML', false, html);
                
                // Also update cover image if needed
                const cover = document.querySelector("input[name='cover_image']");
                if (cover && !cover.value && type === 'image') cover.value = data.location;
                
                editor_sync(id);
            } else {
                alert('Upload failed');
            }
        });
        input.value = '';
    };

    window.editor_sync = function(id) {
        const div = document.getElementById(id + '_editor');
        const area = document.getElementById(id);
        if(div && area) {
            area.value = div.innerHTML;
        }
    }
    
    window.SimpleMarkdownParse = function(text) {
        if(!text) return '<p class="text-gray-400 italic">Preview area...</p>';
        
        let md = text
            // Headers
            .replace(/^### (.*$)/gim, '<h3 class="text-xl font-bold my-3 text-ink-900 dark:text-gray-100">$1</h3>')
            .replace(/^## (.*$)/gim, '<h2 class="text-2xl font-bold my-4 text-ink-900 dark:text-gray-100">$1</h2>')
            .replace(/^# (.*$)/gim, '<h1 class="text-3xl font-bold my-5 pb-2 border-b border-gray-100 dark:border-white/10 text-ink-900 dark:text-gray-100">$1</h1>')
            // Bold/Italic
            .replace(/\*\*(.*)\*\*/gim, '<strong>$1</strong>')
            .replace(/\*(.*)\*/gim, '<em>$1</em>')
            // Images
            .replace(/!\[(.*?)\]\((.*?)\)/gim, '<img src="$2" alt="$1" class="max-w-full h-auto rounded-lg my-4 shadow-sm" loading="lazy" />')
            // Links
            .replace(/\[(.*?)\]\((.*?)\)/gim, '<a href="$2" target="_blank" class="text-blue-500 hover:underline">$1</a>')
            // Video (Custom tag we support)
            .replace(/<video src="(.*?)" (.*?)><\/video>/gim, '<video src="$1" $2></video>')
            // Paragraphs (Double newline) - basic
            .replace(/\n\n/gim, '</p><p class="my-3 leading-relaxed">')
            ;
            
        if (md === text && !text.includes('<')) {
            md = md.replace(/\n/g, '<br>');
        }
        
        return typeof DOMPurify !== 'undefined' ? DOMPurify.sanitize(md) : md;
    }
}
</script>

<?php if ($enableMentions): ?>
<script>
(function() {
    let atPage = 1;
    let atLoading = false;
    let atHasMore = true;
    let atQuery = '';
    const editorId = '<?= $id ?>';
    const dropdownId = '<?= $id ?>_at_dropdown';
    
    window.editor_toggleAtList = function(btn, id) {
        if (id !== editorId) return; // Basic safety
        const dropdown = document.getElementById(dropdownId);
        if (dropdown.classList.contains('hidden')) {
            const rect = btn.getBoundingClientRect();
            dropdown.style.top = (rect.bottom + 5) + 'px';
            dropdown.style.left = rect.left + 'px';
            dropdown.classList.remove('hidden');
            document.getElementById(editorId + '_at_input').focus();
            loadAtUsers(true);
        } else {
            dropdown.classList.add('hidden');
        }
    };

    // Close on click outside
    document.addEventListener('click', (e) => {
        const dropdown = document.getElementById(dropdownId);
        if (dropdown && !dropdown.classList.contains('hidden') && !e.target.closest('#' + dropdownId) && !e.target.closest('button[title="@User"]')) {
            dropdown.classList.add('hidden');
        }
    });

    // Inputs
    const input = document.getElementById(editorId + '_at_input');
    const list = document.getElementById(editorId + '_at_list');
    
    if(input) {
        input.addEventListener('input', (e) => {
            atQuery = e.target.value;
            loadAtUsers(true);
        });
    }

    if(list) {
        list.addEventListener('scroll', (e) => {
            if (e.target.scrollTop + e.target.clientHeight >= e.target.scrollHeight - 20) {
                if (!atLoading && atHasMore) loadAtUsers();
            }
        });
    }

    function loadAtUsers(reset = false) {
        if (reset) { atPage = 1; atHasMore = true; if(list) list.innerHTML = ''; }
        if (!atHasMore) return;
        atLoading = true;
        
        fetch(`/api/users/search?q=${atQuery}&page=${atPage}`)
            .then(res => res.json())
            .then(res => {
                atLoading = false;
                if (res.success) {
                    if (res.data.length < 10) atHasMore = false;
                    atPage++;
                    renderAtUsers(res.data);
                }
            });
    }

    function renderAtUsers(users) {
        if (users.length === 0 && list.children.length === 0) { list.innerHTML = '<div class="text-center text-gray-400 text-xs py-4">No user found</div>'; return; }
        users.forEach(u => {
            const div = document.createElement('div');
            div.className = 'flex items-center gap-2 p-2 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg cursor-pointer transition-colors';
            div.onclick = () => {
                insertAt(u.username);
                document.getElementById(dropdownId).classList.add('hidden');
            };
            div.innerHTML = `<img src="${u.avatar || '/assets/default-avatar.png'}" class="w-6 h-6 rounded-full object-cover"><span class="text-sm font-bold text-gray-700 dark:text-gray-200">${u.username}</span>`;
            list.appendChild(div);
        });
    }
    
    function insertAt(username) {
        // For ContentEditable div
        const div = document.getElementById(editorId + '_editor');
        if(div) {
            div.focus();
            const text = `@${username} `;
            document.execCommand('insertText', false, text);
            // Sync
            editor_sync(editorId);
        }
    }
})();
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const id = '<?= $id ?>';
    const div = document.getElementById(id + '_editor');
    const area = document.getElementById(id);
    
    if (div && area && area.value) {
        // Wait for SimpleMarkdownParse to be defined if it's in the same bundle execution
        if (window.SimpleMarkdownParse) {
             div.innerHTML = window.SimpleMarkdownParse(area.value);
        } else {
             // Fallback if somehow not defined yet (race condition? unlikely if script is above)
             div.innerHTML = area.value.replace(/\n/g, '<br>');
        }
    }

    // Fix: Force plain text paste inside code blocks
    if (div) {
        div.addEventListener('paste', (e) => {
            let node = window.getSelection().anchorNode;
            if (!node) return;
            
            // Normalize to element
            if (node.nodeType === 3) node = node.parentNode;
            
            // Check if inside pre or code
            const codeBlock = node.closest('pre');
            if (codeBlock || node.tagName === 'PRE' || node.tagName === 'CODE') {
                e.preventDefault();
                e.stopPropagation();
                
                const text = (e.clipboardData || window.clipboardData).getData('text/plain');
                
                // 手动插入文本节点，保持换行符
                const selection = window.getSelection();
                if (!selection.rangeCount) return;
                
                const range = selection.getRangeAt(0);
                range.deleteContents();
                
                // 创建纯文本节点（保留 \n 换行符）
                const textNode = document.createTextNode(text);
                range.insertNode(textNode);
                
                // 将光标移到插入内容的末尾
                range.setStartAfter(textNode);
                range.collapse(true);
                selection.removeAllRanges();
                selection.addRange(range);
                
                // 同步内容
                editor_sync(id);
            }
        });
    }
});
</script>
